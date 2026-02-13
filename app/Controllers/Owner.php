<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MaterialModel;
use App\Models\UserModel;
use App\Models\ParticipantModel;
use App\Models\PackageModel;

class Owner extends BaseController
{
    public function index()
    {
        $materialModel = new MaterialModel();
        $userModel = new UserModel();
        $participantModel = new \App\Models\ParticipantModel();
        $packageModel = new \App\Models\PackageModel();

        // Get Filter Parameters
        $filters = [
            'start_date' => $this->request->getGet('start_date'),
            'end_date' => $this->request->getGet('end_date'),
            'agency_id' => $this->request->getGet('agency_id'),
            'package_id' => $this->request->getGet('package_id'),
        ];

        // 1. Stats Calculation
        $registrations = $participantModel->getFilteredParticipants($filters) ?? [];

        $stats = [
            'total_jamaah' => count($registrations),
            'total_agencies' => $userModel->where('role', 'agency')->countAllResults(),
            'total_packages' => $packageModel->countAllResults(),
            'verified_jamaah' => 0,
            'pending_jamaah' => 0,
        ];

        foreach ($registrations as $reg) {
            if ($reg['status'] == 'verified')
                $stats['verified_jamaah']++;
            else
                $stats['pending_jamaah']++;
        }

        // 2. Chart Data: Trends (Last 30 Days if no date filter, otherwise filtered)
        $registrationTrends = $participantModel->getRegistrationTrends() ?? [];

        // 3. Agency Distribution
        $agencyPerformance = $participantModel->select('users.full_name, COUNT(participants.id) as total')
            ->join('users', 'users.id = participants.agency_id')
            ->groupBy('participants.agency_id')
            ->orderBy('total', 'DESC')
            ->limit(5)
            ->findAll() ?? [];

        // 4. Package distribution
        $packageDistribution = $participantModel->select('travel_packages.name, COUNT(participants.id) as total')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->groupBy('participants.package_id')
            ->orderBy('total', 'DESC')
            ->findAll() ?? [];

        $data = [
            'filters' => $filters,
            'stats' => $stats,
            'registrations' => $registrations,
            'trends' => $registrationTrends,
            'agencies_list' => $userModel->where('role', 'agency')->findAll() ?? [],
            'packages_list' => $packageModel->findAll() ?? [],
            'agency_perf' => $agencyPerformance,
            'package_dist' => $packageDistribution,
            'materials_count' => $materialModel->countAllResults()
        ];

        return view('owner/dashboard', $data);
    }

    public function materials()
    {
        $materialModel = new MaterialModel();
        $data = [
            'materials' => $materialModel->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('owner/materials/index', $data);
    }

    public function uploadMaterial()
    {
        return view('owner/materials/create');
    }

    public function storeMaterial()
    {
        $type = $this->request->getPost('type');
        $rules = [
            'type' => 'required|in_list[file,youtube,url]',
            'title' => 'required|min_length[3]|max_length[255]',
        ];

        if ($type == 'file') {
            $rules['attachment'] = [
                'uploaded[attachment]',
                'mime_in[attachment,application/pdf,image/jpg,image/jpeg,image/png]',
                'max_size[attachment,4096]', // 4MB
            ];
        }
        else {
            $rules['url'] = 'required|valid_url';
        }

        if (!$this->validate($rules)) {
            return view('owner/upload_material', ['validation' => $this->validator]);
        }

        $data = [
            'type' => $type,
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'created_by' => session()->get('id')
        ];

        if ($type == 'file') {
            $file = $this->request->getFile('attachment');
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads', $newName);
                $data['file_path'] = 'uploads/' . $newName;
            }
            else {
                return redirect()->back()->with('error', $file->getErrorString());
            }
        }
        else {
            $data['url'] = $this->request->getPost('url');
        }

        $materialModel = new MaterialModel();
        $materialModel->save($data);

        return redirect()->to('owner/materials')->with('msg', 'Materi berhasil diupload');
    }

    public function editMaterial($id)
    {
        $materialModel = new MaterialModel();
        $material = $materialModel->find($id);
        if (!$material) {
            return redirect()->to('owner/materials')->with('error', 'Materi tidak ditemukan.');
        }
        return view('owner/materials/edit', ['material' => $material]);
    }

    public function updateMaterial($id)
    {
        $materialModel = new MaterialModel();
        $material = $materialModel->find($id);
        if (!$material) {
            return redirect()->to('owner/materials')->with('error', 'Materi tidak ditemukan.');
        }
        $type = $this->request->getPost('type');
        $rules = [
            'type' => 'required|in_list[file,youtube,url]',
            'title' => 'required|min_length[3]|max_length[255]',
        ];
        if ($type == 'file') {
            $file = $this->request->getFile('attachment');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $rules['attachment'] = ['uploaded[attachment]', 'mime_in[attachment,application/pdf,image/jpg,image/jpeg,image/png]', 'max_size[attachment,4096]'];
            }
        } else {
            $rules['url'] = 'required|valid_url';
        }
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = ['type' => $type, 'title' => $this->request->getPost('title'), 'description' => $this->request->getPost('description')];
        if ($type == 'file') {
            $file = $this->request->getFile('attachment');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                if (!empty($material['file_path']) && is_file(ROOTPATH . 'public/' . $material['file_path'])) {
                    @unlink(ROOTPATH . 'public/' . $material['file_path']);
                }
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads', $newName);
                $data['file_path'] = 'uploads/' . $newName;
                $data['url'] = null;
            } else {
                $data['file_path'] = $material['file_path'];
                $data['url'] = null;
            }
        } else {
            $data['url'] = $this->request->getPost('url');
            if (!empty($material['file_path']) && is_file(ROOTPATH . 'public/' . $material['file_path'])) {
                @unlink(ROOTPATH . 'public/' . $material['file_path']);
            }
            $data['file_path'] = null;
        }
        $materialModel->update($id, $data);
        return redirect()->to('owner/materials')->with('msg', 'Materi berhasil diperbarui.');
    }

    public function deleteMaterial($id)
    {
        $materialModel = new MaterialModel();
        $material = $materialModel->find($id);
        if (!$material) {
            return redirect()->to('owner/materials')->with('error', 'Materi tidak ditemukan.');
        }
        if (!empty($material['file_path']) && is_file(ROOTPATH . 'public/' . $material['file_path'])) {
            @unlink(ROOTPATH . 'public/' . $material['file_path']);
        }
        $materialModel->delete($id);
        return redirect()->to('owner/materials')->with('msg', 'Materi berhasil dihapus.');
    }

    public function paymentVerification()
    {
        $paymentModel = new \App\Models\PaymentModel();
        $participantModel = new ParticipantModel();

        // Tab default: Perlu Verifikasi (pending)
        $tab = $this->request->getGet('tab') ?? 'pending';
        $search = $this->request->getGet('search');
        $participantId = $this->request->getGet('participant_id');
        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $paymentModel->select('participant_payments.*, participants.name as participant_name, participants.nik as participant_nik, participants.upgrade_cost, users.full_name as agency_name, travel_packages.name as package_name, travel_packages.price as package_price')
            ->join('participants', 'participants.id = participant_payments.participant_id')
            ->join('users', 'users.id = participants.agency_id')
            ->join('travel_packages', 'travel_packages.id = participants.package_id');

        // Filter by jamaah (participant) yang dipilih
        if ($participantId !== null && $participantId !== '') {
            $builder->where('participants.id', (int) $participantId);
        }

        // Apply Search Filters
        if ($search) {
            $builder->groupStart()
                ->like('participants.name', $search)
                ->orLike('participants.nik', $search)
                ->orLike('users.full_name', $search)
                ->orLike('travel_packages.name', $search)
                ->groupEnd();
        }

        // Apply Date Filters
        if ($startDate) {
            $builder->where('DATE(participant_payments.payment_date) >=', $startDate);
        }
        if ($endDate) {
            $builder->where('DATE(participant_payments.payment_date) <=', $endDate);
        }

        if ($tab === 'history') {
            $builder->whereIn('participant_payments.status', ['verified', 'rejected']);
            $builder->orderBy('participant_payments.updated_at', 'DESC');
        } else {
            $builder->where('participant_payments.status', 'pending');
            $builder->orderBy('participant_payments.created_at', 'ASC');
        }

        $payments = $builder->findAll();

        // Enrich data with payment progress (total = harga paket + upgrade)
        foreach ($payments as &$p) {
            $paid = $paymentModel->getTotalPaid($p['participant_id']);
            $p['total_paid_verified'] = $paid['amount'] ?? 0;
            $total_target = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['total_target'] = $total_target;
            $p['remaining_balance'] = $total_target - (float)$p['total_paid_verified'];

            if ($total_target > 0) {
                $p['progress_percentage'] = min(100, round(($p['total_paid_verified'] / $total_target) * 100));
            } else {
                $p['progress_percentage'] = 0;
            }
        }

        // Daftar jamaah untuk dropdown filter (select by nama jamaah)
        $participantsList = $participantModel->select('id, name')->orderBy('name', 'ASC')->findAll();

        $data = [
            'payments' => $payments,
            'active_tab' => $tab,
            'participants_list' => $participantsList,
            'filters' => [
                'search' => $search,
                'participant_id' => $participantId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ];

        return view('owner/payment_verification', $data);
    }

    public function verifyPayment()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status');
        $notes = $this->request->getPost('notes');

        $paymentModel = new \App\Models\PaymentModel();
        
        // Use Transaction to handle double verification prevention or related side effects if needed in future
        $paymentModel->update($id, [
            'status' => $status,
            'notes' => $notes
        ]);

        $msg = ($status == 'verified') ? 'Pembayaran berhasil diverifikasi.' : 'Pembayaran telah ditolak.';
        return redirect()->to('owner/payment-verification')->with('msg', $msg);
    }

    public function verifyParticipant()
    {
        $id = $this->request->getPost('id');
        $status = $this->request->getPost('status'); // 'verified' or 'pending'

        $participantModel = new \App\Models\ParticipantModel();
        $participantModel->update($id, [
            'is_verified' => ($status === 'verified'),
            'verified_at' => ($status === 'verified') ? date('Y-m-d H:i:s') : null,
            'verified_by' => ($status === 'verified') ? session()->get('id') : null,
            'status' => ($status === 'verified') ? 'verified' : 'pending' // Sync status
        ]);

        return redirect()->back()->with('msg', 'Status verifikasi jamaah diperbarui.');
    }

    public function checklist($id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $equipmentModel = new \App\Models\EquipmentModel();
        $db = \Config\Database::connect();

        $participant = $participantModel->select('participants.*, travel_packages.name as package_name, travel_packages.freebies, travel_packages.price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', $id)
            ->first();

        if (!$participant) {
            return redirect()->to('owner')->with('error', 'Jamaah tidak ditemukan.');
        }

        // Get documents
        $documents = $db->table('participant_documents')
            ->where('participant_id', $id)
            ->get()
            ->getResultArray();

        $docsFormatted = [];
        foreach ($documents as $doc) {
            $docsFormatted[$doc['type']] = $doc['file_path'];
        }

        $total_goal = 7;
        $verified_count = $db->table('participant_documents')
            ->where('participant_id', $id)
            ->where('is_verified', 1)
            ->countAllResults();
        $doc_progress = $total_goal > 0 ? min(100, round(($verified_count / $total_goal) * 100)) : 0;

        // Get equipment status
        $collectedEquipment = $equipmentModel->getByParticipant($id);
        $collectedMap = [];
        foreach ($collectedEquipment as $eq) {
            $collectedMap[$eq['item_name']] = $eq;
        }

        // Prepare freebies list from package
        $freebies = json_decode($participant['freebies'], true) ?? [];

        return view('agency/checklist', [
            'title' => 'Verifikasi Jamaah - ' . $participant['name'],
            'participant' => $participant,
            'documents' => $docsFormatted,
            'freebies' => $freebies,
            'collectedMap' => $collectedMap,
            'doc_progress' => $doc_progress,
            'verified_count' => $verified_count,
            'total_goal' => $total_goal,
            'is_owner' => true
        ]);
    }    public function settings()
    {
        $userModel = new \App\Models\UserModel();
        $data = [
            'user' => $userModel->find(session()->get('id'))
        ];
        return view('owner/settings', $data);
    }

    public function updateSettings()
    {
        $userModel = new \App\Models\UserModel();
        $id = session()->get('id');

        $rules = [
            'username' => "required|min_length[3]|is_unique[users.username,id,{$id}]",
            'full_name' => 'required|min_length[3]',
            'phone' => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Cek kembali isian Anda.');
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'full_name' => $this->request->getPost('full_name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone'),
            'address' => $this->request->getPost('address'),
            'company_name' => $this->request->getPost('company_name'),
            'slogan' => $this->request->getPost('slogan'),
            'no_sk_perijinan' => $this->request->getPost('no_sk_perijinan'),
            'tanggal_sk_perijinan' => $this->request->getPost('tanggal_sk_perijinan') ?: null,
        ];

        // Optional password update
        $password = $this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter');
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Handle Profile Picture
        $img = $this->request->getFile('profile_pic');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move('uploads/profiles', $newName);
            $data['profile_pic'] = 'uploads/profiles/' . $newName;
        }

        // Handle Company Logo
        $logo = $this->request->getFile('company_logo');
        if ($logo && $logo->isValid() && !$logo->hasMoved()) {
            $newName = $logo->getRandomName();
            $logo->move('uploads/logos', $newName);
            $data['company_logo'] = 'uploads/logos/' . $newName;
        }

        if ($userModel->update($id, $data)) {
            return redirect()->to('owner/settings')->with('msg', 'Pengaturan berhasil diperbarui');
        }

        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui pengaturan');
    }

    /**
     * Kelola testimoni jamaah: daftar semua, filter status, verifikasi agar dipublikasikan.
     */
    public function testimoni()
    {
        $testimonialModel = new \App\Models\TestimonialModel();
        $status = $this->request->getGet('status');
        $filters = [];
        if ($status && in_array($status, ['pending', 'verified'], true)) {
            $filters['status'] = $status;
        }
        $data = [
            'testimonials' => $testimonialModel->getListForAdmin($filters),
            'filter_status' => $status,
        ];
        return view('owner/testimoni/index', $data);
    }

    /**
     * Verifikasi testimoni agar tampil di halaman depan.
     */
    public function verifyTestimoni($id)
    {
        $testimonialModel = new \App\Models\TestimonialModel();
        $row = $testimonialModel->find($id);
        if (!$row) {
            return redirect()->to('owner/testimoni')->with('error', 'Testimoni tidak ditemukan.');
        }
        $testimonialModel->update($id, [
            'status' => 'verified',
            'verified_at' => date('Y-m-d H:i:s'),
            'verified_by' => session()->get('id'),
        ]);
        return redirect()->to('owner/testimoni')->with('msg', 'Testimoni telah diverifikasi dan akan tampil di halaman depan.');
    }

    /**
     * Form edit testimoni.
     */
    public function editTestimoni($id)
    {
        $testimonialModel = new \App\Models\TestimonialModel();
        $packageModel = new \App\Models\PackageModel();
        $row = $testimonialModel->find($id);
        if (!$row) {
            return redirect()->to('owner/testimoni')->with('error', 'Testimoni tidak ditemukan.');
        }
        $packages = $packageModel->orderBy('name', 'ASC')->findAll();
        $data = [
            'testimonial' => $row,
            'packages' => $packages,
        ];
        return view('owner/testimoni/edit', $data);
    }

    /**
     * Update testimoni (nama, paket, isi, rating, status).
     */
    public function updateTestimoni($id)
    {
        $testimonialModel = new \App\Models\TestimonialModel();
        $row = $testimonialModel->find($id);
        if (!$row) {
            return redirect()->to('owner/testimoni')->with('error', 'Testimoni tidak ditemukan.');
        }
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'testimonial' => 'required|min_length[10]',
            'rating' => 'required|in_list[1,2,3,4,5]',
            'status' => 'required|in_list[pending,verified]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Lengkapi nama, testimoni (min. 10 karakter), rating, dan status.');
        }
        $package_id = $this->request->getPost('package_id');
        $newStatus = $this->request->getPost('status');
        $updateData = [
            'name' => $this->request->getPost('name'),
            'package_id' => $package_id ? (int) $package_id : null,
            'testimonial' => $this->request->getPost('testimonial'),
            'rating' => (int) $this->request->getPost('rating'),
            'status' => $newStatus,
        ];
        if ($newStatus === 'verified') {
            $updateData['verified_at'] = $row['verified_at'] ?? date('Y-m-d H:i:s');
            $updateData['verified_by'] = $row['verified_by'] ?? session()->get('id');
        } else {
            $updateData['verified_at'] = null;
            $updateData['verified_by'] = null;
        }
        $testimonialModel->update($id, $updateData);
        return redirect()->to('owner/testimoni')->with('msg', 'Testimoni berhasil diperbarui.');
    }

    /**
     * Hapus testimoni.
     */
    public function deleteTestimoni($id)
    {
        $testimonialModel = new \App\Models\TestimonialModel();
        $row = $testimonialModel->find($id);
        if (!$row) {
            return redirect()->to('owner/testimoni')->with('error', 'Testimoni tidak ditemukan.');
        }
        $testimonialModel->delete($id);
        return redirect()->to('owner/testimoni')->with('msg', 'Testimoni telah dihapus.');
    }

    /**
     * Kelola banner (upload foto) untuk slider halaman login.
     */
    public function banners()
    {
        $bannerModel = new \App\Models\BannerModel();
        $data = [
            'banners' => $bannerModel->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')->findAll(),
        ];
        return view('owner/banners/index', $data);
    }

    /**
     * Simpan banner baru (upload gambar).
     */
    public function storeBanner()
    {
        if (!$this->validate([
            'image' => 'uploaded[image]|max_size[image,5120]|is_image[image]',
        ])) {
            return redirect()->to('owner/banners')->with('error', 'Pilih file gambar (JPG/PNG, maks. 5MB).');
        }
        $file = $this->request->getFile('image');
        if (!$file->isValid() || $file->hasMoved()) {
            return redirect()->to('owner/banners')->with('error', 'Gagal mengunggah file.');
        }
        $dir = FCPATH . 'uploads/banners';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $newName = $file->getRandomName();
        $file->move($dir, $newName);
        $path = 'uploads/banners/' . $newName;
        if (!is_file(FCPATH . $path)) {
            return redirect()->to('owner/banners')->with('error', 'File gagal disimpan.');
        }

        $bannerModel = new \App\Models\BannerModel();
        $maxOrder = $bannerModel->selectMax('sort_order')->get()->getRowArray();
        $sortOrder = ((int)($maxOrder['sort_order'] ?? 0)) + 1;
        $bannerModel->insert([
            'image' => $path,
            'sort_order' => $sortOrder,
        ]);
        return redirect()->to('owner/banners')->with('msg', 'Banner berhasil diunggah.');
    }

    /**
     * Hapus banner.
     */
    public function deleteBanner($id)
    {
        $bannerModel = new \App\Models\BannerModel();
        $row = $bannerModel->find($id);
        if (!$row) {
            return redirect()->to('owner/banners')->with('error', 'Banner tidak ditemukan.');
        }
        if (!empty($row['image']) && is_file(FCPATH . $row['image'])) {
            @unlink(FCPATH . $row['image']);
        }
        $bannerModel->delete($id);
        return redirect()->to('owner/banners')->with('msg', 'Banner berhasil dihapus.');
    }

    /**
     * Ambil notifikasi untuk navbar: jamaah pending, pembayaran pending, setoran pending.
     */
    public function getNotifications()
    {
        if (session()->get('role') != 'owner') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        // Gunakan DB query langsung untuk memastikan tidak ada cache
        $db = \Config\Database::connect();
        
        // Hitung jamaah pending (status != verified dan != cancelled)
        $pendingParticipants = $db->table('participants')
            ->where('status !=', 'verified')
            ->where('status !=', 'cancelled')
            ->countAllResults();
        
        // Hitung pembayaran pending
        $pendingPayments = $db->table('participant_payments')
            ->where('status', 'pending')
            ->countAllResults();
        
        // Hitung setoran tabungan pending
        $pendingDeposits = $db->table('travel_savings_deposits')
            ->where('status', 'pending')
            ->countAllResults();

        $total = (int)$pendingParticipants + (int)$pendingPayments + (int)$pendingDeposits;

        $response = [
            'status' => 'success',
            'notifications' => [
                'participants' => [
                    'count' => (int)$pendingParticipants,
                    'url' => base_url('owner/participant?status=pending'),
                    'label' => 'Jamaah Menunggu Verifikasi'
                ],
                'payments' => [
                    'count' => (int)$pendingPayments,
                    'url' => base_url('owner/payment-verification'),
                    'label' => 'Pembayaran Menunggu Verifikasi'
                ],
                'deposits' => [
                    'count' => (int)$pendingDeposits,
                    'url' => base_url('owner/tabungan?tab=verifikasi'),
                    'label' => 'Setoran Tabungan Menunggu Verifikasi'
                ],
                'total' => $total
            ]
        ];

        // Set header untuk memastikan response JSON
        $this->response->setContentType('application/json');
        return $this->response->setJSON($response);
    }
}

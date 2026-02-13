<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MaterialModel;
use App\Models\MemberModel;

class Agency extends BaseController
{
    public function index()
    {
        $materialModel = new MaterialModel();
        $participantModel = new \App\Models\ParticipantModel();
        $agencyId = session()->get('id');

        // Stats
        $totalJamaah = $participantModel->where('agency_id', $agencyId)->countAllResults();
        $verifiedJamaah = $participantModel->where('agency_id', $agencyId)->where('status', 'verified')->countAllResults();

        // Total penghasilan = komisi yang sudah diverifikasi (dibayar) oleh admin
        $commissionModel = new \App\Models\AgencyCommissionModel();
        $totalIncome = $commissionModel->getTotalPaidCommissionForAgency($agencyId);

        // Verifikasi setoran tabungan: sudah diverifikasi vs belum (pending)
        $depositModel = new \App\Models\TravelSavingDepositModel();
        $depositCounts = $depositModel->getCountsByAgency($agencyId);

        // Recent Verified Jamaah
        $recentParticipants = $participantModel->select('participants.*, travel_packages.name as package_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.agency_id', $agencyId)
            ->where('participants.status', 'verified')
            ->orderBy('participants.updated_at', 'DESC')
            ->limit(5)
            ->findAll();

        $data = [
            'stats' => [
                'total_jamaah' => $totalJamaah,
                'verified_jamaah' => $verifiedJamaah,
                'total_income' => $totalIncome,
                'setoran_verified' => $depositCounts['verified'],
                'setoran_pending' => $depositCounts['pending'],
            ],
            'recent_participants' => $recentParticipants
        ];
        return view('agency/dashboard', $data);
    }

    public function materials()
    {
        $materialModel = new MaterialModel();
        $data = [
            'materials' => $materialModel->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('agency/materials', $data);
    }

    public function income()
    {
        $agencyId = session()->get('id');
        $commissionModel = new \App\Models\AgencyCommissionModel();
        $packageModel = new \App\Models\PackageModel();

        $filters = [
            'start_date' => $this->request->getGet('start_date'),
            'end_date'   => $this->request->getGet('end_date'),
            'package_id' => $this->request->getGet('package_id'),
        ];

        $commissions = $commissionModel->getCommissionsForAgency($agencyId, $filters);
        $total_income = $commissionModel->getTotalPaidCommissionForAgency($agencyId, $filters);
        $total_pending = $commissionModel->getTotalPendingCommissionForAgency($agencyId, $filters);

        $packages = $packageModel->select('id, name')->orderBy('name')->findAll();

        $data = [
            'commissions'   => $commissions,
            'total_income'  => $total_income,
            'total_pending' => $total_pending,
            'packages'      => $packages,
            'filters'       => $filters,
        ];

        return view('agency/income', $data);
    }

    /**
     * Halaman ubah profil agency: nama lengkap, foto, telepon, alamat, password.
     */
    public function profile()
    {
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find(session()->get('id'));
        if (!$user) {
            return redirect()->to('agency')->with('error', 'Sesi tidak valid.');
        }
        return view('agency/profile', ['user' => $user]);
    }

    /**
     * Proses update profil agency.
     */
    public function updateProfile()
    {
        $userModel = new \App\Models\UserModel();
        $id = session()->get('id');

        $rules = [
            'full_name' => 'required|min_length[2]',
            'phone'     => 'required|min_length[8]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Nama lengkap dan nomor telepon wajib diisi dengan benar.');
        }

        $data = [
            'full_name' => $this->request->getPost('full_name'),
            'email'     => $this->request->getPost('email'),
            'phone'     => $this->request->getPost('phone'),
            'address'   => $this->request->getPost('address'),
        ];

        $password = $this->request->getPost('password');
        if (!empty($password)) {
            if (strlen($password) < 6) {
                return redirect()->back()->withInput()->with('error', 'Password minimal 6 karakter.');
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $img = $this->request->getFile('profile_pic');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $img->move('uploads/profiles', $newName);
            $data['profile_pic'] = 'uploads/profiles/' . $newName;
        }

        if ($userModel->update($id, $data)) {
            return redirect()->to('agency/profile')->with('msg', 'Profil berhasil diperbarui.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil.');
    }

    /**
     * Menu input testimoni jamaah (form + daftar kiriman agency).
     */
    public function testimoni()
    {
        $packageModel = new \App\Models\PackageModel();
        $testimonialModel = new \App\Models\TestimonialModel();
        $data = [
            'packages' => $packageModel->select('id, name')->orderBy('name')->findAll(),
            'testimonials' => $testimonialModel->getListForAgency(session()->get('id')),
        ];
        return view('agency/testimoni', $data);
    }

    /**
     * Submit testimoni dari dashboard agency (tanpa captcha).
     */
    public function submitTestimoni()
    {
        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'package_id' => 'permit_empty|integer',
            'testimonial' => 'required|min_length[10]',
            'rating' => 'required|in_list[1,2,3,4,5]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to('agency/testimoni')->withInput()->with('error', 'Lengkapi nama, rating, dan testimoni dengan benar.');
        }
        $testimonialModel = new \App\Models\TestimonialModel();
        $testimonialModel->insert([
            'name' => $this->request->getPost('name'),
            'package_id' => $this->request->getPost('package_id') ?: null,
            'testimonial' => $this->request->getPost('testimonial'),
            'rating' => (int) $this->request->getPost('rating'),
            'status' => 'pending',
            'source' => 'agency',
            'agency_id' => session()->get('id'),
        ]);
        return redirect()->to('agency/testimoni')->with('msg', 'Testimoni berhasil dikirim. Akan dipublikasikan setelah diverifikasi admin.');
    }

    /**
     * Tabungan Jamaah: daftar tabungan milik agensi ini.
     */
    public function tabunganIndex()
    {
        $savingModel = new \App\Models\TravelSavingModel();
        $agencyId = session()->get('id');
        $status = $this->request->getGet('status');
        $cari = trim((string) $this->request->getGet('cari'));
        $builder = $savingModel->getByAgency($agencyId);
        if ($status !== null && $status !== '') {
            $builder->where('status', $status);
        }
        if ($cari !== '') {
            $builder->groupStart()
                ->like('nik', $cari)
                ->orLike('name', $cari)
                ->groupEnd();
        }
        $savings = $builder->findAll();
        $data = ['savings' => $savings, 'filterStatus' => $status, 'filterCari' => $cari];
        return view('agency/tabungan/index', $data);
    }

    /**
     * Form tambah jamaah tabungan (agency_id = session).
     */
    public function tabunganCreate()
    {
        return view('agency/tabungan/create');
    }

    /**
     * Simpan jamaah tabungan baru.
     */
    public function tabunganStore()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'nik' => 'required|min_length[16]|max_length[20]',
            'phone' => 'permit_empty',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $savingModel = new \App\Models\TravelSavingModel();
        $data = [
            'agency_id' => session()->get('id'),
            'name' => $this->request->getPost('name'),
            'nik' => $this->request->getPost('nik'),
            'phone' => $this->request->getPost('phone') ?: null,
            'total_balance' => 0,
            'status' => 'menabung',
            'notes' => $this->request->getPost('notes') ?: null,
        ];
        if ($savingModel->insert($data)) {
            return redirect()->to('agency/tabungan')->with('msg', 'Jamaah tabungan berhasil didaftarkan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
    }

    /**
     * Form tambah setoran (transfer). Setoran dari agency status = pending sampai diverifikasi admin.
     */
    public function tabunganDeposit($id)
    {
        $savingModel = new \App\Models\TravelSavingModel();
        $depositModel = new \App\Models\TravelSavingDepositModel();
        $saving = $savingModel->find($id);
        $agencyId = session()->get('id');
        if (!$saving || (int) $saving['agency_id'] !== (int) $agencyId || $saving['status'] !== 'menabung') {
            return redirect()->to('agency/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $deposits = $depositModel->getBySaving($id);
        $data = ['saving' => $saving, 'deposits' => $deposits];
        return view('agency/tabungan/deposit', $data);
    }

    /**
     * Simpan setoran dari agency: status pending, wajib upload bukti transfer.
     */
    public function tabunganStoreDeposit()
    {
        $savingModel = new \App\Models\TravelSavingModel();
        $depositModel = new \App\Models\TravelSavingDepositModel();
        $travelSavingId = (int) $this->request->getPost('travel_saving_id');
        $saving = $savingModel->find($travelSavingId);
        $agencyId = session()->get('id');
        if (!$saving || (int) $saving['agency_id'] !== (int) $agencyId || $saving['status'] !== 'menabung') {
            return redirect()->to('agency/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
            'payment_date' => 'required|valid_date',
        ];
        if ($this->request->getFile('proof')->isValid()) {
            $rules['proof'] = 'uploaded[proof]|max_size[proof,5120]|mime_in[proof,image/jpeg,image/png,image/webp,application/pdf]';
        }
        if (!$this->validate($rules)) {
            return redirect()->to("agency/tabungan/deposit/{$travelSavingId}")->withInput()->with('errors', $this->validator->getErrors());
        }
        // Parse amount: format-rupiah mengirim angka tanpa separator (dari data-value atau value yang sudah di-strip)
        $amountStr = $this->request->getPost('amount');
        // Hapus semua karakter non-digit kecuali titik desimal, lalu hapus titik (ribuan separator)
        $amount = (float) preg_replace('/[^\d]/', '', str_replace('.', '', $amountStr));
        
        if ($amount <= 0) {
            return redirect()->to("agency/tabungan/deposit/{$travelSavingId}")->withInput()->with('errors', ['amount' => 'Jumlah harus lebih dari 0']);
        }
        
        $file = $this->request->getFile('proof');
        $proof = null;
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = FCPATH . 'uploads/tabungan';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $proof = 'uploads/tabungan/' . $file->getRandomName();
            $file->move($dir, basename($proof));
        }
        
        $insertData = [
            'travel_saving_id' => $travelSavingId,
            'amount' => $amount,
            'payment_date' => $this->request->getPost('payment_date'),
            'proof' => $proof,
            'status' => 'pending',
            'notes' => $this->request->getPost('notes') ?: null,
        ];
        
        if (!$depositModel->insert($insertData)) {
            return redirect()->to("agency/tabungan/deposit/{$travelSavingId}")->withInput()->with('error', 'Gagal menyimpan setoran.');
        }
        return redirect()->to("agency/tabungan/deposit/{$travelSavingId}")->with('msg', 'Setoran berhasil dikirim. Menunggu verifikasi admin untuk masuk ke saldo.');
    }

    public function team()
    {
        $memberModel = new MemberModel();
        $data = [
            'members' => $memberModel->where('agency_id', session()->get('id'))->orderBy('created_at', 'DESC')->findAll()
        ];
        return view('agency/team', $data);
    }

    public function addMember()
    {
        return view('agency/add_member');
    }

    public function storeMember()
    {
        $rules = [
            'name' => 'required|min_length[3]',
            'email' => 'required|valid_email',
            'phone' => 'required|min_length[8]'
        ];

        if (!$this->validate($rules)) {
            return view('agency/add_member', ['validation' => $this->validator]);
        }

        $memberModel = new MemberModel();
        $memberModel->save([
            'agency_id' => session()->get('id'),
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'phone' => $this->request->getPost('phone')
        ]);

        return redirect()->to('agency/team')->with('msg', 'Anggota berhasil ditambahkan');
    }

    /** Label Hotel [Kota] dari master hotel (city di travel_hotels), urut ikut master kota. */
    private function getPackageCityLabelsForAgency()
    {
        $cityModel = new \App\Models\CityModel();
        $hotelModel = new \App\Models\HotelModel();
        $kotaOrdered = $cityModel->getOrdered();
        $hotels = $hotelModel->select('city')->distinct()->findAll();
        $citySet = array_column($hotels, 'city');
        $out = [];
        foreach ($kotaOrdered as $k) {
            $name = $k['name'];
            if (in_array($name, $citySet, true)) {
                $out[] = $name;
                if (count($out) >= 2) {
                    break;
                }
            }
        }
        $c1 = $out[0] ?? ($kotaOrdered[0]['name'] ?? 'Kota 1');
        $c2 = $out[1] ?? ($kotaOrdered[1]['name'] ?? 'Kota 2');
        return ['city1_name' => $c1, 'city2_name' => $c2];
    }

    /** Isi package dengan data tampilan hotel dari master (relasi display). */
    private function enrichPackageWithHotelMaster(array &$package)
    {
        $hotelModel = new \App\Models\HotelModel();
        $package['display_hotel_1'] = null;
        $package['display_hotel_2'] = null;
        if (!empty($package['hotel_mekkah_id'])) {
            $h = $hotelModel->find((int) $package['hotel_mekkah_id']);
            if ($h) {
                $package['display_hotel_1'] = [
                    'name' => $h['name'],
                    'city' => $h['city'] ?? '',
                    'stars' => (int) ($h['star_rating'] ?? 0),
                ];
            }
        }
        if ($package['display_hotel_1'] === null && !empty($package['hotel_mekkah'])) {
            $package['display_hotel_1'] = [
                'name' => $package['hotel_mekkah'],
                'city' => '',
                'stars' => (int) ($package['hotel_mekkah_stars'] ?? 0),
            ];
        }
        if (!empty($package['hotel_madinah_id'])) {
            $h = $hotelModel->find((int) $package['hotel_madinah_id']);
            if ($h) {
                $package['display_hotel_2'] = [
                    'name' => $h['name'],
                    'city' => $h['city'] ?? '',
                    'stars' => (int) ($h['star_rating'] ?? 0),
                ];
            }
        }
        if ($package['display_hotel_2'] === null && !empty($package['hotel_madinah'])) {
            $package['display_hotel_2'] = [
                'name' => $package['hotel_madinah'],
                'city' => '',
                'stars' => (int) ($package['hotel_madinah_stars'] ?? 0),
            ];
        }
    }

    public function packages()
    {
        $packageModel = new \App\Models\PackageModel();
        $labels = $this->getPackageCityLabelsForAgency();
        $packages = $packageModel->findAll();
        foreach ($packages as &$p) {
            $this->enrichPackageWithHotelMaster($p);
        }
        $data = [
            'packages' => $packages,
            'city1_name' => $labels['city1_name'],
            'city2_name' => $labels['city2_name'],
        ];
        return view('agency/packages', $data);
    }

    public function packageDetail($id)
    {
        $packageModel = new \App\Models\PackageModel();
        $package = $packageModel->find($id);

        if (!$package) {
            return redirect()->to('agency/packages')->with('error', 'Paket tidak ditemukan');
        }

        $this->enrichPackageWithHotelMaster($package);
        $labels = $this->getPackageCityLabelsForAgency();
        $data = [
            'package' => $package,
            'city1_name' => $labels['city1_name'],
            'city2_name' => $labels['city2_name'],
        ];
        return view('agency/package_detail', $data);
    }

    public function register($package_id)
    {
        $packageModel = new \App\Models\PackageModel();
        $package = $packageModel->find($package_id);

        if (!$package) {
            return redirect()->to('agency/packages')->with('error', 'Paket tidak ditemukan');
        }

        $data = [
            'package' => $package
        ];
        return view('agency/register', $data);
    }

    public function storeRegistration()
    {
        $rules = [
            'package_id' => 'required',
            'nik' => 'required|min_length[16]|max_length[20]',
            'name' => 'required|min_length[3]',
            'place_of_birth' => 'required',
            'date_of_birth' => 'required|valid_date',
            'gender' => 'required',
            'phone' => 'required|min_length[8]',
            'emergency_name' => 'required|min_length[2]',
            'emergency_relationship' => 'required|min_length[2]',
            'emergency_phone' => 'required|min_length[8]',
            'address' => 'required',
            'kecamatan' => 'required',
            'kabupaten' => 'required',
            'provinsi' => 'required',
        ];
        // Data paspor bersifat opsional; tidak ada validasi wajib untuk paspor.

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi seluruh biodata wajib sesuai KTP.');
        }

        $passportNumber = trim($this->request->getPost('passport_number') ?? '');
        $passportFullName = trim($this->request->getPost('passport_full_name') ?? '');
        $hasPassport = $passportNumber !== '' || $passportFullName !== '';

        $db = \Config\Database::connect();
        $db->transStart();

        $participantModel = new \App\Models\ParticipantModel();
        $participantData = [
            'package_id' => $this->request->getPost('package_id'),
            'agency_id' => session()->get('id'),
            'nik' => $this->request->getPost('nik'),
            'name' => $this->request->getPost('name'),
            'place_of_birth' => $this->request->getPost('place_of_birth'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'address' => $this->request->getPost('address'),
            'rt_rw' => $this->request->getPost('rt_rw'),
            'kelurahan' => $this->request->getPost('kelurahan'),
            'kecamatan' => $this->request->getPost('kecamatan'),
            'kabupaten' => $this->request->getPost('kabupaten'),
            'provinsi' => $this->request->getPost('provinsi'),
            'religion' => $this->request->getPost('religion'),
            'marital_status' => $this->request->getPost('marital_status'),
            'occupation' => $this->request->getPost('occupation'),
            'blood_type' => $this->request->getPost('blood_type'),
            'nationality' => $hasPassport ? ($this->request->getPost('passport_nationality') ?: 'Indonesian') : ($this->request->getPost('nationality') ?? 'WNI'),
            'phone' => $this->request->getPost('phone'),
            'emergency_name' => $this->request->getPost('emergency_name'),
            'emergency_relationship' => $this->request->getPost('emergency_relationship'),
            'emergency_phone' => $this->request->getPost('emergency_phone'),
            'status' => 'pending',
            'has_passport' => $hasPassport ? 1 : 0,
        ];
        $participantData['passport_number'] = $hasPassport ? ($this->request->getPost('passport_number') ?? null) : null;
        $participantData['passport_type'] = $hasPassport ? ($this->request->getPost('passport_type') ?: null) : null;
        $participantData['passport_full_name'] = $hasPassport ? ($this->request->getPost('passport_full_name') ?? null) : null;
        $participantData['passport_place_of_birth'] = $hasPassport ? ($this->request->getPost('passport_place_of_birth') ?: null) : null;
        $participantData['passport_issuance_date'] = $hasPassport ? ($this->request->getPost('passport_issuance_date') ?: null) : null;
        $participantData['passport_expiry_date'] = $hasPassport ? ($this->request->getPost('passport_expiry_date') ?: null) : null;
        $participantData['passport_issuance_city'] = $hasPassport ? ($this->request->getPost('passport_issuance_city') ?: null) : null;
        $participantData['passport_reg_number'] = $hasPassport ? ($this->request->getPost('passport_reg_number') ?: null) : null;
        $participantData['passport_issuing_office'] = $hasPassport ? ($this->request->getPost('passport_issuing_office') ?: null) : null;
        $participantData['passport_name_idn'] = $hasPassport ? ($this->request->getPost('passport_name_idn') ?: null) : null;

        $participantId = $participantModel->insert($participantData);

        // Handle Document Uploads
        $docTypes = ['passport', 'id_card', 'vaccine'];
        foreach ($docTypes as $type) {
            $file = $this->request->getFile($type);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/documents', $newName);
                $db->table('participant_documents')->insert([
                    'participant_id' => $participantId,
                    'type' => $type,
                    'file_path' => 'uploads/documents/' . $newName,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pendaftaran.');
        }

        return redirect()->to('agency/packages')->with('msg', 'Pendaftaran jamaah ' . $participantData['name'] . ' berhasil! Mohon tunggu verifikasi berkas.');
    }

    public function participants()
    {
        $participantModel = new \App\Models\ParticipantModel();
        $paymentModel = new \App\Models\PaymentModel();

        $keyword = $this->request->getGet('search');
        $builder = $participantModel->select('participants.*, travel_packages.name as package_name, travel_packages.freebies, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.agency_id', session()->get('id'));

        if ($keyword) {
            $builder->groupStart()
                ->like('participants.name', $keyword)
                ->orLike('participants.nik', $keyword)
            ->groupEnd();
        }

        $participants = $builder->orderBy('participants.created_at', 'DESC')->findAll();

        $db = \Config\Database::connect();
        $equipmentModel = new \App\Models\EquipmentModel();

        foreach ($participants as &$p) {
            $p['doc_count'] = $db->table('participant_documents')->where('participant_id', $p['id'])->countAllResults();
            $p['doc_total'] = 3;
            $freebies = json_decode($p['freebies'], true) ?? [];
            $p['equip_total'] = count($freebies);
            $p['equip_count'] = $equipmentModel->where('participant_id', $p['id'])->where('status', 'collected')->countAllResults();

            // Data pembayaran: harga paket + upgrade (hotel/kamar/bed)
            $p['total_paid'] = (float)($paymentModel->getTotalPaid($p['id'])['amount'] ?? 0);
            $p['total_target'] = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['pembayaran_lunas'] = $p['total_target'] > 0 && $p['total_paid'] >= $p['total_target'];
            $p['progress_pembayaran'] = $p['total_target'] > 0 ? min(100, round(($p['total_paid'] / $p['total_target']) * 100)) : 0;
        }

        $data = [
            'participants' => $participants,
            'keyword' => $keyword
        ];
        return view('agency/participants', $data);
    }

    public function payments()
    {
        $participantModel = new \App\Models\ParticipantModel();
        $keyword = $this->request->getGet('search');

        $builder = $participantModel->select('participants.id, participants.name, participants.nik, participants.upgrade_cost, travel_packages.name as package_name, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.agency_id', session()->get('id'));

        if ($keyword) {
            $builder->groupStart()
                ->like('participants.name', $keyword)
                ->orLike('participants.nik', $keyword)
            ->groupEnd();
        }

        $data = [
            'participants' => $builder->findAll(),
            'keyword' => $keyword
        ];

        $paymentModel = new \App\Models\PaymentModel();
        foreach ($data['participants'] as &$p) {
            $paid = $paymentModel->getTotalPaid($p['id']);
            $p['total_paid'] = $paid['amount'] ?? 0;
            $total_tagihan = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['total_tagihan'] = $total_tagihan;
            $p['remaining'] = $total_tagihan - (float)$p['total_paid'];
        }

        return view('agency/payments', $data);
    }

    public function paymentDetail($participant_id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->find($participant_id);

        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/payments')->with('error', 'Jamaah tidak ditemukan');
        }

        $paymentModel = new \App\Models\PaymentModel();
        $total_paid = (float)($paymentModel->getTotalPaid($participant_id)['amount'] ?? 0);
        $total_tagihan = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $sisa_tagihan = $total_tagihan - $total_paid;

        $data = [
            'participant' => $participant,
            'installments' => $paymentModel->getInstallments($participant_id),
            'total_paid' => $total_paid,
            'total_tagihan' => $total_tagihan,
            'sisa_tagihan' => $sisa_tagihan
        ];

        return view('agency/payment_detail', $data);
    }

    /**
     * Cetak kwitansi pembayaran lunas (sama seperti admin) — semua pembayaran terverifikasi.
     */
    public function receipt($participant_id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->getParticipantBuilder()
            ->where('participants.id', $participant_id)
            ->get()->getRowArray();
        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/payments')->with('error', 'Jamaah tidak ditemukan.');
        }

        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->where('participant_id', $participant_id)
            ->where('status', 'verified')
            ->orderBy('payment_date', 'ASC')
            ->findAll();

        $data = [
            'participant' => $participant,
            'payments' => $payments,
            'title' => 'Kwitansi Pendaftaran - ' . $participant['name'],
        ];
        return view('owner/participant/receipt_print', $data);
    }

    /**
     * Cetak kwitansi pembayaran (sama seperti admin) — hanya untuk pembayaran yang sudah diverifikasi.
     */
    public function transactionReceipt($payment_id)
    {
        $paymentModel = new \App\Models\PaymentModel();
        $payment = $paymentModel->find($payment_id);
        if (!$payment) {
            return redirect()->to('agency/payments')->with('error', 'Pembayaran tidak ditemukan.');
        }
        if ($payment['status'] !== 'verified') {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dicetak setelah pembayaran diverifikasi oleh admin.');
        }

        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->getParticipantBuilder()
            ->where('participants.id', $payment['participant_id'])
            ->get()->getRowArray();
        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/payments')->with('error', 'Jamaah tidak ditemukan.');
        }

        $userModel = new \App\Models\UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $namaDirektur = $owner['full_name'] ?? '—';
        $namaPt = $owner['company_name'] ?? '';
        $alamatPt = $owner['address'] ?? '';
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');

        $tanggalSignature = !empty($payment['updated_at']) ? date('d/m/Y H:i', strtotime($payment['updated_at'])) : date('d/m/Y H:i', strtotime($payment['payment_date']));
        $qrData = 'PAY#' . $payment['id'] . '#' . $payment['amount'] . '#' . ($payment['updated_at'] ?? $payment['payment_date']);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . rawurlencode($qrData);

        $data = [
            'participant' => $participant,
            'payment' => $payment,
            'nama_direktur' => $namaDirektur,
            'nama_pt' => $namaPt,
            'alamat_pt' => $alamatPt,
            'company_logo_url' => $companyLogo,
            'tanggal_signature' => $tanggalSignature,
            'qr_url' => $qrUrl,
            'title' => 'Kwitansi Pembayaran - ' . $participant['name'],
        ];
        return view('owner/participant/payment_receipt_print', $data);
    }

    public function storePayment()
    {
        $rules = [
            'participant_id' => 'required',
            'amount'         => 'required|numeric',
            'payment_date'   => 'required|valid_date',
            'proof'          => 'uploaded[proof]|max_size[proof,4096]|is_image[proof]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi data pembayaran dengan benar.');
        }

        $file = $this->request->getFile('proof');
        if ($file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/payments', $newName);

            $paymentModel = new \App\Models\PaymentModel();
            $paymentModel->insert([
                'participant_id' => $this->request->getPost('participant_id'),
                'amount'         => $this->request->getPost('amount'),
                'payment_date'   => $this->request->getPost('payment_date'),
                'notes'          => $this->request->getPost('notes'),
                'proof'          => 'uploads/payments/' . $newName,
                'status'         => 'pending'
            ]);

            return redirect()->back()->with('msg', 'Bukti pembayaran berhasil diunggah dan sedang diverifikasi.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti pembayaran.');
    }
    public function editParticipant($id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($id);

        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan');
        }

        $db = \Config\Database::connect();
        $documents = $db->table('participant_documents')
            ->where('participant_id', $id)
            ->get()
            ->getResultArray();

        $docsFormatted = [];
        foreach ($documents as $doc) {
            $docsFormatted[$doc['type']] = $doc['file_path'];
        }

        $packageModel = new \App\Models\PackageModel();
        $data = [
            'participant' => $participant,
            'package' => $packageModel->find($participant['package_id']),
            'documents' => $docsFormatted
        ];
        return view('agency/edit_participant', $data);
    }

    public function updateParticipant($id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($id);

        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan');
        }

        $rules = [
            'nik' => 'required|min_length[16]|max_length[20]',
            'name' => 'required|min_length[3]',
            'phone' => 'required|min_length[8]',
            'emergency_name' => 'required|min_length[2]',
            'emergency_relationship' => 'required|min_length[2]',
            'emergency_phone' => 'required|min_length[8]',
        ];
        // Data paspor bersifat opsional.

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi data wajib dengan benar.');
        }

        $passportNumber = trim($this->request->getPost('passport_number') ?? '');
        $passportFullName = trim($this->request->getPost('passport_full_name') ?? '');
        $hasPassport = $passportNumber !== '' || $passportFullName !== '';

        $participantData = [
            'nik' => $this->request->getPost('nik'),
            'name' => $this->request->getPost('name'),
            'place_of_birth' => $this->request->getPost('place_of_birth'),
            'date_of_birth' => $this->request->getPost('date_of_birth'),
            'gender' => $this->request->getPost('gender'),
            'address' => $this->request->getPost('address'),
            'rt_rw' => $this->request->getPost('rt_rw'),
            'kelurahan' => $this->request->getPost('kelurahan'),
            'kecamatan' => $this->request->getPost('kecamatan'),
            'kabupaten' => $this->request->getPost('kabupaten'),
            'provinsi' => $this->request->getPost('provinsi'),
            'religion' => $this->request->getPost('religion'),
            'marital_status' => $this->request->getPost('marital_status'),
            'occupation' => $this->request->getPost('occupation'),
            'blood_type' => $this->request->getPost('blood_type'),
            'phone' => $this->request->getPost('phone'),
            'emergency_name' => $this->request->getPost('emergency_name'),
            'emergency_relationship' => $this->request->getPost('emergency_relationship'),
            'emergency_phone' => $this->request->getPost('emergency_phone'),
            'has_passport' => $hasPassport ? 1 : 0,
        ];
        $participantData['nationality'] = $hasPassport ? ($this->request->getPost('passport_nationality') ?: 'Indonesian') : ($this->request->getPost('nationality') ?? $participant['nationality'] ?? 'WNI');
        $participantData['passport_number'] = $hasPassport ? ($this->request->getPost('passport_number') ?? null) : null;
        $participantData['passport_type'] = $hasPassport ? ($this->request->getPost('passport_type') ?: null) : null;
        $participantData['passport_full_name'] = $hasPassport ? ($this->request->getPost('passport_full_name') ?? null) : null;
        $participantData['passport_place_of_birth'] = $hasPassport ? ($this->request->getPost('passport_place_of_birth') ?: null) : null;
        $participantData['passport_issuance_date'] = $hasPassport ? ($this->request->getPost('passport_issuance_date') ?: null) : null;
        $participantData['passport_expiry_date'] = $hasPassport ? ($this->request->getPost('passport_expiry_date') ?: null) : null;
        $participantData['passport_issuance_city'] = $hasPassport ? ($this->request->getPost('passport_issuance_city') ?: null) : null;
        $participantData['passport_reg_number'] = $hasPassport ? ($this->request->getPost('passport_reg_number') ?: null) : null;
        $participantData['passport_issuing_office'] = $hasPassport ? ($this->request->getPost('passport_issuing_office') ?: null) : null;
        $participantData['passport_name_idn'] = $hasPassport ? ($this->request->getPost('passport_name_idn') ?: null) : null;

        $db = \Config\Database::connect();
        $db->transStart();

        $participantModel->update($id, $participantData);

        // Handle Document Revisions
        $docTypes = ['passport', 'id_card', 'vaccine'];
        foreach ($docTypes as $type) {
            $file = $this->request->getFile($type);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/documents', $newName);

                // Check if document already exists
                $existingDoc = $db->table('participant_documents')
                    ->where('participant_id', $id)
                    ->where('type', $type)
                    ->get()
                    ->getRow();

                if ($existingDoc) {
                    // Update
                    $db->table('participant_documents')
                        ->where('id', $existingDoc->id)
                        ->update([
                            'file_path' => 'uploads/documents/' . $newName,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    
                    // Optional: delete old file safely
                    $oldPath = $existingDoc->file_path;
                    if (!empty($oldPath) && file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                } else {
                    // Insert
                    $db->table('participant_documents')->insert([
                        'participant_id' => $id,
                        'type' => $type,
                        'file_path' => 'uploads/documents/' . $newName,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data jamaah.');
        }

        return redirect()->to('agency/participants')->with('msg', 'Data jamaah ' . $participant['name'] . ' berhasil diperbarui.');
    }

    /**
     * Cetak formulir pendaftaran jamaah (sama seperti di owner).
     */
    public function registrationFormPrint($id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, travel_packages.departure_date as package_departure_date, travel_packages.freebies as package_freebies, travel_packages.inclusions as package_inclusions, travel_packages.hotel_mekkah_id, travel_packages.hotel_madinah_id, users.full_name as agency_name, travel_hotels.name as hotel_upgrade_name, travel_hotel_rooms.name as room_upgrade_name, travel_hotel_rooms.type as room_upgrade_type')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->join('travel_hotels', 'travel_hotels.id = participants.hotel_upgrade_id', 'left')
            ->join('travel_hotel_rooms', 'travel_hotel_rooms.id = participants.room_upgrade_id', 'left')
            ->where('participants.id', $id)
            ->where('participants.agency_id', session()->get('id'))
            ->first();

        if (!$participant) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan.');
        }

        $userModel = new \App\Models\UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');
        $companyName = $owner['company_name'] ?? 'Nama Perusahaan';
        $companyAddress = $owner['address'] ?? '';
        $namaDirektur = $owner['full_name'] ?? '—';

        $hotelModel = new \App\Models\HotelModel();
        $hotelsPackage = [];
        if (!empty($participant['hotel_mekkah_id'])) {
            $h = $hotelModel->find($participant['hotel_mekkah_id']);
            if ($h) $hotelsPackage['Mekkah'] = $h['name'] . ($h['city'] ? ' (' . $h['city'] . ')' : '');
        }
        if (!empty($participant['hotel_madinah_id'])) {
            $h = $hotelModel->find($participant['hotel_madinah_id']);
            if ($h) $hotelsPackage['Madinah'] = $h['name'] . ($h['city'] ? ' (' . $h['city'] . ')' : '');
        }
        if (!empty($participant['hotel_upgrade_name'])) {
            $hotelsPackage['Upgrade'] = $participant['hotel_upgrade_name'] . (!empty($participant['room_upgrade_name']) ? ' — ' . $participant['room_upgrade_name'] : '');
        }

        $equipmentModel = new \App\Models\EquipmentModel();
        $collectedEquipment = $equipmentModel->getByParticipant($id);
        $collectedMap = [];
        foreach ($collectedEquipment as $eq) {
            $collectedMap[$eq['item_name']] = $eq;
        }

        $masterEquipmentModel = new \App\Models\MasterEquipmentModel();
        $masterAttributes = $masterEquipmentModel->getActive();

        $paymentModel = new \App\Models\PaymentModel();
        $payments = $paymentModel->where('participant_id', $id)->where('status', 'verified')->orderBy('payment_date', 'ASC')->findAll();
        $totalPaidRow = $paymentModel->getTotalPaid($id);
        $totalPaid = (float)($totalPaidRow['amount'] ?? 0);
        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);

        $freebies = json_decode($participant['package_freebies'] ?? '[]', true) ?? [];
        $inclusions = json_decode($participant['package_inclusions'] ?? '[]', true) ?? [];

        $tanggalSignature = date('d/m/Y H:i');
        $qrData = 'REG#' . $id . '#' . $participant['nik'] . '#' . $tanggalSignature;
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . rawurlencode($qrData);

        $data = [
            'participant' => $participant,
            'payments' => $payments,
            'total_paid' => $totalPaid,
            'total_target' => $totalTarget,
            'freebies' => $freebies,
            'inclusions' => $inclusions,
            'hotels_package' => $hotelsPackage,
            'master_attributes' => $masterAttributes,
            'collected_map' => $collectedMap,
            'company_logo_url' => $companyLogo,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'nama_direktur' => $namaDirektur,
            'tanggal_signature' => $tanggalSignature,
            'qr_url' => $qrUrl,
            'title' => 'Formulir Pendaftaran - ' . $participant['name'],
        ];
        return view('owner/participant/registration_form_print', $data);
    }

    /**
     * Halaman melengkapi berkas jamaah (paspor, KTP, vaksin).
     */
    public function documents($id)
    {
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($id);
        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan.');
        }

        $docModel = new \App\Models\DocumentModel();
        $documents = $docModel->where('participant_id', $id)->orderBy('created_at', 'DESC')->findAll();

        $package = (new \App\Models\PackageModel())->find($participant['package_id']);
        $data = [
            'participant' => $participant,
            'package' => $package,
            'documents' => $documents,
        ];
        return view('agency/documents', $data);
    }

    /**
     * Upload multiple berkas (sama seperti admin).
     */
    public function uploadDocument()
    {
        $participantId = (int) $this->request->getPost('participant_id');
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($participantId);
        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan.');
        }

        $files = $this->request->getFileMultiple('files');
        $types = $this->request->getPost('types');
        $titles = $this->request->getPost('titles');

        if (!$files || !is_array($files)) {
            return redirect()->to('agency/documents/' . $participantId)->with('error', 'Tidak ada file dipilih.');
        }

        $docModel = new \App\Models\DocumentModel();
        $uploadedCount = 0;

        // Semua jenis dari form; untuk DB hanya passport, id_card, vaccine, other (sisanya simpan sebagai other + label di title)
        $typeLabels = [
            'passport' => 'Paspor', 'id_card' => 'KTP', 'vaccine' => 'Kartu Vaksin',
            'visa' => 'Visa', 'vaccine_meningitis' => 'Vaksin Meningitis', 'vaccine_covid' => 'Vaksin Covid',
            'insurance' => 'Asuransi', 'ticket' => 'Tiket', 'photo' => 'Pas Foto 4x6', 'other' => 'Lainnya',
        ];
        $singleTypes = ['passport', 'id_card', 'vaccine'];

        foreach ($files as $index => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $rawType = isset($types[$index]) ? $types[$index] : 'other';
                $dbType = in_array($rawType, $singleTypes, true) ? $rawType : 'other';
                $label = isset($titles[$index]) && trim($titles[$index]) !== '' ? trim($titles[$index]) : ($typeLabels[$rawType] ?? $rawType);
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/documents', $newName);

                if (in_array($dbType, $singleTypes, true)) {
                    $docModel->where('participant_id', $participantId)->where('type', $dbType)->delete();
                }

                $docModel->insert([
                    'participant_id' => $participantId,
                    'type' => $dbType,
                    'title' => $label,
                    'file_path' => 'uploads/documents/' . $newName,
                    'is_verified' => 0,
                ]);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            return redirect()->to('agency/documents/' . $participantId)->with('msg', $uploadedCount . ' berkas berhasil diupload.');
        }
        return redirect()->to('agency/documents/' . $participantId)->with('error', 'Gagal mengupload berkas.');
    }

    /**
     * Simpan/update berkas (paspor, KTP, vaksin) dari form melengkapi berkas.
     */
    public function updateDocuments()
    {
        $participantId = (int) $this->request->getPost('participant_id');
        $participantModel = new \App\Models\ParticipantModel();
        $participant = $participantModel->find($participantId);
        if (!$participant || $participant['agency_id'] != session()->get('id')) {
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan.');
        }

        $db = \Config\Database::connect();
        $docTypes = ['passport', 'id_card', 'vaccine'];
        foreach ($docTypes as $type) {
            $file = $this->request->getFile($type);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/documents', $newName);
                $path = 'uploads/documents/' . $newName;

                $existing = $db->table('participant_documents')->where('participant_id', $participantId)->where('type', $type)->get()->getRow();
                if ($existing) {
                    $oldPath = $existing->file_path;
                    if (!empty($oldPath) && is_file(ROOTPATH . 'public/' . $oldPath)) {
                        @unlink(ROOTPATH . 'public/' . $oldPath);
                    }
                    $db->table('participant_documents')->where('id', $existing->id)->update(['file_path' => $path, 'updated_at' => date('Y-m-d H:i:s')]);
                } else {
                    $db->table('participant_documents')->insert([
                        'participant_id' => $participantId,
                        'type' => $type,
                        'file_path' => $path,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
        }

        return redirect()->to('agency/documents/' . $participantId)->with('msg', 'Berkas berhasil diperbarui.');
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
            return redirect()->to('agency/participants')->with('error', 'Jamaah tidak ditemukan.');
        }

        $documents = $db->table('participant_documents')->where('participant_id', $id)->get()->getResultArray();
        $docsFormatted = [];
        foreach ($documents as $doc) {
            $docsFormatted[$doc['type']] = $doc['file_path'];
        }

        // Calculate document progress
        $total_goal = 3; // passport, id_card, vaccine
        $verified_count = $db->table('participant_documents')
            ->where('participant_id', $id)
            ->where('status', 'verified')
            ->countAllResults();
        $doc_progress = $total_goal > 0 ? round(($verified_count / $total_goal) * 100) : 0;


        $collectedEquipment = $equipmentModel->getByParticipant($id);
        $collectedMap = [];
        foreach ($collectedEquipment as $eq) {
            $collectedMap[$eq['item_name']] = $eq;
        }

        $freebies = json_decode($participant['freebies'], true) ?? [];

        return view('agency/checklist', [
            'title' => 'Cek List & Kelengkapan - ' . $participant['name'],
            'participant' => $participant,
            'documents' => $docsFormatted,
            'freebies' => $freebies,
            'collectedMap' => $collectedMap,
            'doc_progress' => $doc_progress,
            'verified_count' => $verified_count,
            'total_goal' => $total_goal
        ]);
    }

    public function toggleEquipment()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $id = $this->request->getPost('participant_id');
        $itemName = $this->request->getPost('item_name');
        $status = $this->request->getPost('status');

        $equipmentModel = new \App\Models\EquipmentModel();
        $existing = $equipmentModel->where('participant_id', $id)->where('item_name', $itemName)->first();

        if ($existing) {
            $equipmentModel->update($existing['id'], [
                'status' => $status,
                'collected_at' => ($status === 'collected') ? date('Y-m-d H:i:s') : null,
                'collected_by' => ($status === 'collected') ? session()->get('user_id') : null
            ]);
        } else {
            $equipmentModel->insert([
                'participant_id' => $id,
                'item_name' => $itemName,
                'status' => $status,
                'collected_at' => ($status === 'collected') ? date('Y-m-d H:i:s') : null,
                'collected_by' => ($status === 'collected') ? session()->get('user_id') : null
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Status kelengkapan diperbarui']);
    }

    /**
     * Daftar boarding jamaah (read-only untuk agency)
     */
    public function boardingList()
    {
        $agencyId = session()->get('id');
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');
        
        $packageModel = new \App\Models\PackageModel();
        $packages = $packageModel->orderBy('departure_date', 'DESC')->findAll();

        $participantModel = new \App\Models\ParticipantModel();
        $builder = $participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date, travel_packages.airline, travel_packages.price as package_price, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.agency_id', $agencyId)
            ->where('participants.status !=', 'cancelled')
            ->orderBy('travel_packages.departure_date', 'ASC')
            ->orderBy('participants.name', 'ASC');

        if ($packageId) {
            $builder->where('participants.package_id', $packageId);
        }
        if ($departureFrom) {
            $builder->where('DATE(travel_packages.departure_date) >=', $departureFrom);
        }
        if ($departureTo) {
            $builder->where('DATE(travel_packages.departure_date) <=', $departureTo);
        }

        $participants = $builder->findAll();
        $paymentModel = new \App\Models\PaymentModel();
        $docModel = new \App\Models\DocumentModel();

        foreach ($participants as &$p) {
            $p['total_paid'] = (float)($paymentModel->getTotalPaid($p['id'])['amount'] ?? 0);
            $p['total_target'] = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['pembayaran_lunas'] = $p['total_target'] > 0 && $p['total_paid'] >= $p['total_target'];
            $docCount = $docModel->where('participant_id', $p['id'])->where('is_verified', 1)->countAllResults();
            $p['doc_progress'] = $docCount >= 7 ? 100 : round(($docCount / 7) * 100);
            $p['berkas_lengkap'] = ($p['doc_progress'] >= 100);
            $dep = $p['departure_date'] ?? null;
            $p['days_until'] = null;
            if ($dep) {
                $p['days_until'] = (int) floor((strtotime(date('Y-m-d', strtotime($dep))) - strtotime(date('Y-m-d'))) / 86400);
            }
            $p['can_boarding'] = ($p['status'] === 'verified' && $p['berkas_lengkap'] && $p['pembayaran_lunas'] && $p['days_until'] !== null && $p['days_until'] <= 15);
        }

        $data = [
            'participants' => $participants,
            'packages' => $packages,
            'selected_package' => $packageId,
            'departure_date_from' => $departureFrom,
            'departure_date_to' => $departureTo,
        ];
        return view('agency/boarding_list', $data);
    }

    /**
     * Daftar pembatalan jamaah (read-only untuk agency)
     */
    public function cancellations()
    {
        $agencyId = session()->get('id');
        $participantModel = new \App\Models\ParticipantModel();
        $paymentModel = new \App\Models\PaymentModel();
        
        $list = $participantModel->getParticipantBuilder()
            ->where('participants.agency_id', $agencyId)
            ->where('participants.status', 'cancelled')
            ->orderBy('participants.cancelled_at', 'DESC')
            ->findAll();

        foreach ($list as &$row) {
            $row['total_paid'] = ($paymentModel->getTotalPaid($row['id'])['amount'] ?? 0);
        }

        $data = ['list' => $list];
        return view('agency/cancellations', $data);
    }
}

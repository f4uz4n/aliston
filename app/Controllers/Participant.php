<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\PackageModel;
use App\Models\DocumentModel;
use App\Models\PaymentModel;
use App\Models\UserModel;

class Participant extends BaseController
{
    protected $participantModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
    }

    private function getDocStats($participantId)
    {
        $docModel = new DocumentModel();
        $docs = $docModel->where('participant_id', $participantId)->where('is_verified', 1)->countAllResults();

        $totalGoal = 7;
        $progress = min(100, round(($docs / $totalGoal) * 100));

        return [
            'verified_count' => $docs,
            'total_goal' => $totalGoal,
            'progress' => $progress
        ];
    }

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $keyword = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->participantModel->getParticipantBuilder();
        $builder->where('participants.status !=', 'cancelled'); // Jamaah batal hanya tampil di menu Pembatalan

        if ($keyword) {
            $builder->groupStart()
                ->like('participants.name', $keyword)
                ->orLike('participants.phone', $keyword)
                ->orLike('travel_packages.name', $keyword)
                ->orLike('users.full_name', $keyword)
                ->groupEnd();
        }

        if ($status === 'verified') {
            $builder->where('participants.status', 'verified');
        }
        elseif ($status === 'pending') {
            $builder->where('participants.status !=', 'verified');
        }

        $participants = $builder->paginate(20);
        $paymentModel = new PaymentModel();

        foreach ($participants as &$p) {
            // Payment Progress (total = harga paket + upgrade hotel/kamar/bed)
            $paid = $paymentModel->getTotalPaid($p['id']);
            $p['total_paid'] = $paid['amount'] ?? 0;
            $totalTarget = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['total_target'] = $totalTarget;

            if ($totalTarget > 0) {
                $p['payment_progress'] = min(100, round(($p['total_paid'] / $totalTarget) * 100));
            } else {
                $p['payment_progress'] = 0;
            }

            // Progress Kelengkapan Berkas (Simplified)
            $stats = $this->getDocStats($p['id']);
            $p['doc_count'] = $stats['verified_count'];
            $p['doc_progress'] = $stats['progress'];
        }

        $data = [
            'participants' => $participants,
            'pager' => $this->participantModel->pager,
            'keyword' => $keyword,
            'status' => $status
        ];
        return view('owner/participant/index', $data);
    }

    public function documents($id = null)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $docModel = new DocumentModel();

        if ($id) {
            $participant = $this->participantModel->find($id);
            if (!$participant) {
                return redirect()->to('owner/participant/documents')->with('error', 'Jamaah tidak ditemukan.');
            }
            if ($participant['status'] === 'cancelled') {
                return redirect()->to('owner/participant/documents')->with('error', 'Jamaah yang dibatalkan tidak ditampilkan di kelengkapan berkas.');
            }

            $stats = $this->getDocStats($id);

            $data = [
                'participant' => $participant,
                'verified_count' => $stats['verified_count'],
                'total_goal' => $stats['total_goal'],
                'doc_progress' => $stats['progress'],
                'documents' => $docModel->where('participant_id', $id)->findAll()
            ];
            return view('owner/participant/documents_view', $data);
        }

        $search = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->participantModel->getParticipantBuilder();
        $builder->where('participants.status !=', 'cancelled'); // Jamaah batal hanya tampil di menu Pembatalan

        if ($search) {
            $builder->groupStart()
                ->like('participants.name', $search)
                ->orLike('participants.phone', $search)
                ->orLike('travel_packages.name', $search)
                ->orLike('users.full_name', $search)
                ->groupEnd();
        }

        $allParticipants = $builder->findAll();
        $filteredParticipants = [];

        foreach ($allParticipants as $p) {
            $stats = $this->getDocStats($p['id']);
            $p['doc_count'] = $stats['verified_count'];
            $p['doc_progress'] = $stats['progress'];

            // Completion Status: 100% = Selesai, < 100% = Proses
            $p['completion_status'] = ($p['doc_progress'] >= 100) ? 'selesai' : 'proses';

            if ($status === 'selesai') {
                if ($p['completion_status'] === 'selesai') {
                    $filteredParticipants[] = $p;
                }
            }
            elseif ($status === 'proses') {
                if ($p['completion_status'] === 'proses') {
                    $filteredParticipants[] = $p;
                }
            }
            else {
                $filteredParticipants[] = $p;
            }
        }

        $perPage = 20;
        $page = max(1, (int) ($this->request->getGet('page') ?? 1));
        $total = count($filteredParticipants);
        $offset = ($page - 1) * $perPage;
        $pagedParticipants = array_slice($filteredParticipants, $offset, $perPage);

        $pager = \Config\Services::pager();
        $pager->only(['search', 'status']);
        $pager->store('default', $page, $perPage, $total);

        $data = [
            'participants' => $pagedParticipants,
            'search' => $search,
            'status' => $status,
            'pager' => $pager,
            'start_number' => $offset,
            'total' => $total,
        ];
        return view('owner/participant/documents_list', $data);
    }

    public function verifyDocument()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $docId = $this->request->getPost('id');
        $status = $this->request->getPost('status');

        $docModel = new DocumentModel();
        $docModel->update($docId, [
            'is_verified' => $status
        ]);

        $msg = $status ? 'Berkas berhasil diverifikasi.' : 'Verifikasi berkas dibatalkan.';
        return redirect()->back()->with('msg', $msg);
    }

    public function uploadDocument()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $id = $this->request->getPost('participant_id');
        $files = $this->request->getFileMultiple('files');
        $types = $this->request->getPost('types');
        $titles = $this->request->getPost('titles');

        if (!$files) {
            return redirect()->back()->with('error', 'Tidak ada file dipilih.');
        }

        $docModel = new DocumentModel();
        $uploadedCount = 0;

        foreach ($files as $index => $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $type = $types[$index] ?? 'other';
                $title = $titles[$index] ?? '';
                $newName = $file->getRandomName();
                $file->move(ROOTPATH . 'public/uploads/documents', $newName);

                if ($type != 'other') {
                    $docModel->where('participant_id', $id)->where('type', $type)->delete();
                }

                $docModel->save([
                    'participant_id' => $id,
                    'type' => $type,
                    'title' => $title,
                    'file_path' => 'uploads/documents/' . $newName,
                    'is_verified' => 1
                ]);
                $uploadedCount++;
            }
        }

        if ($uploadedCount > 0) {
            return redirect()->back()->with('msg', "$uploadedCount berkas berhasil diupload.");
        }

        return redirect()->back()->with('error', 'Gagal mengupload berkas.');
    }

    /**
     * Hapus berkas digital jamaah (pemilik / admin kantor).
     */
    public function deleteDocument()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $docId = (int) $this->request->getPost('id');
        $docModel = new DocumentModel();
        $doc = $docModel->find($docId);
        if (!$doc) {
            return redirect()->back()->with('error', 'Berkas tidak ditemukan.');
        }

        if (!$this->participantModel->find($doc['participant_id'])) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }

        if (!empty($doc['file_path']) && is_file(ROOTPATH . 'public/' . $doc['file_path'])) {
            @unlink(ROOTPATH . 'public/' . $doc['file_path']);
        }
        $docModel->delete($docId);

        return redirect()->back()->with('msg', 'Berkas berhasil dihapus.');
    }

    /**
     * Edit berkas: jenis, keterangan, opsional ganti file (verifikasi direset).
     */
    public function updateDocument()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $docId = (int) $this->request->getPost('document_id');
        $participantId = (int) $this->request->getPost('participant_id');
        $participant = $this->participantModel->find($participantId);
        if (!$participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }

        $docModel = new DocumentModel();
        $doc = $docModel->find($docId);
        if (!$doc || (int) $doc['participant_id'] !== $participantId) {
            return redirect()->back()->with('error', 'Berkas tidak ditemukan.');
        }

        $rawType = (string) $this->request->getPost('type');
        $titleInput = trim((string) $this->request->getPost('title'));
        $typeLabels = [
            'passport' => 'Paspor', 'id_card' => 'KTP', 'kk' => 'KK', 'vaccine' => 'Dokumen pendukung (akta lahir/ijazah/buku nikah)',
            'visa' => 'Visa', 'vaccine_meningitis' => 'Vaksin Meningitis', 'vaccine_covid' => 'Vaksin Covid',
            'insurance' => 'Asuransi', 'ticket' => 'Tiket', 'photo' => 'Pas Foto 4x6', 'other' => 'Lainnya',
        ];
        $singleTypes = ['passport', 'id_card', 'kk', 'vaccine'];
        $dbType = in_array($rawType, $singleTypes, true) ? $rawType : 'other';
        $label = $titleInput !== '' ? $titleInput : ($typeLabels[$rawType] ?? $rawType);

        $file = $this->request->getFile('file');
        $hasNewFile = $file && $file->isValid() && !$file->hasMoved();

        if (in_array($dbType, $singleTypes, true)) {
            $dupes = $docModel->where('participant_id', $participantId)->where('type', $dbType)->where('id !=', $docId)->findAll();
            foreach ($dupes as $d) {
                if (!empty($d['file_path']) && is_file(ROOTPATH . 'public/' . $d['file_path'])) {
                    @unlink(ROOTPATH . 'public/' . $d['file_path']);
                }
                $docModel->delete($d['id']);
            }
        }

        $update = [
            'type' => $dbType,
            'title' => $label,
            'is_verified' => 0,
        ];

        if ($hasNewFile) {
            if (!empty($doc['file_path']) && is_file(ROOTPATH . 'public/' . $doc['file_path'])) {
                @unlink(ROOTPATH . 'public/' . $doc['file_path']);
            }
            $newName = $file->getRandomName();
            $file->move(ROOTPATH . 'public/uploads/documents', $newName);
            $update['file_path'] = 'uploads/documents/' . $newName;
        }

        $docModel->update($docId, $update);

        return redirect()->to('owner/participant/documents/' . $participantId)->with('msg', 'Berkas berhasil diperbarui.');
    }

    public function receipt($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel->getParticipantBuilder()->where('participants.id', $id)->get()->getRowArray();
        if (!$participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }
        $participant = format_participant_name_row($participant);

        $paymentModel = new PaymentModel();
        $payments = $paymentModel->where('participant_id', $id)
            ->where('status', 'verified')
            ->orderBy('payment_date', 'ASC')
            ->findAll();

        $userModel = new UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $namaPenandatangan = !empty($owner['nama_sekretaris_bendahara']) ? $owner['nama_sekretaris_bendahara'] : ($owner['full_name'] ?? '—');

        $totalPaid = 0;
        foreach ($payments as $p) {
            $totalPaid += $p['amount'];
        }
        $qrData = 'RECEIPT#' . $id . '#' . $totalPaid . '#' . date('Y-m-d');
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . rawurlencode($qrData);

        $data = [
            'participant' => $participant,
            'payments' => $payments,
            'nama_penandatangan' => $namaPenandatangan,
            'title' => 'Kwitansi Pendaftaran - ' . $participant['name'],
            'qr_url' => $qrUrl,
        ];

        return view('owner/participant/receipt_print', $data);
    }

    /**
     * Cetak formulir pendaftaran lengkap: kop, paket, hotel, pembayaran, fasilitas, checklist atribut, tanda tangan digital.
     */
    public function registrationFormPrint($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, travel_packages.departure_date as package_departure_date, travel_packages.freebies as package_freebies, travel_packages.inclusions as package_inclusions, travel_packages.exclusions as package_exclusions, travel_packages.hotel_mekkah_id, travel_packages.hotel_madinah_id, users.full_name as agency_name, travel_hotels.name as hotel_upgrade_name, travel_hotel_rooms.name as room_upgrade_name, travel_hotel_rooms.type as room_upgrade_type')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->join('travel_hotels', 'travel_hotels.id = participants.hotel_upgrade_id', 'left')
            ->join('travel_hotel_rooms', 'travel_hotel_rooms.id = participants.room_upgrade_id', 'left')
            ->where('participants.id', $id)
            ->first();

        if (!$participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }

        $userModel = new UserModel();
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

        $paymentModel = new PaymentModel();
        $payments = $paymentModel->where('participant_id', $id)->where('status', 'verified')->orderBy('payment_date', 'ASC')->findAll();
        $totalPaid = ($paymentModel->getTotalPaid($id)['amount'] ?? 0);
        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);

        $freebies = json_decode($participant['package_freebies'] ?? '[]', true) ?? [];
        $inclusions = json_decode($participant['package_inclusions'] ?? '[]', true) ?? [];
        $exclusions = json_decode($participant['package_exclusions'] ?? '[]', true) ?? [];

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
            'exclusions' => $exclusions,
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
            'back_url' => base_url('owner/participant'),
        ];
        return view('owner/participant/registration_form_print', $data);
    }

    public function paymentHistory($id)
    {
        if (! is_back_office()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $paymentModel = new PaymentModel();
        $participant = $this->participantModel
            ->select('participants.id, participants.upgrade_cost, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', (int) $id)
            ->first();

        if (!$participant) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jamaah tidak ditemukan']);
        }

        $history = $paymentModel->where('participant_id', $id)
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        $totalPaid = (float) ($paymentModel->getTotalPaid($id)['amount'] ?? 0);
        $totalTarget = (float) ($participant['package_price'] ?? 0) + (float) ($participant['upgrade_cost'] ?? 0);
        $remaining = max(0, $totalTarget - $totalPaid);

        return $this->response->setJSON([
            'status' => 'success',
            'summary' => [
                'total_paid' => $totalPaid,
                'remaining' => $remaining,
                'total_target' => $totalTarget,
            ],
            'data' => $history
        ]);
    }

    public function transactionReceipt($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $paymentModel = new PaymentModel();
        $payment = $paymentModel->find($id);

        if (!$payment || $payment['status'] !== 'verified') {
            return redirect()->back()->with('error', 'Pembayaran tidak ditemukan atau belum diverifikasi.');
        }

        $participant = $this->participantModel->getParticipantBuilder()
            ->where('participants.id', $payment['participant_id'])
            ->get()->getRowArray();

        if (!$participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }
        $participant = format_participant_name_row($participant);

        $userModel = new UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $namaPenandatangan = !empty($owner['nama_sekretaris_bendahara']) ? $owner['nama_sekretaris_bendahara'] : ($owner['full_name'] ?? '—');
        $namaPt = $owner['company_name'] ?? '';
        $alamatPt = $owner['address'] ?? '';
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');

        $tanggalSignature = !empty($payment['updated_at']) ? date('d/m/Y H:i', strtotime($payment['updated_at'])) : date('d/m/Y H:i', strtotime($payment['payment_date']));
        $qrData = 'PAY#' . $payment['id'] . '#' . $payment['amount'] . '#' . ($payment['updated_at'] ?? $payment['payment_date']);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . rawurlencode($qrData);

        $data = [
            'participant' => $participant,
            'payment' => $payment,
            'nama_penandatangan' => $namaPenandatangan,
            'nama_pt' => $namaPt,
            'alamat_pt' => $alamatPt,
            'company_logo_url' => $companyLogo,
            'tanggal_signature' => $tanggalSignature,
            'qr_url' => $qrUrl,
            'title' => 'Kwitansi Pembayaran - ' . $participant['name']
        ];

        return view('owner/participant/payment_receipt_print', $data);
    }

    /**
     * Halaman kelola jamaah: monitoring berkas & pembayaran, ganti jadwal, hotel, kamar.
     */
    public function kelola($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.price as package_price, travel_packages.airline as package_airline, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.id', $id)
            ->first();

        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }

        $packageModel = new PackageModel();
        $hotelModel = new \App\Models\HotelModel();
        $roomModel = new \App\Models\RoomModel();
        $paymentModel = new PaymentModel();

        $packages = $packageModel->orderBy('departure_date', 'DESC')->findAll();
        $hotels = $hotelModel->findAll();
        $roomBedModel = new \App\Models\RoomBedModel();
        $upgradeBedModel = new \App\Models\ParticipantUpgradeBedModel();
        foreach ($hotels as &$h) {
            $h['rooms'] = $roomModel->where('hotel_id', $h['id'])->findAll();
            foreach ($h['rooms'] as &$r) {
                $r['beds'] = $roomBedModel->getByRoom($r['id']);
            }
        }
        $participant_upgrade_beds = $upgradeBedModel->getQtyByParticipant($id);

        $stats = $this->getDocStats($id);
        $paid = $paymentModel->getTotalPaid($id);

        $departure = participant_effective_departure($participant['package_departure_date'] ?? null, $participant['departure_note'] ?? null);
        $daysUntilDeparture = participant_days_until_departure($departure);
        $allow_ubah_jadwal_hotel = ($participant['status'] !== 'cancelled');

        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $totalPaid = (float)($paid['amount'] ?? 0);
        $berkas_lengkap = ($stats['progress'] >= 100);
        $pembayaran_lunas = ($totalTarget > 0 && $totalPaid >= $totalTarget);
        $h15_or_less = ($daysUntilDeparture !== null && $daysUntilDeparture <= 15);
        $can_boarding = ($participant['status'] !== 'cancelled' && $participant['status'] === 'verified' && $berkas_lengkap && $pembayaran_lunas && $h15_or_less);

        $verifiedPayments = $paymentModel->where('participant_id', $id)
            ->where('status', 'verified')
            ->orderBy('payment_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $data = [
            'participant' => $participant,
            'packages' => $packages,
            'hotels' => $hotels,
            'participant_upgrade_beds' => $participant_upgrade_beds,
            'doc_progress' => $stats['progress'],
            'doc_count' => $stats['verified_count'],
            'total_goal' => $stats['total_goal'],
            'total_paid' => $paid['amount'] ?? 0,
            'total_target' => $totalTarget,
            'days_until_departure' => $daysUntilDeparture,
            'allow_ubah_jadwal_hotel' => $allow_ubah_jadwal_hotel,
            'can_boarding' => $can_boarding,
            'verified_payments' => $verifiedPayments,
            'pembayaran_lunas' => $pembayaran_lunas,
        ];
        return view('owner/participant/kelola', $data);
    }

    /**
     * Halaman edit data jamaah (owner) — biodata & dokumen.
     */
    public function editParticipant($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
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

        $packageModel = new PackageModel();
        $data = [
            'participant' => $participant,
            'package' => $packageModel->find($participant['package_id']),
            'documents' => $docsFormatted,
        ];
        return view('owner/participant/edit', $data);
    }

    /**
     * Simpan perubahan data jamaah (owner).
     */
    public function updateParticipant($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel->find($id);
        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }

        $rules = [
            'nik' => 'required|min_length[16]|max_length[20]',
            'name' => 'required|min_length[3]',
            'phone' => 'required|min_length[8]',
            'emergency_name' => 'required|min_length[2]',
            'emergency_relationship' => 'required|min_length[2]',
            'emergency_phone' => 'required|min_length[8]',
        ];
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

        $this->participantModel->update($id, $participantData);

        $docTypes = ['passport', 'id_card', 'kk', 'vaccine'];
        foreach ($docTypes as $type) {
            $file = $this->request->getFile($type);
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move('uploads/documents', $newName);
                $existingDoc = $db->table('participant_documents')->where('participant_id', $id)->where('type', $type)->get()->getRow();
                if ($existingDoc) {
                    $db->table('participant_documents')->where('id', $existingDoc->id)->update([
                        'file_path' => 'uploads/documents/' . $newName,
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    if (!empty($existingDoc->file_path) && is_file(FCPATH . $existingDoc->file_path)) {
                        @unlink(FCPATH . $existingDoc->file_path);
                    }
                } else {
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

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data jamaah.');
        }
        return redirect()->to('owner/participant')->with('msg', 'Data jamaah ' . $participant['name'] . ' berhasil diperbarui.');
    }

    private function checkH30ForChange($participantId)
    {
        $p = $this->participantModel
            ->select('participants.*, travel_packages.departure_date as pkg_dep_date')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', $participantId)
            ->first();
        if (!$p || $p['status'] === 'cancelled') {
            return ['allowed' => false, 'message' => 'Jamaah tidak ditemukan atau sudah dibatalkan.'];
        }
        $dep = participant_effective_departure($p['pkg_dep_date'] ?? null, $p['departure_note'] ?? null);
        if (!$dep) {
            return ['allowed' => false, 'message' => 'Jadwal keberangkatan tidak ditemukan.'];
        }
        if (participant_days_until_departure($dep) === null) {
            return ['allowed' => false, 'message' => 'Jadwal keberangkatan tidak valid.'];
        }
        return ['allowed' => true];
    }

    /**
     * Ganti jadwal pemberangkatan (ubah paket).
     */
    public function updateSchedule()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $participantId = (int) $this->request->getPost('participant_id');
        $packageId = $this->request->getPost('package_id');
        if (!$participantId || !$packageId) {
            return redirect()->back()->with('error', 'Data tidak lengkap.');
        }
        $check = $this->checkH30ForChange($participantId);
        if (!$check['allowed']) {
            return redirect()->back()->with('error', $check['message']);
        }
        $noteRaw = $this->request->getPost('departure_note');
        $note = $noteRaw !== null && $noteRaw !== '' ? trim((string) $noteRaw) : '';
        $update = [
            'package_id' => (int) $packageId,
            'departure_note' => $note !== '' ? $note : null,
        ];
        $this->participantModel->update($participantId, $update);
        return redirect()->back()->with('msg', 'Jadwal pemberangkatan berhasil diubah.');
    }

    public function getUpgradeOptions($id)
    {
        if (! is_back_office()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $hotelModel = new \App\Models\HotelModel();
        $roomModel = new \App\Models\RoomModel();
        $roomBedModel = new \App\Models\RoomBedModel();
        $upgradeBedModel = new \App\Models\ParticipantUpgradeBedModel();

        $hotels = $hotelModel->findAll();
        foreach ($hotels as &$h) {
            $h['rooms'] = $roomModel->where('hotel_id', $h['id'])->findAll();
            foreach ($h['rooms'] as &$r) {
                $r['beds'] = $roomBedModel->getByRoom($r['id']);
            }
        }

        $participant = $this->participantModel->find($id);
        $participant_upgrade_beds = $upgradeBedModel->getQtyByParticipant($id);

        return $this->response->setJSON([
            'status' => 'success',
            'hotels' => $hotels,
            'current' => [
                'hotel_upgrade_id' => $participant['hotel_upgrade_id'],
                'room_upgrade_id' => $participant['room_upgrade_id'],
                'upgrade_cost' => $participant['upgrade_cost'],
                'bed_qty' => $participant_upgrade_beds,
            ]
        ]);
    }

    public function saveUpgrade()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $id = (int) $this->request->getPost('participant_id');
        $check = $this->checkH30ForChange($id);
        if (!$check['allowed']) {
            return redirect()->back()->with('error', $check['message']);
        }

        $roomModel = new \App\Models\RoomModel();
        $roomBedModel = new \App\Models\RoomBedModel();
        $upgradeBedModel = new \App\Models\ParticipantUpgradeBedModel();

        $hotelUpgradeId = $this->request->getPost('hotel_upgrade_id') ?: null;
        $roomUpgradeId = $this->request->getPost('room_upgrade_id') ?: null;
        $bedQty = $this->request->getPost('bed_qty');
        if (!is_array($bedQty)) {
            $bedQty = [];
        }

        $upgradeCost = 0;
        if ($roomUpgradeId) {
            $room = $roomModel->find($roomUpgradeId);
            if ($room) {
                $upgradeCost = (float) ($room['price_per_pax'] ?? 0);
                $beds = $roomBedModel->getByRoom($roomUpgradeId);
                foreach ($beds as $bed) {
                    $qty = (int) ($bedQty[$bed['id']] ?? 0);
                    if ($qty > 0) {
                        $upgradeCost += (float) $bed['price'] * $qty;
                    }
                }
            }
        }

        $upgradeBedModel->replaceForParticipant($id, $bedQty);

        $data = [
            'hotel_upgrade_id' => $hotelUpgradeId,
            'room_upgrade_id' => $roomUpgradeId,
            'upgrade_cost' => $upgradeCost,
        ];

        $this->participantModel->update($id, $data);
        return redirect()->back()->with('msg', 'Upgrade fasilitas berhasil disimpan.');
    }

    public function boarding()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $packageId = $this->request->getGet('package_id');
        $packageModel = new PackageModel();
        $packages = $packageModel->findAll();

        $participants = [];
        if ($packageId) {
            $participants = $this->participantModel->getParticipantBuilder()
                ->where('participants.package_id', $packageId)
                ->where('participants.status', 'verified')
                ->orderBy('participants.name', 'ASC')
                ->findAll();
        }

        $data = [
            'packages' => $packages,
            'selected_package' => $packageId,
            'participants' => $participants
        ];

        return view('owner/participant/boarding', $data);
    }

    public function processBoarding()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $packageId = $this->request->getPost('package_id');
        $participantIds = $this->request->getPost('participant_ids');

        if ($packageId) {
            // 1. Mark selected as boarded
            if (!empty($participantIds)) {
                $this->participantModel->set('is_boarded', 1)
                    ->set('boarded_at', date('Y-m-d H:i:s'))
                    ->whereIn('id', $participantIds)
                    ->where('status', 'verified')
                    ->update();
            }

            // 2. Mark unselected (jamaah terverifikasi di paket ini) as not boarded
            $allInPackage = $this->participantModel->where('package_id', $packageId)->where('status', 'verified')->findColumn('id') ?? [];
            $idsToKeep = $participantIds ?? [];
            $idsToReset = array_diff($allInPackage, $idsToKeep);

            if (!empty($idsToReset)) {
                $this->participantModel->set('is_boarded', 0)
                    ->set('boarded_at', null)
                    ->whereIn('id', $idsToReset)
                    ->update();
            }
        }

        return redirect()->back()->with('msg', 'Data keberangkatan berhasil diperbarui.');
    }

    /**
     * Konfirmasi boarding per jamaah (dari kelola atau dari menu boarding list).
     * Syarat: berkas lengkap, pembayaran lunas, H-15.
     */
    public function confirmBoarding()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $participantId = (int) $this->request->getPost('participant_id');
        $participant = $this->participantModel
            ->select('participants.*, travel_packages.departure_date as pkg_dep_date, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', $participantId)
            ->first();
        if (!$participant || $participant['status'] === 'cancelled') {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan atau sudah dibatalkan.');
        }
        if ($participant['status'] !== 'verified') {
            return redirect()->back()->with('error', 'Boarding hanya untuk jamaah yang sudah terverifikasi.');
        }
        $stats = $this->getDocStats($participantId);
        $paymentModel = new PaymentModel();
        $totalPaid = (float)($paymentModel->getTotalPaid($participantId)['amount'] ?? 0);
        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $dep = participant_effective_departure($participant['pkg_dep_date'] ?? null, $participant['departure_note'] ?? null);
        $days = participant_days_until_departure($dep);
        $berkas_ok = ($stats['progress'] >= 100);
        $lunas = ($totalTarget > 0 && $totalPaid >= $totalTarget);
        $h15 = ($days !== null && $days <= 15);
        if (!$berkas_ok || !$lunas || !$h15) {
            return redirect()->back()->with('error', 'Boarding hanya bisa dilakukan jika berkas lengkap, pembayaran lunas, dan sudah H-15 atau kurang.');
        }
        $this->participantModel->update($participantId, [
            'is_boarded' => 1,
            'boarded_at' => date('Y-m-d H:i:s'),
        ]);
        return redirect()->back()->with('msg', 'Verifikasi keseluruhan (boarding) berhasil dicatat.');
    }

    /**
     * Menu Boarding: daftar jamaah dengan tanggal, maskapai, nama; klik Boarding buka modal konfirmasi.
     */
    public function boardingList()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');
        $packageModel = new PackageModel();
        $packages = $packageModel->orderBy('departure_date', 'DESC')->findAll();

        $builder = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.airline, travel_packages.price as package_price, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.status', 'verified')
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
        $paymentModel = new PaymentModel();
        $docModel = new DocumentModel();

        foreach ($participants as &$p) {
            $p['total_paid'] = (float)($paymentModel->getTotalPaid($p['id'])['amount'] ?? 0);
            $p['total_target'] = (float)($p['package_price'] ?? 0) + (float)($p['upgrade_cost'] ?? 0);
            $p['pembayaran_lunas'] = $p['total_target'] > 0 && $p['total_paid'] >= $p['total_target'];
            $docCount = $docModel->where('participant_id', $p['id'])->where('is_verified', 1)->countAllResults();
            $p['doc_progress'] = $docCount >= 7 ? 100 : round(($docCount / 7) * 100);
            $p['berkas_lengkap'] = ($p['doc_progress'] >= 100);
            $eff = participant_effective_departure($p['package_departure_date'] ?? null, $p['departure_note'] ?? null);
            $p['days_until'] = participant_days_until_departure($eff);
            $p['boarding_departure_label'] = $eff ? date('d/m/Y H:i', strtotime($eff)) : '—';
            $p['can_boarding'] = ($p['berkas_lengkap'] && $p['pembayaran_lunas'] && $p['days_until'] !== null && $p['days_until'] <= 15);
        }
        unset($p);

        $data = [
            'participants' => $participants,
            'packages' => $packages,
            'selected_package' => $packageId,
            'departure_date_from' => $departureFrom,
            'departure_date_to' => $departureTo,
        ];
        return view('owner/participant/boarding_list', $data);
    }

    /**
     * Cetak daftar list jamaah boarding (filter sama dengan boarding list).
     */
    public function boardingListPrint()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');

        $builder = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.airline, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.status', 'verified')
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
        foreach ($participants as &$p) {
            $eff = participant_effective_departure($p['package_departure_date'] ?? null, $p['departure_note'] ?? null);
            $p['boarding_departure_label'] = $eff ? date('d/m/Y H:i', strtotime($eff)) : '—';
        }
        unset($p);

        $userModel = new UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $companyName = $owner['company_name'] ?? 'Perusahaan';
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : '';

        $data = [
            'participants' => $participants,
            'company_name' => $companyName,
            'company_logo_url' => $companyLogo,
            'departure_date_from' => $departureFrom,
            'departure_date_to' => $departureTo,
        ];
        return view('owner/participant/boarding_list_print', $data);
    }

    /**
     * Export daftar jamaah boarding ke Excel (CSV) sesuai filter.
     */
    public function boardingListExport()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');

        $builder = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.airline, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.status', 'verified')
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

        $filename = 'daftar-boarding-jamaah-' . date('Y-m-d-His') . '.csv';
        $out = fopen('php://temp', 'r+');
        fprintf($out, "\xEF\xBB\xBF"); // UTF-8 BOM agar Excel baca benar

        $headers = ['No', 'Tanggal Berangkat', 'Maskapai', 'Nama Jamaah', 'NIK', 'Agensi', 'Paket', 'Telepon', 'Status Boarding'];
        fputcsv($out, $headers, ';');

        foreach ($participants as $i => $p) {
            $eff = participant_effective_departure($p['package_departure_date'] ?? null, $p['departure_note'] ?? null);
            $row = [
                $i + 1,
                $eff ? date('d/m/Y H:i', strtotime($eff)) : '',
                $p['airline'] ?? '',
                $p['name'] ?? '',
                $p['nik'] ?? '',
                $p['agency_name'] ?? '',
                $p['package_name'] ?? '',
                $p['phone'] ?? '',
                !empty($p['is_boarded']) ? 'Boarding' : 'Belum',
            ];
            fputcsv($out, $row, ';');
        }
        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csv);
    }

    public function boardingManifest($packageId)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $packageModel = new PackageModel();
        $package = $packageModel->find($packageId);

        // Get boarded participants
        $participants = $this->participantModel->getParticipantBuilder()
            ->join('travel_hotels', 'travel_hotels.id = participants.hotel_upgrade_id', 'left')
            ->join('travel_hotel_rooms', 'travel_hotel_rooms.id = participants.room_upgrade_id', 'left')
            ->select('participants.*, travel_hotels.name as hotel_name, travel_hotel_rooms.name as room_name, travel_hotel_rooms.type as room_type')
            ->where('participants.package_id', $packageId)
            ->where('participants.status', 'verified')
            ->where('participants.is_boarded', 1)
            ->orderBy('participants.name', 'ASC')
            ->findAll();

        $data = [
            'package' => $package,
            'participants' => $participants,
            'title' => 'Manifest Keberangkatan - ' . $package['name']
        ];

        return view('owner/reports/boarding_manifest', $data);
    }

    /**
     * Daftar jamaah yang dibatalkan (menu Pembatalan).
     */
    public function cancellations()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $search = trim($this->request->getGet('search') ?? '');
        $dateFrom = $this->request->getGet('date_from') ?? '';
        $dateTo = $this->request->getGet('date_to') ?? '';

        $builder = $this->participantModel->getParticipantBuilder()
            ->where('participants.status', 'cancelled')
            ->orderBy('participants.cancelled_at', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('participants.name', $search)
                ->orLike('participants.nik', $search)
                ->groupEnd();
        }
        if ($dateFrom !== '') {
            $builder->where('DATE(participants.cancelled_at) >=', $dateFrom);
        }
        if ($dateTo !== '') {
            $builder->where('DATE(participants.cancelled_at) <=', $dateTo);
        }

        $list = $builder->findAll();

        $paymentModel = new PaymentModel();
        foreach ($list as &$row) {
            $row['total_paid'] = ($paymentModel->getTotalPaid($row['id'])['amount'] ?? 0);
        }

        $data = [
            'list' => $list,
            'search' => $search,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
        return view('owner/participant/cancellations', $data);
    }

    /**
     * Form pembatalan jamaah: nominal refund default = total dibayar, admin bisa ubah.
     */
    public function cancelForm($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.price as package_price, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.id', $id)
            ->first();

        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($participant['status'] === 'cancelled') {
            return redirect()->to('owner/participant/cancellations')->with('error', 'Jamaah ini sudah dibatalkan.');
        }

        $paymentModel = new PaymentModel();
        $totalPaid = ($paymentModel->getTotalPaid($id)['amount'] ?? 0);
        $defaultRefund = $totalPaid;

        $data = [
            'participant' => $participant,
            'total_paid' => $totalPaid,
            'default_refund' => $defaultRefund,
        ];
        return view('owner/participant/cancel_form', $data);
    }

    /**
     * Simpan pembatalan: status cancelled, refund_amount, cancellation_notes, cancelled_at.
     */
    public function storeCancellation()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participantId = (int) $this->request->getPost('participant_id');
        $refundAmount = $this->request->getPost('refund_amount');
        $notes = $this->request->getPost('cancellation_notes');
        $refundRekening = trim($this->request->getPost('refund_rekening') ?? '');
        $refundBankName = trim($this->request->getPost('refund_bank_name') ?? '');

        $p = $this->participantModel->find($participantId);
        if (!$p) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($p['status'] === 'cancelled') {
            return redirect()->to('owner/participant/cancellations')->with('error', 'Jamaah ini sudah dibatalkan.');
        }
        if (empty($refundRekening)) {
            return redirect()->back()->withInput()->with('error', 'No. rekening yang ditransfer wajib diisi.');
        }

        $refund = is_numeric($refundAmount) ? (float) $refundAmount : 0;

        $this->participantModel->update($participantId, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'refund_amount' => $refund,
            'cancellation_notes' => $notes,
            'refund_rekening' => $refundRekening,
            'refund_bank_name' => $refundBankName ?: null,
        ]);

        return redirect()->to('owner/participant/cancellations')->with('msg', 'Pembatalan berhasil dicatat. Refund: Rp ' . number_format($refund, 0, ',', '.'));
    }

    /**
     * Aktifkan kembali jamaah yang dibatalkan (status cancelled → pending).
     */
    public function reactivate($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $p = $this->participantModel->find($id);
        if (!$p) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($p['status'] !== 'cancelled') {
            return redirect()->to('owner/participant/kelola/' . $id)->with('error', 'Jamaah tidak dalam status batal.');
        }
        $this->participantModel->update($id, [
            'status' => 'pending',
            'cancelled_at' => null,
            'refund_amount' => null,
            'cancellation_notes' => null,
            'refund_rekening' => null,
            'refund_bank_name' => null,
        ]);
        return redirect()->to('owner/participant/kelola/' . $id)->with('msg', 'Jamaah berhasil diaktifkan kembali.');
    }

    /**
     * Cetak surat pernyataan pembatalan beserta nominal refund dan no rekening.
     */
    public function cancellationStatement($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $participant = $this->participantModel->getParticipantBuilder()
            ->where('participants.id', $id)
            ->where('participants.status', 'cancelled')
            ->first();
        if (!$participant) {
            return redirect()->to('owner/participant/cancellations')->with('error', 'Data jamaah batal tidak ditemukan.');
        }
        $paymentModel = new PaymentModel();
        $participant['total_paid'] = ($paymentModel->getTotalPaid($id)['amount'] ?? 0);
        $userModel = new UserModel();
        $owner = $userModel->where('role', 'owner')->first();
        $companyName = $owner['company_name'] ?? 'Perusahaan';
        $data = [
            'participant' => $participant,
            'company_name' => $companyName,
            'company_logo_url' => !empty($owner['company_logo']) ? base_url($owner['company_logo']) : '',
            'company_slogan' => $owner['slogan'] ?? '',
            'company_address' => $owner['address'] ?? '',
            'company_email' => $owner['email'] ?? '',
            'company_phone' => $owner['phone'] ?? '',
            'no_sk_perijinan' => $owner['no_sk_perijinan'] ?? '',
            'tanggal_sk_perijinan' => $owner['tanggal_sk_perijinan'] ?? '',
            'nama_pemilik' => $owner['full_name'] ?? '',
        ];
        return view('owner/participant/cancellation_statement_print', $data);
    }

    /**
     * Registrasi jamaah langsung dari kantor (bukan dari agensi).
     * List paket untuk dipilih.
     */
    public function registerFromOffice()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $agencyId = $this->getKantorPusatAgencyId();
        if (!$agencyId) {
            return redirect()->to('owner/participant')->with('error', 'User Kantor Pusat belum diset. Jalankan migrasi.');
        }
        $packageModel = new PackageModel();
        $today = date('Y-m-d');
        $packages = $packageModel->where('is_active', 1)
            ->where('departure_date >=', $today)
            ->orderBy('departure_date', 'ASC')
            ->findAll();
        $data = ['packages' => $packages];
        return view('owner/participant/register_list', $data);
    }

    /**
     * Form pendaftaran jamaah untuk paket tertentu (dari kantor).
     */
    public function registerFromOfficeForm($package_id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $agencyId = $this->getKantorPusatAgencyId();
        if (!$agencyId) {
            return redirect()->to('owner/participant')->with('error', 'User Kantor Pusat belum diset.');
        }
        $packageModel = new PackageModel();
        $package = $packageModel->find($package_id);
        if (!$package) {
            return redirect()->to('owner/participant/register')->with('error', 'Paket tidak ditemukan.');
        }
        if (empty($package['is_active'])) {
            return redirect()->to('owner/participant/register')->with('error', 'Paket tidak dapat menerima pendaftaran (kuota penuh).');
        }
        $depDate = isset($package['departure_date']) ? substr((string)$package['departure_date'], 0, 10) : '';
        if ($depDate !== '' && $depDate < date('Y-m-d')) {
            return redirect()->to('owner/participant/register')->with('error', 'Paket sudah expired. Tidak dapat mendaftarkan jamaah.');
        }
        $data = ['package' => $package];
        return view('owner/participant/register_form', $data);
    }

    /**
     * Simpan pendaftaran jamaah dari kantor (agency_id = kantor pusat).
     */
    public function storeRegistrationFromOffice()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $agencyId = $this->getKantorPusatAgencyId();
        if (!$agencyId) {
            return redirect()->back()->with('error', 'User Kantor Pusat belum diset.');
        }

        $rules = [
            'package_id' => 'required',
            'nik' => 'required|min_length[16]|max_length[20]',
            'name' => 'required|min_length[3]',
            'place_of_birth' => 'required',
            'date_of_birth' => 'required|valid_date',
            'gender' => 'required',
            'phone' => 'required|min_length[8]',
            'address' => 'required',
            'kecamatan' => 'required',
            'kabupaten' => 'required',
            'provinsi' => 'required',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Mohon lengkapi seluruh biodata wajib sesuai KTP.');
        }

        $packageId = (int) $this->request->getPost('package_id');
        $packageModel = new PackageModel();
        $package = $packageModel->find($packageId);
        if (!$package) {
            return redirect()->back()->withInput()->with('error', 'Paket tidak ditemukan.');
        }
        if (empty($package['is_active'])) {
            return redirect()->back()->withInput()->with('error', 'Paket tidak dapat menerima pendaftaran (kuota penuh).');
        }
        $depDate = isset($package['departure_date']) ? substr((string)$package['departure_date'], 0, 10) : '';
        if ($depDate !== '' && $depDate < date('Y-m-d')) {
            return redirect()->back()->withInput()->with('error', 'Paket sudah expired. Tidak dapat mendaftarkan jamaah.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $participantData = [
            'package_id' => $packageId,
            'agency_id' => $agencyId,
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
            'nationality' => $this->request->getPost('nationality') ?? 'WNI',
            'phone' => $this->request->getPost('phone'),
            'status' => 'pending',
        ];

        $participantId = $this->participantModel->insert($participantData);

        $docTypes = ['passport', 'id_card', 'kk', 'vaccine'];
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
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses pendaftaran.');
        }

        return redirect()->to('owner/participant')->with('msg', 'Pendaftaran jamaah ' . $participantData['name'] . ' dari kantor berhasil. Mohon verifikasi berkas.');
    }

    private function getKantorPusatAgencyId()
    {
        $userModel = new UserModel();
        $user = $userModel->where('username', 'kantor_pusat')->first();
        return $user ? (int) $user['id'] : null;
    }

    /**
     * Form tambah pembayaran jamaah dari kantor (admin input langsung).
     */
    public function addPayment($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.id', $id)
            ->first();

        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($participant['status'] === 'cancelled') {
            return redirect()->back()->with('error', 'Jamaah sudah dibatalkan.');
        }

        $paymentModel = new PaymentModel();
        $totalPaid = ($paymentModel->getTotalPaid($id)['amount'] ?? 0);
        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);

        $data = [
            'participant' => $participant,
            'total_paid' => $totalPaid,
            'total_target' => $totalTarget,
        ];
        return view('owner/participant/add_payment', $data);
    }

    /**
     * Simpan pembayaran dari kantor (status verified otomatis).
     */
    public function storePaymentFromOffice()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participantId = (int) $this->request->getPost('participant_id');
        $amount = $this->request->getPost('amount');
        $paymentDate = $this->request->getPost('payment_date');
        $notes = $this->request->getPost('notes');

        $participant = $this->participantModel->find($participantId);
        if (!$participant) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($participant['status'] === 'cancelled') {
            return redirect()->back()->with('error', 'Jamaah sudah dibatalkan.');
        }

        $rules = [
            'participant_id' => 'required|integer',
            'amount' => 'required|numeric',
            'payment_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Mohon isi nominal dan tanggal pembayaran dengan benar.');
        }

        $proofPath = null;
        $file = $this->request->getFile('proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move('uploads/payments', $newName);
            $proofPath = 'uploads/payments/' . $newName;
        }

        $paymentModel = new PaymentModel();
        $paymentModel->insert([
            'participant_id' => $participantId,
            'amount' => $amount,
            'payment_date' => $paymentDate,
            'notes' => $notes,
            'proof' => $proofPath,
            'status' => 'verified',
        ]);

        return redirect()->to('owner/participant/kelola/' . $participantId)->with('msg', 'Pembayaran dari kantor berhasil dicatat (Rp ' . number_format((float) $amount, 0, ',', '.') . ').');
    }
}

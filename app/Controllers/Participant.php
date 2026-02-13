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
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $keyword = $this->request->getGet('search');
        $status = $this->request->getGet('status');

        $builder = $this->participantModel->getParticipantBuilder();

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
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $docModel = new DocumentModel();

        if ($id) {
            $participant = $this->participantModel->find($id);
            if (!$participant) {
                return redirect()->to('owner/participant/documents')->with('error', 'Jamaah tidak ditemukan.');
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

        $data = [
            'participants' => $filteredParticipants,
            'search' => $search,
            'status' => $status
        ];
        return view('owner/participant/documents_list', $data);
    }

    public function verifyDocument()
    {
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
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

    public function receipt($id)
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel->getParticipantBuilder()->where('participants.id', $id)->get()->getRowArray();
        if (!$participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }

        $paymentModel = new PaymentModel();
        $payments = $paymentModel->where('participant_id', $id)
            ->where('status', 'verified')
            ->orderBy('payment_date', 'ASC')
            ->findAll();

        $data = [
            'participant' => $participant,
            'payments' => $payments,
            'title' => 'Kwitansi Pendaftaran - ' . $participant['name']
        ];

        return view('owner/participant/receipt_print', $data);
    }

    /**
     * Cetak formulir pendaftaran lengkap: kop, paket, hotel, pembayaran, fasilitas, checklist atribut, tanda tangan digital.
     */
    public function registrationFormPrint($id)
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, travel_packages.departure_date as package_departure_date, travel_packages.freebies as package_freebies, travel_packages.inclusions as package_inclusions, travel_packages.hotel_mekkah_id, travel_packages.hotel_madinah_id, users.full_name as agency_name, travel_hotels.name as hotel_upgrade_name, travel_hotel_rooms.name as room_upgrade_name, travel_hotel_rooms.type as room_upgrade_type')
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

    public function paymentHistory($id)
    {
        if (session()->get('role') != 'owner') {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }

        $paymentModel = new PaymentModel();
        $history = $paymentModel->where('participant_id', $id)
            ->orderBy('payment_date', 'DESC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $history
        ]);
    }

    public function transactionReceipt($id)
    {
        if (session()->get('role') != 'owner') {
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

        $userModel = new UserModel();
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
            'title' => 'Kwitansi Pembayaran - ' . $participant['name']
        ];

        return view('owner/participant/payment_receipt_print', $data);
    }

    /**
     * Halaman kelola jamaah: monitoring berkas & pembayaran, ganti jadwal, hotel, kamar.
     */
    public function kelola($id)
    {
        if (session()->get('role') != 'owner') {
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

        $departure = $participant['package_departure_date'] ?? null;
        $daysUntilDeparture = null;
        $allow_ubah_jadwal_hotel = false;
        if ($departure) {
            $today = date('Y-m-d');
            $dep = date('Y-m-d', strtotime($departure));
            $daysUntilDeparture = (int) floor((strtotime($dep) - strtotime($today)) / 86400);
            $allow_ubah_jadwal_hotel = ($daysUntilDeparture >= 30) && ($participant['status'] !== 'cancelled');
        }

        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $totalPaid = (float)($paid['amount'] ?? 0);
        $berkas_lengkap = ($stats['progress'] >= 100);
        $pembayaran_lunas = ($totalTarget > 0 && $totalPaid >= $totalTarget);
        $h15_or_less = ($daysUntilDeparture !== null && $daysUntilDeparture <= 15);
        $can_boarding = ($participant['status'] !== 'cancelled' && $participant['status'] === 'verified' && $berkas_lengkap && $pembayaran_lunas && $h15_or_less);

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
        ];
        return view('owner/participant/kelola', $data);
    }

    private function checkH30ForChange($participantId)
    {
        $p = $this->participantModel
            ->select('participants.*, travel_packages.departure_date')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', $participantId)
            ->first();
        if (!$p || $p['status'] === 'cancelled') {
            return ['allowed' => false, 'message' => 'Jamaah tidak ditemukan atau sudah dibatalkan.'];
        }
        $dep = $p['departure_date'] ?? null;
        if (!$dep) {
            return ['allowed' => false, 'message' => 'Jadwal keberangkatan tidak ditemukan.'];
        }
        $today = date('Y-m-d');
        $depDate = date('Y-m-d', strtotime($dep));
        $days = (int) floor((strtotime($depDate) - strtotime($today)) / 86400);
        if ($days < 30) {
            return ['allowed' => false, 'message' => 'Perubahan jadwal/hotel/kamar hanya dapat dilakukan minimal H-30 sebelum keberangkatan. Saat ini H-' . abs($days) . '.'];
        }
        return ['allowed' => true];
    }

    /**
     * Ganti jadwal pemberangkatan (ubah paket).
     */
    public function updateSchedule()
    {
        if (session()->get('role') != 'owner') {
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
        $this->participantModel->update($participantId, ['package_id' => $packageId]);
        return redirect()->back()->with('msg', 'Jadwal pemberangkatan berhasil diubah.');
    }

    public function getUpgradeOptions($id)
    {
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
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
                    ->update();
            }

            // 2. Mark unselected (in this package) as not boarded
            $allInPackage = $this->participantModel->where('package_id', $packageId)->findColumn('id') ?? [];
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
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }
        $participantId = (int) $this->request->getPost('participant_id');
        $participant = $this->participantModel
            ->select('participants.*, travel_packages.departure_date, travel_packages.price as package_price')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.id', $participantId)
            ->first();
        if (!$participant || $participant['status'] === 'cancelled') {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan atau sudah dibatalkan.');
        }
        $stats = $this->getDocStats($participantId);
        $paymentModel = new PaymentModel();
        $totalPaid = (float)($paymentModel->getTotalPaid($participantId)['amount'] ?? 0);
        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $dep = $participant['departure_date'] ?? null;
        $days = null;
        if ($dep) {
            $days = (int) floor((strtotime(date('Y-m-d', strtotime($dep))) - strtotime(date('Y-m-d'))) / 86400);
        }
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
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');
        $packageModel = new PackageModel();
        $packages = $packageModel->orderBy('departure_date', 'DESC')->findAll();

        $builder = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date, travel_packages.airline, travel_packages.price as package_price, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
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
        $paymentModel = new PaymentModel();
        $docModel = new DocumentModel();

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
        return view('owner/participant/boarding_list', $data);
    }

    /**
     * Cetak daftar list jamaah boarding (filter sama dengan boarding list).
     */
    public function boardingListPrint()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }
        $packageId = $this->request->getGet('package_id');
        $departureFrom = $this->request->getGet('departure_date_from');
        $departureTo = $this->request->getGet('departure_date_to');

        $builder = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date, travel_packages.airline, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
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

    public function boardingManifest($packageId)
    {
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $paymentModel = new PaymentModel();
        $list = $this->participantModel->getParticipantBuilder()
            ->where('participants.status', 'cancelled')
            ->orderBy('participants.cancelled_at', 'DESC')
            ->findAll();

        foreach ($list as &$row) {
            $row['total_paid'] = ($paymentModel->getTotalPaid($row['id'])['amount'] ?? 0);
        }

        $data = ['list' => $list];
        return view('owner/participant/cancellations', $data);
    }

    /**
     * Form pembatalan jamaah: nominal refund default (H-30 full, else kena biaya), admin bisa ubah.
     */
    public function cancelForm($id)
    {
        if (session()->get('role') != 'owner') {
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
        $departure = $participant['package_departure_date'] ?? null;
        $daysUntil = null;
        if ($departure) {
            $today = date('Y-m-d');
            $dep = date('Y-m-d', strtotime($departure));
            $daysUntil = (int) floor((strtotime($dep) - strtotime($today)) / 86400);
        }

        $totalTarget = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
        $feePercent = 10;
        if ($daysUntil !== null && $daysUntil >= 30) {
            $defaultRefund = $totalPaid;
            $refundNote = 'Refund penuh (pembatalan H-30 atau lebih).';
        } else {
            $fee = $totalTarget * ($feePercent / 100);
            $defaultRefund = max(0, $totalPaid - $fee);
            $refundNote = 'Pembatalan kurang dari H-30: dikenakan biaya ' . $feePercent . '% dari total paket (Rp ' . number_format($fee, 0, ',', '.') . '). Nominal refund default = total dibayar dikurangi biaya.';
        }

        $data = [
            'participant' => $participant,
            'total_paid' => $totalPaid,
            'days_until_departure' => $daysUntil,
            'default_refund' => $defaultRefund,
            'refund_note' => $refundNote,
        ];
        return view('owner/participant/cancel_form', $data);
    }

    /**
     * Simpan pembatalan: status cancelled, refund_amount, cancellation_notes, cancelled_at.
     */
    public function storeCancellation()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $participantId = (int) $this->request->getPost('participant_id');
        $refundAmount = $this->request->getPost('refund_amount');
        $notes = $this->request->getPost('cancellation_notes');

        $p = $this->participantModel->find($participantId);
        if (!$p) {
            return redirect()->to('owner/participant')->with('error', 'Jamaah tidak ditemukan.');
        }
        if ($p['status'] === 'cancelled') {
            return redirect()->to('owner/participant/cancellations')->with('error', 'Jamaah ini sudah dibatalkan.');
        }

        $refund = is_numeric($refundAmount) ? (float) $refundAmount : 0;

        $this->participantModel->update($participantId, [
            'status' => 'cancelled',
            'cancelled_at' => date('Y-m-d H:i:s'),
            'refund_amount' => $refund,
            'cancellation_notes' => $notes,
        ]);

        return redirect()->to('owner/participant/cancellations')->with('msg', 'Pembatalan berhasil dicatat. Refund: Rp ' . number_format($refund, 0, ',', '.'));
    }

    /**
     * Registrasi jamaah langsung dari kantor (bukan dari agensi).
     * List paket untuk dipilih.
     */
    public function registerFromOffice()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }
        $agencyId = $this->getKantorPusatAgencyId();
        if (!$agencyId) {
            return redirect()->to('owner/participant')->with('error', 'User Kantor Pusat belum diset. Jalankan migrasi.');
        }
        $packageModel = new PackageModel();
        $packages = $packageModel->orderBy('departure_date', 'DESC')->findAll();
        $data = ['packages' => $packages];
        return view('owner/participant/register_list', $data);
    }

    /**
     * Form pendaftaran jamaah untuk paket tertentu (dari kantor).
     */
    public function registerFromOfficeForm($package_id)
    {
        if (session()->get('role') != 'owner') {
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
        $data = ['package' => $package];
        return view('owner/participant/register_form', $data);
    }

    /**
     * Simpan pendaftaran jamaah dari kantor (agency_id = kantor pusat).
     */
    public function storeRegistrationFromOffice()
    {
        if (session()->get('role') != 'owner') {
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

        $db = \Config\Database::connect();
        $db->transStart();

        $participantData = [
            'package_id' => $this->request->getPost('package_id'),
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
        if (session()->get('role') != 'owner') {
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
        if (session()->get('role') != 'owner') {
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

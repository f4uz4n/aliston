<?php

namespace App\Controllers;

use App\Models\TravelSavingModel;
use App\Models\TravelSavingDepositModel;
use App\Models\UserModel;
use App\Models\PackageModel;
use App\Models\ParticipantModel;
use App\Models\PaymentModel;

class Tabungan extends BaseController
{
    protected $savingModel;
    protected $depositModel;
    protected $userModel;
    protected $packageModel;
    protected $participantModel;
    protected $paymentModel;

    public function __construct()
    {
        $this->savingModel = new TravelSavingModel();
        $this->depositModel = new TravelSavingDepositModel();
        $this->userModel = new UserModel();
        $this->packageModel = new PackageModel();
        $this->participantModel = new ParticipantModel();
        $this->paymentModel = new PaymentModel();
    }

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $status = $this->request->getGet('status');
        $cari = trim((string) $this->request->getGet('cari'));
        $builder = $this->savingModel->getWithAgency();
        if ($status !== null && $status !== '') {
            $builder->where('travel_savings.status', $status);
        }
        if ($cari !== '') {
            $builder->groupStart()
                ->like('travel_savings.nik', $cari)
                ->orLike('travel_savings.name', $cari)
                ->groupEnd();
        }
        $savings = $builder->findAll();
        $pending_deposits = $this->depositModel->getPendingWithSavingAndAgency();
        $activeTab = $this->request->getGet('tab');
        if (!in_array($activeTab, ['menabung', 'klaim'])) {
            $activeTab = 'menabung';
        }

        // Ambil semua setoran (pending dan verified) untuk setiap tabungan
        $all_deposits_by_saving = [];
        foreach ($savings as $s) {
            $deposits = $this->depositModel
                ->select('travel_savings_deposits.*, travel_savings.name as saving_name, travel_savings.nik as saving_nik, travel_savings.phone as saving_phone, users.full_name as agency_name, users.nomor_rekening, users.nama_bank')
                ->join('travel_savings', 'travel_savings.id = travel_savings_deposits.travel_saving_id')
                ->join('users', 'users.id = travel_savings.agency_id')
                ->where('travel_savings_deposits.travel_saving_id', $s['id'])
                ->orderBy('travel_savings_deposits.payment_date', 'DESC')
                ->orderBy('travel_savings_deposits.created_at', 'DESC')
                ->findAll();
            $all_deposits_by_saving[$s['id']] = $deposits;
        }

        $data = [
            'savings' => $savings,
            'pending_deposits' => $pending_deposits,
            'all_deposits_by_saving' => $all_deposits_by_saving,
            'filterStatus' => $status,
            'filterCari' => $cari,
            'activeTab' => $activeTab,
            'title' => 'Tabungan Perjalanan',
        ];
        return view('owner/tabungan/index', $data);
    }

    /**
     * Laporan tabungan (khusus pemilik): total saldo, jumlah pembayaran, dan komposisi MOU.
     */
    public function report()
    {
        if (! is_owner()) {
            return redirect()->to('owner')->with('error', 'Hanya pemilik yang dapat membuka laporan tabungan.');
        }

        $db = \Config\Database::connect();
        $startDate = (string) ($this->request->getGet('start_date') ?? '');
        $endDate = (string) ($this->request->getGet('end_date') ?? '');
        $agencyId = (string) ($this->request->getGet('agency_id') ?? '');
        $institutionName = trim((string) ($this->request->getGet('institution_name') ?? ''));

        $applySavingFilter = function ($builder) use ($startDate, $endDate, $agencyId, $institutionName) {
            if ($startDate !== '') {
                $builder->where('travel_savings.created_at >=', $startDate . ' 00:00:00');
            }
            if ($endDate !== '') {
                $builder->where('travel_savings.created_at <=', $endDate . ' 23:59:59');
            }
            if ($agencyId !== '') {
                $builder->where('travel_savings.agency_id', (int) $agencyId);
            }
            if ($institutionName !== '') {
                $builder->where('travel_savings.registration_type', 'mou');
                $builder->where('travel_savings.institution_name', $institutionName);
            }
            return $builder;
        };

        $applyDepositFilter = function ($builder) use ($startDate, $endDate, $agencyId, $institutionName) {
            if ($startDate !== '') {
                $builder->where('travel_savings_deposits.payment_date >=', $startDate);
            }
            if ($endDate !== '') {
                $builder->where('travel_savings_deposits.payment_date <=', $endDate);
            }
            if ($agencyId !== '') {
                $builder->where('travel_savings.agency_id', (int) $agencyId);
            }
            if ($institutionName !== '') {
                $builder->where('travel_savings.registration_type', 'mou');
                $builder->where('travel_savings.institution_name', $institutionName);
            }
            return $builder;
        };

        $savingStatsBuilder = $db->table('travel_savings')
            ->select('COUNT(id) as total_jamaah_tabungan, COALESCE(SUM(total_balance),0) as total_saldo_tabungan');
        $savingStats = $applySavingFilter($savingStatsBuilder)->get()->getRowArray() ?? [];

        $depositStatsBuilder = $db->table('travel_savings_deposits')
            ->select('COUNT(travel_savings_deposits.id) as jumlah_pembayaran, COALESCE(SUM(travel_savings_deposits.amount),0) as total_nominal_pembayaran')
            ->join('travel_savings', 'travel_savings.id = travel_savings_deposits.travel_saving_id')
            ->where('travel_savings_deposits.status', 'verified');
        $depositStats = $applyDepositFilter($depositStatsBuilder)->get()->getRowArray() ?? [];

        $typeStatsBuilder = $db->table('travel_savings')
            ->select("COALESCE(registration_type, 'mandiri') as registration_type, COUNT(id) as total_jamaah, COALESCE(SUM(total_balance),0) as total_saldo")
            ->groupBy("COALESCE(registration_type, 'mandiri')");
        $typeStats = $applySavingFilter($typeStatsBuilder)->get()->getResultArray();

        $mouInstitutionSummaryBuilder = $db->table('travel_savings')
            ->select('travel_savings.institution_name, COUNT(travel_savings.id) as total_jamaah, COALESCE(SUM(travel_savings.total_balance),0) as total_saldo')
            ->where('travel_savings.registration_type', 'mou')
            ->where('travel_savings.institution_name IS NOT NULL', null, false)
            ->where('travel_savings.institution_name !=', '')
            ->groupBy('travel_savings.institution_name')
            ->orderBy('total_saldo', 'DESC');
        $mouInstitutionSummary = $applySavingFilter($mouInstitutionSummaryBuilder)->get()->getResultArray();

        $agencySummaryBuilder = $db->table('travel_savings')
            ->select('users.full_name as agency_name, COUNT(travel_savings.id) as total_jamaah, COALESCE(SUM(travel_savings.total_balance),0) as total_saldo')
            ->join('users', 'users.id = travel_savings.agency_id')
            ->groupBy('travel_savings.agency_id')
            ->orderBy('total_saldo', 'DESC');
        $agencySummary = $applySavingFilter($agencySummaryBuilder)->get()->getResultArray();

        $recentDepositsBuilder = $db->table('travel_savings_deposits')
            ->select('travel_savings_deposits.*, travel_savings.name as saving_name, travel_savings.registration_type, users.full_name as agency_name')
            ->join('travel_savings', 'travel_savings.id = travel_savings_deposits.travel_saving_id')
            ->join('users', 'users.id = travel_savings.agency_id')
            ->where('travel_savings_deposits.status', 'verified')
            ->orderBy('travel_savings_deposits.payment_date', 'DESC')
            ->orderBy('travel_savings_deposits.id', 'DESC')
            ->limit(10);
        $recentDeposits = $applyDepositFilter($recentDepositsBuilder)->get()->getResultArray();

        $jamaahSavingSummaryBuilder = $db->table('travel_savings')
            ->select('travel_savings.id, travel_savings.name, travel_savings.nik, travel_savings.registration_type, travel_savings.institution_name, travel_savings.total_balance, users.full_name as agency_name, COUNT(travel_savings_deposits.id) as total_setoran, COALESCE(SUM(travel_savings_deposits.amount),0) as total_nominal_setoran')
            ->join('users', 'users.id = travel_savings.agency_id')
            ->join('travel_savings_deposits', "travel_savings_deposits.travel_saving_id = travel_savings.id AND travel_savings_deposits.status = 'verified'", 'left')
            ->groupBy('travel_savings.id')
            ->orderBy('total_setoran', 'DESC')
            ->orderBy('total_nominal_setoran', 'DESC')
            ->limit(100);
        $jamaahSavingSummary = $applySavingFilter($jamaahSavingSummaryBuilder)->get()->getResultArray();

        $agencies = $this->userModel->where('role', 'agency')->orderBy('full_name', 'ASC')->findAll();
        $institutions = $db->table('travel_savings')
            ->select('institution_name')
            ->where('registration_type', 'mou')
            ->where('institution_name IS NOT NULL', null, false)
            ->where('institution_name !=', '')
            ->groupBy('institution_name')
            ->orderBy('institution_name', 'ASC')
            ->get()
            ->getResultArray();

        return view('owner/tabungan/report', [
            'title' => 'Laporan Tabungan',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'agency_id' => $agencyId,
            'institution_name' => $institutionName,
            'agencies' => $agencies,
            'institutions' => $institutions,
            'total_jamaah_tabungan' => (int) ($savingStats['total_jamaah_tabungan'] ?? 0),
            'total_saldo_tabungan' => (float) ($savingStats['total_saldo_tabungan'] ?? 0),
            'jumlah_pembayaran' => (int) ($depositStats['jumlah_pembayaran'] ?? 0),
            'total_nominal_pembayaran' => (float) ($depositStats['total_nominal_pembayaran'] ?? 0),
            'type_stats' => $typeStats,
            'mou_institution_summary' => $mouInstitutionSummary,
            'agency_summary' => $agencySummary,
            'recent_deposits' => $recentDeposits,
            'jamaah_saving_summary' => $jamaahSavingSummary,
        ]);
    }

    public function create()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $agencies = $this->userModel->where('role', 'agency')->where('is_active', 1)->findAll();
        $kantor = $this->userModel->where('username', 'kantor_pusat')->first();
        if ($kantor) {
            $agencies = array_merge([$kantor], $agencies);
        }
        $data = [
            'agencies' => $agencies,
            'title' => 'Tambah Jamaah Tabungan',
        ];
        return view('owner/tabungan/create', $data);
    }

    public function store()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $registrationType = (string) ($this->request->getPost('registration_type') ?: 'mandiri');
        if (!in_array($registrationType, ['mandiri', 'mou'], true)) {
            $registrationType = 'mandiri';
        }

        $rules = [
            'agency_id' => 'required|integer',
            'registration_type' => 'required|in_list[mandiri,mou]',
            'notes' => 'permit_empty',
        ];

        if ($registrationType === 'mou') {
            $rules['institution_name'] = 'required|min_length[3]';
            $rules['institution_address'] = 'required|min_length[5]';
            $rules['institution_pic_name'] = 'required|min_length[3]';
            $rules['institution_phone'] = 'required|min_length[8]|max_length[20]';
            $rules['mou_file'] = 'uploaded[mou_file]|max_size[mou_file,5120]|mime_in[mou_file,image/jpeg,image/png,image/webp,application/pdf]';
            $rules['names.*'] = 'required|min_length[3]';
            $rules['niks.*'] = 'required|min_length[16]|max_length[20]';
            $rules['phones.*'] = 'permit_empty|max_length[20]';
        } else {
            $rules['name'] = 'required|min_length[3]';
            $rules['nik'] = 'required|min_length[16]|max_length[20]';
            $rules['phone'] = 'permit_empty|max_length[20]';
            $rules['ktp_file'] = 'uploaded[ktp_file]|max_size[ktp_file,5120]|mime_in[ktp_file,image/jpeg,image/png,image/webp,application/pdf]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $uploadDir = FCPATH . 'uploads/tabungan';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ktpFilePath = null;
        if ($registrationType === 'mandiri') {
            $ktpFile = $this->request->getFile('ktp_file');
            if ($ktpFile && $ktpFile->isValid() && !$ktpFile->hasMoved()) {
                $ktpName = $ktpFile->getRandomName();
                $ktpFile->move($uploadDir, $ktpName);
                $ktpFilePath = 'uploads/tabungan/' . $ktpName;
            }
        }

        $mouFilePath = null;
        if ($registrationType === 'mou') {
            $mouFile = $this->request->getFile('mou_file');
            if ($mouFile && $mouFile->isValid() && !$mouFile->hasMoved()) {
                $mouName = $mouFile->getRandomName();
                $mouFile->move($uploadDir, $mouName);
                $mouFilePath = 'uploads/tabungan/' . $mouName;
            }
        }

        $agencyId = (int) $this->request->getPost('agency_id');
        $institutionName = $registrationType === 'mou' ? (string) $this->request->getPost('institution_name') : null;
        $institutionAddress = $registrationType === 'mou' ? (string) $this->request->getPost('institution_address') : null;
        $institutionPicName = $registrationType === 'mou' ? (string) $this->request->getPost('institution_pic_name') : null;
        $institutionPhone = $registrationType === 'mou' ? (string) $this->request->getPost('institution_phone') : null;
        $groupRef = $registrationType === 'mou' ? ('MOU-' . date('YmdHis') . '-' . mt_rand(100, 999)) : null;

        $records = [];
        if ($registrationType === 'mou') {
            $names = (array) $this->request->getPost('names');
            $niks = (array) $this->request->getPost('niks');
            $phones = (array) $this->request->getPost('phones');
            $ktpFiles = $this->request->getFiles()['ktp_files'] ?? [];
            foreach ($names as $i => $nm) {
                $name = trim((string) $nm);
                $nik = trim((string) ($niks[$i] ?? ''));
                $phone = trim((string) ($phones[$i] ?? ''));
                if ($name === '' || $nik === '') {
                    continue;
                }

                $rowKtpPath = null;
                $rowKtpFile = $ktpFiles[$i] ?? null;
                if (! $rowKtpFile || ! $rowKtpFile->isValid() || $rowKtpFile->hasMoved()) {
                    return redirect()->back()->withInput()->with('error', 'Setiap jamaah MOU wajib upload KTP yang valid.');
                }
                if ($rowKtpFile->getSizeByUnit('kb') > 5120) {
                    return redirect()->back()->withInput()->with('error', 'Ukuran file KTP maksimal 5MB per jamaah.');
                }
                $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
                if (!in_array($rowKtpFile->getClientMimeType(), $allowedMime, true)) {
                    return redirect()->back()->withInput()->with('error', 'Format file KTP MOU harus JPG, PNG, WEBP, atau PDF.');
                }
                $rowKtpName = $rowKtpFile->getRandomName();
                $rowKtpFile->move($uploadDir, $rowKtpName);
                $rowKtpPath = 'uploads/tabungan/' . $rowKtpName;

                $records[] = [
                    'agency_id' => $agencyId,
                    'name' => $name,
                    'nik' => $nik,
                    'phone' => $phone !== '' ? $phone : null,
                    'total_balance' => 0,
                    'status' => 'menabung',
                    'notes' => $this->request->getPost('notes') ?: null,
                    'registration_type' => 'mou',
                    'ktp_file' => $rowKtpPath,
                    'mou_file' => $mouFilePath,
                    'institution_name' => $institutionName,
                    'institution_address' => $institutionAddress,
                    'institution_pic_name' => $institutionPicName,
                    'institution_phone' => $institutionPhone,
                    'group_ref' => $groupRef,
                ];
            }
        } else {
            $records[] = [
                'agency_id' => $agencyId,
                'name' => (string) $this->request->getPost('name'),
                'nik' => (string) $this->request->getPost('nik'),
                'phone' => $this->request->getPost('phone') ?: null,
                'total_balance' => 0,
                'status' => 'menabung',
                'notes' => $this->request->getPost('notes') ?: null,
                'registration_type' => 'mandiri',
                'ktp_file' => $ktpFilePath,
                'mou_file' => null,
                'institution_name' => null,
                'institution_address' => null,
                'institution_pic_name' => null,
                'institution_phone' => null,
                'group_ref' => null,
            ];
        }

        if (empty($records)) {
            return redirect()->back()->withInput()->with('error', 'Data jamaah belum lengkap.');
        }

        foreach ($records as $record) {
            if (!$this->savingModel->insert($record)) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
            }
        }

        if ($registrationType === 'mou') {
            return redirect()->to('owner/tabungan')->with('msg', count($records) . ' jamaah tabungan MOU berhasil didaftarkan.');
        }
        return redirect()->to('owner/tabungan')->with('msg', 'Jamaah tabungan mandiri berhasil didaftarkan.');
    }

    /**
     * Form edit jamaah tabungan (hanya status menabung).
     */
    public function edit($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $saving = $this->savingModel->find($id);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $agencies = $this->userModel->where('role', 'agency')->where('is_active', 1)->findAll();
        $kantor = $this->userModel->where('username', 'kantor_pusat')->first();
        if ($kantor) {
            $agencies = array_merge([$kantor], $agencies);
        }
        $data = [
            'saving' => $saving,
            'agencies' => $agencies,
            'title' => 'Edit Jamaah Tabungan',
        ];
        return view('owner/tabungan/edit', $data);
    }

    /**
     * Update data jamaah tabungan (hanya status menabung).
     */
    public function update($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $saving = $this->savingModel->find($id);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $rules = [
            'agency_id' => 'required|integer',
            'name' => 'required|min_length[3]',
            'nik' => 'required|min_length[16]|max_length[20]',
            'phone' => 'permit_empty',
            'ktp_file' => 'permit_empty|max_size[ktp_file,5120]|mime_in[ktp_file,image/jpeg,image/png,image/webp,application/pdf]',
        ];
        $registrationType = (string)($saving['registration_type'] ?? 'mandiri');
        if ($registrationType === 'mou') {
            $rules['institution_name'] = 'required|min_length[3]';
            $rules['institution_address'] = 'required|min_length[5]';
            $rules['institution_pic_name'] = 'required|min_length[3]';
            $rules['institution_phone'] = 'required|min_length[8]|max_length[20]';
            $rules['mou_file'] = 'permit_empty|max_size[mou_file,5120]|mime_in[mou_file,image/jpeg,image/png,image/webp,application/pdf]';
        }
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $uploadDir = FCPATH . 'uploads/tabungan';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ktpPath = $saving['ktp_file'] ?? null;
        $ktpFile = $this->request->getFile('ktp_file');
        if ($ktpFile && $ktpFile->isValid() && !$ktpFile->hasMoved()) {
            if (!empty($ktpPath) && is_file(FCPATH . $ktpPath)) {
                @unlink(FCPATH . $ktpPath);
            }
            $newKtp = $ktpFile->getRandomName();
            $ktpFile->move($uploadDir, $newKtp);
            $ktpPath = 'uploads/tabungan/' . $newKtp;
        }

        $mouPath = $saving['mou_file'] ?? null;
        if ($registrationType === 'mou') {
            $mouFile = $this->request->getFile('mou_file');
            if ($mouFile && $mouFile->isValid() && !$mouFile->hasMoved()) {
                if (!empty($mouPath) && is_file(FCPATH . $mouPath)) {
                    @unlink(FCPATH . $mouPath);
                }
                $newMou = $mouFile->getRandomName();
                $mouFile->move($uploadDir, $newMou);
                $mouPath = 'uploads/tabungan/' . $newMou;
            }
        } else {
            $mouPath = null;
        }

        $data = [
            'agency_id' => (int) $this->request->getPost('agency_id'),
            'name' => $this->request->getPost('name'),
            'nik' => $this->request->getPost('nik'),
            'phone' => $this->request->getPost('phone') ?: null,
            'notes' => $this->request->getPost('notes') ?: null,
            'ktp_file' => $ktpPath,
            'mou_file' => $mouPath,
            'institution_name' => $registrationType === 'mou' ? ($this->request->getPost('institution_name') ?: null) : null,
            'institution_address' => $registrationType === 'mou' ? ($this->request->getPost('institution_address') ?: null) : null,
            'institution_pic_name' => $registrationType === 'mou' ? ($this->request->getPost('institution_pic_name') ?: null) : null,
            'institution_phone' => $registrationType === 'mou' ? ($this->request->getPost('institution_phone') ?: null) : null,
        ];
        if ($this->savingModel->update($id, $data)) {
            return redirect()->to('owner/tabungan')->with('msg', 'Data tabungan berhasil diperbarui.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal memperbarui data.');
    }

    /**
     * Hapus jamaah tabungan (hanya status menabung, dan saldo = 0 atau konfirmasi).
     */
    public function delete($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $saving = $this->savingModel->find($id);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $db = \Config\Database::connect();
        $db->transStart();
        $this->depositModel->where('travel_saving_id', $id)->delete();
        $this->savingModel->delete($id);
        $db->transComplete();
        if ($db->transStatus() === false) {
            return redirect()->to('owner/tabungan')->with('error', 'Gagal menghapus data tabungan.');
        }
        return redirect()->to('owner/tabungan')->with('msg', 'Jamaah tabungan berhasil dihapus.');
    }

    /**
     * Cetak kwitansi tabungan (Print, Download PDF, Share WA) — sama seperti kwitansi lain.
     */
    public function receipt($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $saving = $this->savingModel->getWithAgency()->where('travel_savings.id', $id)->first();
        if (!$saving) {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan.');
        }
        $owner = $this->userModel->where('role', 'owner')->first();
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');
        $companyName = $owner['company_name'] ?? 'Nama Perusahaan';
        $companyAddress = $owner['address'] ?? '';
        $namaPenerima = !empty($owner['nama_sekretaris_bendahara']) ? $owner['nama_sekretaris_bendahara'] : ($owner['full_name'] ?? '—');
        $data = [
            'saving' => $saving,
            'company_logo_url' => $companyLogo,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'nama_direktur' => $namaPenerima,
            'title' => 'Kwitansi Tabungan - ' . $saving['name'],
        ];
        return view('owner/print/tabungan_receipt', $data);
    }

    public function addDeposit($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $saving = $this->savingModel->find($id);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $saving['agency_name'] = $this->userModel->find($saving['agency_id'])['full_name'] ?? '-';
        $deposits = $this->depositModel->getBySaving($id);
        $data = ['saving' => $saving, 'deposits' => $deposits, 'title' => 'Tambah Setoran'];
        return view('owner/tabungan/deposit', $data);
    }

    public function storeDeposit()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $travelSavingId = (int) $this->request->getPost('travel_saving_id');
        $saving = $this->savingModel->find($travelSavingId);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }

        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
            'payment_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to("owner/tabungan/deposit/{$travelSavingId}")->withInput()->with('errors', $this->validator->getErrors());
        }

        $amount = (float) str_replace(',', '', $this->request->getPost('amount'));
        $proof = null;
        $file = $this->request->getFile('proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = FCPATH . 'uploads/tabungan';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $proof = 'uploads/tabungan/' . $file->getRandomName();
            $file->move($dir, basename($proof));
        }

        $db = \Config\Database::connect();
        $db->transStart();
        $this->depositModel->insert([
            'travel_saving_id' => $travelSavingId,
            'amount' => $amount,
            'payment_date' => $this->request->getPost('payment_date'),
            'proof' => $proof,
            'status' => 'verified',
            'notes' => $this->request->getPost('notes') ?: null,
        ]);
        $newTotal = $this->depositModel->getTotalVerified($travelSavingId);
        $this->savingModel->update($travelSavingId, ['total_balance' => $newTotal]);
        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to("owner/tabungan/deposit/{$travelSavingId}")->with('error', 'Gagal menyimpan setoran.');
        }
        return redirect()->to("owner/tabungan/deposit/{$travelSavingId}")->with('msg', 'Setoran berhasil ditambahkan.');
    }

    /**
     * Form edit setoran (owner).
     */
    public function editDeposit($depositId)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $deposit = $this->depositModel->find($depositId);
        if (!$deposit) {
            return redirect()->to('owner/tabungan')->with('error', 'Setoran tidak ditemukan.');
        }
        $saving = $this->savingModel->find($deposit['travel_saving_id']);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $saving['agency_name'] = $this->userModel->find($saving['agency_id'])['full_name'] ?? '-';
        $data = [
            'deposit' => $deposit,
            'saving' => $saving,
            'title' => 'Edit Setoran',
        ];
        return view('owner/tabungan/deposit_edit', $data);
    }

    /**
     * Update setoran (owner). Setelah update, recalc total_balance tabungan.
     */
    public function updateDeposit($depositId)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $deposit = $this->depositModel->find($depositId);
        if (!$deposit) {
            return redirect()->to('owner/tabungan')->with('error', 'Setoran tidak ditemukan.');
        }
        $travelSavingId = (int) $deposit['travel_saving_id'];
        $saving = $this->savingModel->find($travelSavingId);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $rules = [
            'amount' => 'required|decimal|greater_than[0]',
            'payment_date' => 'required|valid_date',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $amount = (float) str_replace(',', '', $this->request->getPost('amount'));
        $proof = $deposit['proof'];
        $file = $this->request->getFile('proof');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $dir = FCPATH . 'uploads/tabungan';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $proof = 'uploads/tabungan/' . $file->getRandomName();
            $file->move($dir, basename($proof));
        }
        $this->depositModel->update($depositId, [
            'amount' => $amount,
            'payment_date' => $this->request->getPost('payment_date'),
            'proof' => $proof,
            'notes' => $this->request->getPost('notes') ?: null,
        ]);
        $newTotal = $this->depositModel->getTotalVerified($travelSavingId);
        $this->savingModel->update($travelSavingId, ['total_balance' => $newTotal]);
        return redirect()->to("owner/tabungan/deposit/{$travelSavingId}")->with('msg', 'Setoran berhasil diperbarui.');
    }

    /**
     * Hapus setoran (owner). Recalc total_balance tabungan.
     */
    public function deleteDeposit($depositId)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $deposit = $this->depositModel->find($depositId);
        if (!$deposit) {
            return redirect()->to('owner/tabungan')->with('error', 'Setoran tidak ditemukan.');
        }
        $travelSavingId = (int) $deposit['travel_saving_id'];
        $saving = $this->savingModel->find($travelSavingId);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $this->depositModel->delete($depositId);
        $newTotal = $this->depositModel->getTotalVerified($travelSavingId);
        $this->savingModel->update($travelSavingId, ['total_balance' => $newTotal]);
        return redirect()->back()->with('msg', 'Setoran berhasil dihapus.');
    }

    /**
     * Verifikasi setoran (dari agency): pending -> verified, lalu update total_balance.
     */
    public function verifyDeposit($depositId)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }
        $deposit = $this->depositModel->find($depositId);
        if (!$deposit || $deposit['status'] === 'verified') {
            return redirect()->to('owner/tabungan')->with('error', 'Setoran tidak ditemukan atau sudah diverifikasi.');
        }
        $travelSavingId = (int) $deposit['travel_saving_id'];
        $this->depositModel->update($depositId, ['status' => 'verified']);
        $newTotal = $this->depositModel->getTotalVerified($travelSavingId);
        $this->savingModel->update($travelSavingId, ['total_balance' => $newTotal]);
        return redirect()->back()->with('msg', 'Setoran telah diverifikasi. Saldo tabungan diperbarui.');
    }

    public function claimForm($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $saving = $this->savingModel->find($id);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $balance = (float) $saving['total_balance'];
        $packages = $this->packageModel->orderBy('departure_date', 'DESC')->findAll();
        $saving['agency_name'] = $this->userModel->find($saving['agency_id'])['full_name'] ?? '-';
        $data = [
            'saving' => $saving,
            'packages' => $packages,
            'totalBalance' => $balance,
            'title' => 'Klaim Tabungan ke Paket',
        ];
        return view('owner/tabungan/claim', $data);
    }

    public function doClaim()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $travelSavingId = (int) $this->request->getPost('travel_saving_id');
        $packageId = (int) $this->request->getPost('package_id');
        $saving = $this->savingModel->find($travelSavingId);
        if (!$saving || $saving['status'] !== 'menabung') {
            return redirect()->to('owner/tabungan')->with('error', 'Data tabungan tidak ditemukan atau sudah diklaim.');
        }
        $package = $this->packageModel->find($packageId);
        if (!$package) {
            return redirect()->back()->with('error', 'Paket tidak ditemukan.');
        }

        $totalBalance = (float) $saving['total_balance'];
        if ($totalBalance <= 0) {
            return redirect()->back()->with('error', 'Saldo tabungan tidak cukup.');
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $participantData = [
            'package_id' => $packageId,
            'agency_id' => $saving['agency_id'],
            'nik' => $saving['nik'],
            'name' => $saving['name'],
            'phone' => $saving['phone'] ?? null,
            'status' => 'pending',
            'place_of_birth' => null,
            'date_of_birth' => null,
            'gender' => null,
            'address' => null,
            'religion' => null,
            'marital_status' => null,
            'occupation' => null,
        ];
        $this->participantModel->insert($participantData);
        $participantId = $this->participantModel->getInsertID();

        $this->paymentModel->insert([
            'participant_id' => $participantId,
            'amount' => $totalBalance,
            'payment_date' => date('Y-m-d'),
            'proof' => null,
            'status' => 'verified',
            'notes' => 'Dari tabungan perjalanan #' . $travelSavingId,
        ]);

        $this->savingModel->update($travelSavingId, [
            'status' => 'claimed',
            'package_id' => $packageId,
            'participant_id' => $participantId,
            'claimed_at' => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to('owner/tabungan')->with('error', 'Gagal mengklaim tabungan.');
        }
        return redirect()->to('owner/tabungan')->with('msg', 'Tabungan berhasil diklaim. Jamaah telah didaftarkan ke paket.');
    }
}

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

        $rules = [
            'agency_id' => 'required|integer',
            'name' => 'required|min_length[3]',
            'nik' => 'required|min_length[16]|max_length[20]',
            'phone' => 'permit_empty',
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'agency_id' => (int) $this->request->getPost('agency_id'),
            'name' => $this->request->getPost('name'),
            'nik' => $this->request->getPost('nik'),
            'phone' => $this->request->getPost('phone') ?: null,
            'total_balance' => 0,
            'status' => 'menabung',
            'notes' => $this->request->getPost('notes') ?: null,
        ];
        if ($this->savingModel->insert($data)) {
            return redirect()->to('owner/tabungan')->with('msg', 'Jamaah tabungan berhasil didaftarkan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menyimpan data.');
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
        ];
        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }
        $data = [
            'agency_id' => (int) $this->request->getPost('agency_id'),
            'name' => $this->request->getPost('name'),
            'nik' => $this->request->getPost('nik'),
            'phone' => $this->request->getPost('phone') ?: null,
            'notes' => $this->request->getPost('notes') ?: null,
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

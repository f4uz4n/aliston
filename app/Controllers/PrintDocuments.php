<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\TravelSavingDepositModel;
use App\Models\TravelSavingModel;
use App\Models\UserModel;
use App\Models\PackageModel;

class PrintDocuments extends BaseController
{
    protected $participantModel;
    protected $depositModel;
    protected $savingModel;
    protected $userModel;
    protected $packageModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
        $this->depositModel = new TravelSavingDepositModel();
        $this->savingModel = new TravelSavingModel();
        $this->userModel = new UserModel();
        $this->packageModel = new PackageModel();
    }

    /**
     * Cetak surat izin cuti untuk jamaah (format resmi, kop dari pengaturan akun)
     */
    public function printLeaveLetter()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $participantId = $this->request->getPost('participant_id') ?: $this->request->getGet('participant_id');
        if (empty($participantId)) {
            return redirect()->to('owner/print-documents')->with('error', 'Pilih jamaah terlebih dahulu.');
        }

        $participant = $this->participantModel
            ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date, travel_packages.duration, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->where('participants.id', $participantId)
            ->first();

        if (!$participant) {
            return redirect()->to('owner/print-documents')->with('error', 'Data jamaah tidak ditemukan.');
        }

        // Data owner untuk kop surat (dari Pengaturan Akun)
        $owner = $this->userModel->where('role', 'owner')->first();
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');
        $companyName = $owner['company_name'] ?? 'Nama Perusahaan';
        $companySlogan = $owner['slogan'] ?? '';
        $companyAddress = $owner['address'] ?? '';
        $companyEmail = $owner['email'] ?? '';
        $companyPhone = $owner['phone'] ?? '';
        $noSkPerijinan = $owner['no_sk_perijinan'] ?? '';
        $tanggalSkPerijinan = $owner['tanggal_sk_perijinan'] ?? '';
        $namaDirektur = $owner['full_name'] ?? '—';

        // Input dari form: tujuan surat, label & isi program studi, label & isi fakultas
        $tujuanSurat = $this->request->getPost('tujuan_surat') ?: $this->request->getGet('tujuan_surat') ?: '';
        $labelProgramStudi = $this->request->getPost('label_program_studi') ?: $this->request->getGet('label_program_studi') ?: 'Program Studi';
        $isiProgramStudi = $this->request->getPost('isi_program_studi') ?: $this->request->getGet('isi_program_studi') ?: '';
        $labelFakultas = $this->request->getPost('label_fakultas') ?: $this->request->getGet('label_fakultas') ?: 'Fakultas';
        $isiFakultas = $this->request->getPost('isi_fakultas') ?: $this->request->getGet('isi_fakultas') ?: '';

        // Nomor surat & perihal (input user)
        $nomorSurat = $this->request->getPost('nomor_surat') ?: $this->request->getGet('nomor_surat') ?: '';
        $perihal = $this->request->getPost('perihal') ?: $this->request->getGet('perihal') ?: 'Permohonan Ijin Cuti';

        // Range tanggal ijin: dari input (tanggal_dari, tanggal_sampai)
        $tanggalDari = $this->request->getPost('tanggal_dari') ?: $this->request->getGet('tanggal_dari');
        $tanggalSampai = $this->request->getPost('tanggal_sampai') ?: $this->request->getGet('tanggal_sampai');
        if (!empty($tanggalDari) && !empty($tanggalSampai)) {
            $departureDate = strtotime($tanggalDari);
            $returnDate = strtotime($tanggalSampai);
            $durationDays = max(1, (int) round(($returnDate - $departureDate) / 86400) + 1);
        } else {
            $durationStr = $participant['duration'] ?? '9';
            preg_match('/(\d+)/', $durationStr, $matches);
            $durationDays = !empty($matches[1]) ? (int)$matches[1] : 9;
            $departureDate = !empty($participant['package_departure_date']) ? strtotime($participant['package_departure_date']) : time();
            $returnDate = strtotime('+' . $durationDays . ' days', $departureDate);
        }

        $data = [
            'participant' => $participant,
            'company_logo_url' => $companyLogo,
            'company_name' => $companyName,
            'company_slogan' => $companySlogan,
            'company_address' => $companyAddress,
            'company_email' => $companyEmail,
            'company_phone' => $companyPhone,
            'no_sk_perijinan' => $noSkPerijinan,
            'tanggal_sk_perijinan' => $tanggalSkPerijinan,
            'nama_direktur' => $namaDirektur,
            'tujuan_surat' => $tujuanSurat,
            'label_program_studi' => $labelProgramStudi,
            'isi_program_studi' => $isiProgramStudi,
            'label_fakultas' => $labelFakultas,
            'isi_fakultas' => $isiFakultas,
            'nomor_surat' => $nomorSurat,
            'perihal' => $perihal,
            'departure_date' => $departureDate,
            'return_date' => $returnDate,
            'duration_days' => $durationDays,
        ];

        // Update counter nomor urut surat keluar (agar nomor berikutnya otomatis +1)
        if (!empty($nomorSurat) && preg_match('/^(\d+)/', $nomorSurat, $m)) {
            $usedNumber = (int) $m[1];
            $ownerId = $owner['id'] ?? null;
            if ($ownerId) {
                $this->userModel->update($ownerId, [
                    'leave_letter_last_number' => $usedNumber,
                    'leave_letter_last_year' => (int) date('Y'),
                ]);
            }
        }

        return view('owner/print/leave_letter', $data);
    }

    /**
     * Cetak bukti setoran tabungan
     */
    public function printDepositReceipt()
    {
        $role = session()->get('role');
        if (!in_array($role, ['owner', 'agency'])) {
            return redirect()->to('/login');
        }

        $depositId = $this->request->getGet('deposit_id');
        if (empty($depositId)) {
            $redirectUrl = $role === 'owner' ? 'owner/print-documents' : 'agency/tabungan';
            return redirect()->to($redirectUrl)->with('error', 'Pilih setoran terlebih dahulu.');
        }

        $deposit = $this->depositModel
            ->select('travel_savings_deposits.*, travel_savings.name as saving_name, travel_savings.nik as saving_nik, travel_savings.phone as saving_phone, travel_savings.agency_id, users.full_name as agency_name, users.nomor_rekening, users.nama_bank')
            ->join('travel_savings', 'travel_savings.id = travel_savings_deposits.travel_saving_id')
            ->join('users', 'users.id = travel_savings.agency_id')
            ->where('travel_savings_deposits.id', $depositId)
            ->first();

        if (!$deposit) {
            $redirectUrl = $role === 'owner' ? 'owner/tabungan' : 'agency/tabungan';
            return redirect()->to($redirectUrl)->with('error', 'Data setoran tidak ditemukan.');
        }

        // Validasi untuk agency: hanya bisa cetak setoran miliknya sendiri
        if ($role === 'agency') {
            $agencyId = session()->get('id');
            if ((int)$deposit['agency_id'] !== (int)$agencyId) {
                return redirect()->to('agency/tabungan')->with('error', 'Anda tidak memiliki akses untuk setoran ini.');
            }
        }

        // Hanya bisa cetak setoran yang sudah verified
        if (($deposit['status'] ?? 'pending') !== 'verified') {
            $redirectUrl = $role === 'owner' ? 'owner/tabungan' : 'agency/tabungan';
            return redirect()->to($redirectUrl)->with('error', 'Setoran belum diverifikasi.');
        }

        // Ambil data owner untuk kop surat
        $owner = $this->userModel->where('role', 'owner')->first();
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');
        $companyName = $owner['company_name'] ?? 'Nama Perusahaan';
        $companyAddress = $owner['address'] ?? '';
        $namaDirektur = $owner['full_name'] ?? '—';

        // Ambil total saldo tabungan
        $saving = $this->savingModel->find($deposit['travel_saving_id']);
        $totalBalance = $saving['total_balance'] ?? 0;

        $data = [
            'deposit' => $deposit,
            'total_balance' => $totalBalance,
            'company_logo_url' => $companyLogo,
            'company_name' => $companyName,
            'company_address' => $companyAddress,
            'nama_direktur' => $namaDirektur,
        ];

        return view('owner/print/deposit_receipt', $data);
    }

    /**
     * Bulan dalam angka Romawi (1-12)
     */
    private function bulanRomawi(int $month): string
    {
        $romawi = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'];
        return $romawi[$month] ?? 'I';
    }

    /**
     * Generate default nomor surat: nomor_urut/ABW-KDR/IC/bulan_romawi/tahun_2digit
     */
    private function defaultNomorSurat(): string
    {
        $owner = $this->userModel->where('role', 'owner')->first();
        $year = (int) date('Y');
        $month = (int) date('n');
        $lastNumber = (int) ($owner['leave_letter_last_number'] ?? 0);
        $lastYear = (int) ($owner['leave_letter_last_year'] ?? 0);
        $next = ($lastYear === $year) ? $lastNumber + 1 : 1;
        $romawi = $this->bulanRomawi($month);
        $tahun2 = $year % 100;
        return sprintf('%03d/ABW-KDR/IC/%s/%02d', $next, $romawi, $tahun2);
    }

    /**
     * Default nomor surat rekomendasi: nomor_urut/ABW/SURAT/bulan_romawi/tahun (contoh: 0175/ABW/SURAT/I/2026)
     */
    private function defaultNomorRekomendasi(): string
    {
        $owner = $this->userModel->where('role', 'owner')->first();
        $year = (int) date('Y');
        $month = (int) date('n');
        $lastNumber = (int) ($owner['recommendation_letter_last_number'] ?? 0);
        $lastYear = (int) ($owner['recommendation_letter_last_year'] ?? 0);
        $next = ($lastYear === $year) ? $lastNumber + 1 : 1;
        $romawi = $this->bulanRomawi($month);
        return sprintf('%04d/ABW/SURAT/%s/%d', $next, $romawi, $year);
    }

    /**
     * Cetak Surat Rekomendasi (rekomendasi penerbitan paspor umrah)
     */
    public function printRecommendationLetter()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $owner = $this->userModel->where('role', 'owner')->first();
        $companyLogo = !empty($owner['company_logo']) ? base_url($owner['company_logo']) : base_url('assets/img/logo_.png');
        $companyName = $owner['company_name'] ?? 'Nama Perusahaan';
        $companySlogan = $owner['slogan'] ?? '';
        $companyAddress = $owner['address'] ?? '';
        $companyEmail = $owner['email'] ?? '';
        $companyPhone = $owner['phone'] ?? '';
        $noSkPerijinan = $owner['no_sk_perijinan'] ?? '';
        $tanggalSkPerijinan = $owner['tanggal_sk_perijinan'] ?? '';
        $namaDirektur = $owner['full_name'] ?? '—';

        $participantId = $this->request->getPost('participant_id') ?: $this->request->getGet('participant_id');
        $participant = null;
        if ($participantId) {
            $participant = $this->participantModel
                ->select('participants.*, travel_packages.name as package_name, travel_packages.departure_date as package_departure_date')
                ->join('travel_packages', 'travel_packages.id = participants.package_id')
                ->where('participants.id', $participantId)
                ->first();
        }

        // Data jamaah: dari participant jika ada, else dari input; input bisa override
        $nama = $this->request->getPost('nama') ?: $this->request->getGet('nama') ?: ($participant['name'] ?? '');
        $namaAyah = $this->request->getPost('nama_ayah') ?: $this->request->getGet('nama_ayah') ?: '';
        $tempatLahir = $this->request->getPost('tempat_lahir') ?: $this->request->getGet('tempat_lahir') ?: ($participant['place_of_birth'] ?? '');
        $tglLahir = $this->request->getPost('tgl_lahir') ?: $this->request->getGet('tgl_lahir') ?: ($participant['date_of_birth'] ?? '');
        $alamat = $this->request->getPost('alamat') ?: $this->request->getGet('alamat') ?: ($participant['address'] ?? '');
        $tanggalKeberangkatan = $this->request->getPost('tanggal_keberangkatan') ?: $this->request->getGet('tanggal_keberangkatan') ?: ($participant['package_departure_date'] ?? date('Y-m-d'));

        $nomorSurat = $this->request->getPost('nomor_surat') ?: $this->request->getGet('nomor_surat') ?: '';
        $sifat = $this->request->getPost('sifat') ?: $this->request->getGet('sifat') ?: 'Segera';
        $lamp = $this->request->getPost('lamp') ?: $this->request->getGet('lamp') ?: '-';
        $perihal = $this->request->getPost('perihal') ?: $this->request->getGet('perihal') ?: 'Surat Rekomendasi Penerbitan Paspor Umrah';
        $tujuanSurat = $this->request->getPost('tujuan_surat') ?: $this->request->getGet('tujuan_surat') ?: '';

        if (empty($nama)) {
            return redirect()->to('owner/print-documents')->with('error', 'Nama jamaah wajib diisi.');
        }

        if (!empty($nomorSurat) && preg_match('/^(\d+)/', $nomorSurat, $m) && $owner) {
            $this->userModel->update($owner['id'], [
                'recommendation_letter_last_number' => (int) $m[1],
                'recommendation_letter_last_year' => (int) date('Y'),
            ]);
        }

        $data = [
            'company_logo_url' => $companyLogo,
            'company_name' => $companyName,
            'company_slogan' => $companySlogan,
            'company_address' => $companyAddress,
            'company_email' => $companyEmail,
            'company_phone' => $companyPhone,
            'no_sk_perijinan' => $noSkPerijinan,
            'tanggal_sk_perijinan' => $tanggalSkPerijinan,
            'nama_direktur' => $namaDirektur,
            'nomor_surat' => $nomorSurat,
            'sifat' => $sifat,
            'lamp' => $lamp,
            'perihal' => $perihal,
            'tujuan_surat' => $tujuanSurat,
            'nama' => $nama,
            'nama_ayah' => $namaAyah,
            'tempat_lahir' => $tempatLahir,
            'tgl_lahir' => $tglLahir,
            'alamat' => $alamat,
            'tanggal_keberangkatan' => $tanggalKeberangkatan,
        ];

        return view('owner/print/recommendation_letter', $data);
    }

    /**
     * Halaman index untuk memilih dokumen yang akan dicetak
     */
    public function index()
    {
        if (session()->get('role') != 'owner') {
            return redirect()->to('/login');
        }

        $participants = $this->participantModel
            ->select('participants.id, participants.name, participants.nik, travel_packages.name as package_name, travel_packages.departure_date')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->where('participants.is_verified', 1)
            ->orderBy('participants.created_at', 'DESC')
            ->findAll();

        $data = [
            'title' => 'Cetak Dokumen',
            'participants' => $participants,
            'default_nomor_surat' => $this->defaultNomorSurat(),
            'default_perihal' => 'Permohonan Ijin Cuti',
            'default_nomor_rekomendasi' => $this->defaultNomorRekomendasi(),
            'default_perihal_rekomendasi' => 'Surat Rekomendasi Penerbitan Paspor Umrah',
        ];

        return view('owner/print/index', $data);
    }
}

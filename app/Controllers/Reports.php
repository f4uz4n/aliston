<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\PaymentModel;
use App\Models\PackageModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reports extends BaseController
{
    /**
     * Builder khusus laporan: peserta + paket + agensi + agregat pembayaran terverifikasi.
     */
    /**
     * @param 'active'|'cancelled'|'all' $scope
     */
    private function getReportParticipantBuilder(string $scope = 'active'): \CodeIgniter\Database\BaseBuilder
    {
        $db = \Config\Database::connect();

        $paymentAgg = "(SELECT participant_id,
            COUNT(CASE WHEN status = 'verified' THEN 1 END) AS payments_count,
            COALESCE(SUM(CASE WHEN status = 'verified' THEN amount ELSE 0 END), 0) AS total_paid
        FROM participant_payments
        GROUP BY participant_id) pay";

        $builder = $db->table('participants');
        $builder->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, users.full_name as agency_name, users.username as agency_username, pay.payments_count, pay.total_paid');
        $builder->join('travel_packages', 'travel_packages.id = participants.package_id');
        $builder->join('users', 'users.id = participants.agency_id');
        $builder->join($paymentAgg, 'pay.participant_id = participants.id', 'left', false);

        if ($scope === 'active') {
            $builder->where('participants.status !=', 'cancelled');
        } elseif ($scope === 'cancelled') {
            $builder->where('participants.status', 'cancelled');
            $builder->orderBy('participants.cancelled_at', 'DESC');
        }

        if ($scope !== 'cancelled') {
            $builder->orderBy('participants.created_at', 'DESC');
        }

        return $builder;
    }

    private function applyRegistrationDateFilter($query, string $startDate, string $endDate)
    {
        if ($startDate !== '') {
            $query->where('participants.created_at >=', $startDate . ' 00:00:00');
        }
        if ($endDate !== '') {
            $query->where('participants.created_at <=', $endDate . ' 23:59:59');
        }

        return $query;
    }

    private function resolveRegistrationTab(?string $tab): string
    {
        return in_array($tab, ['active', 'cancelled'], true) ? $tab : 'active';
    }

    private function streamRegistrationsXlsx(array $rows, string $tab): \CodeIgniter\HTTP\ResponseInterface
    {
        $isCancelled = $tab === 'cancelled';
        $filename = ($isCancelled ? 'riwayat-jamaah-batal-' : 'riwayat-jamaah-aktif-') . date('Y-m-d-His') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($isCancelled ? 'Jamaah Batal' : 'Jamaah Aktif');

        if ($isCancelled) {
            $headers = [
                'No',
                'Jamaah',
                'NIK',
                'Agensi',
                'Paket',
                'Tanggal Daftar',
                'Jumlah Pembayaran',
                'Total Terbayar',
                'Tanggal Batal',
                'Refund (Rp)',
                'Catatan Pembatalan',
            ];
            $sheet->fromArray($headers, null, 'A1');

            $rowNum = 2;
            foreach ($rows as $i => $reg) {
                $sheet->fromArray([
                    $i + 1,
                    $reg['name'] ?? '',
                    $reg['nik'] ?? '',
                    $reg['agency_name'] ?? '',
                    $reg['package_name'] ?? '',
                    ! empty($reg['created_at']) ? date('d/m/Y H:i', strtotime($reg['created_at'])) : '',
                    (int) ($reg['payments_count'] ?? 0),
                    (float) ($reg['total_paid'] ?? 0),
                    ! empty($reg['cancelled_at']) ? date('d/m/Y H:i', strtotime($reg['cancelled_at'])) : '',
                    (float) ($reg['refund_amount'] ?? 0),
                    $reg['cancellation_notes'] ?? '',
                ], null, 'A' . $rowNum);
                $rowNum++;
            }

            $lastCol = 'K';
            $moneyCols = ['H', 'J'];
        } else {
            $headers = [
                'No',
                'Jamaah',
                'NIK',
                'Agensi',
                'Paket',
                'Status',
                'Tanggal Daftar',
                'Jumlah Pembayaran',
                'Total Terbayar',
                'Detail Pembayaran',
            ];
            $sheet->fromArray($headers, null, 'A1');

            $baseDetailUrl = base_url('owner/reports/payment-detail-export');
            $rowNum = 2;
            foreach ($rows as $i => $reg) {
                $participantId = (int) ($reg['id'] ?? 0);
                $detailUrl = $participantId > 0 ? ($baseDetailUrl . '/' . $participantId) : '';

                $sheet->fromArray([
                    $i + 1,
                    $reg['name'] ?? '',
                    $reg['nik'] ?? '',
                    $reg['agency_name'] ?? '',
                    $reg['package_name'] ?? '',
                    strtoupper($reg['status'] ?? ''),
                    ! empty($reg['created_at']) ? date('d/m/Y H:i', strtotime($reg['created_at'])) : '',
                    (int) ($reg['payments_count'] ?? 0),
                    (float) ($reg['total_paid'] ?? 0),
                    $detailUrl,
                ], null, 'A' . $rowNum);

                if ($detailUrl !== '') {
                    $sheet->getCell('J' . $rowNum)->getHyperlink()->setUrl($detailUrl);
                }
                $rowNum++;
            }

            $lastCol = 'J';
            $moneyCols = ['I'];
        }

        $sheet->getStyle('A1:' . $lastCol . '1')->getFont()->setBold(true);
        foreach (range('A', $lastCol) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        if ($rowNum > 2) {
            foreach ($moneyCols as $col) {
                $sheet->getStyle($col . '2:' . $col . ($rowNum - 1))->getNumberFormat()->setFormatCode('#,##0');
            }
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsx = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($xlsx);
    }

    public function index()
    {
        if (! is_owner()) {
            return redirect()->to('owner')->with('error', 'Hanya pemilik yang dapat membuka laporan bisnis.');
        }

        $participantModel = new ParticipantModel();
        $packageModel = new PackageModel();
        $userModel = new \App\Models\UserModel();

        // Range tanggal: default kosong = ambil semua data
        $startDate = (string) ($this->request->getGet('start_date') ?? '');
        $endDate = (string) ($this->request->getGet('end_date') ?? '');
        $registrationTab = $this->resolveRegistrationTab($this->request->getGet('registration_tab'));

        // Helper to apply date filter
        $filterDate = function ($query) use ($startDate, $endDate) {
            return $this->applyRegistrationDateFilter($query, $startDate, $endDate);
        };

        // 1. Participant Status Breakdown (Filtered)
        $statusBreakdown = [
            'pending' => $filterDate($participantModel->where('status', 'pending'))->countAllResults(),
            'verified' => $filterDate($participantModel->where('status', 'verified'))->countAllResults(),
            'cancelled' => $filterDate($participantModel->where('status', 'cancelled'))->countAllResults(),
        ];

        // 2. Package Popularity (Filtered)
        $packagePopularityBuilder = $packageModel->select('travel_packages.name, COUNT(participants.id) as total_jamaah')
            ->join('participants', 'participants.package_id = travel_packages.id', 'left')
            ->where('participants.status', 'verified')
            ->groupBy('travel_packages.id')
            ->orderBy('total_jamaah', 'DESC');
        $packagePopularity = $filterDate($packagePopularityBuilder)->findAll();

        // 3. Agency Performance (Filtered, tanpa jamaah batal)
        $agencyPerformanceBuilder = $userModel
            ->select("users.full_name, users.username, COUNT(CASE WHEN participants.status != 'cancelled' THEN participants.id END) as total_jamaah", false)
            ->where('role', 'agency')
            ->join('participants', 'participants.agency_id = users.id', 'left')
            ->groupBy('users.id')
            ->orderBy('total_jamaah', 'DESC');
        $agencyPerformance = $filterDate($agencyPerformanceBuilder)->findAll();

        // 4. Latest Registrations per tab
        $latestRegistrations = $filterDate($this->getReportParticipantBuilder($registrationTab))->get()->getResultArray();

        $data = [
            'total_jamaah' => $filterDate($participantModel->where('status !=', 'cancelled'))->countAllResults(),
            'total_packages' => $packageModel->countAllResults(),
            'total_agencies' => $userModel->where('role', 'agency')->countAllResults(),
            'status_breakdown' => $statusBreakdown,
            'package_popularity' => $packagePopularity,
            'agency_performance' => $agencyPerformance,
            'latest_registrations' => $latestRegistrations,
            'registration_tab' => $registrationTab,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        return view('owner/report/index', $data);
    }

    /**
     * Export riwayat pendaftaran terbaru ke Excel (XLSX) sesuai filter tanggal.
     */
    public function registrationsExport()
    {
        if (! is_owner()) {
            return redirect()->to('owner')->with('error', 'Hanya pemilik yang dapat membuka laporan bisnis.');
        }

        $startDate = (string) ($this->request->getGet('start_date') ?? '');
        $endDate = (string) ($this->request->getGet('end_date') ?? '');
        $registrationTab = $this->resolveRegistrationTab($this->request->getGet('registration_tab'));

        $rows = $this->applyRegistrationDateFilter(
            $this->getReportParticipantBuilder($registrationTab),
            $startDate,
            $endDate
        )->get()->getResultArray();

        return $this->streamRegistrationsXlsx($rows, $registrationTab);
    }

    /**
     * Download detail pembayaran per jamaah (XLSX).
     */
    public function paymentDetailExport($participantId)
    {
        if (! is_owner()) {
            return redirect()->to('owner')->with('error', 'Hanya pemilik yang dapat membuka laporan bisnis.');
        }

        $participantId = (int) $participantId;
        if ($participantId <= 0) {
            return redirect()->back()->with('error', 'ID jamaah tidak valid.');
        }

        $participant = $this->getReportParticipantBuilder('all')
            ->where('participants.id', $participantId)
            ->get()
            ->getRowArray();

        if (! $participant) {
            return redirect()->back()->with('error', 'Jamaah tidak ditemukan.');
        }

        if (($participant['status'] ?? '') === 'cancelled') {
            return redirect()->back()->with('error', 'Jamaah yang dibatalkan tidak tersedia di laporan.');
        }

        $paymentModel = new PaymentModel();
        $payments = $paymentModel
            ->where('participant_id', $participantId)
            ->orderBy('payment_date', 'DESC')
            ->orderBy('id', 'DESC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detail Pembayaran');

        $sheet->setCellValue('A1', 'Detail Pembayaran Jamaah');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->fromArray([
            ['Nama Jamaah', $participant['name'] ?? ''],
            ['NIK', $participant['nik'] ?? ''],
            ['Agensi', $participant['agency_name'] ?? ''],
            ['Paket', $participant['package_name'] ?? ''],
            ['Status Jamaah', strtoupper($participant['status'] ?? '')],
        ], null, 'A3');

        $startTableRow = 10;
        $sheet->fromArray(['No', 'Tanggal Bayar', 'Nominal', 'Status', 'Catatan', 'ID Pembayaran'], null, 'A' . $startTableRow);
        $sheet->getStyle('A' . $startTableRow . ':F' . $startTableRow)->getFont()->setBold(true);

        $r = $startTableRow + 1;
        foreach ($payments as $i => $p) {
            $sheet->fromArray([
                $i + 1,
                !empty($p['payment_date']) ? date('d/m/Y', strtotime($p['payment_date'])) : '',
                (float)($p['amount'] ?? 0),
                strtoupper($p['status'] ?? ''),
                $p['notes'] ?? '',
                (int)($p['id'] ?? 0),
            ], null, 'A' . $r);
            $r++;
        }

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        if ($r > ($startTableRow + 1)) {
            $sheet->getStyle('C' . ($startTableRow + 1) . ':C' . ($r - 1))->getNumberFormat()->setFormatCode('#,##0');
        }

        $safeName = preg_replace('/[^a-z0-9 _-]/i', '-', (string)($participant['name'] ?? 'jamaah'));
        $filename = 'detail-pembayaran-' . $safeName . '-' . date('Y-m-d-His') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $xlsx = ob_get_clean();

        return $this->response
            ->setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($xlsx);
    }

    public function equipment()
    {
        if (! is_owner()) {
            return redirect()->to('owner')->with('error', 'Hanya pemilik yang dapat membuka laporan bisnis.');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('participant_equipment pe');
        $builder->select('pe.item_name, 
                         COUNT(CASE WHEN pe.status = "collected" THEN 1 END) as collected_count,
                         COUNT(CASE WHEN pe.status = "pending" THEN 1 END) as pending_count,
                         COUNT(pe.id) as total_count');
        $builder->groupBy('pe.item_name');
        $itemStats = $builder->get()->getResultArray();

        // Get details per agency
        $builder = $db->table('participant_equipment pe');
        $builder->select('u.full_name as agency_name, pe.item_name, 
                         COUNT(CASE WHEN pe.status = "collected" THEN 1 END) as collected_count,
                         COUNT(pe.id) as total_count');
        $builder->join('participants p', 'p.id = pe.participant_id');
        $builder->join('users u', 'u.id = p.agency_id');
        $builder->groupBy('u.id, pe.item_name');
        $agencyStats = $builder->get()->getResultArray();

        $data = [
            'item_stats' => $itemStats,
            'agency_stats' => $agencyStats,
            'title' => 'Laporan Distribusi Perlengkapan'
        ];

        return view('owner/report/equipment', $data);
    }
}

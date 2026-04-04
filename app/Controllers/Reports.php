<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use App\Models\PackageModel;

class Reports extends BaseController
{
    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $participantModel = new ParticipantModel();
        $packageModel = new PackageModel();
        $userModel = new \App\Models\UserModel();

        // Range tanggal: Default 6 bulan ke belakang
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-6 months'));
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');

        // Helper to apply date filter
        $filterDate = function ($query) use ($startDate, $endDate) {
            return $query->where('participants.created_at >=', $startDate . ' 00:00:00')
            ->where('participants.created_at <=', $endDate . ' 23:59:59');
        };

        // 1. Participant Status Breakdown (Filtered)
        $statusBreakdown = [
            'pending' => $filterDate($participantModel->where('status', 'pending'))->countAllResults(),
            'verified' => $filterDate($participantModel->where('status', 'verified'))->countAllResults(),
            'cancelled' => $filterDate($participantModel->where('status', 'cancelled'))->countAllResults(),
        ];

        // 2. Package Popularity (Filtered)
        $packagePopularity = $packageModel->select('travel_packages.name, COUNT(participants.id) as total_jamaah')
            ->join('participants', 'participants.package_id = travel_packages.id', 'left')
            ->where('participants.created_at >=', $startDate . ' 00:00:00')
            ->where('participants.created_at <=', $endDate . ' 23:59:59')
            ->groupBy('travel_packages.id')
            ->orderBy('total_jamaah', 'DESC')
            ->findAll();

        // 3. Agency Performance (Filtered)
        $agencyPerformance = $userModel->select('users.full_name, users.username, COUNT(participants.id) as total_jamaah')
            ->where('role', 'agency')
            ->join('participants', 'participants.agency_id = users.id', 'left')
            ->where('participants.created_at >=', $startDate . ' 00:00:00')
            ->where('participants.created_at <=', $endDate . ' 23:59:59')
            ->groupBy('users.id')
            ->orderBy('total_jamaah', 'DESC')
            ->findAll();

        // 4. Latest Registrations (Filtered)
        $latestRegistrations = $filterDate($participantModel->getParticipantBuilder())->limit(10)->findAll();

        $data = [
            'total_jamaah' => $filterDate($participantModel)->countAllResults(),
            'total_packages' => $packageModel->countAllResults(),
            'total_agencies' => $userModel->where('role', 'agency')->countAllResults(),
            'status_breakdown' => $statusBreakdown,
            'package_popularity' => $packagePopularity,
            'agency_performance' => $agencyPerformance,
            'latest_registrations' => $latestRegistrations,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];

        return view('owner/report/index', $data);
    }

    /**
     * Export riwayat pendaftaran terbaru ke Excel (CSV) sesuai filter tanggal.
     */
    public function registrationsExport()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $startDate = $this->request->getGet('start_date') ?: date('Y-m-d', strtotime('-6 months'));
        $endDate = $this->request->getGet('end_date') ?: date('Y-m-d');

        $participantModel = new ParticipantModel();
        $filterDate = function ($query) use ($startDate, $endDate) {
            return $query->where('participants.created_at >=', $startDate . ' 00:00:00')
                ->where('participants.created_at <=', $endDate . ' 23:59:59');
        };

        $rows = $filterDate($participantModel->getParticipantBuilder())->findAll();

        $filename = 'riwayat-pendaftaran-' . date('Y-m-d-His') . '.csv';
        $out = fopen('php://temp', 'r+');
        fprintf($out, "\xEF\xBB\xBF");

        $headers = ['No', 'Jamaah', 'NIK', 'Agensi', 'Paket', 'Status', 'Tanggal Daftar'];
        fputcsv($out, $headers, ';');

        foreach ($rows as $i => $reg) {
            $row = [
                $i + 1,
                $reg['name'] ?? '',
                $reg['nik'] ?? '',
                $reg['agency_name'] ?? '',
                $reg['package_name'] ?? '',
                strtoupper($reg['status'] ?? ''),
                !empty($reg['created_at']) ? date('d/m/Y H:i', strtotime($reg['created_at'])) : '',
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

    public function equipment()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
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

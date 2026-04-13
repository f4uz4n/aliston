<?php

namespace App\Models;

use CodeIgniter\Model;

class TravelSavingModel extends Model
{
    protected $table = 'travel_savings';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'agency_id', 'name', 'nik', 'phone', 'total_balance', 'status',
        'package_id', 'participant_id', 'claimed_at', 'notes'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['normalizeTabunganJamaahName'];
    protected $beforeUpdate = ['normalizeTabunganJamaahName'];
    protected $afterFind = ['formatTabunganNameAfterFind'];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function normalizeTabunganJamaahName(array $data)
    {
        if (! array_key_exists('name', $data['data'])) {
            return $data;
        }
        $v = $data['data']['name'];
        if (is_string($v)) {
            helper('participant');
            $data['data']['name'] = format_nama_jamaah($v);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function formatTabunganNameAfterFind(array $data)
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        helper('participant');
        $payload = $data['data'];

        if ($payload !== [] && array_keys($payload) === range(0, count($payload) - 1)) {
            foreach ($payload as $i => $row) {
                if (is_array($row) && isset($row['name']) && is_string($row['name']) && $row['name'] !== '') {
                    $data['data'][$i]['name'] = format_nama_jamaah($row['name']);
                }
            }

            return $data;
        }

        if (isset($payload['name']) && is_string($payload['name']) && $payload['name'] !== '') {
            $data['data']['name'] = format_nama_jamaah($payload['name']);
        }

        return $data;
    }

    public function getWithAgency()
    {
        return $this->select('travel_savings.*, users.full_name as agency_name')
            ->join('users', 'users.id = travel_savings.agency_id')
            ->orderBy('travel_savings.created_at', 'DESC');
    }

    /** Daftar tabungan per agensi (untuk menu agency). */
    public function getByAgency($agencyId)
    {
        return $this->where('agency_id', $agencyId)->orderBy('created_at', 'DESC');
    }
}

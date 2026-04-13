<?php

namespace App\Models;

use CodeIgniter\Model;

class ParticipantModel extends Model
{
    protected $table = 'participants';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'package_id', 'agency_id', 'nik', 'name', 'place_of_birth',
        'date_of_birth', 'gender', 'address', 'rt_rw', 'kelurahan',
        'kecamatan', 'kabupaten', 'provinsi', 'religion',
        'marital_status', 'occupation', 'blood_type', 'nationality',
        'phone', 'status', 'is_verified', 'verified_at', 'verified_by',
        'has_passport', 'passport_number', 'passport_type', 'passport_full_name', 'passport_place_of_birth',
        'passport_issuance_date', 'passport_expiry_date', 'passport_issuance_city',
        'passport_reg_number', 'passport_issuing_office', 'passport_name_idn',
        'emergency_name', 'emergency_relationship',
        'emergency_phone', 'checklist',
        'hotel_upgrade_id', 'room_upgrade_id', 'upgrade_cost',
        'is_boarded', 'boarded_at',
        'cancelled_at', 'refund_amount', 'cancellation_notes', 'refund_rekening', 'refund_bank_name'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $beforeInsert = ['normalizeJamaahNames'];
    protected $beforeUpdate = ['normalizeJamaahNames'];
    protected $afterFind = ['formatJamaahNamesAfterFind'];

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function normalizeJamaahNames(array $data)
    {
        helper('participant');
        $fields = ['name', 'passport_full_name', 'passport_name_idn', 'emergency_name'];
        foreach ($fields as $field) {
            if (! array_key_exists($field, $data['data'])) {
                continue;
            }
            $v = $data['data'][$field];
            if ($v === null) {
                continue;
            }
            if (is_string($v)) {
                $data['data'][$field] = format_nama_jamaah($v);
            }
        }

        return $data;
    }

    /**
     * Tampilan konsisten huruf besar untuk data lama & baru.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function formatJamaahNamesAfterFind(array $data)
    {
        if (! isset($data['data']) || ! is_array($data['data'])) {
            return $data;
        }

        helper('participant');
        $payload = $data['data'];

        if ($payload !== [] && array_keys($payload) === range(0, count($payload) - 1)) {
            foreach ($payload as $i => $row) {
                if (is_array($row)) {
                    $data['data'][$i] = $this->applyJamaahNameFormatting($row);
                }
            }

            return $data;
        }

        if (isset($payload['id']) || isset($payload['name'])) {
            $data['data'] = $this->applyJamaahNameFormatting($payload);
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function applyJamaahNameFormatting(array $row): array
    {
        return format_participant_name_row($row);
    }

    public function getParticipantBuilder()
    {
        return $this->select('participants.*, travel_packages.name as package_name, travel_packages.price as package_price, users.full_name as agency_name, users.username as agency_username')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id')
            ->orderBy('participants.created_at', 'DESC');
    }

    public function getParticipantsWithDetails()
    {
        return $this->getParticipantBuilder()->findAll();
    }

    public function getFilteredParticipants($filters = [])
    {
        $builder = $this->select('participants.*, travel_packages.name as package_name, users.full_name as agency_name')
            ->join('travel_packages', 'travel_packages.id = participants.package_id')
            ->join('users', 'users.id = participants.agency_id');

        if (!empty($filters['start_date'])) {
            $builder->where('participants.created_at >=', $filters['start_date'] . ' 00:00:00');
        }
        if (!empty($filters['end_date'])) {
            $builder->where('participants.created_at <=', $filters['end_date'] . ' 23:59:59');
        }
        if (!empty($filters['agency_id'])) {
            $builder->where('participants.agency_id', $filters['agency_id']);
        }
        if (!empty($filters['package_id'])) {
            $builder->where('participants.package_id', $filters['package_id']);
        }
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('participants.name', $filters['search'])
                ->orLike('participants.nik', $filters['search'])
                ->groupEnd();
        }

        return $builder->orderBy('participants.created_at', 'DESC')->findAll();
    }

    public function getRegistrationTrends($days = 30)
    {
        return $this->select("DATE(created_at) as date, COUNT(id) as total")
            ->where('created_at >=', date('Y-m-d', strtotime("-$days days")))
            ->groupBy("DATE(created_at)")
            ->orderBy("DATE(created_at)", "ASC")
            ->findAll();
    }
}

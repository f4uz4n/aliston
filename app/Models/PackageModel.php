<?php

namespace App\Models;

use CodeIgniter\Model;

class PackageModel extends Model
{
    protected $table = 'travel_packages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name', 'image', 'departure_date', 'show_departure_month_year_only', 'pamphlet_departure_months', 'pamphlet_departure_year', 'duration', 'location_start_end',
        'hotel_mekkah', 'hotel_mekkah_stars', 'hotel_mekkah_id',
        'hotel_madinah', 'hotel_madinah_stars', 'hotel_madinah_id',
        'airline', 'flight_route', 'price', 'price_unit', 'commission_per_pax',
        'inclusions', 'freebies', 'exclusions', 'branch_info', 'is_active'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Validation
    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'price' => 'required|decimal',
    ];
}

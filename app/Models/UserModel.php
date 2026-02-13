<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username', 'password', 'role', 'is_active', 'full_name', 'email', 'phone', 'address', 'nomor_rekening', 'nama_bank', 'profile_pic', 'company_logo', 'company_name', 'slogan', 'no_sk_perijinan', 'tanggal_sk_perijinan', 'leave_letter_last_number', 'leave_letter_last_year', 'recommendation_letter_last_number', 'recommendation_letter_last_year', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
}

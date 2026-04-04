<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * Akun demo Admin Kantor (jalankan sekali: php spark db:seed OfficeAdminSeeder).
 * Lewati jika username sudah ada.
 */
class OfficeAdminSeeder extends Seeder
{
    public function run()
    {
        $username = 'admin_kantor';
        if ($this->db->table('users')->where('username', $username)->countAllResults() > 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->table('users')->insert([
            'username'   => $username,
            'password'   => password_hash('AdminKantor123', PASSWORD_DEFAULT),
            'role'       => 'office_admin',
            'is_active'  => 1,
            'full_name'  => 'Admin Kantor',
            'email'      => null,
            'phone'      => '081000000000',
            'address'    => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}

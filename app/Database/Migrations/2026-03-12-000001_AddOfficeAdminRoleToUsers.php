<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Menambah role office_admin (Admin Kantor) — akses back office tanpa melihat komisi.
 */
class AddOfficeAdminRoleToUsers extends Migration
{
    public function up()
    {
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('owner','agency','office_admin') NOT NULL DEFAULT 'agency'");
    }

    public function down()
    {
        $this->db->query("UPDATE `users` SET `role` = 'agency' WHERE `role` = 'office_admin'");
        $this->db->query("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('owner','agency') NOT NULL DEFAULT 'agency'");
    }
}

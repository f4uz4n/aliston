<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeTanggalSkPerijinanToString extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('users', [
            'tanggal_sk_perijinan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('users', [
            'tanggal_sk_perijinan' => [
                'type' => 'DATE',
                'null' => true,
            ],
        ]);
    }
}

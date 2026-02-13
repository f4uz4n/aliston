<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSkPerijinanToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'no_sk_perijinan' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'slogan'
            ],
            'tanggal_sk_perijinan' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'no_sk_perijinan'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['no_sk_perijinan', 'tanggal_sk_perijinan']);
    }
}

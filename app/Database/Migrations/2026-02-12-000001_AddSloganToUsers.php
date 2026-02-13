<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSloganToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'slogan' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'after' => 'company_name'
            ],
        ];
        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'slogan');
    }
}

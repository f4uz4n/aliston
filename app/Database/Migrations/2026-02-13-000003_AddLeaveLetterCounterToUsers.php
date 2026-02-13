<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLeaveLetterCounterToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'leave_letter_last_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => true,
                'after' => 'tanggal_sk_perijinan'
            ],
            'leave_letter_last_year' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => true,
                'after' => 'leave_letter_last_number'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['leave_letter_last_number', 'leave_letter_last_year']);
    }
}

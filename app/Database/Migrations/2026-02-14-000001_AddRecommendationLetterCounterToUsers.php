<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecommendationLetterCounterToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'recommendation_letter_last_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'null' => true,
                'after' => 'leave_letter_last_year'
            ],
            'recommendation_letter_last_year' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => true,
                'after' => 'recommendation_letter_last_number'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['recommendation_letter_last_number', 'recommendation_letter_last_year']);
    }
}

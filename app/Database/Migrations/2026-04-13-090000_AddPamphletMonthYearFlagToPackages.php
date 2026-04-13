<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPamphletMonthYearFlagToPackages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_packages', [
            'show_departure_month_year_only' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'departure_date',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_packages', ['show_departure_month_year_only']);
    }
}

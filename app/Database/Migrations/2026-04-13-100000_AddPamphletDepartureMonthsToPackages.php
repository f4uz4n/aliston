<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPamphletDepartureMonthsToPackages extends Migration
{
    public function up()
    {
        $this->forge->addColumn('travel_packages', [
            'pamphlet_departure_months' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'after' => 'show_departure_month_year_only',
            ],
            'pamphlet_departure_year' => [
                'type' => 'INT',
                'constraint' => 4,
                'null' => true,
                'after' => 'pamphlet_departure_months',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('travel_packages', ['pamphlet_departure_months', 'pamphlet_departure_year']);
    }
}

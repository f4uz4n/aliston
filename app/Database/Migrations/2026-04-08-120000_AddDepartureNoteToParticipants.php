<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDepartureNoteToParticipants extends Migration
{
    public function up()
    {
        if (! $this->db->fieldExists('departure_note', 'participants')) {
            $this->forge->addColumn('participants', [
                'departure_note' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 500,
                    'null'       => true,
                    'after'      => 'package_id',
                ],
            ]);
        }
    }

    public function down()
    {
        if ($this->db->fieldExists('departure_note', 'participants')) {
            $this->forge->dropColumn('participants', 'departure_note');
        }
    }
}

<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabel riwayat_transaksi untuk database tenant.
 * Kolom mengikuti field list dari INSERT (invoice/payment gateway).
 */
class CreateRiwayatTransaksiTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'trx_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'nisn_siswa' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'merchant_kode' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'customer_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'product' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'total_amount' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,2',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 32,
                'null'       => true,
            ],
            'status_message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'gateway_response' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'gateway_url' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'gateway_reference' => [
                'type'       => 'VARCHAR',
                'constraint' => 128,
                'null'       => true,
            ],
            'expired_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('trx_id');
        $this->forge->addKey('nisn_siswa');
        $this->forge->addKey('status');
        $this->forge->createTable('riwayat_transaksi');
    }

    public function down()
    {
        $this->forge->dropTable('riwayat_transaksi', true);
    }
}

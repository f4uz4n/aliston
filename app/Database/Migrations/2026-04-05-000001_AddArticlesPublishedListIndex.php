<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Indeks komposit untuk query daftar artikel publik:
 * WHERE is_published = 1 AND published_at <= ? ORDER BY published_at DESC
 */
class AddArticlesPublishedListIndex extends Migration
{
    public function up()
    {
        $this->db->query(
            'ALTER TABLE `articles` ADD INDEX `idx_articles_published_list` (`is_published`, `published_at`)'
        );
    }

    public function down()
    {
        $this->db->query('ALTER TABLE `articles` DROP INDEX `idx_articles_published_list`');
    }
}

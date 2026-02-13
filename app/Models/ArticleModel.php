<?php

namespace App\Models;

use CodeIgniter\Model;

class ArticleModel extends Model
{
    protected $table = 'articles';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'slug', 'excerpt', 'content', 'image',
        'author_id', 'is_published', 'published_at', 'created_at', 'updated_at'
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';

    /**
     * Daftar artikel untuk admin (semua)
     */
    public function getListForAdmin()
    {
        return $this->select('articles.*, users.full_name as author_name')
            ->join('users', 'users.id = articles.author_id', 'left')
            ->orderBy('articles.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Artikel yang dipublikasi untuk tampilan depan (urut terbaru)
     */
    public function getPublishedList($limit = 10, $offset = 0)
    {
        return $this->where('is_published', 1)
            ->where('published_at <=', date('Y-m-d H:i:s'))
            ->orderBy('published_at', 'DESC')
            ->findAll($limit, $offset);
    }

    /**
     * Satu artikel by slug (hanya yang published)
     */
    public function getBySlugPublic($slug)
    {
        return $this->where('slug', $slug)
            ->where('is_published', 1)
            ->where('published_at <=', date('Y-m-d H:i:s'))
            ->first();
    }

    /**
     * Generate slug unik dari judul
     */
    public function generateSlug($title, $excludeId = null)
    {
        $slug = url_title($title, '-', true);
        $builder = $this->where('slug', $slug);
        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }
        if ($builder->first()) {
            $slug .= '-' . time();
        }
        return $slug;
    }
}

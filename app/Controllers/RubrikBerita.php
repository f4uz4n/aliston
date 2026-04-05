<?php

namespace App\Controllers;

use App\Models\ArticleModel;
use App\Models\UserModel;

class RubrikBerita extends BaseController
{
    protected $articleModel;
    protected $userModel;

    public function __construct()
    {
        $this->articleModel = new ArticleModel();
        $this->userModel = new UserModel();
    }

    /**
     * Ubah nilai datetime-local (YYYY-MM-DDTHH:MM) ke format MySQL datetime.
     * Jika kosong saat publikasi, gunakan $fallbackIfEmpty (mis. tanggal terbit lama saat edit).
     */
    protected function normalizePublishedAt(?string $raw, bool $published, ?string $fallbackIfEmpty = null): ?string
    {
        if (! $published) {
            return null;
        }
        $raw = trim((string) $raw);
        if ($raw === '') {
            if ($fallbackIfEmpty !== null && $fallbackIfEmpty !== '') {
                $ts = strtotime($fallbackIfEmpty);

                return $ts !== false ? date('Y-m-d H:i:s', $ts) : date('Y-m-d H:i:s');
            }

            return date('Y-m-d H:i:s');
        }
        $raw = str_replace('T', ' ', $raw);
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $raw)) {
            $raw .= ':00';
        }
        $ts = strtotime($raw);
        if ($ts === false) {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', $ts);
    }

    public function index()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $articles = $this->articleModel->getListForAdmin();
        $data = [
            'title' => 'Rubrik Berita',
            'articles' => $articles,
        ];
        return view('owner/rubrik_berita/index', $data);
    }

    public function create()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $data = ['title' => 'Tulis Artikel'];
        return view('owner/rubrik_berita/form', $data);
    }

    public function store()
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $title = $this->request->getPost('title');
        if (empty(trim($title))) {
            return redirect()->back()->withInput()->with('error', 'Judul wajib diisi.');
        }

        $slug = $this->articleModel->generateSlug($title);
        $published = (int) $this->request->getPost('is_published');
        $publishedAt = $this->normalizePublishedAt($this->request->getPost('published_at'), (bool) $published);

        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->request->getPost('excerpt'),
            'content' => $this->request->getPost('content'),
            'author_id' => session()->get('id'),
            'is_published' => $published,
            'published_at' => $publishedAt,
        ];

        $img = $this->request->getFile('image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $dir = FCPATH . 'uploads/articles';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $newName = $img->getRandomName();
            $img->move($dir, $newName);
            $data['image'] = 'uploads/articles/' . $newName;
        }

        $this->articleModel->insert($data);
        return redirect()->to('owner/rubrik-berita')->with('msg', 'Artikel berhasil ditambahkan.');
    }

    public function edit($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $article = $this->articleModel->find($id);
        if (!$article) {
            return redirect()->to('owner/rubrik-berita')->with('error', 'Artikel tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Artikel',
            'article' => $article,
        ];
        return view('owner/rubrik_berita/form', $data);
    }

    public function update($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $article = $this->articleModel->find($id);
        if (!$article) {
            return redirect()->to('owner/rubrik-berita')->with('error', 'Artikel tidak ditemukan.');
        }

        $title = $this->request->getPost('title');
        if (empty(trim($title))) {
            return redirect()->back()->withInput()->with('error', 'Judul wajib diisi.');
        }

        $slug = $this->articleModel->generateSlug($title, $id);
        $published = (int) $this->request->getPost('is_published');
        $publishedAt = $this->normalizePublishedAt(
            $this->request->getPost('published_at'),
            (bool) $published,
            $article['published_at'] ?? null
        );

        $data = [
            'title' => $title,
            'slug' => $slug,
            'excerpt' => $this->request->getPost('excerpt'),
            'content' => $this->request->getPost('content'),
            'is_published' => $published,
            'published_at' => $publishedAt,
        ];

        $img = $this->request->getFile('image');
        if ($img && $img->isValid() && !$img->hasMoved()) {
            $newName = $img->getRandomName();
            $dir = FCPATH . 'uploads/articles';
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $img->move($dir, $newName);
            $data['image'] = 'uploads/articles/' . $newName;
        }

        $this->articleModel->update($id, $data);
        return redirect()->to('owner/rubrik-berita')->with('msg', 'Artikel berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $article = $this->articleModel->find($id);
        if (!$article) {
            return redirect()->to('owner/rubrik-berita')->with('error', 'Artikel tidak ditemukan.');
        }

        $this->articleModel->delete($id);
        return redirect()->to('owner/rubrik-berita')->with('msg', 'Artikel berhasil dihapus.');
    }

    /**
     * Ubah status publikasi dari daftar (toggle Draft ↔ Dipublikasikan).
     */
    public function toggleStatus($id)
    {
        if (! is_back_office()) {
            return redirect()->to('/login');
        }

        $article = $this->articleModel->find($id);
        if (!$article) {
            return redirect()->to('owner/rubrik-berita')->with('error', 'Artikel tidak ditemukan.');
        }

        $newStatus = empty($article['is_published']) ? 1 : 0;
        $publishedAt = $newStatus ? date('Y-m-d H:i:s') : null;

        $this->articleModel->update($id, [
            'is_published' => $newStatus,
            'published_at' => $publishedAt,
        ]);

        $msg = $newStatus ? 'Artikel dipublikasikan dan tampil di halaman depan.' : 'Artikel dijadikan draft (tidak tampil di depan).';
        return redirect()->to('owner/rubrik-berita')->with('msg', $msg);
    }
}

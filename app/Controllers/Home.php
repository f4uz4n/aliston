<?php

namespace App\Controllers;

use App\Models\PackageModel;
use App\Models\UserModel;
use App\Models\TestimonialModel;
use App\Models\ArticleModel;

class Home extends BaseController
{
    public function index()
    {
        helper('branding');
        helper('package');
        $packageModel = new PackageModel();
        $userModel = new UserModel();
        $bannerModel = new \App\Models\BannerModel();
        $testimonialModel = new TestimonialModel();
        $articleModel = new ArticleModel();

        $today = date('Y-m-d');
        $allActive = $packageModel->where('departure_date >=', $today)->orderBy('departure_date', 'ASC')->findAll();

        $kategori = $this->request->getGet('kategori');
        $durasi = $this->request->getGet('durasi');

        // Kategori = daftar nama paket yang sudah dibuat (paket aktif)
        $packageListForCategory = $allActive;

        $packages = $allActive;
        if ($kategori && $kategori !== 'semua' && is_numeric($kategori)) {
            $packages = array_filter($packages, function ($p) use ($kategori) {
                return (string) ($p['id'] ?? '') === (string) $kategori;
            });
            $packages = array_values($packages);
        }
        if ($durasi && $durasi !== 'semua' && $durasi !== '') {
            $packages = array_filter($packages, function ($p) use ($durasi) {
                return (trim($p['duration'] ?? '') === $durasi);
            });
        }
        $packages = array_values($packages);

        // Durasi hanya dari paket yang sudah dibuat (aktif)
        $durations = array_unique(array_filter(array_map(function ($p) {
            return trim($p['duration'] ?? '');
        }, $allActive)));
        sort($durations);

        $agencies = $userModel->where('role', 'agency')->where('is_active', 1)->orderBy('full_name', 'ASC')->findAll();
        $owner = $userModel->where('role', 'owner')->first();
        $banners = $bannerModel->getForSlider();
        $testimonials = $testimonialModel->getVerifiedList(20);
        $articlesLatest = $articleModel->getPublishedList(6);
        $captchaA = rand(1, 15);
        $captchaB = rand(15, 25);
        session()->set('testimoni_captcha', $captchaA + $captchaB);
        $data = [
            'packages' => $packages,
            'agencies' => $agencies,
            'owner' => $owner,
            'banners' => $banners,
            'testimonials' => $testimonials,
            'captcha_a' => $captchaA,
            'captcha_b' => $captchaB,
            'durations' => $durations,
            'package_list_for_category' => $packageListForCategory,
            'filter_kategori' => $kategori,
            'filter_durasi' => $durasi,
            'articles_latest' => $articlesLatest,
        ];
        return view('home/index', $data);
    }

    /**
     * Halaman daftar berita/artikel (publik).
     */
    public function berita()
    {
        helper('branding');
        $articleModel = new ArticleModel();
        $articles = $articleModel->getPublishedList(20);
        $data = ['articles' => $articles];
        return view('home/berita', $data);
    }

    /**
     * Halaman detail artikel by slug (publik).
     */
    public function beritaDetail($slug)
    {
        helper('branding');
        $articleModel = new ArticleModel();
        $article = $articleModel->getBySlugPublic($slug);
        if (!$article) {
            return redirect()->to('/berita')->with('error', 'Artikel tidak ditemukan.');
        }
        $data = ['article' => $article];
        return view('home/berita_detail', $data);
    }

    /**
     * Halaman detail paket (publik): hanya paket dengan tanggal keberangkatan >= hari ini.
     */
    public function packageDetail($id)
    {
        helper('branding');
        helper('package');
        $packageModel = new PackageModel();
        $userModel = new UserModel();
        $pkg = $packageModel->find($id);
        if (!$pkg) {
            return redirect()->to('/')->with('error', 'Paket tidak ditemukan.');
        }
        $today = date('Y-m-d');
        $dep = $pkg['departure_date'] ?? '';
        if (is_string($dep) && strlen($dep) >= 10) {
            $depDate = substr($dep, 0, 10);
            if ($depDate < $today) {
                return redirect()->to('/')->with('error', 'Paket ini sudah lewat tanggal keberangkatan.');
            }
        }
        $agencies = $userModel->where('role', 'agency')->where('is_active', 1)->orderBy('full_name', 'ASC')->findAll();
        $owner = $userModel->where('role', 'owner')->first();
        $data = [
            'package' => $pkg,
            'agencies' => $agencies,
            'owner' => $owner,
        ];
        return view('home/package_detail', $data);
    }

    /**
     * Halaman daftar semua agen mitra (publik).
     */
    public function agenMitra()
    {
        helper('branding');
        $userModel = new UserModel();
        $agencies = $userModel->where('role', 'agency')->where('is_active', 1)->orderBy('full_name', 'ASC')->findAll();
        $data = ['agencies' => $agencies];
        return view('home/agen_mitra', $data);
    }

    /**
     * Halaman testimoni jamaah (publik): form input + daftar testimoni yang sudah diverifikasi.
     */
    public function testimoni()
    {
        helper('branding');
        $packageModel = new PackageModel();
        $testimonialModel = new TestimonialModel();

        $captchaA = rand(1, 15);
        $captchaB = rand(1, 15);
        session()->set('testimoni_captcha', $captchaA + $captchaB);

        $data = [
            'packages' => $packageModel->orderBy('name')->findAll(),
            'testimonials' => $testimonialModel->getVerifiedList(30),
            'captcha_a' => $captchaA,
            'captcha_b' => $captchaB,
        ];
        return view('home/testimoni', $data);
    }

    /**
     * Submit testimoni dari form publik (dengan captcha).
     */
    public function submitTestimoni()
    {
        $expected = session()->get('testimoni_captcha');
        session()->remove('testimoni_captcha');
        $answer = (int) $this->request->getPost('captcha_answer');

        if ($expected === null || $answer !== $expected) {
            return redirect()->to('testimoni-jamaah')->withInput()->with('error', 'Captcha salah. Silakan coba lagi.');
        }

        $rules = [
            'name' => 'required|min_length[2]|max_length[255]',
            'package_id' => 'permit_empty|integer',
            'testimonial' => 'required|min_length[10]',
            'rating' => 'required|in_list[1,2,3,4,5]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to('testimoni-jamaah')->withInput()->with('error', 'Lengkapi nama, rating, dan testimoni dengan benar.');
        }

        $testimonialModel = new TestimonialModel();
        $testimonialModel->insert([
            'name' => $this->request->getPost('name'),
            'package_id' => $this->request->getPost('package_id') ?: null,
            'testimonial' => $this->request->getPost('testimonial'),
            'rating' => (int) $this->request->getPost('rating'),
            'status' => 'pending',
            'source' => 'public',
            'agency_id' => null,
        ]);
        return redirect()->to(base_url() . '#form-testimoni')->with('msg', 'Terima kasih! Testimoni Anda telah dikirim dan akan ditinjau oleh admin sebelum dipublikasikan.');
    }
}

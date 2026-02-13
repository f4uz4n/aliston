<?php
helper('branding');
helper('package');
$companyName = get_company_name();
$owner = $owner ?? null;
$ownerPhone = $owner['phone'] ?? '';
$ownerEmail = $owner['email'] ?? '';
$this->extend('layouts/public');
?>
<?= $this->section('content') ?>

<?php $banners = $banners ?? []; ?>
<section id="hero" class="position-relative text-white">
    <?php if (!empty($banners)): ?>
    <!-- Slider banner full width -->
    <div id="heroBannerCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="15000">
        <div class="carousel-inner">
            <?php foreach ($banners as $i => $b): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <img src="<?= base_url($b['image']) ?>" class="d-block w-100" alt="Banner <?= $i + 1 ?>" style="object-fit: cover; width: 100%; height: 70vh; min-height: 400px;">
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($banners) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Sebelumnya</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroBannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Selanjutnya</span>
        </button>
        <div class="carousel-indicators">
            <?php foreach ($banners as $i => $b): ?>
            <button type="button" data-bs-target="#heroBannerCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <!-- Overlay gelap + teks di atas slider -->
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center" style="background: linear-gradient(135deg, rgba(26,26,46,0.75) 0%, rgba(154,24,48,0.6) 100%); pointer-events: none;">
        <div class="container py-5" style="pointer-events: auto;">
            <div class="row justify-content-center text-center">
                <div class="col-lg-8">
                    <h1 class="display-5 fw-bold mb-3">Perjalanan Ibadah Anda, Prioritas Kami</h1>
                    <p class="lead mb-4 opacity-90"><?= esc($companyName) ?> menyediakan paket perjalanan berkualitas dengan kemudahan pendaftaran melalui admin atau agen mitra terpercaya di sekitar Anda.</p>
                    <a href="#paket" class="btn btn-light btn-lg rounded-pill px-4 fw-bold me-2"><i class="bi bi-grid-3x3-gap me-2"></i>Lihat Paket</a>
                    <a href="#kontak" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">Hubungi Kami</a>
                </div>
            </div>
        </div>
    </div>
    <?php else: ?>
    <!-- Tanpa banner: tampilan default gradient -->
    <div class="hero-gradient py-5">
        <div class="container py-5">
            <div class="row align-items-center min-vh-50 py-5">
                <div class="col-lg-7 text-center text-lg-start">
                    <h1 class="display-5 fw-bold mb-3">Perjalanan Ibadah Anda, Prioritas Kami</h1>
                    <p class="lead mb-4 opacity-90"><?= esc($companyName) ?> menyediakan paket perjalanan berkualitas dengan kemudahan pendaftaran melalui admin atau agen mitra terpercaya di sekitar Anda.</p>
                    <a href="#paket" class="btn btn-light btn-lg rounded-pill px-4 fw-bold me-2"><i class="bi bi-grid-3x3-gap me-2"></i>Lihat Paket</a>
                    <a href="#kontak" class="btn btn-outline-light btn-lg rounded-pill px-4 fw-bold">Hubungi Kami</a>
                </div>
                <div class="col-lg-5 text-center mt-5 mt-lg-0">
                    <i class="bi bi-airplane-engines display-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<!-- Our Services -->
<section id="layanan" class="section-services py-5">
    <div class="services-overlay"></div>
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title display-6 mb-2">Layanan Kami</h2>
            <p class="text-secondary">Berbagai layanan perjalanan ibadah dan wisata dengan kualitas terbaik</p>
        </div>
        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="text-start">
                    <div class="service-icon-circle">
                        <i class="bi bi-clipboard2-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Beragam Layanan</h5>
                    <p class="text-secondary small mb-0">Kami menyediakan layanan paket umroh, paket haji, Haji tanpa antri/furoda, paket wisata, badal haji, badal umroh dan sebagainya.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-start">
                    <div class="service-icon-circle">
                        <i class="bi bi-megaphone"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Pembimbing Ibadah / Tour Leader</h5>
                    <p class="text-secondary small mb-0">SDM yang handal, menguasai peran dan memahami kebutuhan jamaah merupakan kunci dalam sebuah layanan. <?= esc($companyName) ?> menerapkan standar kualitas yang tinggi bagi pembimbing dan tour leader.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-start">
                    <div class="service-icon-circle">
                        <i class="bi bi-cash-stack"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Harga Terjangkau</h5>
                    <p class="text-secondary small mb-0">Dapatkan beragam pilihan paket umroh & haji dengan penawaran terbaik. Jaminan harga yang terjangkau dengan fasilitas dan layanan yang maksimal.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="text-start">
                    <div class="service-icon-circle">
                        <i class="bi bi-award"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-3">Travel Terpercaya</h5>
                    <p class="text-secondary small mb-0">Berpengalaman lebih dari 20 tahun, <?= esc($companyName) ?> memiliki izin sebagai penyelenggara umrah dan haji khusus serta telah bersertifikat resmi kualitas layanan terbaik.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
$durations = $durations ?? [];
$package_list_for_category = $package_list_for_category ?? [];
$filter_kategori = $filter_kategori ?? '';
$filter_durasi = $filter_durasi ?? '';
$paketChunks = array_chunk($packages ?? [], 3);
?>
<section id="paket" class="py-5 bg-light">
    <div class="container py-4">
        <div class="text-center mb-4">
            <h2 class="section-title display-6 mb-2">Paket Perjalanan</h2>
            <p class="text-secondary">Pilih paket yang sesuai. Untuk pendaftaran, hubungi admin atau agen mitra kami.</p>
        </div>

        <!-- Form Pencarian (overlay gelap) -->
        <div class="rounded-4 overflow-hidden shadow-sm mb-5" style="background: linear-gradient(135deg, rgba(26,26,46,0.92) 0%, rgba(40,40,60,0.9) 100%); min-height: 140px;">
            <div class="p-4 p-md-5">
                <h3 class="text-white mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-search"></i>
                    Paket Umrah Murah
                </h3>
                <form method="get" action="<?= base_url() ?>#paket" class="row g-3 align-items-end flex-wrap">
                    <div class="col-12 col-md">
                        <label class="form-label text-white small mb-1">Pilih Paket</label>
                        <select name="kategori" class="form-select form-select-lg bg-white">
                            <option value="semua" <?= ($filter_kategori === '' || $filter_kategori === 'semua') ? 'selected' : '' ?>>Semua Paket</option>
                            <?php foreach ($package_list_for_category as $pCat): ?>
                            <option value="<?= (int) $pCat['id'] ?>" <?= (string)($filter_kategori) === (string)($pCat['id']) ? 'selected' : '' ?>><?= esc($pCat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md">
                        <label class="form-label text-white small mb-1">Pilih Durasi</label>
                        <select name="durasi" class="form-select form-select-lg bg-white">
                            <option value="semua" <?= ($filter_durasi === '' || $filter_durasi === 'semua') ? 'selected' : '' ?>>Semua Durasi</option>
                            <?php foreach ($durations as $dr): ?>
                            <option value="<?= esc($dr) ?>" <?= $filter_durasi === $dr ? 'selected' : '' ?>><?= esc($dr) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-danger btn-lg w-100 w-md-auto px-4 d-inline-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-search"></i>
                            Cari Tour
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if (empty($packages)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted display-4"></i>
            <p class="text-secondary mt-3">Tidak ada paket yang sesuai filter. Coba ubah kategori atau durasi.</p>
        </div>
        <?php else: ?>
        <!-- Slider paket (3 kartu per slide) -->
        <div id="paketCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
            <div class="carousel-inner">
                <?php foreach ($paketChunks as $slideIndex => $chunk): ?>
                <div class="carousel-item <?= $slideIndex === 0 ? 'active' : '' ?>">
                    <div class="row g-4">
                        <?php foreach ($chunk as $p): ?>
                        <div class="col-md-4">
                            <div class="card card-package h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                                <?php if (!empty($p['image']) && is_file(FCPATH . $p['image'])): ?>
                                    <img src="<?= base_url($p['image']) ?>" class="card-img-top" alt="<?= esc($p['name']) ?>" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="bi bi-image text-primary display-4"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title fw-bold text-dark"><?= esc($p['name']) ?></h5>
                                    <p class="text-primary fw-bold mb-2"><?= format_price_display($p['price']) ?></p>
                                    <p class="small text-secondary mb-3">
                                        <i class="bi bi-calendar-event me-1"></i> <?= date('d M Y', strtotime($p['departure_date'])) ?>
                                        &nbsp;&middot;&nbsp;
                                        <i class="bi bi-clock me-1"></i> <?= esc($p['duration'] ?? '-') ?>
                                    </p>
                                    <a href="<?= base_url('package/' . $p['id']) ?>" class="btn btn-outline-primary rounded-pill w-100">
                                        <i class="bi bi-info-circle me-1"></i> Detail Paket
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($chunk) < 3): for ($k = count($chunk); $k < 3; $k++): ?>
                        <div class="col-md-4"></div>
                        <?php endfor; endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($paketChunks) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#paketCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#paketCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Selanjutnya</span>
            </button>
            <div class="carousel-indicators position-relative mt-4">
                <?php foreach ($paketChunks as $i => $ch): ?>
                <button type="button" data-bs-target="#paketCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?> bg-secondary" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php
$articles_latest = $articles_latest ?? [];
if (!empty($articles_latest)):
?>
<section id="berita" class="py-5 bg-white">
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
            <div>
                <h2 class="section-title display-6 mb-2">Berita & Artikel</h2>
                <p class="text-secondary mb-0">Artikel terbaru dari <?= esc($companyName) ?></p>
            </div>
            <a href="<?= base_url('berita') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Semua Berita <i class="bi bi-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-4">
            <?php foreach (array_slice($articles_latest, 0, 3) as $art): ?>
            <div class="col-md-4">
                <a href="<?= base_url('berita/' . esc($art['slug'])) ?>" class="text-decoration-none text-dark">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-package">
                        <?php if (!empty($art['image'])): ?>
                            <img src="<?= base_url($art['image']) ?>" class="card-img-top" alt="<?= esc($art['title']) ?>" style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="bi bi-newspaper text-primary display-4"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-2"><?= esc($art['title']) ?></h5>
                            <p class="small text-secondary mb-0"><?= esc(strlen($art['excerpt'] ?? '') > 100 ? substr($art['excerpt'], 0, 100) . '…' : ($art['excerpt'] ?? '')) ?></p>
                            <small class="text-muted d-block mt-2"><?= !empty($art['published_at']) ? date('d M Y', strtotime($art['published_at'])) : '' ?></small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php
$testimonials = $testimonials ?? [];
$captcha_a = $captcha_a ?? 1;
$captcha_b = $captcha_b ?? 1;
$testimoniChunks = array_chunk($testimonials, 3);
?>
<!-- Section 1: Slider Testimoni (3 per slide) -->
<section id="testimoni" class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h2 class="section-title display-6 mb-2">Testimoni Jamaah</h2>
            <p class="text-secondary">Pengalaman jamaah yang telah berangkat bersama kami</p>
        </div>

        <?php if (!empty($testimonials)): ?>
        <div id="testimoniCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
            <div class="carousel-inner">
                <?php foreach ($testimoniChunks as $slideIndex => $chunk): ?>
                <div class="carousel-item <?= $slideIndex === 0 ? 'active' : '' ?>">
                    <div class="row g-4">
                        <?php foreach ($chunk as $t): ?>
                        <div class="col-md-4">
                            <div class="card border-0 shadow rounded-4 h-100">
                                <div class="card-body p-4">
                                    <div class="text-warning mb-2" style="font-size: 1rem;">
                                        <?php $r = (int)($t['rating'] ?? 5); echo str_repeat('★', $r) . str_repeat('☆', 5 - $r); ?>
                                    </div>
                                    <?php $txt = preg_replace('/\s+/', ' ', strip_tags($t['testimonial'])); $txt = mb_strlen($txt) > 120 ? mb_substr($txt, 0, 120) . '...' : $txt; ?>
                                    <p class="text-secondary small mb-3" style="min-height: 4.5rem;">"<?= esc($txt) ?>"</p>
                                    <h6 class="fw-bold text-dark mb-0"><?= esc($t['name']) ?></h6>
                                    <small class="text-muted"><?= esc($t['package_name'] ?? 'Paket Umum') ?></small>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if (count($chunk) < 3): for ($k = count($chunk); $k < 3; $k++): ?>
                        <div class="col-md-4"></div>
                        <?php endfor; endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php if (count($testimoniChunks) > 1): ?>
            <button class="carousel-control-prev" type="button" data-bs-target="#testimoniCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Sebelumnya</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#testimoniCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                <span class="visually-hidden">Selanjutnya</span>
            </button>
            <div class="carousel-indicators position-relative mt-4">
                <?php foreach ($testimoniChunks as $i => $ch): ?>
                <button type="button" data-bs-target="#testimoniCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?> bg-secondary" aria-label="Slide <?= $i + 1 ?>"></button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow rounded-4">
            <div class="card-body py-5 text-center text-muted">
                <i class="bi bi-chat-quote display-4 opacity-25"></i>
                <p class="mb-0 mt-2">Belum ada testimoni yang dipublikasikan.</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Section Kontak + Form Testimoni (2 kolom: kiri Kontak, kanan Form) -->
<section id="kontak" class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="section-title display-6 mb-2">Kontak & Kirim Testimoni</h2>
            <p class="text-secondary">Hubungi kami atau bagikan pengalaman Anda</p>
        </div>
        <div class="row g-4 align-items-start">
            <!-- Kolom kiri: Kontak -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-telephone text-primary me-2"></i>Kontak</h5>
                        <p class="text-secondary mb-3">Ingin daftar atau butuh informasi? Hubungi kami.</p>
                        <h6 class="fw-bold text-dark mb-3"><?= esc($companyName) ?></h6>
                        <?php if ($ownerPhone): ?>
                        <p class="mb-2"><i class="bi bi-whatsapp text-success me-2"></i> <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $ownerPhone) ?>" target="_blank" rel="noopener"><?= esc($ownerPhone) ?></a></p>
                        <?php endif; ?>
                        <?php if ($ownerEmail): ?>
                        <p class="mb-2"><i class="bi bi-envelope text-primary me-2"></i> <a href="mailto:<?= esc($ownerEmail) ?>"><?= esc($ownerEmail) ?></a></p>
                        <?php endif; ?>
                        <?php if (!$ownerPhone && !$ownerEmail): ?>
                        <p class="text-secondary mb-0">Silakan hubungi admin untuk pendaftaran.</p>
                        <?php endif; ?>
                        <hr>
                        <p class="small text-secondary mb-0">Untuk pendaftaran paket, hubungi admin (kontak di atas).</p>
                    </div>
                </div>
            </div>
            <!-- Kolom kanan: Form Kirim Testimoni -->
            <div class="col-lg-6" id="form-testimoni">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                    <div class="card-body">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-pencil-square text-primary me-2"></i>Kirim Testimoni</h5>
                        <?php if (session()->getFlashdata('msg')): ?>
                            <div class="alert alert-success border-0 small"><?= session()->getFlashdata('msg') ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger border-0 small"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>
                        <form action="<?= base_url('testimoni-jamaah/submit') ?>" method="post" id="formTestimoniLanding">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?= esc(old('name')) ?>" required placeholder="Nama lengkap atau inisial" maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Paket (opsional)</label>
                                <select name="package_id" class="form-select">
                                    <option value="">-- Pilih paket --</option>
                                    <?php foreach ($packages as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= old('package_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Rating <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-1" id="ratingStarsLanding">
                                    <?php $oldRating = (int)old('rating') ?: 0; for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star-select text-warning" data-value="<?= $i ?>" style="cursor:pointer;font-size:1.5rem;">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInputLanding" value="<?= $oldRating ?: 5 ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Testimoni <span class="text-danger">*</span></label>
                                <textarea name="testimonial" class="form-control" rows="4" required placeholder="Tulis pengalaman Anda (min. 10 karakter)"><?= esc(old('testimonial')) ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Captcha <span class="text-danger">*</span></label>
                                <p class="small text-secondary mb-1">Berapa <strong><?= (int)$captcha_a ?> + <?= (int)$captcha_b ?></strong> ?</p>
                                <input type="number" name="captcha_answer" class="form-control" required placeholder="Jawaban" min="0" max="99">
                            </div>
                            <button type="submit" class="btn btn-primary-public w-100 rounded-pill fw-bold py-2">Kirim Testimoni</button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">Testimoni akan ditinjau oleh admin sebelum dipublikasikan.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var c = document.getElementById('ratingStarsLanding');
    var input = document.getElementById('ratingInputLanding');
    if (!c || !input) return;
    var stars = c.querySelectorAll('.star-select');
    function up(v) { var n = parseInt(v, 10) || 0; stars.forEach(function(s, i) { s.style.opacity = (i + 1) <= n ? '1' : '0.35'; }); }
    up(input.value);
    stars.forEach(function(s) { s.addEventListener('click', function() { input.value = this.getAttribute('data-value'); up(input.value); }); });
})();
<?php if (session()->getFlashdata('msg') || session()->getFlashdata('error')): ?>
document.addEventListener('DOMContentLoaded', function() { var el = document.getElementById('form-testimoni') || document.getElementById('kontak'); if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' }); });
<?php endif; ?>
</script>
<?= $this->endSection() ?>

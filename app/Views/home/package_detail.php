<?php
helper('branding');
helper('package');
$companyName = get_company_name();
$package = $package ?? [];
$agencies = $agencies ?? [];
$owner = $owner ?? null;
$this->extend('layouts/public');
$bulanMapPamphlet = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$departurePamphletLabel = date('d M Y', strtotime($package['departure_date'] ?? 'now'));
if (!empty($package['show_departure_month_year_only'])) {
    $monthsCsv = (string) ($package['pamphlet_departure_months'] ?? '');
    $yearPamphlet = trim((string) ($package['pamphlet_departure_year'] ?? ''));
    $monthNames = [];
    if ($monthsCsv !== '') {
        foreach (explode(',', $monthsCsv) as $m) {
            $mi = (int) trim($m);
            if (isset($bulanMapPamphlet[$mi])) {
                $monthNames[] = $bulanMapPamphlet[$mi];
            }
        }
        $monthNames = array_values(array_unique($monthNames));
    }
    if (!empty($monthNames) && preg_match('/^\d{4}$/', $yearPamphlet) === 1) {
        $departurePamphletLabel = implode(', ', $monthNames) . ' ' . $yearPamphlet;
    } else {
        $tsDep = strtotime($package['departure_date'] ?? 'now');
        $departurePamphletLabel = ($bulanMapPamphlet[(int) date('n', $tsDep)] ?? date('F', $tsDep)) . ' ' . date('Y', $tsDep);
    }
}
?>
<?= $this->section('content') ?>

<section class="py-5 bg-light">
    <div class="container py-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="<?= base_url() ?>#paket" class="text-decoration-none">Paket Perjalanan</a></li>
                <li class="breadcrumb-item active fw-bold" aria-current="page"><?= esc($package['name']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
                    <?php if (!empty($package['image']) && is_file(FCPATH . $package['image'])): ?>
                        <img src="<?= base_url($package['image']) ?>" alt="<?= esc($package['name']) ?>" class="img-fluid w-100" style="max-height: 450px; object-fit: cover;">
                    <?php else: ?>
                        <div class="p-5 text-center bg-primary bg-opacity-10" style="height: 300px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-image text-primary display-1 opacity-25"></i>
                        </div>
                    <?php endif; ?>
                    <div class="card-body p-4 p-md-5">
                        <h1 class="section-title h3 mb-4"><?= esc($package['name']) ?></h1>
                        <div class="row g-3 mb-4">
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 rounded-4 bg-light border">
                                    <i class="bi bi-calendar-check fs-4 text-primary mb-2"></i>
                                    <small class="text-secondary d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Keberangkatan</small>
                                    <span class="fw-bold text-dark small"><?= esc($departurePamphletLabel) ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 rounded-4 bg-light border">
                                    <i class="bi bi-clock-history fs-4 text-primary mb-2"></i>
                                    <small class="text-secondary d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Durasi</small>
                                    <span class="fw-bold text-dark small"><?= esc($package['duration'] ?? '—') ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 rounded-4 bg-light border">
                                    <i class="bi bi-geo-alt-fill fs-4 text-primary mb-2"></i>
                                    <small class="text-secondary d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Rute</small>
                                    <span class="fw-bold text-dark small"><?= esc($package['location_start_end'] ?? '—') ?></span>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="text-center p-3 rounded-4 bg-light border">
                                    <i class="bi bi-airplane fs-4 text-primary mb-2"></i>
                                    <small class="text-secondary d-block text-uppercase fw-bold" style="font-size: 0.65rem;">Maskapai</small>
                                    <span class="fw-bold text-dark small"><?= esc($package['airline'] ?? '—') ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold text-dark mb-3">Fasilitas Termasuk</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php
                                    $incs = json_decode($package['inclusions'] ?? '[]', true);
                                    if (is_array($incs) && !empty($incs)):
                                        foreach ($incs as $inc):
                                    ?>
                                    <li class="text-secondary mb-2 d-flex align-items-center">
                                        <i class="bi bi-check-circle-fill text-success me-2 small"></i> <?= esc($inc) ?>
                                    </li>
                                    <?php endforeach; else: ?>
                                    <li class="text-muted small">—</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark mb-3">Bonus & Freebies</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php
                                    $frees = json_decode($package['freebies'] ?? '[]', true);
                                    if (is_array($frees) && !empty($frees)):
                                        foreach ($frees as $free):
                                    ?>
                                    <li class="text-secondary mb-2 d-flex align-items-center">
                                        <i class="bi bi-gift-fill text-warning me-2 small"></i> <?= esc($free) ?>
                                    </li>
                                    <?php endforeach; else: ?>
                                    <li class="text-muted small">—</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <?php
                        $excls = json_decode($package['exclusions'] ?? '[]', true);
                        if (is_array($excls) && !empty($excls)):
                        ?>
                        <div class="row g-4 mt-2">
                            <div class="col-12">
                                <h6 class="fw-bold text-dark mb-3">Belum Termasuk</h6>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach ($excls as $ex): ?>
                                    <li class="text-secondary mb-2 d-flex align-items-center">
                                        <i class="bi bi-x-circle text-secondary me-2 small"></i> <?= esc($ex) ?>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 text-white position-relative" style="background: linear-gradient(135deg, var(--primary, #c41e3a) 0%, var(--primary-dark, #9a1830) 100%);">
                    <div class="position-absolute top-0 end-0 opacity-10" style="transform: translate(20%, -20%);">
                        <i class="bi bi-tags-fill" style="font-size: 8rem;"></i>
                    </div>
                    <div class="card-body p-4 p-md-5 position-relative">
                        <h6 class="text-white-50 fw-bold text-uppercase ls-2 mb-2">Harga Paket Mulai Dari</h6>
                        <h2 class="fw-800 mb-2 display-6"><?= format_price_display($package['price']) ?></h2>
                        <p class="mb-4 text-white-50 small">*Sesuai syarat dan ketentuan berlaku</p>
                        <a href="<?= base_url() ?>#kontak" class="btn btn-light btn-lg rounded-pill fw-bold px-4 d-inline-flex align-items-center gap-2">
                            <i class="bi bi-telephone-fill"></i> Hubungi Kami
                        </a>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-4">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-3"><i class="bi bi-building me-2 text-primary"></i>Akomodasi Hotel</h5>
                    <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-4">
                        <div class="bg-white rounded-circle p-2 shadow-sm me-3">
                            <i class="bi bi-stars text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 small"><?= esc($package['hotel_mekkah'] ?? '—') ?></h6>
                            <small class="text-secondary">Mekkah</small>
                            <div class="text-warning mt-1" style="font-size: 0.7rem;"><?= str_repeat('★', (int)($package['hotel_mekkah_stars'] ?? 0)) ?></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center p-3 bg-light rounded-4">
                        <div class="bg-white rounded-circle p-2 shadow-sm me-3">
                            <i class="bi bi-stars text-warning"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 small"><?= esc($package['hotel_madinah'] ?? '—') ?></h6>
                            <small class="text-secondary">Madinah</small>
                            <div class="text-warning mt-1" style="font-size: 0.7rem;"><?= str_repeat('★', (int)($package['hotel_madinah_stars'] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-3"><i class="bi bi-airplane-engines me-2 text-primary"></i>Penerbangan</h5>
                    <div class="p-3 bg-light rounded-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary small">Maskapai</span>
                            <span class="fw-bold text-dark"><?= esc($package['airline'] ?? '—') ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary small">Rute</span>
                            <span class="fw-bold text-dark"><?= esc($package['flight_route'] ?? '—') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="<?= base_url() ?>#paket" class="btn btn-outline-primary rounded-pill px-4">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Paket
            </a>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

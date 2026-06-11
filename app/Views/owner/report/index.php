<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Laporan Bisnis Terperinci</h2>
        <p class="text-secondary mb-0">Analisis performa jamaah, paket, dan agensi Aliston</p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <button onclick="window.print()" class="btn btn-light border rounded-pill px-4 fw-bold">
            <i class="bi bi-printer me-2"></i>Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Section -->
<div class="row mb-5">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-4">
                <form action="<?= base_url('owner/reports') ?>" method="get">
                    <input type="hidden" name="registration_tab" value="<?= esc($registration_tab ?? 'active') ?>">
                    <div class="row align-items-end g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Mulai</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-event text-secondary"></i></span>
                                <input type="date" name="start_date" class="form-control bg-light border-0" value="<?= $start_date ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Akhir</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-calendar-check text-secondary"></i></span>
                                <input type="date" name="end_date" class="form-control bg-light border-0" value="<?= $end_date ?>">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                                    <i class="bi bi-filter me-2"></i>Terapkan Filter
                                </button>
                                <a href="<?= base_url('owner/reports?registration_tab=' . esc($registration_tab ?? 'active')) ?>" class="btn btn-light rounded-pill fw-bold border">
                                    Reset
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Header Cards -->
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-5 g-4 mb-5">
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
            <div class="card-body p-4">
                <div class="bg-white bg-opacity-25 rounded-circle p-2 d-inline-block mb-3">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <h6 class="text-white-50 small fw-bold text-uppercase ls-1">Total Jamaah Aktif</h6>
                <h2 class="fw-800 mb-0"><?= $total_jamaah ?></h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-success">
            <div class="card-body p-4">
                <div class="bg-success-soft text-success rounded-circle p-2 d-inline-block mb-3">
                    <i class="bi bi-check-circle-fill fs-4"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1">Terverifikasi</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $status_breakdown['verified'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-warning">
            <div class="card-body p-4">
                <div class="bg-warning-soft text-warning rounded-circle p-2 d-inline-block mb-3">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1">Menunggu</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $status_breakdown['pending'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-danger">
            <div class="card-body p-4">
                <div class="bg-danger-soft text-danger rounded-circle p-2 d-inline-block mb-3">
                    <i class="bi bi-x-circle-fill fs-4"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1">Dibatalkan</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $status_breakdown['cancelled'] ?></h2>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100 border-start border-4 border-info">
            <div class="card-body p-4">
                <div class="bg-info-soft text-info rounded-circle p-2 d-inline-block mb-3">
                    <i class="bi bi-shop fs-4"></i>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1">Total Agensi</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $total_agencies ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Performa Agensi -->
    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Ranking Performa Agensi</h5>
                <span class="badge bg-light text-dark border rounded-pill px-3">Berdasarkan Jumlah Jamaah</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 small fw-bold text-secondary text-uppercase py-3">Nama Agensi</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-center">Total Jamaah</th>
                                <th class="pe-4 border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Kontribusi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($agency_performance as $agency): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark d-block"><?= esc($agency['full_name'] ?: $agency['username']) ?></span>
                                    <small class="text-secondary">@<?= esc($agency['username']) ?></small>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-800 fs-6">
                                        <?= $agency['total_jamaah'] ?>
                                    </span>
                                </td>
                                <td class="pe-4 text-end">
                                    <?php 
                                        $percent = $total_jamaah > 0 ? ($agency['total_jamaah'] / $total_jamaah) * 100 : 0;
                                    ?>
                                    <div class="d-flex align-items-center justify-content-end">
                                        <div class="progress rounded-pill me-2" style="width: 60px; height: 6px; background-color: #f1f3f5;">
                                            <div class="progress-bar bg-success rounded-pill" style="width: <?= $percent ?>%"></div>
                                        </div>
                                        <span class="small fw-bold text-dark"><?= round($percent, 1) ?>%</span>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Paket Terpopuler -->
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0">Popularitas Paket</h5>
            </div>
            <div class="card-body p-4">
                <?php foreach($package_popularity as $pkg): ?>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark font-outfit"><?= esc($pkg['name']) ?></span>
                            <span class="small fw-bold text-primary"><?= $pkg['total_jamaah'] ?> Jamaah</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px; background-color: #f1f3f5;">
                            <?php 
                                $pkgPercent = $total_jamaah > 0 ? ($pkg['total_jamaah'] / $total_jamaah) * 100 : 0;
                            ?>
                            <div class="progress-bar bg-primary rounded-pill shadow-sm" style="width: <?= $pkgPercent ?>%"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Pendaftaran Terbaru (Footer) -->
    <?php
    $registrationTab = $registration_tab ?? 'active';
    $reportFilterQuery = array_filter([
        'start_date' => $start_date ?? '',
        'end_date' => $end_date ?? '',
    ]);
    $urlActiveTab = base_url('owner/reports') . '?' . http_build_query(array_merge($reportFilterQuery, ['registration_tab' => 'active']));
    $urlCancelledTab = base_url('owner/reports') . '?' . http_build_query(array_merge($reportFilterQuery, ['registration_tab' => 'cancelled']));
    $exportActiveUrl = base_url('owner/reports/registrations-export') . '?' . http_build_query(array_merge($reportFilterQuery, ['registration_tab' => 'active']));
    $exportCancelledUrl = base_url('owner/reports/registrations-export') . '?' . http_build_query(array_merge($reportFilterQuery, ['registration_tab' => 'cancelled']));
    ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                    <h5 class="fw-bold text-dark mb-0">Riwayat Pendaftaran Terbaru</h5>
                    <a href="<?= esc($registrationTab === 'cancelled' ? $exportCancelledUrl : $exportActiveUrl) ?>"
                       class="btn btn-success btn-sm rounded-pill px-3"
                       title="Export riwayat pendaftaran ke Excel">
                        <i class="bi bi-file-earmark-excel me-1"></i> Cetak Excel
                    </a>
                </div>
                <ul class="nav nav-pills bg-light p-1 rounded-pill d-inline-flex" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="<?= esc($urlActiveTab) ?>" class="nav-link rounded-pill px-4 <?= $registrationTab === 'active' ? 'active fw-bold' : 'text-secondary' ?>">
                            <i class="bi bi-person-check me-2"></i>Jamaah Aktif
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="<?= esc($urlCancelledTab) ?>" class="nav-link rounded-pill px-4 <?= $registrationTab === 'cancelled' ? 'active fw-bold' : 'text-secondary' ?>">
                            <i class="bi bi-person-x me-2"></i>Jamaah Batal
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <?php if ($registrationTab === 'cancelled'): ?>
                    <table class="table align-middle mb-0 table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 small fw-bold text-secondary text-uppercase py-3">Jamaah</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Agensi</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Paket</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-center">Jumlah Pembayaran</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Total Pembayaran</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Refund</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Tgl. Batal</th>
                                <th class="pe-4 border-0 small fw-bold text-secondary text-uppercase py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latest_registrations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">Belum ada jamaah batal pada periode ini.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($latest_registrations as $reg): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark"><?= esc($reg['name']) ?></span>
                                </td>
                                <td><?= esc($reg['agency_name']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= esc($reg['package_name']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-800">
                                        <?= (int) ($reg['payments_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    Rp <?= number_format((float) ($reg['total_paid'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-end fw-bold text-danger">
                                    Rp <?= number_format((float) ($reg['refund_amount'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-secondary small">
                                    <?= ! empty($reg['cancelled_at']) ? date('d M Y, H:i', strtotime($reg['cancelled_at'])) : '—' ?>
                                </td>
                                <td class="pe-4 small text-muted" style="max-width: 220px;">
                                    <?= esc($reg['cancellation_notes'] ?? '—') ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <table class="table align-middle mb-0 table-sm">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0 small fw-bold text-secondary text-uppercase py-3">Jamaah</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Agensi</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Paket</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3">Status</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-center">Jumlah Pembayaran</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Total Pembayaran</th>
                                <th class="border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Aksi</th>
                                <th class="pe-4 border-0 small fw-bold text-secondary text-uppercase py-3 text-end">Tanggal Daftar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($latest_registrations)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">Belum ada jamaah aktif pada periode ini.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($latest_registrations as $reg): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-dark"><?= esc($reg['name']) ?></span>
                                </td>
                                <td><?= esc($reg['agency_name']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border rounded-pill px-3"><?= esc($reg['package_name']) ?></span>
                                </td>
                                <td>
                                    <?php
                                        $statusBadge = [
                                            'pending' => 'bg-warning-soft text-warning',
                                            'verified' => 'bg-success-soft text-success',
                                        ][$reg['status']] ?? 'bg-light text-secondary';
                                    ?>
                                    <span class="badge <?= $statusBadge ?> rounded-pill px-3 py-1 fw-bold"><?= strtoupper($reg['status']) ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-800">
                                        <?= (int) ($reg['payments_count'] ?? 0) ?>
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-dark">
                                    Rp <?= number_format((float) ($reg['total_paid'] ?? 0), 0, ',', '.') ?>
                                </td>
                                <td class="text-end">
                                    <a href="<?= base_url('owner/reports/payment-detail-export/' . (int) ($reg['id'] ?? 0)) ?>"
                                       class="btn btn-outline-success btn-sm rounded-circle d-inline-flex align-items-center justify-content-center"
                                       style="width: 32px; height: 32px;"
                                       title="Lihat detail pembayaran">
                                        <i class="bi bi-download"></i>
                                    </a>
                                </td>
                                <td class="pe-4 text-end text-secondary small">
                                    <?= date('d M Y, H:i', strtotime($reg['created_at'])) ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>


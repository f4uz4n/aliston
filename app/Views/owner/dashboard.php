<?= $this->extend('layouts/main') ?>
<?php helper('access'); ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Dashboard Analitik</h2>
        <p class="text-secondary mb-0">Pantau performa pendaftaran Aliston secara realtime</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <div class="d-flex justify-content-md-end gap-2">
            <a href="<?= base_url('owner/materials/create') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-cloud-plus me-2"></i>Materi Baru
            </a>
            <a href="<?= base_url('package/create') ?>" class="btn-premium d-inline-flex align-items-center gap-2">
                <i class="bi bi-plus-circle-fill"></i> Tambah Paket
            </a>
        </div>
    </div>
</div>

<!-- Dynamic Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden border-top border-4 border-primary">
    <div class="card-body p-4">
        <form action="<?= base_url('owner') ?>" method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Rentang Tanggal</label>
                <div class="input-group">
                    <input type="date" name="start_date" class="form-control bg-light border-0" value="<?= $filters['start_date'] ?? '' ?>">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-arrow-right small"></i></span>
                    <input type="date" name="end_date" class="form-control bg-light border-0" value="<?= $filters['end_date'] ?? '' ?>">
                </div>
            </div>
            <?php if (is_owner()): ?>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Pilih Agensi</label>
                <select name="agency_id" class="form-select bg-light border-0">
                    <option value="">Semua Agensi</option>
                    <?php if(!empty($agencies_list)): ?>
                        <?php foreach($agencies_list as $agency): ?>
                            <option value="<?= $agency['id'] ?>" <?= ($filters['agency_id'] ?? '') == $agency['id'] ? 'selected' : '' ?>>
                                <?= esc($agency['full_name'] ?: $agency['username']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Paket Perjalanan</label>
                <select name="package_id" class="form-select bg-light border-0">
                    <option value="">Semua Paket</option>
                    <?php if(!empty($packages_list)): ?>
                        <?php foreach($packages_list as $pkg): ?>
                            <option value="<?= $pkg['id'] ?>" <?= ($filters['package_id'] ?? '') == $pkg['id'] ? 'selected' : '' ?>>
                                <?= esc($pkg['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                        <i class="bi bi-filter me-2"></i>Terapkan
                    </button>
                    <a href="<?= base_url('owner') ?>" class="btn btn-light border w-100 rounded-pill fw-bold">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-5">
    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-primary-soft text-primary rounded-circle p-2">
                        <i class="bi bi-people-fill fs-4"></i>
                    </div>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-1">Total Jamaah</h6>
                <div class="d-flex align-items-baseline gap-2">
                    <h2 class="fw-800 mb-0"><?= $stats['total_jamaah'] ?? 0 ?></h2>
                    <small class="text-secondary">Pendaftar</small>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-success-soft text-success rounded-circle p-2">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-1">Verifikasi</h6>
                <h2 class="fw-800 mb-0 text-success"><?= $stats['verified_jamaah'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-warning">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-warning-soft text-warning rounded-circle p-2">
                        <i class="bi bi-hourglass-split fs-4"></i>
                    </div>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-1">Menunggu</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $stats['pending_jamaah'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white border-bottom border-4 border-danger">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-danger-soft text-danger rounded-circle p-2">
                        <i class="bi bi-x-circle fs-4"></i>
                    </div>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-1">Jamaah Batal</h6>
                <h2 class="fw-800 mb-0 text-danger"><?= $stats['cancelled_jamaah'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl">
        <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="bg-info-soft text-info rounded-circle p-2">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                </div>
                <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-1">Total Paket</h6>
                <h2 class="fw-800 mb-0 text-dark"><?= $stats['total_packages'] ?? 0 ?></h2>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-5">
    <!-- Trend Chart -->
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Tren Pendaftaran (30 Hari Terakhir)</h5>
                <i class="bi bi-info-circle text-muted" data-bs-toggle="tooltip" title="Statistik harian pendaftaran jamaah baru"></i>
            </div>
            <div class="card-body p-4">
                <canvas id="registrationTrendChart" style="max-height: 350px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Package Distribution Chart (Donut) -->
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0">Kontribusi Paket</h5>
            </div>
            <div class="card-body p-4">
                <canvas id="packageDistChart" style="max-height: 350px;"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Grafik Pembatalan & Pemberangkatan (mengikuti filter tanggal) -->
<div class="row g-4 mb-5">
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0">Pembatalan</h5>
                <small class="text-muted">Sesuai rentang tanggal filter</small>
            </div>
            <div class="card-body p-4">
                <canvas id="cancellationChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h5 class="fw-bold text-dark mb-0">Pemberangkatan</h5>
                <small class="text-muted">Sesuai rentang tanggal filter</small>
            </div>
            <div class="card-body p-4">
                <canvas id="departureChart" style="max-height: 280px;"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Latest Activity -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Aktivitas Pendaftaran Terbaru</h5>
                <a href="<?= base_url('owner/participant') ?>" class="small text-primary fw-bold text-decoration-none">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 border-0 small fw-bold text-secondary text-uppercase">Nama / Paket</th>
                                <th class="py-3 border-0 small fw-bold text-secondary text-uppercase text-center">Status</th>
                                <th class="pe-4 py-3 border-0 small fw-bold text-secondary text-uppercase text-end">Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($registrations)): ?>
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="bi bi-search text-muted fs-1 mb-3"></i>
                                            <p class="text-secondary mb-0">Tidak ada pendaftaran ditemukan dengan kriteria ini.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach(array_slice($registrations, 0, 8) as $reg): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark d-block"><?= esc($reg['name']) ?></span>
                                        <small class="text-secondary badge bg-light rounded-pill px-2 border"><?= esc($reg['package_name']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $badge = match ($reg['status']) {
                                                'verified' => 'bg-success-soft text-success',
                                                'cancelled' => 'bg-danger-soft text-danger',
                                                default => 'bg-warning-soft text-warning',
                                            };
                                        ?>
                                        <span class="badge <?= $badge ?> rounded-pill px-3 py-1 fw-bold"><?= strtoupper($reg['status']) ?></span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <small class="text-secondary d-block"><?= date('H:i', strtotime($reg['created_at'])) ?></small>
                                        <small class="text-muted smaller"><?= date('d/m/Y', strtotime($reg['created_at'])) ?></small>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar on Dashboard: Top Agencies (hanya pemilik) -->
    <div class="col-12 col-lg-5">
        <?php if (is_owner()): ?>
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0 text-center">Ranking Agensi Teraktif</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if(empty($agency_perf)): ?>
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">Belum ada data agensi.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($agency_perf as $index => $perf): ?>
                        <div class="list-group-item px-4 py-3 border-0 border-bottom d-flex align-items-center">
                            <div class="ranking-number me-3 fs-5 fw-800 text-<?= $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : 'primary-soft') ?>">
                                #<?= $index + 1 ?>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold text-dark mb-0"><?= esc($perf['full_name']) ?></h6>
                                <small class="text-secondary"><?= $perf['total'] ?> Jamaah Berhasil Terdaftar</small>
                            </div>
                            <div class="bg-primary-soft text-primary rounded-pill px-3 py-1 fw-bold smaller border border-primary">
                                TERBAIK
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Materials Count / Quick Access -->
        <div class="card border-0 shadow-sm rounded-4 bg-dark text-white p-4 text-center">
            <i class="bi bi-file-earmark-arrow-up-fill fs-1 text-primary-color mb-3 opacity-75"></i>
            <h5 class="fw-bold mb-1">Materi Promosi</h5>
            <p class="text-white-50 small mb-4">Total <?= $materials_count ?? 0 ?> materi siap dibagikan ke agensi mitra.</p>
            <a href="<?= base_url('owner/materials') ?>" class="btn btn-outline-light rounded-pill w-100 py-3 fw-bold">
                Buka Hub Materi
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Trend Chart
    const trendCtx = document.getElementById('registrationTrendChart')?.getContext('2d');
    if(trendCtx) {
        const trendData = <?= json_encode($trends ?? []) ?>;
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(d => d.date),
                datasets: [{
                    label: 'Jumlah Jamaah',
                    data: trendData.map(d => d.total),
                    borderColor: '#ef3338',
                    backgroundColor: 'rgba(239, 51, 56, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderWidth: 2,
                    pointBorderColor: '#ef3338'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 2. Package Distribution Chart (Donut)
    const distCtx = document.getElementById('packageDistChart')?.getContext('2d');
    if(distCtx) {
        const distData = <?= json_encode($package_dist ?? []) ?>;
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: distData.map(d => d.name),
                datasets: [{
                    data: distData.map(d => d.total),
                    backgroundColor: ['#ef3338', '#00a651', '#fcc200', '#20c997', '#6f42c1'],
                    offset: 10,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { usePointStyle: true, padding: 20 } }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Pembatalan 3 bulan terakhir (Bar)
    const cancelCtx = document.getElementById('cancellationChart')?.getContext('2d');
    if(cancelCtx) {
        const months = <?= json_encode($chart_months ?? []) ?>;
        const cancelData = <?= json_encode($chart_cancellation ?? []) ?>;
        new Chart(cancelCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Jumlah Pembatalan',
                    data: cancelData,
                    backgroundColor: 'rgba(220, 53, 69, 0.7)',
                    borderColor: '#dc3545',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // 4. Pemberangkatan 3 bulan terakhir (Bar)
    const depCtx = document.getElementById('departureChart')?.getContext('2d');
    if(depCtx) {
        const months = <?= json_encode($chart_months ?? []) ?>;
        const depData = <?= json_encode($chart_departure ?? []) ?>;
        new Chart(depCtx, {
            type: 'bar',
            data: {
                labels: months,
                datasets: [{
                    label: 'Jumlah Pemberangkatan',
                    data: depData,
                    backgroundColor: 'rgba(0, 166, 81, 0.7)',
                    borderColor: '#00a651',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }
});
</script>
<?= $this->endSection() ?>
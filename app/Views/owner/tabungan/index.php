<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php 
$activeTab = $activeTab ?? 'menabung'; 
$pending = $pending_deposits ?? [];
$savings_menabung = array_filter($savings ?? [], function($s) { return ($s['status'] ?? '') === 'menabung'; });
$savings_claimed = array_filter($savings ?? [], function($s) { return ($s['status'] ?? '') === 'claimed'; });
?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Tabungan Perjalanan</h2>
        <p class="text-secondary mb-0">Jamaah menabung tanpa paket; klaim ke paket ketika saldo cukup</p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <a href="<?= base_url('owner/tabungan/create') ?>" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-plus-lg me-2"></i>Tambah Jamaah Tabungan</a>
    </div>
</div>

<ul class="nav nav-tabs nav-tabs-card mb-4 border-0" id="tabunganTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link rounded-pill me-2 <?= $activeTab === 'menabung' ? 'active' : '' ?>" href="<?= base_url('owner/tabungan?tab=menabung') ?>" role="tab">Menabung <span class="badge bg-warning text-dark ms-1"><?= count($pending) ?></span></a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link rounded-pill <?= $activeTab === 'klaim' ? 'active' : '' ?>" href="<?= base_url('owner/tabungan?tab=klaim') ?>" role="tab">Klaim</a>
    </li>
</ul>

<div class="tab-content" id="tabunganTabContent">
    <!-- Tab Menabung -->
    <div class="tab-pane fade <?= $activeTab === 'menabung' ? 'show active' : '' ?>" role="tabpanel">
        <div class="row mb-3">
            <div class="col-12">
                <form action="<?= base_url('owner/tabungan') ?>" method="get" class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <input type="hidden" name="tab" value="menabung">
                    <input type="text" name="cari" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Cari NIK atau nama..." value="<?= esc($filterCari ?? '') ?>">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (($filterCari ?? '') !== ''): ?>
                    <a href="<?= base_url('owner/tabungan?tab=menabung') ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <!-- Tabel Tabungan Status Menabung -->
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="mb-0 fw-bold">Daftar Tabungan Status Menabung</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">Nama</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">NIK</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Agensi</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Tipe</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Tanggal Daftar</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase text-end">Saldo</th>
                                    <th class="pe-4 py-3 text-secondary small fw-bold text-uppercase text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($savings_menabung)): ?>
                                <tr><td colspan="7" class="text-center py-5"><i class="bi bi-safe2 text-muted fs-1 d-block mb-3"></i><p class="text-secondary mb-0">Belum ada data tabungan dengan status menabung.</p></td></tr>
                                <?php else: ?>
                                <?php foreach ($savings_menabung as $s): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= esc($s['name']) ?></td>
                                    <td><?= esc($s['nik']) ?></td>
                                    <td><?= esc($s['agency_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (($s['registration_type'] ?? 'mandiri') === 'mou'): ?>
                                            <span class="badge bg-primary-soft text-primary rounded-pill px-3">MOU</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-soft text-success rounded-pill px-3">MANDIRI</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= !empty($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : '—' ?></td>
                                    <td class="text-end fw-bold">Rp <?= number_format($s['total_balance'], 0, ',', '.') ?></td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= base_url('owner/tabungan/deposit/' . $s['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill me-1">Setoran</a>
                                        <a href="<?= base_url('owner/tabungan/claim/' . $s['id']) ?>" class="btn btn-outline-success btn-sm rounded-pill me-1">Klaim ke Paket</a>
                                        <a href="<?= base_url('owner/tabungan/edit/' . $s['id']) ?>" class="btn btn-outline-info btn-sm rounded-pill me-1" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <form action="<?= base_url('owner/tabungan/delete/' . $s['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus jamaah tabungan ini? Semua data setoran juga akan terhapus.');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill me-1" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                        <a href="<?= base_url('owner/tabungan/receipt/' . $s['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill" title="Cetak Kwitansi"><i class="bi bi-printer"></i></a>
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
    </div>
    
    <!-- Tab Klaim -->
    <div class="tab-pane fade <?= $activeTab === 'klaim' ? 'show active' : '' ?>" role="tabpanel">
        <div class="row mb-3">
            <div class="col-12">
                <form action="<?= base_url('owner/tabungan') ?>" method="get" class="d-flex flex-wrap gap-2 align-items-center mb-3">
                    <input type="hidden" name="tab" value="klaim">
                    <input type="text" name="cari" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Cari NIK atau nama..." value="<?= esc($filterCari ?? '') ?>">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-search me-1"></i>Cari</button>
                    <?php if (($filterCari ?? '') !== ''): ?>
                    <a href="<?= base_url('owner/tabungan?tab=klaim') ?>" class="btn btn-outline-secondary btn-sm rounded-pill">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-0 py-3">
                        <h6 class="mb-0 fw-bold">Daftar Tabungan Status Terklaim</h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4 py-3 text-secondary small fw-bold text-uppercase">Nama</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">NIK</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Agensi</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Tipe</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase">Tanggal Daftar</th>
                                    <th class="py-3 text-secondary small fw-bold text-uppercase text-end">Saldo</th>
                                    <th class="pe-4 py-3 text-secondary small fw-bold text-uppercase text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($savings_claimed)): ?>
                                <tr><td colspan="7" class="text-center py-5"><i class="bi bi-check-circle text-success fs-1 d-block mb-3"></i><p class="text-secondary mb-0">Belum ada data tabungan yang terklaim.</p></td></tr>
                                <?php else: ?>
                                <?php foreach ($savings_claimed as $s): ?>
                                <tr>
                                    <td class="ps-4 fw-bold"><?= esc($s['name']) ?></td>
                                    <td><?= esc($s['nik']) ?></td>
                                    <td><?= esc($s['agency_name'] ?? '-') ?></td>
                                    <td>
                                        <?php if (($s['registration_type'] ?? 'mandiri') === 'mou'): ?>
                                            <span class="badge bg-primary-soft text-primary rounded-pill px-3">MOU</span>
                                        <?php else: ?>
                                            <span class="badge bg-success-soft text-success rounded-pill px-3">MANDIRI</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small"><?= !empty($s['created_at']) ? date('d M Y', strtotime($s['created_at'])) : '—' ?></td>
                                    <td class="text-end fw-bold">Rp <?= number_format($s['total_balance'], 0, ',', '.') ?></td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= base_url('owner/participant/kelola/' . $s['participant_id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill me-1">Lihat Jamaah</a>
                                        <a href="<?= base_url('owner/tabungan/receipt/' . $s['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill" title="Cetak Kwitansi"><i class="bi bi-printer"></i></a>
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
    </div>
</div>

<style>
.nav-tabs-card .nav-link { font-weight: 600; color: #6c757d; border: none; padding: 0.5rem 1rem; }
.nav-tabs-card .nav-link:hover { color: var(--primary, #0d6efd); }
.nav-tabs-card .nav-link.active { color: var(--primary); background: rgba(13, 110, 253, 0.1); }
.bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
</style>
<?= $this->endSection() ?>

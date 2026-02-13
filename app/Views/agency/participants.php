<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Daftar Jamaah</h2>
        <p class="text-secondary mb-0">Kelola dan pantau status keberangkatan jamaah Anda</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= base_url('agency/packages') ?>" class="btn-premium px-4 py-3 rounded-pill d-inline-flex align-items-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Daftarkan Jamaah Baru
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <form action="" method="get">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0 rounded-end-pill py-2" placeholder="Cari berdasarkan Nama atau NIK..." value="<?= esc($keyword ?? '') ?>">
                <button type="submit" class="btn btn-primary rounded-pill px-4 ms-2">Cari</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 border-0 text-secondary small fw-bold text-uppercase">Jamaah</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Paket & NIK</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Status</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Progress Berkas</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Progress & Status Pembayaran</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($participants)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-people text-muted fs-1 opacity-25 mb-3 d-block"></i>
                                    <p class="text-secondary mb-0">Belum ada jamaah yang terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($participants as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-dark mb-0"><?= esc($p['name']) ?></h6>
                                        <small class="text-secondary"><?= esc($p['gender'] ?? '-') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="fw-bold text-dark d-block"><?= esc($p['package_name']) ?></span>
                                <small class="text-secondary">NIK: <?= esc($p['nik']) ?></small>
                            </td>
                            <td>
                                <?php if($p['status'] == 'pending'): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                        <i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi
                                    </span>
                                <?php elseif($p['status'] == 'verified'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Terverifikasi
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">
                                        <i class="bi bi-x-circle-fill me-1"></i> Dibatalkan
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="mb-1 d-flex justify-content-between">
                                    <small class="text-secondary" style="font-size: 0.7rem;">Berkas: <?= $p['doc_count'] ?>/<?= $p['doc_total'] ?></small>
                                    <small class="text-secondary" style="font-size: 0.7rem;">Souvenir: <?= $p['equip_count'] ?>/<?= $p['equip_total'] ?></small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <?php 
                                        $total_tasks = $p['doc_total'] + $p['equip_total'];
                                        $completed_tasks = $p['doc_count'] + $p['equip_count'];
                                        $percent = $total_tasks > 0 ? ($completed_tasks / $total_tasks) * 100 : 0;
                                    ?>
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $percent ?>%"></div>
                                </div>
                            </td>
                            <td>
                                <div class="mb-1">
                                    <small class="text-secondary d-block" style="font-size: 0.7rem;">Rp <?= number_format($p['total_paid'] ?? 0, 0, ',', '.') ?> / Rp <?= number_format($p['total_target'] ?? 0, 0, ',', '.') ?></small>
                                    <div class="progress mt-1" style="height: 6px;">
                                        <?php $payPercent = $p['progress_pembayaran'] ?? 0; ?>
                                        <div class="progress-bar <?= ($p['pembayaran_lunas'] ?? false) ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $payPercent ?>%"></div>
                                    </div>
                                </div>
                                <?php if (!empty($p['pembayaran_lunas'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill small mt-1"><i class="bi bi-check-circle me-1"></i> Lunas</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill small mt-1"><i class="bi bi-clock me-1"></i> Belum Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-flex gap-2 justify-content-end flex-wrap">
                                    <a href="<?= base_url('agency/payment-detail/'.$p['id']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Laporan Pembayaran">
                                        <i class="bi bi-wallet2 me-1"></i> Laporan Pembayaran
                                    </a>
                                    <?php if (!empty($p['pembayaran_lunas'])): ?>
                                        <a href="<?= base_url('agency/receipt/'.$p['id']) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3" title="Cetak Pembayaran Lunas">
                                            <i class="bi bi-printer-fill me-1"></i> Cetak Pembayaran
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= base_url('agency/registration-form/'.$p['id']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="bi bi-printer me-1"></i> Cetak Formulir
                                    </a>
                                    <a href="<?= base_url('agency/documents/'.$p['id']) ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                        <i class="bi bi-file-earmark-check me-1"></i> Melengkapi Berkas
                                    </a>
                                    <a href="<?= base_url('agency/edit-participant/'.$p['id']) ?>" class="btn btn-sm btn-light border rounded-pill px-3">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

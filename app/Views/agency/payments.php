<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Laporan Pembayaran</h2>
        <p class="text-secondary mb-0">Pantau progres pelunasan biaya jamaah Anda</p>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <form action="" method="get">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0 ps-0 rounded-end-pill py-2" placeholder="Cari Nama Jamaah atau NIK..." value="<?= esc($keyword ?? '') ?>">
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
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Paket</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Total Harga</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Telah Terbayar</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Sisa Saldo</th>
                        <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($participants)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-wallet2 text-muted fs-1 opacity-25 mb-3 d-block"></i>
                                    <p class="text-secondary mb-0">Belum ada data pembayaran jamaah.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($participants as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <h6 class="fw-bold text-dark mb-0"><?= esc($p['name']) ?></h6>
                                <small class="text-secondary">NIK: <?= esc($p['nik']) ?></small>
                            </td>
                            <td><span class="badge bg-light text-dark fw-normal border"><?= esc($p['package_name']) ?></span></td>
                            <td class="fw-bold text-dark">Rp <?= number_format((float)($p['total_tagihan'] ?? $p['price'] ?? 0), 0, ',', '.') ?></td>
                            <td class="text-success fw-bold">Rp <?= number_format((float)$p['total_paid'], 0, ',', '.') ?></td>
                            <td class="<?= $p['remaining'] > 0 ? 'text-danger' : 'text-success' ?> fw-bold">
                                Rp <?= number_format((float)$p['remaining'], 0, ',', '.') ?>
                                <?php if($p['remaining'] <= 0): ?>
                                    <span class="badge bg-success ms-1 small">Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?= base_url('agency/payment-detail/'.$p['id']) ?>" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-plus-circle me-1"></i> Cicilan Baru
                                </a>
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

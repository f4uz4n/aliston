<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Boarding</h2>
        <p class="text-secondary mb-0">Hanya jamaah terverifikasi. Berkas lengkap, pembayaran lunas, dan H-15.</p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('agency/boarding') ?>" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="col-form-label small fw-bold">Paket</label>
                <select name="package_id" class="form-select form-select-sm bg-light border-0 rounded-pill" style="min-width: 220px;">
                    <option value="">Semua Paket</option>
                    <?php foreach ($packages as $pkg): ?>
                        <option value="<?= $pkg['id'] ?>" <?= ($selected_package ?? '') == $pkg['id'] ? 'selected' : '' ?>>
                            <?= esc($pkg['name']) ?> — <?= !empty($pkg['departure_date']) ? date('d/m/Y', strtotime($pkg['departure_date'])) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="col-form-label small fw-bold">Tanggal Berangkat</label>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="date" name="departure_date_from" class="form-control form-control-sm bg-light border-0 rounded-pill" value="<?= esc($departure_date_from ?? '') ?>" placeholder="Dari">
                    <span class="small text-muted">s/d</span>
                    <input type="date" name="departure_date_to" class="form-control form-control-sm bg-light border-0 rounded-pill" value="<?= esc($departure_date_to ?? '') ?>" placeholder="Sampai">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?= base_url('agency/boarding') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3">Tanggal Berangkat</th>
                    <th class="border-0 py-3">Maskapai</th>
                    <th class="border-0 py-3">Nama Jamaah</th>
                    <th class="border-0 py-3">Paket</th>
                    <th class="border-0 py-3 text-center">Berkas</th>
                    <th class="border-0 py-3 text-center">Pembayaran</th>
                    <th class="border-0 py-3 text-center">H-</th>
                    <th class="border-0 py-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($participants)): ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada data jamaah.</td></tr>
                <?php else: ?>
                <?php foreach ($participants as $p): ?>
                <tr>
                    <td><?= !empty($p['departure_date']) ? date('d/m/Y', strtotime($p['departure_date'])) : '—' ?></td>
                    <td><?= esc($p['airline'] ?? '—') ?></td>
                    <td><strong><?= esc($p['name']) ?></strong><br><small class="text-muted"><?= esc($p['nik'] ?? '') ?></small></td>
                    <td class="small"><?= esc($p['package_name'] ?? '—') ?></td>
                    <td class="text-center">
                        <?php if ($p['berkas_lengkap']): ?>
                            <span class="badge bg-success rounded-pill">Lengkap</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark rounded-pill"><?= $p['doc_progress'] ?>%</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($p['pembayaran_lunas']): ?>
                            <span class="badge bg-success rounded-pill">Lunas</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill">Rp <?= number_format($p['total_paid'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $p['days_until'] !== null ? $p['days_until'] : '—' ?></td>
                    <td class="text-center">
                        <?php if (!empty($p['is_boarded'])): ?>
                            <span class="badge bg-success rounded-pill">Boarding</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark rounded-pill">Belum</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

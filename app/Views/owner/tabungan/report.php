<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row align-items-center mb-4">
    <div class="col-12 col-md-7">
        <h2 class="fw-800 text-dark mb-1">Laporan Tabungan</h2>
        <p class="text-secondary mb-0">Ringkasan total saldo, transaksi tabungan, dan komposisi MOU vs Mandiri.</p>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
        <a href="<?= base_url('owner/tabungan') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Tabungan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="<?= base_url('owner/tabungan/report') ?>" method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-secondary text-uppercase">Tanggal Mulai</label>
                <input type="date" name="start_date" class="form-control bg-light border-0" value="<?= esc($start_date ?? '') ?>">
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label small fw-bold text-secondary text-uppercase">Tanggal Akhir</label>
                <input type="date" name="end_date" class="form-control bg-light border-0" value="<?= esc($end_date ?? '') ?>">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary text-uppercase">Agensi</label>
                <select name="agency_id" class="form-select bg-light border-0">
                    <option value="">Semua Agensi</option>
                    <?php foreach (($agencies ?? []) as $ag): ?>
                        <option value="<?= (int)$ag['id'] ?>" <?= ((string)($agency_id ?? '') === (string)$ag['id']) ? 'selected' : '' ?>>
                            <?= esc($ag['full_name'] ?: $ag['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small fw-bold text-secondary text-uppercase">Lembaga MOU</label>
                <select name="institution_name" class="form-select bg-light border-0">
                    <option value="">Semua Lembaga</option>
                    <?php foreach (($institutions ?? []) as $ins): ?>
                        <?php $insName = (string)($ins['institution_name'] ?? ''); if ($insName === '') continue; ?>
                        <option value="<?= esc($insName) ?>" <?= ((string)($institution_name ?? '') === $insName) ? 'selected' : '' ?>>
                            <?= esc($insName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                    <i class="bi bi-filter me-2"></i>Terapkan
                </button>
                <a href="<?= base_url('owner/tabungan/report') ?>" class="btn btn-light border w-100 rounded-pill fw-bold">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="text-secondary small fw-bold text-uppercase mb-1">Total Tabungan</h6>
                <h3 class="fw-800 mb-0 text-primary">Rp <?= number_format((float)($total_saldo_tabungan ?? 0), 0, ',', '.') ?></h3>
                <small class="text-muted"><?= (int)($total_jamaah_tabungan ?? 0) ?> jamaah tabungan</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="text-secondary small fw-bold text-uppercase mb-1">Jumlah Pembayaran Tabungan</h6>
                <h3 class="fw-800 mb-0 text-success"><?= number_format((int)($jumlah_pembayaran ?? 0), 0, ',', '.') ?></h3>
                <small class="text-muted">transaksi verified</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h6 class="text-secondary small fw-bold text-uppercase mb-1">Nominal Pembayaran</h6>
                <h3 class="fw-800 mb-0 text-dark">Rp <?= number_format((float)($total_nominal_pembayaran ?? 0), 0, ',', '.') ?></h3>
                <small class="text-muted">total setoran verified</small>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <?php
                $mouCount = 0;
                foreach (($type_stats ?? []) as $t) {
                    if (($t['registration_type'] ?? '') === 'mou') {
                        $mouCount = (int)($t['total_jamaah'] ?? 0);
                    }
                }
                ?>
                <h6 class="text-secondary small fw-bold text-uppercase mb-1">Jumlah Tabungan MOU</h6>
                <h3 class="fw-800 mb-0 text-info"><?= number_format($mouCount, 0, ',', '.') ?></h3>
                <small class="text-muted">jamaah dengan tipe MOU</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12 col-xl-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold">Komposisi Tipe Tabungan</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-secondary text-uppercase">Tipe</th>
                                <th class="py-3 small text-secondary text-uppercase text-center">Jumlah Jamaah</th>
                                <th class="pe-4 py-3 small text-secondary text-uppercase text-end">Total Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($type_stats)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                            <?php else: ?>
                                <?php foreach ($type_stats as $t): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            <?php if (($t['registration_type'] ?? 'mandiri') === 'mou'): ?>
                                                <span class="badge bg-primary-soft text-primary rounded-pill px-3">MOU</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-soft text-success rounded-pill px-3">MANDIRI</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center"><?= number_format((int)($t['total_jamaah'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="pe-4 text-end fw-bold">Rp <?= number_format((float)($t['total_saldo'] ?? 0), 0, ',', '.') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-transparent border-0 py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold">Ringkasan Per Agensi</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-secondary text-uppercase">Agensi</th>
                                <th class="py-3 small text-secondary text-uppercase text-center">Jamaah</th>
                                <th class="pe-4 py-3 small text-secondary text-uppercase text-end">Saldo Tabungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($agency_summary)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data.</td></tr>
                            <?php else: ?>
                                <?php foreach ($agency_summary as $ag): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= esc($ag['agency_name'] ?? '-') ?></td>
                                        <td class="text-center"><?= number_format((int)($ag['total_jamaah'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="pe-4 text-end fw-bold">Rp <?= number_format((float)($ag['total_saldo'] ?? 0), 0, ',', '.') ?></td>
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

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 py-3 px-4 border-bottom">
                <h6 class="mb-0 fw-bold">Total Tabungan Berdasarkan Lembaga MOU</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3 small text-secondary text-uppercase">Nama Lembaga</th>
                                <th class="py-3 small text-secondary text-uppercase text-center">Jumlah Jamaah</th>
                                <th class="pe-4 py-3 small text-secondary text-uppercase text-end">Total Tabungan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($mou_institution_summary)): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">Belum ada data lembaga MOU pada filter ini.</td></tr>
                            <?php else: ?>
                                <?php foreach ($mou_institution_summary as $mou): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= esc($mou['institution_name'] ?? '-') ?></td>
                                        <td class="text-center"><?= number_format((int)($mou['total_jamaah'] ?? 0), 0, ',', '.') ?></td>
                                        <td class="pe-4 text-end fw-bold">Rp <?= number_format((float)($mou['total_saldo'] ?? 0), 0, ',', '.') ?></td>
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

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-transparent border-0 py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold">Rincian Jamaah Tabungan & Frekuensi Menabung</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 small text-secondary text-uppercase">Jamaah</th>
                        <th class="py-3 small text-secondary text-uppercase">Agensi</th>
                        <th class="py-3 small text-secondary text-uppercase">Tipe</th>
                        <th class="py-3 small text-secondary text-uppercase">Lembaga MOU</th>
                        <th class="py-3 small text-secondary text-uppercase text-center">Jumlah Menabung</th>
                        <th class="py-3 small text-secondary text-uppercase text-end">Total Setoran</th>
                        <th class="pe-4 py-3 small text-secondary text-uppercase text-end">Saldo Saat Ini</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jamaah_saving_summary)): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada data jamaah tabungan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($jamaah_saving_summary as $j): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold d-block"><?= esc($j['name'] ?? '-') ?></span>
                                    <small class="text-muted"><?= esc($j['nik'] ?? '-') ?></small>
                                </td>
                                <td><?= esc($j['agency_name'] ?? '-') ?></td>
                                <td>
                                    <?php if (($j['registration_type'] ?? 'mandiri') === 'mou'): ?>
                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3">MOU</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-soft text-success rounded-pill px-3">MANDIRI</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($j['institution_name'] ?? '-') ?></td>
                                <td class="text-center fw-bold"><?= number_format((int)($j['total_setoran'] ?? 0), 0, ',', '.') ?>x</td>
                                <td class="text-end fw-bold">Rp <?= number_format((float)($j['total_nominal_setoran'] ?? 0), 0, ',', '.') ?></td>
                                <td class="pe-4 text-end fw-bold text-primary">Rp <?= number_format((float)($j['total_balance'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-transparent border-0 py-3 px-4 border-bottom">
        <h6 class="mb-0 fw-bold">10 Transaksi Setoran Tabungan Terbaru (Verified)</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 small text-secondary text-uppercase">Tanggal</th>
                        <th class="py-3 small text-secondary text-uppercase">Jamaah</th>
                        <th class="py-3 small text-secondary text-uppercase">Agensi</th>
                        <th class="py-3 small text-secondary text-uppercase">Tipe</th>
                        <th class="pe-4 py-3 small text-secondary text-uppercase text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_deposits)): ?>
                        <tr><td colspan="5" class="text-center py-4 text-muted">Belum ada transaksi setoran.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recent_deposits as $d): ?>
                            <tr>
                                <td class="ps-4"><?= !empty($d['payment_date']) ? date('d M Y', strtotime($d['payment_date'])) : '—' ?></td>
                                <td class="fw-bold"><?= esc($d['saving_name'] ?? '-') ?></td>
                                <td><?= esc($d['agency_name'] ?? '-') ?></td>
                                <td>
                                    <?php if (($d['registration_type'] ?? 'mandiri') === 'mou'): ?>
                                        <span class="badge bg-primary-soft text-primary rounded-pill px-3">MOU</span>
                                    <?php else: ?>
                                        <span class="badge bg-success-soft text-success rounded-pill px-3">MANDIRI</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end fw-bold">Rp <?= number_format((float)($d['amount'] ?? 0), 0, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

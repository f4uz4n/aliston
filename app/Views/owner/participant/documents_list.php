<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Kelengkapan Berkas</h2>
        <p class="text-secondary mb-0">Verifikasi dokumen persyaratan calon jamaah</p>
        <?php if (($total ?? 0) > 0): ?>
            <small class="text-muted">Menampilkan <?= count($participants) ?> dari <?= (int) $total ?> jamaah</small>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-3">
                <form action="<?= base_url('owner/participant/documents') ?>" method="get" class="row g-3">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari nama, nomor HP, atau agensi..." value="<?= esc($search ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-funnel text-secondary"></i></span>
                            <select name="status" class="form-select bg-light border-0">
                                <option value="">Semua Status Kelengkapan</option>
                                <option value="proses" <?= ($status ?? '') === 'proses' ? 'selected' : '' ?>>Proses</option>
                                <option value="selesai" <?= ($status ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai (100%)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>
                            <?php if(!empty($search) || ($status ?? '') !== ''): ?>
                                <a href="<?= base_url('owner/participant/documents') ?>" class="btn btn-light rounded-pill fw-bold border">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary small fw-bold text-uppercase" width="60">No</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Nama Jamaah</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Persentase Berkas</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase text-center">Status Verifikasi</th>
                            <th class="pe-4 py-3 border-0 text-secondary small fw-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($participants)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-file-earmark-text text-muted fs-1 mb-3"></i>
                                        <p class="text-secondary mb-0">Belum ada jamaah atau data tidak ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($participants as $i => $part): ?>
                            <tr>
                                <td class="ps-4 text-secondary fw-bold"><?= ($start_number ?? 0) + $i + 1 ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info-soft text-info p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="bi bi-person"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= esc($part['name']) ?></span>
                                            <small class="text-secondary smaller"><?= esc($part['agency_name']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td style="width: 300px;">
                                    <div class="d-flex align-items-center">
                                        <div class="progress flex-grow-1 rounded-pill me-3" style="height: 8px; background-color: #f1f3f5;">
                                            <div class="progress-bar <?= ($part['doc_progress'] >= 100) ? 'bg-success' : 'bg-primary' ?> rounded-pill" role="progressbar" style="width: <?= $part['doc_progress'] ?>%;" aria-valuenow="<?= $part['doc_progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <span class="small fw-bold text-dark"><?= $part['doc_progress'] ?>%</span>
                                    </div>
                                    <small class="text-muted smaller"><?= $part['doc_count'] ?> dari 7 berkas diverifikasi</small>
                                </td>
                                <td class="text-center">
                                    <?php if($part['doc_progress'] >= 100): ?>
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-bold">SELESAI</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-bold">PROSES</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="<?= base_url('owner/participant/documents/'.$part['id']) ?>" class="btn btn-light btn-sm rounded-pill px-3 fw-bold border shadow-sm">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if (!empty($pager)): ?>
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

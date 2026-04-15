<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row align-items-center mb-5">
    <div class="col-12">
        <h2 class="fw-800 text-dark mb-1">Testimoni Jamaah</h2>
        <p class="text-secondary mb-0">Verifikasi testimoni dari form publik dan agency sebelum dipublikasikan di halaman depan</p>
    </div>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<?php $packages = $packages ?? []; ?>
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Testimoni</h6>
        <form action="<?= base_url('owner/testimoni/store') ?>" method="post" class="row g-3">
            <?= csrf_field() ?>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Nama</label>
                <input type="text" name="name" class="form-control bg-light border-0" required minlength="2" maxlength="255" value="<?= esc(old('name')) ?>" placeholder="Nama jamaah">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Paket (Opsional)</label>
                <select name="package_id" class="form-select bg-light border-0">
                    <option value="">Tanpa paket</option>
                    <?php foreach ($packages as $pkg): ?>
                        <option value="<?= (int)($pkg['id'] ?? 0) ?>" <?= (string)old('package_id') === (string)($pkg['id'] ?? '') ? 'selected' : '' ?>>
                            <?= esc($pkg['name'] ?? '-') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Rating</label>
                <select name="rating" class="form-select bg-light border-0" required>
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <option value="<?= $i ?>" <?= (string)old('rating', '5') === (string)$i ? 'selected' : '' ?>><?= $i ?> Bintang</option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select bg-light border-0" required>
                    <option value="pending" <?= old('status', 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="verified" <?= old('status') === 'verified' ? 'selected' : '' ?>>Langsung Verifikasi</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label small fw-bold">Isi Testimoni</label>
                <textarea name="testimonial" class="form-control bg-light border-0" rows="3" required minlength="10" placeholder="Tulis testimoni (min. 10 karakter)"><?= esc(old('testimonial')) ?></textarea>
            </div>
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Simpan Testimoni</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="<?= base_url('owner/testimoni') ?>" method="get" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="form-label small fw-bold text-secondary mb-0">Status</label>
                <select name="status" class="form-select form-select-sm" style="width: auto;">
                    <option value="">Semua</option>
                    <option value="pending" <?= ($filter_status ?? '') === 'pending' ? 'selected' : '' ?>>Menunggu verifikasi</option>
                    <option value="verified" <?= ($filter_status ?? '') === 'verified' ? 'selected' : '' ?>>Sudah diverifikasi</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Tanggal</th>
                        <th class="py-3">Nama</th>
                        <th class="py-3">Rating</th>
                        <th class="py-3">Paket</th>
                        <th class="py-3">Sumber</th>
                        <th class="py-3">Status</th>
                        <th class="pe-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($testimonials)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Belum ada data testimoni.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($testimonials as $t): ?>
                        <tr>
                            <td class="ps-4 small"><?= date('d M Y H:i', strtotime($t['created_at'])) ?></td>
                            <td class="fw-bold"><?= esc($t['name']) ?></td>
                            <td><span class="text-warning" title="<?= (int)($t['rating'] ?? 0) ?> bintang"><?= str_repeat('★', (int)($t['rating'] ?? 5)) ?><?= str_repeat('☆', 5 - (int)($t['rating'] ?? 5)) ?></span></td>
                            <td><?= esc($t['package_name'] ?? '—') ?></td>
                            <td>
                                <?php if (($t['source'] ?? '') === 'agency'): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info">Agency</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">Publik</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (($t['status'] ?? '') === 'verified'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Dipublikasikan</span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <a href="<?= base_url('owner/testimoni/edit/'.$t['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1">Edit</a>
                                <?php if (($t['status'] ?? '') === 'pending'): ?>
                                    <form action="<?= base_url('owner/testimoni/verify/'.$t['id']) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 me-1">Verifikasi</button>
                                    </form>
                                <?php endif; ?>
                                <form action="<?= base_url('owner/testimoni/delete/'.$t['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus testimoni ini?');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <tr class="border-0">
                            <td colspan="7" class="ps-4 pt-0 pb-4 text-secondary small" style="vertical-align: top;">
                                <strong>Testimoni:</strong> <?= nl2br(esc($t['testimonial'])) ?>
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

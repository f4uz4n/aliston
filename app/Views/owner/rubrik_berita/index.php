<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Rubrik Berita</h2>
        <p class="text-secondary mb-0">Kelola artikel yang ditampilkan di halaman depan</p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <a href="<?= base_url('owner/rubrik-berita/create') ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
            <i class="bi bi-plus-lg me-2"></i> Tulis Artikel
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('msg') ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3">Judul</th>
                        <th class="py-3">Penulis</th>
                        <th class="py-3">Status</th>
                        <th class="py-3">Terbit</th>
                        <th class="pe-4 py-3 text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada artikel. Klik <strong>Tulis Artikel</strong> untuk menambah.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as $a): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?= esc($a['title']) ?></div>
                                <?php if (!empty($a['excerpt'])): ?>
                                    <div class="small text-secondary mt-1"><?= esc(strlen($a['excerpt'] ?? '') > 80 ? substr($a['excerpt'], 0, 80) . '…' : ($a['excerpt'] ?? '')) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= esc($a['author_name'] ?? '—') ?></td>
                            <td>
                                <?php if (!empty($a['is_published'])): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Dipublikasikan</span>
                                    <form action="<?= base_url('owner/rubrik-berita/toggle-status/' . $a['id']) ?>" method="post" class="d-inline ms-1">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-secondary small">Jadikan Draft</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary rounded-pill">Draft</span>
                                    <form action="<?= base_url('owner/rubrik-berita/toggle-status/' . $a['id']) ?>" method="post" class="d-inline ms-1">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-link btn-sm p-0 text-success small">Publikasikan</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= !empty($a['published_at']) ? date('d M Y', strtotime($a['published_at'])) : '—' ?></td>
                            <td class="pe-4 text-end">
                                <a href="<?= base_url('berita/' . ($a['slug'] ?? '')) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-3 me-1" title="Lihat di depan"><i class="bi bi-box-arrow-up-right"></i></a>
                                <a href="<?= base_url('owner/rubrik-berita/edit/' . $a['id']) ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 me-1">Edit</a>
                                <a href="<?= base_url('owner/rubrik-berita/delete/' . $a['id']) ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('Yakin hapus artikel ini?');">Hapus</a>
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

<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php
$isEdit = isset($article);
$article = $article ?? [];
?>
<div class="row align-items-center mb-4">
    <div class="col-12">
        <a href="<?= base_url('owner/rubrik-berita') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-2"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <h2 class="fw-800 text-dark mb-1"><?= $isEdit ? 'Edit Artikel' : 'Tulis Artikel' ?></h2>
        <p class="text-secondary mb-0"><?= $isEdit ? 'Perbarui artikel' : 'Artikel akan tampil di halaman depan setelah dipublikasikan' ?></p>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<form action="<?= $isEdit ? base_url('owner/rubrik-berita/update/' . ($article['id'] ?? '')) : base_url('owner/rubrik-berita/store') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Judul <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc(old('title', $article['title'] ?? '')) ?>" required placeholder="Judul artikel">
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold">Ringkasan (excerpt)</label>
                        <textarea name="excerpt" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="Ringkasan singkat untuk tampilan di list"><?= esc(old('excerpt', $article['excerpt'] ?? '')) ?></textarea>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-bold">Konten</label>
                        <textarea name="content" class="form-control bg-light border-0 rounded-3" rows="12" placeholder="Isi artikel (bisa HTML)"><?= esc(old('content', $article['content'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <label class="form-label fw-bold">Gambar Utama</label>
                    <?php if (!empty($article['image']) && is_file(FCPATH . $article['image'])): ?>
                        <div class="mb-2">
                            <img src="<?= base_url($article['image']) ?>" alt="" class="img-fluid rounded-3" style="max-height: 160px; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="image" class="form-control bg-light border-0 rounded-3" accept="image/*">
                    <small class="text-muted d-block mt-1">Opsional. JPG/PNG, max 2MB</small>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input type="hidden" name="is_published" value="0">
                        <input class="form-check-input" type="checkbox" name="is_published" value="1" id="is_published" <?= (old('is_published', $article['is_published'] ?? 0)) ? 'checked' : '' ?>>
                        <label class="form-check-label fw-bold" for="is_published">Publikasikan (tampil di depan)</label>
                    </div>
                    <div class="mb-0">
                        <label class="form-label small fw-bold text-secondary">Tanggal Terbit</label>
                        <input type="datetime-local" name="published_at" class="form-control bg-light border-0 rounded-3" value="<?= old('published_at', !empty($article['published_at']) ? date('Y-m-d\TH:i', strtotime($article['published_at'])) : date('Y-m-d\TH:i')) ?>">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-5 py-3 fw-bold w-100">
                <i class="bi bi-check-lg me-2"></i> <?= $isEdit ? 'Simpan Perubahan' : 'Publikasikan Artikel' ?>
            </button>
        </div>
    </div>
</form>
<?= $this->endSection() ?>

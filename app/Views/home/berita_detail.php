<?php
helper('branding');
$this->extend('layouts/public');
$article = $article ?? [];
?>
<?= $this->section('content') ?>
<div class="container py-5">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="<?= base_url('berita') ?>" class="text-decoration-none">Berita</a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= esc($article['title'] ?? 'Artikel') ?></li>
        </ol>
    </nav>

    <article class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <?php if (!empty($article['image'])): ?>
            <img src="<?= base_url($article['image']) ?>" class="card-img-top w-100" alt="<?= esc($article['title']) ?>" style="max-height: 400px; object-fit: cover;">
        <?php endif; ?>
        <div class="card-body p-4 p-md-5">
            <h1 class="fw-800 text-dark mb-3"><?= esc($article['title'] ?? '') ?></h1>
            <p class="text-muted small mb-4">
                <?= !empty($article['published_at']) ? date('d F Y', strtotime($article['published_at'])) : '' ?>
            </p>
            <div class="article-content" style="line-height: 1.8;">
                <?php if (!empty($article['content'])): ?>
                    <?= $article['content'] ?>
                <?php else: ?>
                    <p class="text-secondary"><?= nl2br(esc($article['excerpt'] ?? '')) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </article>

    <div class="mt-4">
        <a href="<?= base_url('berita') ?>" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Berita</a>
    </div>
</div>
<?= $this->endSection() ?>

<?php
helper('branding');
$this->extend('layouts/public');
?>
<?= $this->section('content') ?>
<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="section-title display-6 mb-2">Berita &amp; Artikel</h1>
        <p class="text-secondary">Artikel dan informasi terbaru dari <?= get_company_name() ?></p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-warning border-0 rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <?php if (empty($articles)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body py-5 text-center text-muted">
                <i class="bi bi-newspaper display-4 opacity-25"></i>
                <p class="mb-0 mt-3">Belum ada artikel yang dipublikasikan.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($articles as $art): ?>
            <div class="col-md-6 col-lg-4">
                <a href="<?= base_url('berita/' . esc($art['slug'])) ?>" class="text-decoration-none text-dark">
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 card-package">
                        <?php if (!empty($art['image'])): ?>
                            <img src="<?= base_url($art['image']) ?>" class="card-img-top" alt="<?= esc($art['title']) ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-primary bg-opacity-10 d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="bi bi-newspaper text-primary display-4"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title fw-bold text-dark mb-2"><?= esc($art['title']) ?></h5>
                            <p class="small text-secondary mb-0"><?= esc(strlen($art['excerpt'] ?? '') > 120 ? substr($art['excerpt'], 0, 120) . '…' : ($art['excerpt'] ?? '')) ?></p>
                            <small class="text-muted d-block mt-2"><?= !empty($art['published_at']) ? date('d F Y', strtotime($art['published_at'])) : '' ?></small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

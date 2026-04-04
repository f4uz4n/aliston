<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="<?= base_url('owner/agency') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-3"><i class="bi bi-arrow-left me-2"></i>Kembali ke Agensi</a>
        <h2 class="fw-800 text-dark mb-1">Tambah Akun Admin Kantor</h2>
        <p class="text-secondary mb-4">Admin kantor dapat mengelola operasional (jamaah, paket, hotel, dll.) tetapi <strong>tidak melihat komisi</strong> agensi.</p>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-4 mb-3"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <form action="<?= base_url('owner/office-admin/store') ?>" method="post" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4 p-4">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Pengguna *</label>
                <input type="text" name="username" class="form-control form-control-lg bg-light border-0" required minlength="3" value="<?= esc(old('username')) ?>" autocomplete="username">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Kata Sandi *</label>
                <input type="password" name="password" class="form-control form-control-lg bg-light border-0" required minlength="6" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Lengkap *</label>
                <input type="text" name="full_name" class="form-control form-control-lg bg-light border-0" required minlength="3" value="<?= esc(old('full_name')) ?>">
            </div>
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Email</label>
                    <input type="email" name="email" class="form-control bg-light border-0" value="<?= esc(old('email')) ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">No. HP *</label>
                    <input type="text" name="phone" class="form-control bg-light border-0" required minlength="8" value="<?= esc(old('phone')) ?>">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Alamat</label>
                <textarea name="address" class="form-control bg-light border-0" rows="2"><?= esc(old('address')) ?></textarea>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Foto profil (opsional)</label>
                <input type="file" name="profile_pic" class="form-control bg-light border-0" accept="image/*">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold"><i class="bi bi-check2 me-2"></i>Simpan Admin Kantor</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

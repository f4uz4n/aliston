<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12">
        <h2 class="fw-800 text-dark mb-1">Pengaturan Akun</h2>
        <p class="text-secondary mb-0">Kelola informasi profil, keamanan, dan branding perusahaan Anda</p>
    </div>
</div>

<?php if(session()->getFlashdata('msg')): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
        <?= session()->getFlashdata('msg') ?>
    </div>
<?php endif; ?>

<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
        <?= session()->getFlashdata('error') ?>
    </div>
<?php endif; ?>

<form action="<?= base_url('owner/update-settings') ?>" method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-4">
        <!-- Profile & Logo Column -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4 text-center">
                    <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-4 text-start">Foto Profil</h6>
                    <div class="mb-3">
                        <?php if($user['profile_pic']): ?>
                            <img src="<?= base_url($user['profile_pic']) ?>" alt="Profile" class="rounded-circle border border-4 border-primary-soft shadow-sm mb-3" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary-soft text-primary d-inline-flex align-items-center justify-content-center border border-4 border-white shadow-sm mb-3" style="width: 120px; height: 120px;">
                                <i class="bi bi-person-fill fs-1"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="profile_pic" class="form-control form-control-sm bg-light border-0 rounded-pill" accept="image/*">
                    <small class="text-muted d-block mt-2">Format: JPG, PNG. Maks: 2MB</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4 text-center">
                    <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-4 text-start">Logo Perusahaan</h6>
                    <div class="mb-3">
                        <?php if($user['company_logo']): ?>
                            <div class="p-3 border rounded-4 bg-light d-inline-block mb-3">
                                <img src="<?= base_url($user['company_logo']) ?>" alt="Logo" style="max-height: 80px; max-width: 100%;">
                            </div>
                        <?php else: ?>
                            <div class="p-4 border border-2 border-dashed rounded-4 bg-light mb-3">
                                <i class="bi bi-image text-muted fs-1 opacity-50"></i>
                                <p class="small text-secondary mb-0 mt-2">Belum ada logo</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <input type="file" name="company_logo" class="form-control form-control-sm bg-light border-0 rounded-pill" accept="image/*">
                    <small class="text-muted d-block mt-2">Digunakan pada laporan, struk & kwitansi</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-3 text-start">Nama PT / Perusahaan</h6>
                    <input type="text" name="company_name" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc($user['company_name'] ?? '') ?>" placeholder="Contoh: PT Aliston Tour & Travel">
                    <small class="text-muted d-block mt-2">Muncul di kop kwitansi & slip komisi</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-3 text-start">Slogan</h6>
                    <input type="text" name="slogan" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc($user['slogan'] ?? '') ?>" placeholder="Contoh: Perjalanan Aman & Nyaman">
                    <small class="text-muted d-block mt-2">Tagline perusahaan (opsional)</small>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-4">
                <div class="card-body p-4">
                    <h6 class="text-secondary small fw-bold text-uppercase ls-1 mb-3 text-start">SK Perijinan</h6>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Nomor SK Perijinan</label>
                        <input type="text" name="no_sk_perijinan" class="form-control bg-light border-0 rounded-3" value="<?= esc($user['no_sk_perijinan'] ?? '') ?>" placeholder="Contoh: 123/DPPT/2024">
                    </div>
                    <div>
                        <label class="form-label small text-muted mb-1">Tanggal SK Perijinan</label>
                        <input type="text" name="tanggal_sk_perijinan" class="form-control bg-light border-0 rounded-3" value="<?= esc($user['tanggal_sk_perijinan'] ?? '') ?>" placeholder="Contoh: 15 Januari 2024">
                    </div>
                    <small class="text-muted d-block mt-2">Surat keterangan perijinan usaha (opsional)</small>
                </div>
            </div>
        </div>

        <!-- Details Column -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Informasi Pribadi</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Lengkap (Nama Direktur)</label>
                            <input type="text" name="full_name" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc($user['full_name']) ?>" required>
                            <small class="text-muted">Digunakan sebagai nama direktur di kwitansi</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nomor HP / WhatsApp</label>
                            <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc($user['phone']) ?>" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Email</label>
                            <input type="email" name="email" class="form-control form-control-lg bg-light border-0 rounded-3" value="<?= esc($user['email']) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Alamat Lengkap</label>
                            <textarea name="address" class="form-control bg-light border-0 rounded-3" rows="3"><?= esc($user['address']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                    <h5 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock-fill me-2 text-warning"></i>Keamanan Akun</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Username</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-at text-secondary"></i></span>
                                <input type="text" name="username" class="form-control form-control-lg bg-light border-0 rounded-end" value="<?= esc($user['username']) ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Ganti Password</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-key text-secondary"></i></span>
                                <input type="password" name="password" class="form-control form-control-lg bg-light border-0 rounded-end" placeholder="Kosongkan jika tidak diubah">
                            </div>
                            <small class="text-muted">Minimal 6 karakter jika ingin mengganti</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn-premium px-5 py-3 shadow-lg rounded-pill border-0">
                    <i class="bi bi-check-circle-fill me-2"></i> Simpan Perubahan Profil
                </button>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
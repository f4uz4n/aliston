<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $s = $saving ?? []; ?>
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <a href="<?= base_url('owner/tabungan') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-3"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <h2 class="fw-800 text-dark mb-1">Edit Jamaah Tabungan</h2>
        <p class="text-secondary mb-4">Ubah data jamaah tabungan</p>
        <?php if (session()->getFlashdata('errors')): $err = session()->getFlashdata('errors'); ?>
        <div class="alert alert-danger border-0 rounded-4 mb-3"><ul class="mb-0 list-unstyled"><?php foreach ($err as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form action="<?= base_url('owner/tabungan/update/' . (int)($s['id'] ?? 0)) ?>" method="post" class="card border-0 shadow-sm rounded-4 p-4">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Agensi *</label>
                <select name="agency_id" class="form-select form-select-lg bg-light border-0" required>
                    <option value="">Pilih Agensi</option>
                    <?php foreach ($agencies ?? [] as $ag): ?>
                    <option value="<?= (int)$ag['id'] ?>" <?= (int)($s['agency_id'] ?? 0) === (int)$ag['id'] || (int)old('agency_id') === (int)$ag['id'] ? 'selected' : '' ?>><?= esc($ag['full_name'] ?? $ag['username']) ?><?= ($ag['username'] ?? '') === 'kantor_pusat' ? ' (Kantor Pusat)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Lengkap *</label>
                <input type="text" name="name" class="form-control form-control-lg bg-light border-0 input-nama-jamaah-upper" required minlength="3" value="<?= esc(old('name', $s['name'] ?? '')) ?>" placeholder="Nama sesuai KTP">
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold">NIK *</label>
                    <input type="text" name="nik" class="form-control form-control-lg bg-light border-0" required minlength="16" maxlength="20" value="<?= esc(old('nik', $s['nik'] ?? '')) ?>" placeholder="16 digit NIK">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">No. HP</label>
                    <input type="text" name="phone" class="form-control form-control-lg bg-light border-0" value="<?= esc(old('phone', $s['phone'] ?? '')) ?>" placeholder="08...">
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Catatan (opsional)</label>
                <textarea name="notes" class="form-control bg-light border-0" rows="2" placeholder="Catatan internal"><?= esc(old('notes', $s['notes'] ?? '')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold"><i class="bi bi-check2 me-2"></i>Simpan Perubahan</button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

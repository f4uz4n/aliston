<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<?php $s = $saving ?? []; ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <a href="<?= base_url('owner/tabungan') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-3"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <h2 class="fw-800 text-dark mb-1">Edit Jamaah Tabungan</h2>
        <p class="text-secondary mb-4">Ubah data jamaah tabungan</p>
        <?php if (session()->getFlashdata('errors')): $err = session()->getFlashdata('errors'); ?>
        <div class="alert alert-danger border-0 rounded-4 mb-3"><ul class="mb-0 list-unstyled"><?php foreach ($err as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <form action="<?= base_url('owner/tabungan/update/' . (int)($s['id'] ?? 0)) ?>" method="post" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4 p-4">
            <?= csrf_field() ?>
            <?php $isMou = (($s['registration_type'] ?? 'mandiri') === 'mou'); ?>
            <input type="hidden" name="registration_type" value="<?= esc($s['registration_type'] ?? 'mandiri') ?>">

            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="small text-secondary fw-bold">Tipe Pendaftaran:</span>
                <?php if ($isMou): ?>
                    <span class="badge bg-primary-soft text-primary rounded-pill px-3 py-2 fw-bold">MOU</span>
                <?php else: ?>
                    <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-bold">MANDIRI</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Agensi *</label>
                <select name="agency_id" class="form-select form-select-lg bg-light border-0" required>
                    <option value="">Pilih Agensi</option>
                    <?php foreach ($agencies ?? [] as $ag): ?>
                    <option value="<?= (int)$ag['id'] ?>" <?= (int)($s['agency_id'] ?? 0) === (int)$ag['id'] || (int)old('agency_id') === (int)$ag['id'] ? 'selected' : '' ?>><?= esc($ag['full_name'] ?? $ag['username']) ?><?= ($ag['username'] ?? '') === 'kantor_pusat' ? ' (Kantor Pusat)' : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if ($isMou): ?>
            <div class="card border-0 bg-light rounded-4 p-3 mb-3">
                <h6 class="fw-bold mb-3">Informasi MOU</h6>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Lembaga *</label>
                        <input type="text" name="institution_name" class="form-control border-0" value="<?= esc(old('institution_name', $s['institution_name'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">No. Telp Lembaga *</label>
                        <input type="text" name="institution_phone" class="form-control border-0" value="<?= esc(old('institution_phone', $s['institution_phone'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Penanggung Jawab *</label>
                        <input type="text" name="institution_pic_name" class="form-control border-0" value="<?= esc(old('institution_pic_name', $s['institution_pic_name'] ?? '')) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Berkas MOU</label>
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <?php if (!empty($s['mou_file'])): ?>
                                <a href="<?= base_url($s['mou_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-paperclip me-1"></i>Lampiran MOU
                                </a>
                            <?php else: ?>
                                <span class="badge bg-light text-secondary border">Belum ada berkas MOU</span>
                            <?php endif; ?>
                        </div>
                        <input type="file" name="mou_file" class="form-control border-0 mt-2" accept=".jpg,.jpeg,.png,.webp,.pdf">
                        <small class="text-muted">Kosongkan jika tidak ganti. Format JPG/PNG/WEBP/PDF (max 5MB).</small>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Alamat Lembaga *</label>
                        <textarea name="institution_address" class="form-control border-0" rows="2" required><?= esc(old('institution_address', $s['institution_address'] ?? '')) ?></textarea>
                    </div>
                </div>
            </div>
            <?php endif; ?>

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
            <div class="mb-3">
                <label class="form-label fw-bold">KTP</label>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <?php if (!empty($s['ktp_file'])): ?>
                        <a href="<?= base_url($s['ktp_file']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="bi bi-card-text me-1"></i>Lihat KTP
                        </a>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">Belum ada file KTP</span>
                    <?php endif; ?>
                </div>
                <input type="file" name="ktp_file" class="form-control bg-light border-0" accept=".jpg,.jpeg,.png,.webp,.pdf">
                <small class="text-muted">Kosongkan jika tidak ganti. Format JPG/PNG/WEBP/PDF (max 5MB).</small>
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

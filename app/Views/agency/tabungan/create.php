<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-11">
        <a href="<?= base_url('agency/tabungan') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-3"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <h2 class="fw-800 text-dark mb-1">Tambah Jamaah Tabungan</h2>
        <p class="text-secondary mb-4">Daftarkan jamaah untuk menabung tanpa memilih paket terlebih dahulu. Setoran via transfer akan diverifikasi admin.</p>
        <?php if (session()->getFlashdata('errors')): $err = session()->getFlashdata('errors'); ?>
        <div class="alert alert-danger border-0 rounded-4 mb-3"><ul class="mb-0 list-unstyled"><?php foreach ($err as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?></ul></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 rounded-4 mb-3"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <form action="<?= base_url('agency/tabungan/store') ?>" method="post" enctype="multipart/form-data" class="card border-0 shadow-sm rounded-4 p-4">
            <?= csrf_field() ?>
            <div class="mb-3">
                <label class="form-label fw-bold">Tipe Pendaftaran *</label>
                <select name="registration_type" id="registrationType" class="form-select form-select-lg bg-light border-0" required>
                    <option value="mandiri" <?= old('registration_type', 'mandiri') === 'mandiri' ? 'selected' : '' ?>>Mandiri</option>
                    <option value="mou" <?= old('registration_type') === 'mou' ? 'selected' : '' ?>>MOU</option>
                </select>
            </div>

            <div id="mandiriFields">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Lengkap *</label>
                    <input type="text" name="name" class="form-control form-control-lg bg-light border-0 input-nama-jamaah-upper" minlength="3" value="<?= esc(old('name')) ?>" placeholder="Nama sesuai KTP">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">NIK *</label>
                        <input type="text" name="nik" class="form-control form-control-lg bg-light border-0 nik-input" inputmode="numeric" minlength="16" maxlength="20" value="<?= esc(old('nik')) ?>" placeholder="16 digit NIK">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">No. HP</label>
                        <input type="text" name="phone" class="form-control form-control-lg bg-light border-0" value="<?= esc(old('phone')) ?>" placeholder="08...">
                    </div>
                </div>
            </div>

            <div id="mouFields" style="display:none;">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nama Lembaga *</label>
                        <input type="text" name="institution_name" class="form-control bg-light border-0" value="<?= esc(old('institution_name')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">No. Telp Lembaga *</label>
                        <input type="text" name="institution_phone" class="form-control bg-light border-0" value="<?= esc(old('institution_phone')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Penanggung Jawab *</label>
                        <input type="text" name="institution_pic_name" class="form-control bg-light border-0" value="<?= esc(old('institution_pic_name')) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Upload Berkas MOU *</label>
                        <input type="file" name="mou_file" class="form-control bg-light border-0 file-validated" accept=".jpg,.jpeg,.png,.webp,.pdf">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Alamat Lembaga *</label>
                        <textarea name="institution_address" class="form-control bg-light border-0" rows="2"><?= esc(old('institution_address')) ?></textarea>
                    </div>
                </div>
                <div class="border rounded-3 p-3 bg-light mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 fw-bold">Daftar Jamaah MOU</h6>
                        <button type="button" id="addMouRow" class="btn btn-sm btn-outline-primary rounded-pill">+ Tambah Jamaah</button>
                    </div>
                    <div id="mouRows">
                        <div class="row g-2 mou-row mb-2">
                            <div class="col-md-4"><input type="text" name="names[]" class="form-control" placeholder="Nama Jamaah"></div>
                            <div class="col-md-3"><input type="text" name="niks[]" class="form-control nik-input" inputmode="numeric" minlength="16" maxlength="20" placeholder="NIK"></div>
                            <div class="col-md-2"><input type="text" name="phones[]" class="form-control" placeholder="No HP"></div>
                            <div class="col-md-2"><input type="file" name="ktp_files[]" class="form-control file-validated" accept=".jpg,.jpeg,.png,.webp,.pdf"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-mou-row">&times;</button></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3" id="mandiriKtpField">
                <label class="form-label fw-bold">Upload KTP *</label>
                <input type="file" id="ktpFileMandiri" name="ktp_file" class="form-control bg-light border-0 file-validated" accept=".jpg,.jpeg,.png,.webp,.pdf" required>
                <small class="text-muted">Bisa gambar atau PDF (maks 5MB).</small>
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Catatan (opsional)</label>
                <textarea name="notes" class="form-control bg-light border-0" rows="2" placeholder="Catatan internal"><?= esc(old('notes')) ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold"><i class="bi bi-check2 me-2"></i>Simpan Jamaah Tabungan</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeEl = document.getElementById('registrationType');
    const mandiri = document.getElementById('mandiriFields');
    const mou = document.getElementById('mouFields');
    const addRowBtn = document.getElementById('addMouRow');
    const mouRows = document.getElementById('mouRows');
    const maxFileSizeBytes = 5 * 1024 * 1024;
    const allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];

    function validateNikInput(input) {
        const digitsOnly = input.value.replace(/\D/g, '');
        if (input.value !== digitsOnly) {
            input.value = digitsOnly;
        }
        if (digitsOnly.length === 0) {
            input.setCustomValidity('');
            return;
        }
        if (digitsOnly.length < 16) {
            input.setCustomValidity('NIK minimal 16 digit.');
        } else if (digitsOnly.length > 20) {
            input.setCustomValidity('NIK maksimal 20 digit.');
        } else {
            input.setCustomValidity('');
        }
    }

    function validateFileInput(input) {
        const file = input.files && input.files[0] ? input.files[0] : null;
        if (!file) {
            input.setCustomValidity('');
            return;
        }
        if (!allowedMimeTypes.includes(file.type)) {
            input.setCustomValidity('File harus berupa JPG, PNG, WEBP, atau PDF.');
            return;
        }
        if (file.size > maxFileSizeBytes) {
            input.setCustomValidity('Ukuran file maksimal 5MB.');
            return;
        }
        input.setCustomValidity('');
    }

    function bindLiveValidation(scope) {
        scope.querySelectorAll('.nik-input').forEach(function (input) {
            input.addEventListener('input', function () { validateNikInput(input); });
            input.addEventListener('blur', function () { validateNikInput(input); });
            validateNikInput(input);
        });
        scope.querySelectorAll('.file-validated').forEach(function (input) {
            input.addEventListener('change', function () {
                validateFileInput(input);
                input.reportValidity();
            });
            validateFileInput(input);
        });
    }

    function toggleMode() {
        const isMou = typeEl.value === 'mou';
        mandiri.style.display = isMou ? 'none' : '';
        mou.style.display = isMou ? '' : 'none';
        const mandiriKtp = document.getElementById('mandiriKtpField');
        const mandiriKtpInput = document.getElementById('ktpFileMandiri');
        if (mandiriKtp) {
            mandiriKtp.style.display = isMou ? 'none' : '';
        }
        if (mandiriKtpInput) {
            mandiriKtpInput.required = !isMou;
        }
        const mouKtpInputs = document.querySelectorAll('input[name="ktp_files[]"]');
        mouKtpInputs.forEach(function (input) {
            input.required = isMou;
        });
    }

    typeEl.addEventListener('change', toggleMode);
    toggleMode();
    bindLiveValidation(document);

    if (addRowBtn && mouRows) {
        addRowBtn.addEventListener('click', function () {
            const isMou = typeEl.value === 'mou';
            const reqAttr = isMou ? ' required' : '';
            const row = document.createElement('div');
            row.className = 'row g-2 mou-row mb-2';
            row.innerHTML = '<div class="col-md-4"><input type="text" name="names[]" class="form-control" placeholder="Nama Jamaah"></div>'
                + '<div class="col-md-3"><input type="text" name="niks[]" class="form-control nik-input" inputmode="numeric" minlength="16" maxlength="20" placeholder="NIK"></div>'
                + '<div class="col-md-2"><input type="text" name="phones[]" class="form-control" placeholder="No HP"></div>'
                + '<div class="col-md-2"><input type="file" name="ktp_files[]" class="form-control file-validated" accept=".jpg,.jpeg,.png,.webp,.pdf"' + reqAttr + '></div>'
                + '<div class="col-md-1"><button type="button" class="btn btn-outline-danger w-100 remove-mou-row">&times;</button></div>';
            mouRows.appendChild(row);
            bindLiveValidation(row);
        });

        mouRows.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-mou-row')) {
                const rows = mouRows.querySelectorAll('.mou-row');
                if (rows.length > 1) {
                    e.target.closest('.mou-row').remove();
                }
            }
        });
    }
});
</script>
<?= $this->endSection() ?>

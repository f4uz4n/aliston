<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 text-dark mb-1">Pendaftaran Jamaah (dari Kantor)</h2>
                <p class="text-secondary mb-0">Paket: <span class="text-primary fw-bold"><?= esc($package['name']) ?></span> — <?= !empty($package['departure_date']) ? date('d/m/Y', strtotime($package['departure_date'])) : '' ?></p>
            </div>
            <a href="<?= base_url('owner/participant/register') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('owner/participant/store-registration-office') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="package_id" value="<?= $package['id'] ?>">

            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-card-heading me-2"></i>Data Identitas (Sesuai KTP WNI)</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="16 Digit NIK" required maxlength="16" minlength="16" value="<?= esc(old('nik')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0 rounded-3 input-nama-jamaah-upper" placeholder="Sesuai KTP" required value="<?= esc(old('name')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Jakarta" required value="<?= esc(old('place_of_birth')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Lahir</label>
                            <input type="date" name="date_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" required value="<?= esc(old('date_of_birth')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Jenis Kelamin</label>
                            <select name="gender" class="form-select form-select-lg bg-light border-0 rounded-3" required>
                                <option value="">Pilih</option>
                                <option value="Laki-laki" <?= old('gender') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= old('gender') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Golongan Darah</label>
                            <input type="text" name="blood_type" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="A/B/O/AB" value="<?= esc(old('blood_type')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Agama</label>
                            <select name="religion" class="form-select form-select-lg bg-light border-0 rounded-3">
                                <option value="Islam" <?= (old('religion') ?: 'Islam') === 'Islam' ? 'selected' : '' ?>>Islam</option>
                                <option value="Kristen">Kristen</option>
                                <option value="Katolik">Katolik</option>
                                <option value="Hindu">Hindu</option>
                                <option value="Budha">Budha</option>
                                <option value="Khonghucu">Khonghucu</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Status Perkawinan</label>
                            <select name="marital_status" class="form-select form-select-lg bg-light border-0 rounded-3">
                                <option value="Belum Kawin">Belum Kawin</option>
                                <option value="Kawin">Kawin</option>
                                <option value="Cerai Hidup">Cerai Hidup</option>
                                <option value="Cerai Mati">Cerai Mati</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Pekerjaan</label>
                            <input type="text" name="occupation" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Karyawan Swasta" value="<?= esc(old('occupation')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-dark text-white py-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-geo-alt-fill me-2"></i>Alamat Lengkap (Domisili KTP)</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Alamat Jalan / Dusun / Lingkungan</label>
                            <textarea name="address" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="Sesuai KTP" required><?= esc(old('address')) ?></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">RT / RW</label>
                            <input type="text" name="rt_rw" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="001/002" value="<?= esc(old('rt_rw')) ?>">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kelurahan / Desa</label>
                            <input type="text" name="kelurahan" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Nama Kelurahan" value="<?= esc(old('kelurahan')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control form-control-lg bg-light border-0 rounded-3" required value="<?= esc(old('kecamatan')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten" class="form-control form-control-lg bg-light border-0 rounded-3" required value="<?= esc(old('kabupaten')) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control form-control-lg bg-light border-0 rounded-3" required value="<?= esc(old('provinsi')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-success text-white py-4 px-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-whatsapp me-2"></i>Kontak Aktif</h5>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nomor WhatsApp Jamaah</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i class="bi bi-phone"></i></span>
                                <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 rounded-end" placeholder="0812..." required value="<?= esc(old('phone')) ?>">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-warning text-dark py-4 px-4">
                            <h5 class="fw-bold mb-0"><i class="bi bi-file-earmark-medical-fill me-2"></i>Pindai Dokumen</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Foto Paspor</label>
                                <input type="file" name="passport" class="form-control form-control-sm bg-light" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Foto KTP</label>
                                <input type="file" name="id_card" class="form-control form-control-sm bg-light" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Upload KK</label>
                                <input type="file" name="kk" class="form-control form-control-sm bg-light" accept="image/*">
                            </div>
                            <div>
                                <label class="form-label small fw-bold text-secondary">Dokumen pendukung (akta lahir/ijazah/buku nikah)</label>
                                <input type="file" name="vaccine" class="form-control form-control-sm bg-light" accept="image/*">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 rounded-pill py-3 fw-bold mb-5">
                <i class="bi bi-check2-circle me-2"></i> Selesaikan & Simpan Pendaftaran
            </button>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

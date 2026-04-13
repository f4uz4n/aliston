<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 text-dark mb-1">Pendaftaran Jamaah</h2>
                <p class="text-secondary mb-0">Paket: <span class="text-primary fw-bold"><?= esc($package['name']) ?></span></p>
            </div>
            <a href="<?= base_url('agency/package-detail/'.$package['id']) ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('agency/store-registration') ?>" method="post" enctype="multipart/form-data">
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
                            <input type="text" name="nik" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="16 Digit NIK" required maxlength="16" minlength="16">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0 rounded-3 input-nama-jamaah-upper" placeholder="Sesuai KTP" required>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tempat Lahir</label>
                            <input type="text" name="place_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Jakarta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Lahir</label>
                            <input type="date" name="date_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Jenis Kelamin</label>
                            <select name="gender" class="form-select form-select-lg bg-light border-0 rounded-3" required>
                                <option value="">Pilih</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Golongan Darah</label>
                            <input type="text" name="blood_type" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="A/B/O/AB">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Agama</label>
                            <select name="religion" class="form-select form-select-lg bg-light border-0 rounded-3">
                                <option value="Islam">Islam</option>
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
                            <input type="text" name="occupation" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Karyawan Swasta">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-info text-white py-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-passport me-2"></i>Data Paspor</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <p class="small text-secondary mb-3">Semua isian di bawah bersifat <strong>opsional</strong>. Isi jika jamaah sudah memiliki paspor.</p>
                    <div class="row g-4">
                        <div class="col-12"><hr class="my-2"></div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Jenis / Type Paspor</label>
                            <select name="passport_type" class="form-select form-select-lg bg-light border-0 rounded-3">
                                <option value="">Pilih</option>
                                <option value="Paspor Biasa">Paspor Biasa</option>
                                <option value="Paspor Diplomatik">Paspor Diplomatik</option>
                                <option value="Paspor Dinas">Paspor Dinas</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nomor Paspor</label>
                            <input type="text" name="passport_number" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Nomor paspor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Lengkap (sesuai paspor)</label>
                            <input type="text" name="passport_full_name" class="form-control form-control-lg bg-light border-0 rounded-3 input-nama-jamaah-upper" placeholder="Nama di paspor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Indonesia (nama IDN di paspor)</label>
                            <input type="text" name="passport_name_idn" class="form-control form-control-lg bg-light border-0 rounded-3 input-nama-jamaah-upper" placeholder="Nama Indonesia di paspor">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kewarganegaraan</label>
                            <input type="text" name="passport_nationality" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Indonesian" value="Indonesian">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tempat Lahir (sesuai paspor)</label>
                            <input type="text" name="passport_place_of_birth" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Jakarta">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Diterbitkan</label>
                            <input type="date" name="passport_issuance_date" class="form-control form-control-lg bg-light border-0 rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Tanggal Habis Berlaku</label>
                            <input type="date" name="passport_expiry_date" class="form-control form-control-lg bg-light border-0 rounded-3">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">No. Reg</label>
                            <input type="text" name="passport_reg_number" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Nomor registrasi">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kantor yang Mengeluarkan</label>
                            <input type="text" name="passport_issuing_office" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="cth: Kantor Imigrasi Kelas I Jakarta Pusat">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kota / Tempat Pengeluaran</label>
                            <input type="text" name="passport_issuance_city" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Kota pengeluaran paspor">
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
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Alamat Jalan / Dusun / Linkungan</label>
                            <textarea name="address" class="form-control bg-light border-0 rounded-3" rows="2" placeholder="Sesuai KTP" required></textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">RT / RW</label>
                            <input type="text" name="rt_rw" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="001/002">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kelurahan / Desa</label>
                            <input type="text" name="kelurahan" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Nama Kelurahan">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kecamatan</label>
                            <input type="text" name="kecamatan" class="form-control form-control-lg bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Kabupaten / Kota</label>
                            <input type="text" name="kabupaten" class="form-control form-control-lg bg-light border-0 rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Provinsi</label>
                            <input type="text" name="provinsi" class="form-control form-control-lg bg-light border-0 rounded-3" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-danger text-white py-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="bi bi-telephone-fill me-2"></i>Kontak Darurat (Keluarga)</h5>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nama Kontak Darurat</label>
                            <input type="text" name="emergency_name" class="form-control form-control-lg bg-light border-0 rounded-3 input-nama-jamaah-upper" placeholder="Nama Keluarga" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Hubungan</label>
                            <input type="text" name="emergency_relationship" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="Istri/Suami/Anak/Orang Tua" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary text-uppercase ls-1">Nomor HP Darurat</label>
                            <input type="text" name="emergency_phone" class="form-control form-control-lg bg-light border-0 rounded-3" placeholder="08..." required minlength="8">
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
                                <input type="text" name="phone" class="form-control form-control-lg bg-light border-0 rounded-end" placeholder="0812..." required>
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
                                <input type="file" name="passport" class="form-control form-control-sm bg-light">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Foto KTP</label>
                                <input type="file" name="id_card" class="form-control form-control-sm bg-light">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-secondary">Upload KK</label>
                                <input type="file" name="kk" class="form-control form-control-sm bg-light">
                            </div>
                            <div>
                                <label class="form-label small fw-bold text-secondary">Dokumen pendukung (akta lahir/ijazah/buku nikah)</label>
                                <input type="file" name="vaccine" class="form-control form-control-sm bg-light">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-premium w-100 rounded-pill py-3 shadow-lg fs-4 fw-bold mb-5">
                <i class="bi bi-check2-circle me-2"></i> Selesaikan & Kirim Pendaftaran
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
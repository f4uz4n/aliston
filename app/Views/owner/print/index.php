<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Cetak Dokumen</h2>
        <p class="text-secondary">Pilih dokumen yang ingin dicetak</p>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<div class="row g-4">
    <!-- Surat Izin Cuti -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-primary-soft rounded-circle p-3 me-3">
                        <i class="bi bi-file-earmark-text-fill text-primary fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Surat Izin Cuti</h5>
                        <p class="text-secondary small mb-0">Cetak surat permohonan izin cuti untuk jamaah (format resmi)</p>
                    </div>
                </div>
                <form action="<?= base_url('owner/print-documents/leave-letter') ?>" method="post" target="_blank">
                    <?= csrf_field() ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="form-control bg-light border-0" value="<?= esc(old('nomor_surat', $default_nomor_surat ?? '')) ?>" placeholder="053/ABW-KDR/IC/I/26">
                            <small class="text-muted">Default: urut/ABW-KDR/IC/bulan romawi/tahun</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Perihal</label>
                            <input type="text" name="perihal" class="form-control bg-light border-0" value="<?= esc(old('perihal', $default_perihal ?? 'Permohonan Ijin Cuti')) ?>" placeholder="Permohonan Ijin Cuti">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Jamaah</label>
                        <select name="participant_id" class="form-select bg-light border-0" required>
                            <option value="">-- Pilih Jamaah --</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?= $p['id'] ?>">
                                    <?= esc($p['name']) ?>
                                    <?php if (!empty($p['nik'])): ?>(NIK: <?= esc($p['nik']) ?>)<?php endif; ?>
                                    — <?= esc($p['package_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Ijin Dari</label>
                            <input type="date" name="tanggal_dari" class="form-control bg-light border-0" value="<?= esc(old('tanggal_dari', '')) ?>" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Ijin Sampai</label>
                            <input type="date" name="tanggal_sampai" class="form-control bg-light border-0" value="<?= esc(old('tanggal_sampai', '')) ?>" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tujuan Surat (di bawah "Kepada Yth.")</label>
                        <textarea name="tujuan_surat" class="form-control bg-light border-0" rows="3" placeholder="Contoh:&#10;Dekan Fakultas Kedokteran Gigi&#10;Universitas Gadjah Mada&#10;Di Tempat"><?= esc(old('tujuan_surat', '')) ?></textarea>
                        <small class="text-muted">Satu baris per baris (nama instansi / Di Tempat)</small>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Label Program Studi</label>
                            <input type="text" name="label_program_studi" class="form-control bg-light border-0" value="<?= esc(old('label_program_studi', 'Program Studi')) ?>" placeholder="Program Studi">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Isi Program Studi</label>
                            <input type="text" name="isi_program_studi" class="form-control bg-light border-0" value="<?= esc(old('isi_program_studi', '')) ?>" placeholder="PROGRAM STUDI HIGIENE GIGI">
                        </div>
                    </div>
                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Label Fakultas</label>
                            <input type="text" name="label_fakultas" class="form-control bg-light border-0" value="<?= esc(old('label_fakultas', 'Fakultas')) ?>" placeholder="Fakultas">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Isi Fakultas</label>
                            <input type="text" name="isi_fakultas" class="form-control bg-light border-0" value="<?= esc(old('isi_fakultas', '')) ?>" placeholder="FAKULTAS KEDOKTERAN GIGI">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100">
                        <i class="bi bi-printer-fill me-2"></i> Cetak Surat Izin Cuti
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Surat Rekomendasi -->
    <div class="col-12 col-md-6">
        <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center mb-4">
                    <div class="bg-success bg-opacity-10 rounded-circle p-3 me-3">
                        <i class="bi bi-file-earmark-check-fill text-success fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Surat Rekomendasi</h5>
                        <p class="text-secondary small mb-0">Rekomendasi penerbitan paspor umrah untuk jamaah</p>
                    </div>
                </div>
                <form action="<?= base_url('owner/print-documents/recommendation-letter') ?>" method="post" target="_blank">
                    <?= csrf_field() ?>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Nomor Surat</label>
                            <input type="text" name="nomor_surat" class="form-control bg-light border-0" value="<?= esc(old('nomor_surat', $default_nomor_rekomendasi ?? '')) ?>" placeholder="0175/ABW/SURAT/I/2026">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Perihal</label>
                            <input type="text" name="perihal" class="form-control bg-light border-0" value="<?= esc(old('perihal', $default_perihal_rekomendasi ?? '')) ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Sifat</label>
                            <input type="text" name="sifat" class="form-control bg-light border-0" value="<?= esc(old('sifat', 'Segera')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Lamp</label>
                            <input type="text" name="lamp" class="form-control bg-light border-0" value="<?= esc(old('lamp', '-')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Tujuan Surat (di bawah "Kepada Yth.")</label>
                        <textarea name="tujuan_surat" class="form-control bg-light border-0" rows="2" placeholder="KEPALA KANTOR IMIGRASI KELAS II NON TPI KEDIRI&#10;Di tempat"><?= esc(old('tujuan_surat', '')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Pilih Jamaah (opsional, untuk isi otomatis)</label>
                        <select name="participant_id" id="rekom_participant" class="form-select bg-light border-0">
                            <option value="">-- Kosongkan jika isi manual --</option>
                            <?php foreach ($participants as $p): ?>
                                <option value="<?= $p['id'] ?>" data-name="<?= esc($p['name']) ?>" data-departure="<?= esc($p['package_departure_date'] ?? '') ?>"><?= esc($p['name']) ?> — <?= esc($p['package_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control bg-light border-0" value="<?= esc(old('nama', '')) ?>" placeholder="Diisi dari data jamaah jika dipilih">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Nama Ayah</label>
                            <input type="text" name="nama_ayah" class="form-control bg-light border-0" value="<?= esc(old('nama_ayah', '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control bg-light border-0" value="<?= esc(old('tempat_lahir', '')) ?>">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Lahir</label>
                            <input type="date" name="tgl_lahir" class="form-control bg-light border-0" value="<?= esc(old('tgl_lahir', '')) ?>">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold small">Tanggal Keberangkatan</label>
                            <input type="date" name="tanggal_keberangkatan" class="form-control bg-light border-0" value="<?= esc(old('tanggal_keberangkatan', '')) ?>">
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-bold small">Alamat</label>
                        <textarea name="alamat" class="form-control bg-light border-0" rows="2"><?= esc(old('alamat', '')) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold w-100">
                        <i class="bi bi-printer-fill me-2"></i> Cetak Surat Rekomendasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
</style>
<?= $this->endSection() ?>

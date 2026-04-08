<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Status Berkas: <?= esc($participant['name']) ?></h2>
        <p class="text-secondary">Verifikasi alur dokumen jamaah</p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <div class="d-flex justify-content-md-end gap-2">
            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="bi bi-upload me-2"></i>Upload Berkas
            </button>
            <a href="<?= base_url('owner/participant/documents') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>
</div>

<?php if (session()->getFlashdata('msg')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('msg') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-12 col-lg-4">
        <!-- Kartu Informasi Jamaah -->
        <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
            <div class="card-body p-4 text-center">
                <div class="rounded-circle bg-primary-soft text-primary mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                    <i class="bi bi-person-fill fs-1"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1"><?= esc($participant['name']) ?></h5>
                <p class="text-secondary small mb-4"><?= esc($participant['nik']) ?></p>
                
                <div class="text-start border-top pt-4">
                    <div class="mb-3">
                        <small class="text-secondary d-block">Telepon</small>
                        <span class="fw-bold text-dark"><?= esc($participant['phone'] ?: '-') ?></span>
                    </div>
                    <div class="mb-3">
                        <small class="text-secondary d-block">No. Paspor</small>
                        <span class="fw-bold text-dark"><?= esc($participant['passport_number'] ?: '-') ?></span>
                    </div>
                    <div class="">
                        <small class="text-secondary d-block">Status</small>
                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-1 fw-bold"><?= strtoupper($participant['status']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ringkasan Kelengkapan Berkas (Simplified) -->
        <div class="card border-0 shadow-sm rounded-4 bg-white sticky-top" style="top: 20px;">
            <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                <h6 class="fw-bold text-dark small text-uppercase mb-0">Progress Kelengkapan</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="small fw-bold text-dark text-uppercase" style="font-size: 0.7rem;">Status Verifikasi Digital</span>
                    <span class="small fw-bold text-primary"><?= $doc_progress ?>%</span>
                </div>
                <div class="progress mb-3" style="height: 12px; background-color: #f1f3f5;">
                    <div class="progress-bar bg-primary rounded-pill" role="progressbar" style="width: <?= $doc_progress ?>%"></div>
                </div>
                <div class="text-center p-3 rounded-4 bg-light border border-dashed">
                    <h3 class="fw-bold text-dark mb-0"><?= $verified_count ?> <small class="text-secondary fw-normal fs-6">/ <?= $total_goal ?></small></h3>
                    <p class="text-secondary small mb-0 mt-1 fw-bold">Berkas Terverifikasi</p>
                </div>
                <div class="mt-4">
                    <div class="d-flex align-items-center p-3 rounded-4 bg-primary-soft text-primary border border-primary border-opacity-10 mb-2">
                        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <small class="fw-bold lh-sm">Unggah 7 berkas wajib untuk mencapai progress 100%.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-lg-8">
        <!-- Daftar Berkas Digital -->
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-header bg-transparent border-0 py-4 px-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Kelola Berkas Digital</h5>
                <span class="badge bg-light text-dark border rounded-pill px-3"><?= count($documents) ?> Berkas</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 border-0">Judul / Jenis</th>
                                <th class="border-0">Upload</th>
                                <th class="border-0">Status</th>
                                <th class="pe-4 border-0 text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($documents)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-secondary">Belum ada berkas digital.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($documents as $doc): ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary-soft text-primary p-2 rounded-3 me-3">
                                                <i class="bi bi-file-earmark-pdf fs-4"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block"><?= esc($doc['title'] ?: strtoupper(str_replace('_', ' ', $doc['type']))) ?></span>
                                                <small class="text-muted text-uppercase" style="font-size: 0.6rem;"><?= str_replace('_', ' ', $doc['type']) ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="small text-secondary"><?= date('d/m/y', strtotime($doc['created_at'])) ?></td>
                                    <td>
                                        <?php if($doc['is_verified']): ?>
                                            <span class="badge bg-success-soft text-success rounded-pill px-3 py-1 fw-bold">VERIFIED</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-1 fw-bold">PENDING</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="<?= base_url($doc['file_path']) ?>" target="_blank" class="btn btn-light btn-sm rounded-pill px-3 border shadow-sm"><i class="bi bi-eye"></i></a>
                                            <?php if (is_owner()): ?>
                                            <form action="<?= base_url('owner/participant/verify-document') ?>" method="post" class="d-inline">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $doc['id'] ?>">
                                                <input type="hidden" name="status" value="<?= $doc['is_verified'] ? '0' : '1' ?>">
                                                <button type="submit" class="btn btn-sm rounded-pill px-3 fw-bold shadow-sm <?= $doc['is_verified'] ? 'btn-outline-danger' : 'btn-success' ?>">
                                                    <?= $doc['is_verified'] ? 'Unverify' : 'Verify' ?>
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Upload Berkas Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('owner/participant/upload-document') ?>" method="post" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="participant_id" value="<?= esc($participant['id']) ?>">
                <div class="modal-body py-4">
                    <div id="uploadContainer">
                        <div class="upload-row mb-4 border-bottom pb-4 position-relative">
                            <div class="row g-3">
                                <div class="col-6">
                                    <label class="form-label small text-uppercase text-secondary fw-bold">Jenis Berkas</label>
                                    <select name="types[]" class="form-select bg-light border-0" required>
                                        <option value="passport">Paspor</option>
                                        <option value="visa">Visa</option>
                                        <option value="vaccine_meningitis">Vaksin Meningitis</option>
                                        <option value="vaccine_covid">Vaksin Covid</option>
                                        <option value="insurance">Asuransi</option>
                                        <option value="ticket">Tiket</option>
                                        <option value="photo">Pas Foto 4x6</option>
                                        <option value="other">Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-uppercase text-secondary fw-bold">Judul (Opsional)</label>
                                    <input type="text" name="titles[]" class="form-control bg-light border-0" placeholder="KTP / Akte / dll">
                                </div>
                                <div class="col-12">
                                    <input type="file" name="files[]" class="form-control bg-light border-0" required accept=".pdf,image/*">
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-bold border w-100 mb-3 py-2" id="btnAddUpload">
                        <i class="bi bi-plus-lg me-1"></i> Tambah Berkas
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold w-100 shadow-sm py-2">Mulai Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-primary-soft { background-color: rgba(13, 110, 253, 0.08); }
.bg-success-soft { background-color: rgba(25, 135, 84, 0.08); }
.bg-warning-soft { background-color: rgba(255, 193, 7, 0.08); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAddUpload = document.getElementById('btnAddUpload');
    const uploadContainer = document.getElementById('uploadContainer');
    
    btnAddUpload.addEventListener('click', function() {
        const firstRow = document.querySelector('.upload-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelectorAll('input').forEach(i => i.value = '');
        newRow.querySelector('select').selectedIndex = 0;
        
        const removeBtn = document.createElement('button');
        removeBtn.className = 'btn btn-sm btn-outline-danger border-0 position-absolute top-0 end-0 mt-n1 me-n1 rounded-circle bg-white shadow-sm';
        removeBtn.innerHTML = '<i class="bi bi-x"></i>';
        removeBtn.style.width = '24px';
        removeBtn.style.height = '24px';
        removeBtn.onclick = () => newRow.remove();
        newRow.appendChild(removeBtn);
        uploadContainer.appendChild(newRow);
    });
});
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 rounded-pill px-4 py-2 d-inline-block mb-2"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-pill px-4 py-2 d-inline-block mb-2"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
        <h2 class="fw-800 text-dark mb-1">Verifikasi Pembayaran</h2>
        <p class="text-secondary mb-0">Kelola konfirmasi pembayaran dari Agency & Jamaah</p>
        <?php if (($total ?? 0) > 0): ?>
            <small class="text-muted">Menampilkan <?= count($payments) ?> dari <?= (int) $total ?> pembayaran</small>
        <?php endif; ?>
        <?php if (!empty($nama_sekretaris_bendahara)): ?>
        <p class="text-muted small mb-0 mt-1"><i class="bi bi-person-badge me-1"></i> Sekretaris/Bendahara: <strong><?= esc($nama_sekretaris_bendahara) ?></strong></p>
        <?php endif; ?>
    </div>
</div>

<?php
$historyScope = $history_scope ?? 'active';
$filterQuery = array_filter([
    'participant_id' => $filters['participant_id'] ?? '',
    'search' => $filters['search'] ?? '',
    'start_date' => $filters['start_date'] ?? '',
    'end_date' => $filters['end_date'] ?? '',
]);
$url_pending = base_url('owner/payment-verification') . (empty($filterQuery) ? '' : '?' . http_build_query($filterQuery));
$url_history_active = base_url('owner/payment-verification') . '?' . http_build_query(array_merge($filterQuery, ['tab' => 'history', 'history_scope' => 'active']));
$url_history_cancelled = base_url('owner/payment-verification') . '?' . http_build_query(array_merge($filterQuery, ['tab' => 'history', 'history_scope' => 'cancelled']));
?>
<!-- Tabs: Perlu Verifikasi aktif saat buka dari menu -->
<div class="mb-4">
    <ul class="nav nav-pills bg-white p-1 rounded-pill shadow-sm d-inline-flex" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="<?= esc($url_pending) ?>" class="nav-link rounded-pill px-4 <?= ($active_tab === 'pending') ? 'active fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-clock-history me-2"></i> Perlu Verifikasi
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="<?= esc($url_history_active) ?>" class="nav-link rounded-pill px-4 <?= ($active_tab === 'history') ? 'active fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-archive me-2"></i> Riwayat
            </a>
        </li>
    </ul>
</div>

<?php if ($active_tab === 'history'): ?>
<div class="mb-4">
    <ul class="nav nav-pills bg-light p-1 rounded-pill d-inline-flex" role="tablist">
        <li class="nav-item" role="presentation">
            <a href="<?= esc($url_history_active) ?>" class="nav-link rounded-pill px-4 <?= $historyScope === 'active' ? 'active fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-person-check me-2"></i> Jamaah Aktif
            </a>
        </li>
        <li class="nav-item" role="presentation">
            <a href="<?= esc($url_history_cancelled) ?>" class="nav-link rounded-pill px-4 <?= $historyScope === 'cancelled' ? 'active fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-person-x me-2"></i> Jamaah Batal
            </a>
        </li>
    </ul>
</div>
<?php endif; ?>

<!-- Filter Form -->
<form action="" method="get" class="card border-0 shadow-sm p-3 mb-4 rounded-4 bg-white">
    <input type="hidden" name="tab" value="<?= esc($active_tab) ?>">
    <?php if ($active_tab === 'history'): ?>
        <input type="hidden" name="history_scope" value="<?= esc($historyScope) ?>">
    <?php endif; ?>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="bi bi-person text-secondary"></i></span>
                <select name="participant_id" class="form-select border-0 bg-light">
                    <option value="">Semua Jamaah</option>
                    <?php foreach (($participants_list ?? []) as $pj): ?>
                        <option value="<?= (int)$pj['id'] ?>" <?= (string)($filters['participant_id'] ?? '') === (string)$pj['id'] ? 'selected' : '' ?>><?= esc(format_nama_jamaah($pj['name'])) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="col-md-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                <input type="text" name="search" class="form-control border-0 bg-light" placeholder="Cari Nama, NIK, Agency, Paket..." value="<?= esc($filters['search'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text bg-light border-0 text-secondary small">Mulai:</span>
                <input type="date" name="start_date" class="form-control border-0 bg-light" value="<?= esc($filters['start_date'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-2">
            <div class="input-group">
                <span class="input-group-text bg-light border-0 text-secondary small">Sampai:</span>
                <input type="date" name="end_date" class="form-control border-0 bg-light" value="<?= esc($filters['end_date'] ?? '') ?>">
            </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary flex-grow-1 rounded-pill fw-bold">Filter</button>
            <?php if(($filters['search'] ?? '') || ($filters['start_date'] ?? '') || ($filters['end_date'] ?? '') || ($filters['participant_id'] ?? '') !== ''): ?>
                <?php
                $resetQuery = ['tab' => $active_tab];
                if ($active_tab === 'history') {
                    $resetQuery['history_scope'] = $historyScope;
                }
                ?>
                <a href="<?= base_url('owner/payment-verification?' . http_build_query($resetQuery)) ?>" class="btn btn-light rounded-pill border" data-bs-toggle="tooltip" title="Reset Filter"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4 py-3 text-secondary small text-uppercase" width="60">No</th>
                        <th class="py-3 text-secondary small text-uppercase">Detail Jamaah</th>
                        <th class="py-3 text-secondary small text-uppercase" width="30%">Progress Pembayaran</th>
                        <th class="py-3 text-secondary small text-uppercase">Nominal Bayar</th>
                        <th class="py-3 text-secondary small text-uppercase">Bukti</th>
                        <th class="pe-4 py-3 text-end text-secondary small text-uppercase">Status/Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($payments)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <div class="py-3">
                                    <?php if($active_tab === 'pending'): ?>
                                        <i class="bi bi-check-circle-fill text-success opacity-25 display-3 d-block mb-2"></i>
                                        <h6 class="text-secondary">Tidak ada data ditemukan</h6>
                                        <p class="small mb-0">Cobalah mengubah filter pencarian atau belum ada pembayaran baru.</p>
                                    <?php elseif ($historyScope === 'cancelled'): ?>
                                        <i class="bi bi-person-x text-secondary opacity-25 display-3 d-block mb-2"></i>
                                        <h6 class="text-secondary">Tidak ada riwayat pembayaran jamaah batal</h6>
                                        <p class="small mb-0">Cobalah mengubah filter pencarian anda.</p>
                                    <?php else: ?>
                                        <i class="bi bi-archive text-secondary opacity-25 display-3 d-block mb-2"></i>
                                        <h6 class="text-secondary">Tidak ada riwayat pembayaran jamaah aktif</h6>
                                        <p class="small mb-0">Cobalah mengubah filter pencarian anda.</p>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach($payments as $i => $p): ?>
                        <tr>
                            <td class="ps-4 text-secondary fw-bold"><?= ($start_number ?? 0) + $i + 1 ?></td>
                            <td>
                                <div class="d-flex align-items-center mb-1 flex-wrap gap-1">
                                    <span class="fw-bold text-dark me-2"><?= esc(format_nama_jamaah($p['participant_name'] ?? '')) ?></span>
                                    <span class="badge bg-light text-secondary border"><?= esc($p['participant_nik']) ?></span>
                                    <?php if (($p['participant_status'] ?? '') === 'cancelled'): ?>
                                        <span class="badge bg-danger-soft text-danger rounded-pill px-2 py-1 small fw-bold">BATAL</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-secondary mb-1">
                                    <i class="bi bi-building me-1"></i> <?= esc($p['agency_name']) ?>
                                </div>
                                <div class="small text-primary">
                                    <i class="bi bi-box-seam me-1"></i> <?= esc($p['package_name']) ?>
                                </div>
                            </td>
                            <td>
                                <div class="mb-2 d-flex justify-content-between small fw-bold">
                                    <span class="text-secondary">Telah Dibayar (Verified)</span>
                                    <span class="text-dark">Rp <?= number_format($p['total_paid_verified'], 0, ',', '.') ?></span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?= $p['progress_percentage'] ?>%" aria-valuenow="<?= $p['progress_percentage'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="mt-2 text-end small">
                                    <span class="text-muted">Total Tagihan:</span>
                                    <span class="fw-bold text-dark ms-1">Rp <?= number_format($p['total_target'] ?? (($p['package_price'] ?? 0) + ($p['upgrade_cost'] ?? 0)), 0, ',', '.') ?></span>
                                </div>
                                <div class="mt-1 text-end small">
                                    <?php $sisaBayar = max(0, (float)($p['total_target'] ?? 0) - (float)($p['total_paid_verified'] ?? 0)); ?>
                                    <span class="text-muted">Kekurangan Bayar:</span>
                                    <span class="fw-bold <?= $sisaBayar > 0 ? 'text-danger' : 'text-success' ?> ms-1">Rp <?= number_format($sisaBayar, 0, ',', '.') ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <h5 class="fw-bold text-dark mb-0 me-2">Rp <?= number_format($p['amount'], 0, ',', '.') ?></h5>
                                    <?php if($p['notes']): ?>
                                        <i class="bi bi-info-circle text-secondary" data-bs-toggle="tooltip" title="<?= esc($p['notes']) ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted d-block mt-1"><?= date('d M Y H:i', strtotime($p['payment_date'])) ?></small>
                            </td>
                            <td>
                                <?php if (!empty($p['proof'])): ?>
                                <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3" onclick="showProof('<?= esc(base_url($p['proof']), 'js') ?>')">
                                    <i class="bi bi-eye me-1"></i> Lihat
                                </button>
                                <?php else: ?>
                                <span class="text-muted small">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <?php if($active_tab === 'pending'): ?>
                                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                        <a href="<?= base_url('owner/payment-verification/edit-payment/' . $p['id']) ?>" class="btn btn-outline-info btn-sm rounded-pill" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <form action="<?= base_url('owner/payment-verification/delete-payment/' . $p['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus pembayaran Rp <?= number_format($p['amount'], 0, ',', '.') ?> ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="verifyPayment(<?= $p['id'] ?>, 'verified')">
                                            <i class="bi bi-check-lg"></i> Terima
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" onclick="verifyPayment(<?= $p['id'] ?>, 'rejected')">
                                            <i class="bi bi-x-lg"></i> Tolak
                                        </button>
                                    </div>
                                <?php else: ?>
                                    <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                        <a href="<?= base_url('owner/payment-verification/edit-payment/' . $p['id']) ?>" class="btn btn-outline-info btn-sm rounded-pill" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                        <form action="<?= base_url('owner/payment-verification/delete-payment/' . $p['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Yakin hapus pembayaran Rp <?= number_format($p['amount'], 0, ',', '.') ?> ini?');">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>
                                        <?php if($p['status'] === 'verified'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3 py-2">
                                                <i class="bi bi-check-circle-fill me-1"></i> Diterima
                                            </span>
                                            <a href="<?= base_url('owner/participant/transaction-receipt/' . $p['id']) ?>" 
                                               target="_blank"
                                               class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                               style="width: 32px; height: 32px;"
                                               data-bs-toggle="tooltip"
                                               title="Cetak Kwitansi">
                                                <i class="bi bi-printer"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2">
                                                <i class="bi bi-x-circle-fill me-1"></i> Ditolak
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (!empty($pager)): ?>
            <div class="card-footer bg-white border-0 py-3 px-4">
                <?= $pager->links() ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Bukti Transfer -->
<div class="modal fade" id="proofModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-body p-0 position-relative bg-dark">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="proofImage" src="" class="img-fluid w-100" alt="Bukti Transfer">
            </div>
            <div class="modal-footer border-0 bg-white">
                <a id="downloadLink" href="" download class="btn btn-primary rounded-pill me-auto">
                    <i class="bi bi-download me-2"></i> Unduh
                </a>
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Verifikasi -->
<div class="modal fade" id="verifyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="verifyTitle">Konfirmasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= base_url('owner/verify-payment') ?>" method="post">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" id="paymentId">
                    <input type="hidden" name="status" id="paymentStatus">
                    
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                            <i id="verifyIcon" class="bi fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark" id="verifyHeader">Header</h5>
                        <p class="text-secondary small mb-0" id="verifyMessage">Message</p>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small text-uppercase text-secondary fw-bold">Catatan (Opsional)</label>
                        <textarea class="form-control bg-light border-0" name="notes" rows="3" placeholder="Tambahkan catatan untuk agency..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 justify-content-center pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" id="confirmBtn">Konfirmasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
  return new bootstrap.Tooltip(tooltipTriggerEl)
})

function showProof(url) {
    document.getElementById('proofImage').src = url;
    document.getElementById('downloadLink').href = url;
    new bootstrap.Modal(document.getElementById('proofModal')).show();
}

function verifyPayment(id, status) {
    document.getElementById('paymentId').value = id;
    document.getElementById('paymentStatus').value = status;
    
    const header = document.getElementById('verifyHeader');
    const msg = document.getElementById('verifyMessage');
    const btn = document.getElementById('confirmBtn');
    const icon = document.getElementById('verifyIcon');
    
    if(status === 'verified') {
        header.innerText = 'Terima Pembayaran?';
        msg.innerText = 'Pembayaran ini akan dicatat sebagai lunas/cicilan masuk.';
        btn.className = 'btn btn-success rounded-pill px-5 fw-bold';
        btn.innerText = 'Ya, Terima';
        icon.className = 'bi bi-check-lg text-success fs-2';
    } else {
        header.innerText = 'Tolak Pembayaran?';
        msg.innerText = 'Agency akan diminta untuk mengupload ulang bukti pembayaran.';
        btn.className = 'btn btn-danger rounded-pill px-5 fw-bold';
        btn.innerText = 'Ya, Tolak';
        icon.className = 'bi bi-x-lg text-danger fs-2';
    }
    
    new bootstrap.Modal(document.getElementById('verifyModal')).show();
}
</script>
<?= $this->endSection() ?>

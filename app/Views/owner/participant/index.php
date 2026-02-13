<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-5">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Daftar Jamaah</h2>
        <p class="text-secondary mb-0">Pantau pendaftaran jamaah dari seluruh agensi</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <a href="<?= base_url('owner/participant/register') ?>" class="btn btn-primary rounded-pill px-4 fw-bold">
            <i class="bi bi-person-plus me-2"></i>Tambah Jamaah
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 bg-white">
            <div class="card-body p-3">
                <form action="<?= base_url('owner/participant') ?>" method="get" class="row g-3">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-secondary"></i></span>
                            <input type="text" name="search" class="form-control bg-light border-0" placeholder="Cari nama, nomor HP, atau agensi..." value="<?= esc($keyword ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-funnel text-secondary"></i></span>
                            <select name="status" class="form-select bg-light border-0">
                                <option value="">Semua Status Verifikasi</option>
                                <option value="verified" <?= ($status ?? '') === 'verified' ? 'selected' : '' ?>>Terverifikasi</option>
                                <option value="pending" <?= ($status ?? '') === 'pending' ? 'selected' : '' ?>>Belum Verifikasi</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill fw-bold">
                                <i class="bi bi-search me-1"></i> Cari
                            </button>
                            <?php if(!empty($keyword) || ($status ?? '') !== ''): ?>
                                <a href="<?= base_url('owner/participant') ?>" class="btn btn-light rounded-pill fw-bold border">
                                    Reset
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 border-0 text-secondary small fw-bold text-uppercase">Nama Jamaah</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase text-center">Status</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase" width="22%">Pembayaran</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase" width="22%">Berkas</th>
                            <th class="py-3 border-0 text-secondary small fw-bold text-uppercase">Agensi</th>
                            <th class="pe-4 py-3 border-0 text-secondary small fw-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($participants)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="py-4">
                                        <i class="bi bi-people-fill text-muted fs-1 mb-3"></i>
                                        <p class="text-secondary mb-0">Belum ada jamaah yang terdaftar.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($participants as $part): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-info-soft text-info p-2 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-dark d-block"><?= esc($part['name']) ?></span>
                                            <small class="text-secondary"><?= esc($part['phone']) ?></small>
                                            <div class="mt-1">
                                                 <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small" style="font-size: 0.65rem;"><?= esc($part['package_name']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <?php if($part['status'] == 'verified'): ?>
                                        <span class="badge bg-success-soft text-success rounded-pill px-3 py-2 fw-bold">TERVERIFIKASI</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-soft text-warning rounded-pill px-3 py-2 fw-bold">PENDING</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span class="text-primary"><?= $part['payment_progress'] ?>%</span>
                                        <span class="text-secondary small">Rp <?= number_format($part['total_paid'] / 1000000, 1) ?>M <span class="text-muted fw-normal">/ <?= number_format(($part['total_target'] ?? $part['package_price']) / 1000000, 1) ?>M</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= ($part['payment_progress'] >= 100) ? 'bg-success' : 'bg-primary' ?>" role="progressbar" style="width: <?= $part['payment_progress'] ?>%" aria-valuenow="<?= $part['payment_progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-between small fw-bold mb-1">
                                        <span class="<?= ($part['doc_progress'] >= 100) ? 'text-success' : 'text-warning' ?>"><?= $part['doc_progress'] ?>%</span>
                                        <span class="text-secondary small"><?= $part['doc_count'] ?> / <span class="text-muted fw-normal">7 Item</span></span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar <?= ($part['doc_progress'] >= 100) ? 'bg-success' : 'bg-warning' ?>" role="progressbar" style="width: <?= $part['doc_progress'] ?>%" aria-valuenow="<?= $part['doc_progress'] ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="bg-secondary rounded-circle me-2" style="width: 6px; height: 6px;"></div>
                                        <span class="text-secondary small fw-bold text-uppercase"><?= esc($part['agency_name']) ?></span>
                                    </div>
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-flex justify-content-end gap-1 flex-wrap">
                                        <a href="<?= base_url('owner/payment-verification?participant_id=' . (int)$part['id'] . '&tab=pending') ?>" 
                                           class="btn btn-success btn-sm rounded-pill px-3 d-flex align-items-center"
                                           title="Kelola (verifikasi pembayaran jamaah ini)">
                                            <i class="bi bi-gear-fill me-1"></i> Kelola
                                        </a>
                                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center btn-history" style="width: 32px; height: 32px;" data-id="<?= $part['id'] ?>" data-name="<?= esc($part['name']) ?>" title="Riwayat Pembayaran"><i class="bi bi-clock-history"></i></button>
                                        <?php $total_target_part = $part['total_target'] ?? ((float)($part['package_price'] ?? 0) + (float)($part['upgrade_cost'] ?? 0)); $lunas = $total_target_part > 0 && ($part['total_paid'] ?? 0) >= $total_target_part; ?>
                                        <?php if ($lunas): ?>
                                        <a href="<?= base_url('owner/participant/receipt/' . $part['id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Cetak Kwitansi Lunas"><i class="bi bi-printer"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= base_url('owner/participant/registration-form/' . $part['id']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Cetak Formulir Pendaftaran"><i class="bi bi-file-earmark-text"></i></a>
                                        <a href="<?= base_url('owner/participant/documents/' . $part['id']) ?>" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 32px; height: 32px;" title="Lihat Berkas"><i class="bi bi-file-earmark-check"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($pager): ?>
                <div class="card-footer bg-white border-0 py-3 px-4">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Riwayat Pembayaran -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Riwayat Pembayaran: <span id="historyParticipantName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">Tanggal</th>
                                <th>Nominal</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th class="pe-3 text-end">Bukti</th>
                            </tr>
                        </thead>
                        <tbody id="historyContent">
                            <!-- Content loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const historyModal = new bootstrap.Modal(document.getElementById('historyModal'));
    const historyContent = document.getElementById('historyContent');
    const historyParticipantName = document.getElementById('historyParticipantName');

    document.querySelectorAll('.btn-history').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            
            historyParticipantName.innerText = name;
            historyContent.innerHTML = '<tr><td colspan="5" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary me-2"></div> Memuat data...</td></tr>';
            historyModal.show();

            fetch(`<?= base_url('owner/participant/payment-history') ?>/${id}`)
                .then(response => response.json())
                .then(res => {
                    if (res.status === 'success') {
                        if (res.data.length === 0) {
                            historyContent.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-muted">Belum ada riwayat pembayaran.</td></tr>';
                        } else {
                            let html = '';
                            res.data.forEach(item => {
                                let statusBadge = '';
                                if (item.status === 'verified') statusBadge = '<span class="badge bg-success-soft text-success rounded-pill px-2">Terverifikasi</span>';
                                else if (item.status === 'pending') statusBadge = '<span class="badge bg-warning-soft text-warning rounded-pill px-2">Pending</span>';
                                else statusBadge = '<span class="badge bg-danger-soft text-danger rounded-pill px-2">Ditolak</span>';

                                const formattedDate = new Date(item.payment_date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
                                const formattedAmount = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(item.amount);

                                html += `
                                    <tr>
                                        <td class="ps-3 small fw-bold">${formattedDate}</td>
                                        <td class="fw-bold text-dark">${formattedAmount}</td>
                                        <td>${statusBadge}</td>
                                        <td class="small text-secondary">${item.notes || '-'}</td>
                                        <td class="pe-3 text-end">
                                            ${item.proof ? `<a href="<?= base_url() ?>${item.proof}" target="_blank" class="btn btn-light btn-sm rounded-pill px-2 border"><i class="bi bi-image"></i></a>` : '-'}
                                        </td>
                                    </tr>
                                `;
                            });
                            historyContent.innerHTML = html;
                        }
                    } else {
                        historyContent.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Gagal memuat data.</td></tr>';
                    }
                })
                .catch(err => {
                    historyContent.innerHTML = '<tr><td colspan="5" class="text-center py-4 text-danger">Terjadi kesalahan sistem.</td></tr>';
                });
        });
    });
});
</script>

<style>
    .bg-info-soft { background: rgba(13, 202, 240, 0.1); }
    .text-info { color: #0dcaf0 !important; }
    .bg-primary-soft { background: rgba(13, 110, 253, 0.1); }
    .text-primary { color: #0d6efd !important; }
    .bg-success-soft { background: rgba(25, 135, 84, 0.1); }
    .text-success { color: #198754 !important; }
    .bg-warning-soft { background: rgba(255, 193, 7, 0.1); }
    .text-warning { color: #ffc107 !important; }
    .bg-danger-soft { background: rgba(220, 53, 69, 0.1); }
    .text-danger { color: #dc3545 !important; }
    .fw-800 { font-weight: 800; }
</style>

<?= $this->endSection() ?>

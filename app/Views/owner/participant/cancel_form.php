<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <a href="<?= base_url('owner/participant/kelola/' . $participant['id']) ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-2">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Kelola Jamaah
        </a>
        <h2 class="fw-800 text-dark mb-1">Batalkan Jamaah</h2>
        <p class="text-secondary mb-0"><?= esc($participant['name']) ?> — <?= esc($participant['agency_name']) ?></p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-light border-0 py-3">
        <h6 class="mb-0 fw-bold"><i class="bi bi-x-circle me-2"></i>Form Pembatalan & Refund</h6>
    </div>
    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Paket</p>
                <strong><?= esc($participant['package_name']) ?></strong>
                <?php if (!empty($participant['package_departure_date'])): ?>
                    <span class="text-muted">(<?= date('d/m/Y', strtotime($participant['package_departure_date'])) ?>)</span>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <p class="small text-secondary mb-1">Total sudah dibayar (verified)</p>
                <strong class="text-primary">Rp <?= number_format($total_paid, 0, ',', '.') ?></strong>
            </div>
        </div>
        <form action="<?= base_url('owner/participant/store-cancellation') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
            <div class="mb-3">
                <label class="form-label fw-bold">Nominal Refund (Rp) <span class="text-danger">*</span></label>
                <input type="number" name="refund_amount" class="form-control form-control-lg" value="<?= (int) $default_refund ?>" min="0" step="0.01" required placeholder="0">
                <div class="form-text">Default diisi otomatis. Admin dapat mengubah nominal refund.</div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">No. Rekening yang Ditransfer <span class="text-danger">*</span></label>
                <input type="text" name="refund_rekening" class="form-control" value="<?= esc(old('refund_rekening', $participant['refund_rekening'] ?? '')) ?>" placeholder="Contoh: 1234567890" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Nama Bank</label>
                <input type="text" name="refund_bank_name" class="form-control" value="<?= esc(old('refund_bank_name', $participant['refund_bank_name'] ?? '')) ?>" placeholder="Contoh: BCA, Mandiri">
            </div>
            <div class="mb-4">
                <label class="form-label fw-bold">Catatan Pembatalan</label>
                <textarea name="cancellation_notes" class="form-control" rows="3" placeholder="Alasan atau catatan pembatalan..."><?= esc($participant['cancellation_notes'] ?? '') ?></textarea>
            </div>
            <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-x-circle me-1"></i> Konfirmasi Pembatalan</button>
            <a href="<?= base_url('owner/participant/kelola/' . $participant['id']) ?>" class="btn btn-outline-secondary rounded-pill px-4 ms-2">Batal</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('agency/payments') ?>" class="text-primary text-decoration-none">Laporan Pembayaran</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Detail Cicilan</li>
                    </ol>
                </nav>
                <h2 class="fw-800 text-dark mb-0">Jamaah: <?= esc($participant['name']) ?></h2>
            </div>
            <a href="<?= base_url('agency/payments') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <?php if(session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> <?= session()->getFlashdata('msg') ?>
            </div>
        <?php endif; ?>
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?= session()->getFlashdata('error') ?>
            </div>
        <?php endif; ?>

        <?php 
        // Total tagihan = harga paket + biaya upgrade (hotel/kamar/bed) — dari controller
        $total_tagihan = (float)($total_tagihan ?? ($participant['package_price'] ?? 0) + ($participant['upgrade_cost'] ?? 0)); 
        $sisa_tagihan = (float)($sisa_tagihan ?? ($total_tagihan - (float)$total_paid));
        $is_lunas = (float)$total_paid >= $total_tagihan; 
        ?>
        <?php if ($is_lunas): ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-success bg-opacity-10 border border-success border-opacity-25">
            <div class="card-body py-4 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div class="d-flex align-items-center">
                    <span class="rounded-circle bg-success bg-opacity-25 p-3 me-3"><i class="bi bi-check2-circle text-success fs-4"></i></span>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Pembayaran Lunas</h6>
                        <p class="text-secondary small mb-0">Total terbayar Rp <?= number_format((float)$total_paid, 0, ',', '.') ?> — cetak kwitansi lunas untuk arsip.</p>
                    </div>
                </div>
                <a href="<?= base_url('agency/receipt/'.$participant['id']) ?>" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold">
                    <i class="bi bi-printer me-2"></i> Cetak Pembayaran Lunas
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-4 h-100">
                    <h6 class="text-white-50 small text-uppercase fw-bold mb-2">Sisa Tagihan</h6>
                    <h3 class="fw-800 mb-0">Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?></h3>
                    <small class="text-white-50 mt-2">Dari total Rp <?= number_format($total_tagihan, 0, ',', '.') ?><?= (!empty($participant['upgrade_cost']) && (float)$participant['upgrade_cost'] > 0) ? ' <span class="opacity-75">(Paket + Upgrade)</span>' : '' ?></small>
                </div>
            </div>
            <div class="col-md-8">
                <?php if ($is_lunas): ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border border-success border-opacity-25">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div class="d-flex align-items-center">
                            <span class="rounded-circle bg-success bg-opacity-25 p-3 me-3"><i class="bi bi-check2-circle text-success fs-4"></i></span>
                            <div>
                                <h5 class="fw-bold text-dark mb-1">Pembayaran Sudah Lunas</h5>
                                <p class="text-secondary small mb-0">Tidak dapat menambah cicilan. Cetak kwitansi lunas untuk arsip.</p>
                            </div>
                        </div>
                        <a href="<?= base_url('agency/receipt/'.$participant['id']) ?>" target="_blank" class="btn btn-success rounded-pill px-4 fw-bold">
                            <i class="bi bi-printer-fill me-2"></i> Cetak Kwitansi Lunas
                        </a>
                    </div>
                </div>
                <?php else: ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <h5 class="fw-bold text-dark mb-3">Lapor Cicilan Baru</h5>
                    <form action="<?= base_url('agency/store-payment') ?>" method="post" enctype="multipart/form-data" class="row g-3" id="formCicilan">
                        <?= csrf_field() ?>
                        <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Nominal Bayar</label>
                            <div class="input-group" id="amount_input_group">
                                <span class="input-group-text bg-light border-0">Rp</span>
                                <input type="text" name="amount_display" id="amount_display" class="form-control bg-light border-0" placeholder="0" required autocomplete="off" aria-describedby="amount_validation_msg">
                                <input type="hidden" name="amount" id="amount" value="">
                            </div>
                            <small class="text-muted d-block">Format: 1.500.000. Maksimal: Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?> (sisa tagihan)</small>
                            <div id="amount_validation_msg" class="invalid-feedback" style="display: none;">Nominal tidak boleh melebihi sisa tagihan (Rp <?= number_format($sisa_tagihan, 0, ',', '.') ?>)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tanggal Bayar</label>
                            <input type="date" name="payment_date" class="form-control bg-light border-0" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Bukti Transfer (Image)</label>
                            <input type="file" name="proof" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label small fw-bold">Catatan (Optional)</label>
                            <input type="text" name="notes" class="form-control bg-light border-0" placeholder="cth: DP Awal / Cicilan 2">
                        </div>
                        <div class="col-12 mt-4 text-end">
                            <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold">
                                <i class="bi bi-cloud-upload me-2"></i> Unggah Laporan
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                <h5 class="fw-bold text-dark mb-0">Riwayat Pembayaran</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Tanggal</th>
                                <th>Nominal</th>
                                <th>Bukti</th>
                                <th>Status</th>
                                <th>Catatan</th>
                                <th class="text-end pe-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($installments)): ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-secondary italic">Belum ada riwayat cicilan</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($installments as $ins): ?>
                                <tr>
                                    <td class="ps-4"><?= date('d M Y', strtotime($ins['payment_date'])) ?></td>
                                    <td class="fw-bold">Rp <?= number_format((float)$ins['amount'], 0, ',', '.') ?></td>
                                    <td>
                                        <?php if(!empty($ins['proof'])): ?>
                                        <a href="<?= base_url($ins['proof']) ?>" target="_blank" class="text-decoration-none small fw-bold">
                                            <i class="bi bi-image me-1"></i> Lihat Bukti
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if($ins['status'] == 'pending'): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1 rounded-pill small">Proses Verifikasi</span>
                                        <?php elseif($ins['status'] == 'verified'): ?>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill small">Terverifikasi</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill small">Ditolak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-secondary small"><?= esc($ins['notes']) ?></td>
                                    <td class="text-end pe-4">
                                        <?php if($ins['status'] == 'verified'): ?>
                                        <a href="<?= base_url('agency/transaction-receipt/'.$ins['id']) ?>" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3" title="Cetak Kwitansi">
                                            <i class="bi bi-printer me-1"></i> Cetak Kwitansi
                                        </a>
                                        <?php else: ?>
                                        <span class="text-muted small">—</span>
                                        <?php endif; ?>
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

<script>
(function() {
    var form = document.getElementById('formCicilan');
    var display = document.getElementById('amount_display');
    if (!form || !display) return; // Form tidak ada saat pembayaran sudah lunas

    var hidden = document.getElementById('amount');
    var msgEl = document.getElementById('amount_validation_msg');

    var sisaTagihan = <?= (int) $sisa_tagihan ?>;   // angka tanpa desimal (rupiah penuh)
    var totalTagihan = <?= (int) $total_tagihan ?>;

    function formatRupiah(n) {
        var u = parseInt(n, 10) || 0;
        return u.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function parseRupiah(s) {
        return (s || '').replace(/\D/g, '');
    }

    function checkAmountValid() {
        var raw = parseRupiah(display.value);
        var num = parseInt(raw, 10) || 0;
        var exceeds = num > sisaTagihan;
        var exceedsTotal = num > totalTagihan;
        if (num > 0 && (exceeds || exceedsTotal)) {
            display.classList.add('is-invalid');
            if (msgEl) {
                msgEl.style.display = 'block';
                msgEl.textContent = 'Nominal tidak boleh melebihi sisa tagihan (Rp ' + formatRupiah(sisaTagihan) + ').';
            }
            return false;
        } else {
            display.classList.remove('is-invalid');
            if (msgEl) msgEl.style.display = 'none';
            return true;
        }
    }

    display.addEventListener('input', function() {
        var raw = parseRupiah(this.value);
        hidden.value = raw;
        var cursor = this.selectionStart;
        var prevLen = this.value.length;
        this.value = raw ? formatRupiah(raw) : '';
        var newLen = this.value.length;
        var newCursor = Math.max(0, cursor + (newLen - prevLen));
        this.setSelectionRange(newCursor, newCursor);
        checkAmountValid();
    });

    display.addEventListener('blur', function() {
        if (hidden.value) this.value = formatRupiah(hidden.value);
        checkAmountValid();
    });

    form.addEventListener('submit', function(e) {
        var raw = parseRupiah(display.value);
        hidden.value = raw;
        var num = parseInt(raw, 10) || 0;
        if (!raw || num < 1) {
            e.preventDefault();
            alert('Nominal bayar harus diisi dan lebih dari 0.');
            display.focus();
            return false;
        }
        if (num > sisaTagihan || num > totalTagihan) {
            e.preventDefault();
            display.classList.add('is-invalid');
            if (msgEl) {
                msgEl.style.display = 'block';
                msgEl.textContent = 'Nominal tidak boleh melebihi sisa tagihan (Rp ' + formatRupiah(sisaTagihan) + ').';
            }
            alert('Nominal tidak boleh melebihi sisa tagihan (Rp ' + formatRupiah(sisaTagihan) + ').');
            display.focus();
            return false;
        }
    });
})();
</script>
<?= $this->endSection() ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'Kwitansi Pembayaran') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f0f0f0; color: #1a1a1a; padding: 20px; }
        .receipt-container {
            max-width: 560px;
            margin: 0 auto;
            background: #fff;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 16px;
            overflow: hidden;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-25deg);
            pointer-events: none;
            z-index: 0;
        }
        .watermark img { max-width: 480px; max-height: 480px; opacity: 0.08; }
        .watermark-text { font-size: 5.5rem; font-weight: 800; color: #1e293b; opacity: 0.06; white-space: nowrap; }
        .receipt-container .kop, .receipt-container .receipt-body { position: relative; z-index: 1; }
        .kop {
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
            border-bottom: 2px solid #e2e8f0;
            padding: 24px 40px;
            text-align: center;
        }
        .kop-logo { max-height: 64px; max-width: 180px; object-fit: contain; margin-bottom: 12px; }
        .kop-nama-pt { font-weight: 800; font-size: 1.15rem; color: #1e293b; margin: 0 0 6px 0; letter-spacing: -0.02em; }
        .kop-alamat { font-size: 0.8rem; color: #64748b; margin: 0; line-height: 1.4; max-width: 360px; margin-left: auto; margin-right: auto; }
        .receipt-body { padding: 32px 40px 40px; }
        .receipt-title { font-weight: 800; color: #ef3338; font-size: 1.15rem; margin-bottom: 24px; border-bottom: 2px solid #f0f0f0; padding-bottom: 12px; }
        .label { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px; }
        .value { font-weight: 700; color: #1e293b; }
        .amount-box { background: #f8fafc; border-radius: 12px; padding: 24px; margin: 24px 0; text-align: center; }
        .amount-box .value { font-size: 1.5rem; color: #0d6efd; }
        .signature-block { margin-top: 32px; padding-top: 24px; border-top: 1px dashed #e2e8f0; text-align: center; }
        .signature-block .ttd-label { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 4px; }
        .signature-block .ttd-pt { font-size: 0.85rem; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .signature-block .qr-ttd { margin: 12px 0; }
        .signature-block .qr-ttd img { display: block; margin: 0 auto 4px; }
        .signature-block .qr-ttd-caption { font-size: 0.7rem; color: #64748b; margin-bottom: 16px; }
        .signature-block .nama-pemilik-label { font-size: 0.7rem; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 2px; }
        .signature-block .nama-pemilik-value { font-weight: 700; font-size: 1.05rem; color: #1e293b; }
        .no-print { margin-top: 24px; }
        @media print {
            body { background: #fff; padding: 0; }
            .receipt-container { box-shadow: none; border: 1px solid #e2e8f0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="receipt-container print-sheet">
    <div class="watermark">
        <?php if (!empty($company_logo_url)): ?>
            <img src="<?= esc($company_logo_url) ?>" alt="">
        <?php elseif (!empty($nama_pt)): ?>
            <div class="watermark-text"><?= esc($nama_pt) ?></div>
        <?php else: ?>
            <div class="watermark-text">Kwitansi</div>
        <?php endif; ?>
    </div>
    <div class="kop">
        <img src="<?= esc($company_logo_url) ?>" alt="Logo" class="kop-logo">
        <?php if (!empty($nama_pt)): ?>
            <p class="kop-nama-pt"><?= esc($nama_pt) ?></p>
        <?php else: ?>
            <p class="kop-nama-pt text-muted">Nama PT diatur di Pengaturan Akun</p>
        <?php endif; ?>
        <?php if (!empty($alamat_pt)): ?>
            <p class="kop-alamat"><?= nl2br(esc($alamat_pt)) ?></p>
        <?php endif; ?>
    </div>
    <div class="receipt-body">
        <div class="receipt-title">Kwitansi Pembayaran</div>

        <div class="row g-2 mb-2">
            <div class="col-4"><span class="label">Jamaah</span></div>
            <div class="col-8"><span class="value"><?= esc($participant['name']) ?></span></div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-4"><span class="label">NIK</span></div>
            <div class="col-8"><span class="value"><?= esc($participant['nik'] ?? '—') ?></span></div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-4"><span class="label">Paket</span></div>
            <div class="col-8"><span class="value"><?= esc($participant['package_name']) ?></span></div>
        </div>
        <div class="row g-2 mb-2">
            <div class="col-4"><span class="label">Agency</span></div>
            <div class="col-8"><span class="value"><?= esc($participant['agency_name']) ?></span></div>
        </div>

        <div class="amount-box">
            <div class="label">Nominal Pembayaran</div>
            <div class="value">Rp <?= number_format($payment['amount'], 0, ',', '.') ?></div>
            <div class="small text-muted mt-2"><?= esc($payment['notes'] ?: 'Pembayaran angsuran') ?></div>
        </div>

        <div class="row g-2">
            <div class="col-4"><span class="label">Tanggal Bayar</span></div>
            <div class="col-8"><span class="value"><?= date('d F Y', strtotime($payment['payment_date'])) ?></span></div>
        </div>

        <div class="signature-block">
            <div class="ttd-label">Manager Keuangan</div>
            <?php if (!empty($nama_pt)): ?>
                <div class="ttd-pt"><?= esc($nama_pt) ?></div>
            <?php endif; ?>
            <div class="qr-ttd">
                <img src="<?= esc($qr_url) ?>" alt="Tanda Tangan Digital" width="120" height="120">
                <div class="qr-ttd-caption">Tanda Tangan Digital — <?= esc($tanggal_signature ?? '') ?></div>
            </div>
            <div class="nama-pemilik-label">Manager Keuangan</div>
            <div class="nama-pemilik-value"><?= esc($nama_penandatangan ?? $nama_direktur ?? '—') ?></div>
        </div>
    </div>
</div>

<div class="no-print mt-4 mb-4 p-4 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="btnDownloadPdf"><i class="bi bi-file-pdf me-2"></i>Download PDF</button>
        <a href="#" class="btn btn-success rounded-pill px-4 text-white" id="btnShareWa" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>Share WA</a>
        <a href="<?= base_url('owner/participant/kelola/' . (int)($participant['id'] ?? 0)) ?>" class="btn btn-light rounded-pill px-4 border"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <a href="javascript:window.close()" class="btn btn-outline-secondary rounded-pill px-4">Tutup</a>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
(function() {
    var btnPdf = document.getElementById('btnDownloadPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function() {
            var el = document.querySelector('.print-sheet');
            if (!el) { el = document.body; }
            var name = <?= json_encode(isset($participant['name']) ? preg_replace('/[^a-z0-9 _-]/i', '-', $participant['name']) : 'kwitansi') ?>;
            var opt = {
                margin: [4, 6, 6, 6],
                filename: 'Kwitansi-' + name + '-<?= date('Y-m-d', strtotime($payment['payment_date'])) ?>.pdf',
                image: { type: 'jpeg', quality: 0.96 },
                html2canvas: {
                    scale: 2,
                    useCORS: true,
                    logging: false,
                    scrollY: 0,
                    scrollX: 0,
                    windowHeight: el.scrollHeight,
                    height: el.scrollHeight,
                    onclone: function(clonedDoc, elClone) {
                        var wrap = clonedDoc.querySelector('.print-sheet');
                        if (wrap) wrap.style.minHeight = 'auto';
                    }
                },
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait', hotfixes: ['px_scaling'] }
            };
            html2pdf().set(opt).from(el).save();
        });
    }
    var btnWa = document.getElementById('btnShareWa');
    if (btnWa) {
        var title = <?= json_encode($title ?? 'Kwitansi Pembayaran') ?>;
        var url = window.location.href;
        btnWa.href = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
    }
})();
</script>
</body>
</html>

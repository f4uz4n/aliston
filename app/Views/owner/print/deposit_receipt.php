<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Setoran Tabungan — <?= esc($deposit['saving_name']) ?></title>
    <?= view('partials/favicon_links') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: A4; margin: 20mm; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        body { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 11pt; color: #1e293b; background: #fff; padding: 20px; }
        .print-sheet { max-width: 210mm; margin: 0 auto; padding: 0; }
        .kop {
            text-align: center;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .kop-logo { max-height: 50px; max-width: 150px; object-fit: contain; margin-bottom: 5px; }
        .kop-nama { font-weight: 800; font-size: 1.1rem; color: #1e293b; margin: 0 0 3px 0; letter-spacing: -0.02em; }
        .kop-alamat { font-size: 0.75rem; color: #64748b; margin: 0; line-height: 1.4; }
        .doc-title { font-weight: 800; font-size: 1rem; text-align: center; margin-bottom: 25px; color: #0f172a; text-transform: uppercase; }
        .receipt-info { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 8px 0; vertical-align: top; font-size: 11pt; }
        .info-table td:first-child { width: 180px; color: #64748b; font-weight: 500; }
        .info-table td:last-child { color: #1e293b; font-weight: 600; }
        .amount-box { background: #fff; border: 2px solid #1e293b; padding: 20px; text-align: center; margin: 25px 0; border-radius: 8px; }
        .amount-box .label { font-size: 10pt; color: #64748b; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
        .amount-box .value { font-size: 1.5rem; font-weight: 800; color: #0d6efd; }
        .signature-block { margin-top: 40px; display: flex; justify-content: space-between; }
        .signature-left, .signature-right { width: 48%; }
        .signature-block .ttd-label { font-size: 9pt; font-weight: 700; color: #64748b; margin-bottom: 60px; }
        .signature-block .ttd-company { font-size: 9pt; font-weight: 600; color: #1e293b; margin-bottom: 5px; }
        .signature-block .nama-pemilik { font-weight: 700; font-size: 10pt; color: #1e293b; margin-top: 5px; }
        .no-print { margin-top: 20px; text-align: center; }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="no-print mt-4 mb-4 p-4 rounded-3 text-center" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="btnDownloadPdf"><i class="bi bi-file-pdf me-2"></i>Download PDF</button>
        <a href="#" class="btn btn-success rounded-pill px-4 text-white" id="btnShareWa" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>Share WA</a>
        <a href="<?= base_url('owner/print-documents') ?>" class="btn btn-light rounded-pill px-4 border"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <a href="javascript:window.close()" class="btn btn-outline-secondary rounded-pill px-4">Tutup</a>
    </div>
</div>

<div class="print-sheet">
    <!-- Kop -->
    <div class="kop">
        <?php if (!empty($company_logo_url)): ?>
            <img src="<?= esc($company_logo_url) ?>" alt="Logo" class="kop-logo">
        <?php endif; ?>
        <p class="kop-nama"><?= esc($company_name) ?></p>
        <?php if (!empty($company_address)): ?>
            <p class="kop-alamat"><?= nl2br(esc($company_address)) ?></p>
        <?php endif; ?>
    </div>

    <p class="doc-title">Bukti Setoran Tabungan Perjalanan</p>

    <div class="receipt-info">
        <table class="info-table">
            <tr>
                <td>No. Setoran</td>
                <td>: #<?= str_pad($deposit['id'], 6, '0', STR_PAD_LEFT) ?></td>
            </tr>
            <tr>
                <td>Tanggal Setoran</td>
                <td>: <?= !empty($deposit['payment_date']) ? date('d F Y', strtotime($deposit['payment_date'])) : date('d F Y', strtotime($deposit['created_at'])) ?></td>
            </tr>
            <tr>
                <td>Nama Jamaah</td>
                <td>: <strong><?= esc($deposit['saving_name']) ?></strong></td>
            </tr>
            <tr>
                <td>NIK</td>
                <td>: <?= esc($deposit['saving_nik'] ?? '—') ?></td>
            </tr>
            <tr>
                <td>No. Telepon</td>
                <td>: <?= esc($deposit['saving_phone'] ?? '—') ?></td>
            </tr>
            <tr>
                <td>Agensi</td>
                <td>: <?= esc($deposit['agency_name']) ?></td>
            </tr>
            <?php if (!empty($deposit['nomor_rekening']) || !empty($deposit['nama_bank'])): ?>
            <tr>
                <td>Rekening Agency</td>
                <td>: 
                    <?php if (!empty($deposit['nomor_rekening'])): ?>
                        <?= esc($deposit['nomor_rekening']) ?>
                    <?php endif; ?>
                    <?php if (!empty($deposit['nomor_rekening']) && !empty($deposit['nama_bank'])): ?>
                        — 
                    <?php endif; ?>
                    <?php if (!empty($deposit['nama_bank'])): ?>
                        <?= esc($deposit['nama_bank']) ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <tr>
                <td>Status</td>
                <td>: <span style="color: #198754; font-weight: 700;"><?= ucfirst($deposit['status']) ?></span></td>
            </tr>
        </table>
    </div>

    <div class="amount-box">
        <div class="label">Nominal Setoran</div>
        <div class="value">Rp <?= number_format($deposit['amount'], 0, ',', '.') ?></div>
    </div>

    <?php if ($total_balance > 0): ?>
    <div style="text-align: center; margin: 20px 0; padding: 15px; background: #f0f9ff; border-radius: 8px;">
        <p style="margin: 0; font-size: 10pt; color: #64748b;">Total Saldo Tabungan Saat Ini</p>
        <p style="margin: 5px 0 0 0; font-size: 1.2rem; font-weight: 700; color: #0d6efd;">Rp <?= number_format($total_balance, 0, ',', '.') ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($deposit['notes'])): ?>
    <div style="margin-top: 20px; padding: 15px; background: #fff7ed; border-left: 4px solid #f59e0b; border-radius: 4px;">
        <p style="margin: 0; font-size: 10pt; color: #92400e;"><strong>Catatan:</strong> <?= esc($deposit['notes']) ?></p>
    </div>
    <?php endif; ?>

    <div class="signature-block">
        <div class="signature-left">
            <p class="ttd-label">Penyetor,</p>
            <p class="nama-pemilik"><?= esc($deposit['saving_name']) ?></p>
        </div>
        <div class="signature-right">
            <p class="ttd-label">Penerima,</p>
            <?php if (!empty($company_name)): ?>
                <p class="ttd-company"><?= esc($company_name) ?></p>
            <?php endif; ?>
            <p class="nama-pemilik"><?= esc($nama_direktur) ?></p>
        </div>
    </div>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
(function() {
    var btnPdf = document.getElementById('btnDownloadPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function() {
            var el = document.querySelector('.print-sheet');
            if (!el) { el = document.body; }
            var name = <?= json_encode(isset($deposit['saving_name']) ? preg_replace('/[^a-z0-9 _-]/i', '-', $deposit['saving_name']) : 'bukti-setoran') ?>;
            var opt = {
                margin: [4, 6, 6, 6],
                filename: 'Bukti-Setoran-Tabungan-' + name + '.pdf',
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
                jsPDF: { unit: 'mm', format: 'legal', orientation: 'portrait', hotfixes: ['px_scaling'] }
            };
            html2pdf().set(opt).from(el).save();
        });
    }
    var btnWa = document.getElementById('btnShareWa');
    if (btnWa) {
        var title = <?= json_encode('Bukti Setoran Tabungan - ' . ($deposit['saving_name'] ?? 'Setoran')) ?>;
        var url = window.location.href;
        btnWa.href = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
    }
})();
</script>
<?= view('partials/developer_footer_print') ?>
</body>
</html>

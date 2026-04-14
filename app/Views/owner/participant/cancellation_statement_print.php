<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Pernyataan Pembatalan — <?= esc($participant['name']) ?></title>
    <?= view('partials/favicon_links') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 20mm; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } .no-print { display: none !important; } }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #1e293b; background: #fff; padding: 20px; }
        .print-sheet { max-width: 210mm; margin: 0 auto; }
        /* Kop sama dengan surat izin */
        .kop-wrap { margin-bottom: 18px; }
        .kop { display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: 10px; }
        .kop-kiri { flex: 0 0 auto; text-align: left; }
        .kop-kiri .logo { max-height: 64px; max-width: 150px; object-fit: contain; margin-bottom: 10px; display: block; }
        .kop-kiri .perijinan { font-size: 10pt; color: #1f2937; line-height: 1.6; font-weight: 400; }
        .kop-kanan { flex: 1; text-align: center; padding-left: 24px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; }
        .kop-kanan .nama-pt { font-size: 20pt; font-weight: 700; color: #dc2626; margin: 0 0 6px 0; letter-spacing: 0.03em; line-height: 1.2; text-transform: uppercase; }
        .kop-kanan .slogan { font-size: 14pt; font-weight: 700; color: #0d9488; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.02em; }
        .kop-kanan .kontak { font-size: 10pt; color: #1f2937; margin: 0; line-height: 1.6; }
        .kop-kanan .kontak-alamat { margin-bottom: 2px; }
        .kop-border { display: flex; flex-direction: column; }
        .kop-border .line-red { height: 2px; background: #dc2626; }
        .kop-border .line-green { height: 4px; background: #059669; margin-top: 0; }
        .ttd-block .cabang { font-weight: 600; color: #1e293b; font-size: 11pt; }
        .content { line-height: 1.8; text-align: justify; }
        .data-box { margin: 16px 0; padding: 12px 16px; background: #f8fafc; border-radius: 8px; border: 1px solid #e2e8f0; }
        .data-box .row-item { margin-bottom: 6px; }
        .ttd-block { margin-top: 50px; text-align: right; }
        .ttd-block .tanggal { margin-bottom: 8px; }
        .ttd-block .rongga-ttd { min-height: 100px; margin-bottom: 8px; }
        .ttd-block .nama-pemilik { font-weight: 700; font-size: 12pt; }
    </style>
</head>
<body>
<div class="print-sheet">
    <!-- Kop surat (sama dengan cetak surat izin) -->
    <div class="kop-wrap">
        <div class="kop">
            <div class="kop-kiri">
                <?php if (!empty($company_logo_url)): ?>
                    <img src="<?= esc($company_logo_url) ?>" alt="Logo" class="logo">
                <?php endif; ?>
                <?php if (!empty($no_sk_perijinan) || !empty($tanggal_sk_perijinan)): ?>
                    <div class="perijinan">
                        <?php if (!empty($no_sk_perijinan)): ?><?= esc($no_sk_perijinan) ?><?php endif; ?>
                        <?php if (!empty($tanggal_sk_perijinan)): ?><?= !empty($no_sk_perijinan) ? '<br>' : '' ?><?= esc($tanggal_sk_perijinan) ?><?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="kop-kanan">
                <p class="nama-pt mb-0"><?= esc($company_name) ?></p>
                <?php if (!empty($company_slogan)): ?>
                    <p class="slogan mb-0"><?= esc($company_slogan) ?></p>
                <?php endif; ?>
                <div class="kontak">
                    <?php if (!empty($company_address)): ?>
                        <p class="kontak-alamat mb-0"><?= esc($company_address) ?></p>
                    <?php endif; ?>
                    <p class="mb-0"><?php if (!empty($company_email)): ?>Email: <?= esc($company_email) ?><?php endif; ?><?php if (!empty($company_email) && !empty($company_phone)): ?> | <?php endif; ?><?php if (!empty($company_phone)): ?>Telp: <?= esc($company_phone) ?><?php endif; ?></p>
                </div>
            </div>
        </div>
        <div class="kop-border">
            <div class="line-red"></div>
            <div class="line-green"></div>
        </div>
    </div>

    <h5 class="text-center fw-bold mb-4">SURAT PERNYATAAN PEMBATALAN & REFUND</h5>

    <div class="content">
        <p>Yang bertanda tangan di bawah ini, <?= esc($company_name) ?>, menyatakan dengan sesungguhnya bahwa:</p>

        <div class="data-box">
            <div class="row-item"><strong>Nama Jamaah</strong>: <?= esc($participant['name']) ?></div>
            <div class="row-item"><strong>NIK</strong>: <?= esc($participant['nik'] ?? '—') ?></div>
            <div class="row-item"><strong>Paket</strong>: <?= esc($participant['package_name'] ?? '—') ?></div>
            <div class="row-item"><strong>Total yang pernah dibayar</strong>: Rp <?= number_format($participant['total_paid'] ?? 0, 0, ',', '.') ?></div>
            <div class="row-item"><strong>Nominal refund yang telah ditransfer</strong>: Rp <?= number_format((float)($participant['refund_amount'] ?? 0), 0, ',', '.') ?></div>
            <div class="row-item"><strong>Rekening tujuan transfer</strong>: <?= esc($participant['refund_rekening'] ?? '—') ?><?php if (!empty($participant['refund_bank_name'])): ?> (<?= esc($participant['refund_bank_name']) ?>)<?php endif; ?></div>
            <?php if (!empty($participant['cancelled_at'])): ?>
            <div class="row-item"><strong>Tanggal pembatalan</strong>: <?= date('d F Y', strtotime($participant['cancelled_at'])) ?></div>
            <?php endif; ?>
        </div>

        <p>Dana refund tersebut telah ditransfer ke rekening di atas. Surat pernyataan ini dibuat untuk keperluan administrasi dan dapat digunakan sebagai bukti oleh jamaah yang bersangkutan.</p>
    </div>

    <div class="ttd-block">
        <p class="tanggal mb-1"><?= date('d F Y') ?></p>
        <?php if (!empty($company_name)): ?>
        <p class="cabang mb-2"><?= esc($company_name) ?></p>
        <?php endif; ?>
        <div class="rongga-ttd"></div>
        <p class="nama-pemilik mb-0"><?= esc($nama_pemilik ?? '') ?></p>
    </div>
</div>
<div class="no-print text-center mt-4">
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="window.print()">Cetak</button>
    <a href="javascript:window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-4 ms-2">Tutup</a>
</div>
<?= view('partials/developer_footer_print') ?>
</body>
</html>

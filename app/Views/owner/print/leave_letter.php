<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permohonan Ijin Cuti — <?= esc($participant['name']) ?></title>
    <?= view('partials/favicon_links') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: 210mm 330mm; margin: 20mm; } /* F4 / Legal */
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #1e293b; background: #fff; padding: 20px; }
        .print-sheet { max-width: 210mm; margin: 0 auto; }
        /* Kop persis gambar: kiri logo + izin; kanan nama PT/slogan/alamat center; garis dua warna */
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
        /* Header surat */
        .header-surat { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 11pt; }
        .header-kiri { }
        .header-kanan { text-align: right; }
        /* Tujuan & isi */
        .content { line-height: 1.7; text-align: justify; }
        .content p { margin-bottom: 10px; }
        .kepada-block { line-height: 1; margin-bottom: 14px; }
        .kepada-block .kepada-yth { margin-bottom: 2px; }
        .tujuan-surat { margin: 0; white-space: pre-line; line-height: 1; }
        .bold-dates { font-weight: 700; }
        .salam-bold { font-weight: 700; }
        .data-jamaah { margin: 12px 0; padding-left: 30px; }
        .data-jamaah table { border-collapse: collapse; width: auto; min-width: 320px; }
        .data-jamaah td { padding: 2px 8px 2px 0; vertical-align: top; font-size: 12pt; }
        .data-jamaah td:first-child { width: 1px; white-space: nowrap; font-weight: 700; }
        .data-jamaah .label-cell { padding-right: 4px; }
        .data-jamaah .value-cell { padding-left: 2px; }
        .ttd-block { margin-top: 36px; text-align: right; }
        .ttd-block .hormat { font-size: 11pt; margin-bottom: 4px; }
        .ttd-block .nama-pt { font-weight: 700; font-size: 11pt; color: #1e293b; margin-bottom: 8px; }
        .ttd-block .rongga-ttd { min-height: 72px; margin-bottom: 4px; }
        .ttd-block .nama-direktur { font-size: 10pt; }
        .no-print { margin-top: 20px; text-align: center; }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="print-sheet">
    <!-- Kop persis gambar: kiri logo + izin; kanan center; garis merah + hijau -->
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
                <p class="nama-pt"><?= esc($company_name) ?></p>
                <?php if (!empty($company_slogan)): ?>
                    <p class="slogan"><?= esc($company_slogan) ?></p>
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

    <!-- Nomor, Lamp, Perihal, Tanggal -->
    <div class="header-surat">
        <div class="header-kiri">
            <div>Nomor &nbsp;&nbsp;: <?= esc($nomor_surat ?: '—') ?></div>
            <div>Lamp &nbsp;&nbsp;: —</div>
            <div>Perihal : <strong><?= esc($perihal ?: 'Permohonan Ijin Cuti') ?></strong></div>
        </div>
        <div class="header-kanan">
            <?php
            $kota = 'Kediri';
            if (!empty($company_address) && preg_match('/\b([A-Za-z]+)\s*$/u', trim($company_address), $m)) {
                $kota = $m[1];
            }
            ?>
            <?= esc($kota) ?>, <?= date('d-M-y') ?>
        </div>
    </div>

    <div class="content">
        <div class="kepada-block">
            <p class="kepada-yth mb-0">Kepada Yth.,</p>
            <div class="tujuan-surat"><?= $tujuan_surat ? nl2br(esc($tujuan_surat)) : 'Pimpinan Instansi<br>Di Tempat' ?></div>
        </div>

        <p class="salam-bold">Assalamu'alaikum Warahmatullahi Wabarokatuh.</p>

        <p>Salam sejahtera kami haturkan kepada Bapak/Ibu pimpinan, semoga dalam menjalankan tugas dan aktifitas sehari-hari senantiasa diberikan kesehatan dan kekuatan oleh Allah SWT. Amiin.</p>

        <p>Sehubungan akan diberangkatkan Jamaah Umroh <?= esc($company_name) ?> ke Tanah Suci yang Insya Allah akan dilaksanakan pada tanggal <span class="bold-dates"><?= date('d F Y', $departure_date) ?></span> sampai dengan <span class="bold-dates"><?= date('d F Y', $return_date) ?></span> maka dengan ini kami mengajukan permohonan izin atas:</p>

        <div class="data-jamaah">
            <table>
                <tr>
                    <td class="label-cell">Nama</td>
                    <td class="value-cell">: <?= esc(strtoupper($participant['name'])) ?></td>
                </tr>
                <tr>
                    <td class="label-cell"><?= esc($label_program_studi) ?></td>
                    <td class="value-cell">: <?= esc($isi_program_studi ?: '—') ?></td>
                </tr>
                <tr>
                    <td class="label-cell"><?= esc($label_fakultas) ?></td>
                    <td class="value-cell">: <?= esc($isi_fakultas ?: '—') ?></td>
                </tr>
            </table>
        </div>

        <p>Demikian surat permohonan izin ini kami sampaikan. Atas terkabulnya permohonan ini kami sampaikan terima kasih.</p>

        <p class="salam-bold">Wassalamu'alaikum Warahmatullahi Wabarokatuh.</p>

        <div class="ttd-block">
            <p class="hormat">Hormat kami,</p>
            <p class="nama-pt"><?= esc($company_name) ?></p>
            <div class="rongga-ttd"><!-- Rongga untuk tanda tangan basah --></div>
            <p class="nama-direktur">(<?= esc($nama_direktur) ?>)</p>
        </div>
    </div>
</div>

<div class="no-print">
    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i> Cetak Surat
    </button>
    <a href="<?= base_url('owner/print-documents') ?>" class="btn btn-light rounded-pill px-4 fw-bold border ms-2">Kembali</a>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<?= view('partials/developer_footer_print') ?>
</body>
</html>

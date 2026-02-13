<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Rekomendasi — <?= esc($nama) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: 210mm 330mm; margin: 20mm; } /* F4 / Legal */
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; color: #1e293b; background: #fff; padding: 20px; }
        .print-sheet { max-width: 210mm; margin: 0 auto; }
        .kop-wrap { margin-bottom: 18px; }
        .kop { display: flex; align-items: flex-start; justify-content: space-between; padding-bottom: 10px; }
        .kop-kiri { flex: 0 0 auto; text-align: left; }
        .kop-kiri .logo { max-height: 64px; max-width: 150px; object-fit: contain; margin-bottom: 10px; display: block; }
        .kop-kiri .perijinan { font-size: 10pt; color: #1f2937; line-height: 1.6; }
        .kop-kanan { flex: 1; text-align: center; padding-left: 24px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; }
        .kop-kanan .nama-pt { font-size: 20pt; font-weight: 700; color: #dc2626; margin: 0 0 6px 0; letter-spacing: 0.03em; line-height: 1.2; text-transform: uppercase; }
        .kop-kanan .slogan { font-size: 14pt; font-weight: 700; color: #0d9488; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: 0.02em; }
        .kop-kanan .kontak { font-size: 10pt; color: #1f2937; margin: 0; line-height: 1.6; }
        .kop-border { display: flex; flex-direction: column; }
        .kop-border .line-red { height: 2px; background: #dc2626; }
        .kop-border .line-green { height: 4px; background: #059669; margin-top: 0; }
        .header-surat { margin-bottom: 18px; font-size: 11pt; }
        .header-surat .baris { margin-bottom: 2px; }
        .content { line-height: 1.6; text-align: justify; }
        .content p { margin-bottom: 10px; }
        .kepada-block { line-height: 1; margin-bottom: 14px; }
        .kepada-block .kepada-yth { font-weight: 700; margin-bottom: 2px; }
        .kepada-block .tujuan-surat { font-weight: 700; white-space: pre-line; line-height: 1; text-transform: uppercase; margin: 0; }
        .salam-bold { font-weight: 700; }
        .data-jamaah { margin: 14px 0; padding-left: 30px; line-height: 1; }
        .data-jamaah table { border-collapse: collapse; width: auto; }
        .data-jamaah td { vertical-align: top; padding: 2px 8px 2px 0; font-size: 12pt; }
        .data-jamaah td:first-child { white-space: nowrap; width: 1px; font-weight: 700; }
        .data-jamaah .nilai { font-weight: 400; }
        .list-jaminan { margin: 10px 0 10px 20px; padding-left: 10px; }
        .list-jaminan li { margin-bottom: 6px; }
        .ttd-block { margin-top: 36px; text-align: right; }
        .ttd-block .tanggal { margin-bottom: 8px; }
        .ttd-block .nama-pt { font-weight: 700; font-size: 11pt; color: #1e293b; margin-bottom: 6px; }
        .ttd-block .rongga-ttd { min-height: 72px; margin-bottom: 4px; }
        .ttd-block .nama-direktur { font-size: 10pt; font-weight: 700; text-decoration: underline; }
        .ttd-block .jabatan { font-size: 10pt; margin-top: 2px; }
        .no-print { margin-top: 20px; text-align: center; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
    </style>
</head>
<body>
<div class="print-sheet">
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
                        <p class="mb-0"><?= esc($company_address) ?></p>
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

    <div class="header-surat">
        <div class="baris">Nomor &nbsp;&nbsp;: <?= esc($nomor_surat ?: '—') ?></div>
        <div class="baris">Sifat &nbsp;&nbsp;&nbsp;: <?= esc($sifat ?: '—') ?></div>
        <div class="baris">Lamp &nbsp;&nbsp;: <?= esc($lamp ?: '-') ?></div>
        <div class="baris">Perihal : <strong><?= esc($perihal ?: 'Surat Rekomendasi Penerbitan Paspor Umrah') ?></strong></div>
    </div>

    <div class="content">
        <div class="kepada-block">
            <p class="kepada-yth mb-0">Kepada Yth.,</p>
            <div class="tujuan-surat"><?= $tujuan_surat ? nl2br(esc($tujuan_surat)) : 'KEPALA KANTOR IMIGRASI KELAS II NON TPI KEDIRI' . "\n" . 'Di tempat' ?></div>
        </div>

        <p class="salam-bold">Assalamu'alaikum Wr. Wb</p>

        <p>Kami selaku kepala penyelenggara perjalanan ibadah umrah dan haji khusus <?= esc($company_name) ?> menerangkan bahwa:</p>

        <?php
        $tempatTglLahir = $tempat_lahir ? trim($tempat_lahir) . ', ' . ($tgl_lahir ? date('d-m-Y', strtotime($tgl_lahir)) : '—') : ($tgl_lahir ? date('d-m-Y', strtotime($tgl_lahir)) : '—');
        ?>
        <div class="data-jamaah">
            <table>
                <tr><td>Nama</td><td class="nilai">: <?= esc(strtoupper($nama)) ?></td></tr>
                <tr><td>Nama Ayah</td><td class="nilai">: <?= esc(strtoupper($nama_ayah ?: '—')) ?></td></tr>
                <tr><td>Tempat / Tgl Lahir</td><td class="nilai">: <?= esc($tempatTglLahir) ?></td></tr>
                <tr><td>Alamat</td><td class="nilai">: <?= esc($alamat ?: '—') ?></td></tr>
            </table>
        </div>

        <p>Dengan ini menerangkan bahwa nama tersebut di atas adalah benar jamaah umrah <?= esc($company_name) ?> yang akan melakukan ibadah umroh pada <strong><?= $tanggal_keberangkatan ? date('d F Y', strtotime($tanggal_keberangkatan)) : '—' ?></strong>.</p>

        <p>Selama pelaksanaan ibadah umrah, jamaah tersebut sepenuhnya menjadi tanggung jawab kami mulai keberangkatan sampai kepulangannya.</p>

        <p>Kami menjamin sepenuhnya bahwa jamaah kami tersebut tidak akan :</p>
        <ul class="list-jaminan">
            <li>Melakukan pelanggaran peraturan keimigrasian berupa penyalahgunaan izin tinggal, dan tidak melebihi izin tinggalnya (overstay).</li>
            <li>Memalsukan atau membuat pasport palsu yang di berikan kepadanya, dan</li>
            <li>Bekerja secara illegal.</li>
        </ul>

        <p>Demikian permohonan ini disampaikan, atas segala perhatian dan kerjasamanya diucapkan terima kasih.</p>

        <div class="ttd-block">
            <p class="tanggal mb-0"><?php $kota = 'Kediri'; if (!empty($company_address) && preg_match('/\b([A-Za-z]+)\s*$/u', trim($company_address), $m)) { $kota = $m[1]; } ?><?= esc($kota) ?>, <?= date('d F Y') ?></p>
            <p class="nama-pt mb-0"><?= esc($company_name) ?></p>
            <div class="rongga-ttd"></div>
            <p class="nama-direktur mb-0"><?= esc($nama_direktur) ?></p>
            <p class="jabatan mb-0">Direktur Utama</p>
        </div>
    </div>
</div>

<div class="no-print">
    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold" onclick="window.print()">
        <i class="bi bi-printer-fill me-2"></i> Cetak Surat
    </button>
    <a href="<?= base_url('owner/print-documents') ?>" class="btn btn-light rounded-pill px-4 fw-bold border ms-2">Kembali</a>
</div>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</body>
</html>

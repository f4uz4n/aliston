<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <?= view('partials/favicon_links') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        @page { size: legal; margin: 10mm; }
        @media print { body { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
        body { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 9pt; color: #1e293b; background: #fff; }
        .print-sheet { max-width: 216mm; min-height: 356mm; margin: 0 auto; padding: 8mm; box-sizing: border-box; }
        .kop {
            text-align: center;
            border-bottom: 2px solid #1e293b;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .kop-logo { max-height: 44px; max-width: 140px; object-fit: contain; margin-bottom: 4px; }
        .kop-nama { font-weight: 800; font-size: 0.95rem; color: #1e293b; margin: 0 0 2px 0; letter-spacing: -0.02em; }
        .kop-alamat { font-size: 0.7rem; color: #64748b; margin: 0; line-height: 1.3; }
        .doc-title { font-weight: 800; font-size: 0.9rem; text-align: center; margin-bottom: 8px; color: #0f172a; }
        .section { margin-bottom: 8px; }
        .section-title { font-weight: 700; font-size: 9pt; color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; margin-bottom: 4px; }
        table.info-table { width: 100%; border-collapse: collapse; }
        table.info-table td { padding: 1px 4px 1px 0; vertical-align: top; font-size: 9pt; }
        table.info-table td:first-child { width: 115px; color: #64748b; }
        .hotel-list { list-style: none; padding: 0; margin: 0; }
        .hotel-list li { padding: 1px 0; font-size: 9pt; }
        .hotel-list li strong { color: #475569; font-size: 8.5pt; }
        .facility-list { margin: 0; padding-left: 14px; font-size: 8.5pt; }
        .facility-list li { margin-bottom: 0; }
        .checklist-table { width: 100%; border: 1px solid #e2e8f0; border-collapse: collapse; font-size: 8.5pt; }
        .checklist-table th, .checklist-table td { border: 1px solid #e2e8f0; padding: 3px 6px; }
        .checklist-table th { background: #f8fafc; font-weight: 700; color: #475569; }
        .checklist-table .col-no { width: 28px; text-align: center; }
        .checklist-table .col-status { width: 48px; text-align: center; }
        .check-box { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #334155; border-radius: 2px; }
        .check-box.checked { background: #1e293b; }
        .check-box.checked::after { content: '✓'; color: #fff; font-size: 9px; font-weight: bold; line-height: 14px; display: block; text-align: center; }
        .notes-list { margin: 0; padding-left: 14px; font-size: 8.5pt; }
        .notes-list li { margin-bottom: 1px; }
        .signature-block { margin-top: 10px; padding-top: 8px; border-top: 1px dashed #cbd5e1; text-align: center; }
        .signature-block .ttd-label { font-size: 7pt; text-transform: uppercase; font-weight: 700; color: #64748b; margin-bottom: 0; }
        .signature-block .ttd-company { font-size: 8pt; font-weight: 600; color: #1e293b; margin-bottom: 4px; }
        .signature-block .qr-ttd img { display: block; margin: 0 auto 2px; width: 80px; height: 80px; }
        .signature-block .qr-caption { font-size: 7pt; color: #64748b; margin-bottom: 4px; }
        .signature-block .nama-pemilik { font-weight: 700; font-size: 9pt; color: #1e293b; }
        .pay-table { font-size: 8.5pt; }
        .pay-table th, .pay-table td { padding: 2px 6px; }
        .footer-print { text-align: center; color: #94a3b8; font-size: 7.5pt; margin-top: 6px; padding-top: 4px; border-top: 1px solid #e2e8f0; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .print-sheet { padding: 0; min-height: auto; }
        }
    </style>
</head>
<body>
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

    <p class="doc-title">FORMULIR PENDAFTARAN JAMAAH</p>

    <!-- 1. Data Paket -->
    <div class="section">
        <div class="section-title">1. Data Paket Perjalanan</div>
        <table class="info-table">
            <tr><td>Nama Paket</td><td><strong><?= esc($participant['package_name']) ?></strong></td></tr>
            <tr><td>Tanggal Keberangkatan</td><td><?= !empty($participant['package_departure_date']) ? date('d F Y', strtotime($participant['package_departure_date'])) : '—' ?></td></tr>
            <tr><td>Harga Paket (per pax)</td><td>Rp <?= number_format($participant['package_price'] ?? 0, 0, ',', '.') ?></td></tr>
            <?php if (!empty($participant['upgrade_cost']) && (float)$participant['upgrade_cost'] > 0): ?>
            <tr><td>Biaya Upgrade</td><td>Rp <?= number_format($participant['upgrade_cost'], 0, ',', '.') ?></td></tr>
            <?php endif; ?>
            <tr><td>Total Target Pembayaran</td><td><strong>Rp <?= number_format($total_target, 0, ',', '.') ?></strong></td></tr>
        </table>
    </div>

    <!-- 2. Daftar Hotel -->
    <div class="section">
        <div class="section-title">2. Daftar Hotel</div>
        <?php if (!empty($hotels_package)): ?>
        <ul class="hotel-list">
            <?php foreach ($hotels_package as $label => $namaHotel): ?>
                <li><strong><?= esc($label) ?>:</strong> <?= esc($namaHotel) ?></li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p class="text-muted small mb-0">Informasi hotel mengikuti paket / belum diisi.</p>
        <?php endif; ?>
    </div>

    <!-- 3. Data Jamaah -->
    <div class="section">
        <div class="section-title">3. Data Jamaah</div>
        <table class="info-table">
            <tr><td>Nama Lengkap</td><td><strong><?= esc($participant['name']) ?></strong></td></tr>
            <tr><td>NIK</td><td><?= esc($participant['nik'] ?? '—') ?></td></tr>
            <tr><td>Tempat, Tanggal Lahir</td><td><?= esc($participant['place_of_birth'] ?? '—') ?>, <?= !empty($participant['date_of_birth']) ? date('d/m/Y', strtotime($participant['date_of_birth'])) : '—' ?></td></tr>
            <tr><td>Jenis Kelamin</td><td><?= esc($participant['gender'] ?? '—') ?></td></tr>
            <tr><td>Alamat</td><td><?= esc($participant['address'] ?? '—') ?></td></tr>
            <tr><td>Kec. / Kab. / Provinsi</td><td><?= esc($participant['kecamatan'] ?? '') ?> / <?= esc($participant['kabupaten'] ?? '') ?> / <?= esc($participant['provinsi'] ?? '') ?></td></tr>
            <tr><td>Telepon / WhatsApp</td><td><?= esc($participant['phone'] ?? '—') ?></td></tr>
            <tr><td>No. Paspor</td><td><?= esc($participant['passport_number'] ?? '—') ?></td></tr>
            <tr><td>Kontak Darurat</td><td><?= esc($participant['emergency_name'] ?? '—') ?> (<?= esc($participant['emergency_relationship'] ?? '') ?>) — <?= esc($participant['emergency_phone'] ?? '—') ?></td></tr>
            <tr><td>Agensi</td><td><?= esc($participant['agency_name'] ?? '—') ?></td></tr>
        </table>
    </div>

    <!-- 4. Ringkasan Pembayaran -->
    <div class="section">
        <div class="section-title">4. Ringkasan Pembayaran</div>
        <p class="mb-2 small">Total dibayar (verified): <strong>Rp <?= number_format($total_paid, 0, ',', '.') ?></strong> dari Rp <?= number_format($total_target, 0, ',', '.') ?></p>
        <?php if (!empty($payments)): ?>
        <table class="table table-sm table-bordered pay-table">
            <thead><tr><th class="col-no">No</th><th>Tanggal</th><th>Nominal</th><th>Keterangan</th></tr></thead>
            <tbody>
                <?php foreach ($payments as $i => $p): ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= date('d/m/Y', strtotime($p['payment_date'])) ?></td>
                    <td>Rp <?= number_format($p['amount'], 0, ',', '.') ?></td>
                    <td><?= esc($p['notes'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted small mb-0">Belum ada pembayaran tercatat.</p>
        <?php endif; ?>
    </div>

    <!-- 5. Fasilitas Jenis Paket -->
    <div class="section">
        <div class="section-title">5. Fasilitas Jenis Paket</div>
        <?php if (!empty($inclusions)): ?>
        <p class="small fw-bold mb-1">Inklusi:</p>
        <ul class="facility-list">
            <?php foreach ($inclusions as $item): ?><li><?= esc($item) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if (!empty($freebies)): ?>
        <p class="small fw-bold mb-1 mt-2">Fasilitas / Perlengkapan Paket:</p>
        <ul class="facility-list">
            <?php foreach ($freebies as $item): ?><li><?= esc($item) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if (!empty($exclusions)): ?>
        <p class="small fw-bold mb-1 mt-2">Belum Termasuk:</p>
        <ul class="facility-list">
            <?php foreach ($exclusions as $item): ?><li><?= esc($item) ?></li><?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <?php if (empty($inclusions) && empty($freebies) && empty($exclusions)): ?>
        <p class="text-muted small mb-0">Tidak ada daftar fasilitas.</p>
        <?php endif; ?>
    </div>

    <!-- 6. Checklist Pengambilan Atribut (berdasarkan master atribut aktif) -->
    <div class="section">
        <div class="section-title">6. Checklist Pengambilan Atribut</div>
        <?php if (!empty($master_attributes)): ?>
        <table class="checklist-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-item">Nama Atribut / Perlengkapan</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($master_attributes as $i => $attr): ?>
                <?php
                    $itemName = $attr['name'] ?? '';
                    $collected = isset($collected_map[$itemName]) && ($collected_map[$itemName]['status'] ?? '') === 'collected';
                    $tglAmbil = $collected && !empty($collected_map[$itemName]['collected_at']) ? date('d/m/Y', strtotime($collected_map[$itemName]['collected_at'])) : '';
                ?>
                <tr>
                    <td class="text-center"><?= $i + 1 ?></td>
                    <td><?= esc($itemName) ?></td>
                    <td class="text-center">
                        <span class="check-box <?= $collected ? 'checked' : '' ?>"></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="text-muted small mb-0">Tidak ada master atribut yang aktif. Atur di menu Master Atribut.</p>
        <?php endif; ?>
    </div>

    <!-- 7. Catatan untuk Jamaah -->
    <div class="section">
        <div class="section-title">7. Catatan yang Harus Diperhatikan Jamaah</div>
        <ul class="notes-list">
            <li>Pastikan data identitas (KTP/Paspor) dan dokumen perjalanan lengkap sebelum keberangkatan.</li>
            <li>Lunasi sisa pembayaran sesuai jadwal yang disepakati dengan agensi/kantor.</li>
            <li>Simpan bukti pembayaran dan kwitansi dengan baik.</li>
            <li>Periksa jadwal keberangkatan dan titik kumpul (boarding) yang akan diinformasikan kemudian.</li>
            <li>Ambil atribut/perlengkapan yang tercantum di checklist sesuai jadwal pengambilan dari panitia.</li>
            <li>Perubahan jadwal atau hotel/kamar hanya dapat dilakukan minimal H-30 sebelum keberangkatan.</li>
            <li>Pembatalan: H-30 refund penuh; di bawah H-30 dikenakan biaya pembatalan.</li>
            <li>Hubungi agensi atau kantor untuk konfirmasi terakhir sebelum berangkat.</li>
        </ul>
    </div>

    <!-- Tanda Tangan Digital -->
    <div class="signature-block">
        <div class="ttd-label">Pihak Perusahaan</div>
        <?php if (!empty($company_name)): ?>
            <div class="ttd-company"><?= esc($company_name) ?></div>
        <?php endif; ?>
        <div class="qr-ttd">
            <img src="<?= esc($qr_url) ?>" alt="Tanda Tangan Digital" width="100" height="100">
            <div class="qr-caption">Tanda Tangan Digital — <?= esc($tanggal_signature ?? '') ?></div>
        </div>
        <div class="ttd-label">Nama Pemilik / Direktur</div>
        <div class="nama-pemilik"><?= esc($nama_direktur) ?></div>
    </div>

    <div class="footer-print">
        Dokumen dicetak pada <?= date('d F Y, H:i') ?> — Formulir Pendaftaran Jamaah
    </div>
</div>

<div class="no-print mt-4 mb-4 p-4 rounded-3" style="background: #f8fafc; border: 1px solid #e2e8f0;">
    <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
        <button type="button" class="btn btn-primary rounded-pill px-4" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak</button>
        <button type="button" class="btn btn-danger rounded-pill px-4" id="btnDownloadPdf"><i class="bi bi-file-pdf me-2"></i>Download PDF</button>
        <a href="#" class="btn btn-success rounded-pill px-4 text-white" id="btnShareWa" target="_blank" rel="noopener"><i class="bi bi-whatsapp me-2"></i>Share WA</a>
        <a href="<?= esc($back_url ?? base_url('owner/participant')) ?>" class="btn btn-light rounded-pill px-4 border"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
        <a href="javascript:window.close()" class="btn btn-outline-secondary rounded-pill px-4">Tutup</a>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<script>
(function() {
    var btnPdf = document.getElementById('btnDownloadPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function() {
            var el = document.querySelector('.print-sheet');
            if (!el) { el = document.body; }
            var name = <?= json_encode(isset($participant['name']) ? preg_replace('/[^a-z0-9 _-]/i', '-', $participant['name']) : 'jamaah') ?>;
            var opt = {
                margin: [4, 6, 6, 6],
                filename: 'Formulir-Pendaftaran-' + name + '.pdf',
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
        var title = <?= json_encode($title ?? 'Formulir Pendaftaran Jamaah') ?>;
        var url = window.location.href;
        btnWa.href = 'https://wa.me/?text=' + encodeURIComponent(title + ' ' + url);
    }
})();
</script>
<?= view('partials/developer_footer_print') ?>
</body>
</html>

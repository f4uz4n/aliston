<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Jamaah Boarding</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4 landscape; margin: 12mm; }
        body { font-size: 9pt; }
        .kop { text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 6px; margin-bottom: 8px; }
        .kop-logo { max-height: 40px; }
        .kop-nama { font-weight: 700; font-size: 1rem; margin: 0; }
        .judul { font-weight: 700; text-align: center; margin-bottom: 10px; font-size: 11pt; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #dee2e6; padding: 4px 6px; }
        th { background: #f8f9fa; font-weight: 600; }
        .no-print { display: none; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
<div class="container-fluid py-3">
    <div class="kop">
        <?php if (!empty($company_logo_url)): ?>
            <img src="<?= esc($company_logo_url) ?>" alt="Logo" class="kop-logo">
        <?php endif; ?>
        <p class="kop-nama"><?= esc($company_name) ?></p>
    </div>
    <p class="judul">DAFTAR JAMAAH BOARDING</p>
    <?php if (!empty($departure_date_from) || !empty($departure_date_to)): ?>
    <p class="small text-muted mb-2">Filter tanggal: <?= $departure_date_from ? date('d/m/Y', strtotime($departure_date_from)) : '—' ?> s/d <?= $departure_date_to ? date('d/m/Y', strtotime($departure_date_to)) : '—' ?></p>
    <?php endif; ?>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th style="width:28px">No</th>
                <th>Tanggal Berangkat</th>
                <th>Maskapai</th>
                <th>Nama Jamaah</th>
                <th>NIK</th>
                <th>Agensi</th>
                <th>Paket</th>
                <th>Telepon</th>
                <th>Status Boarding</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($participants)): ?>
            <tr><td colspan="9" class="text-center py-4 text-muted">Tidak ada data.</td></tr>
            <?php else: ?>
            <?php foreach ($participants as $i => $p): ?>
            <tr>
                <td class="text-center"><?= $i + 1 ?></td>
                <td><?= esc($p['boarding_departure_label'] ?? '—') ?></td>
                <td><?= esc($p['airline'] ?? '—') ?></td>
                <td><?= esc($p['name']) ?></td>
                <td><?= esc($p['nik'] ?? '—') ?></td>
                <td><?= esc($p['agency_name'] ?? '—') ?></td>
                <td><?= esc($p['package_name'] ?? '—') ?></td>
                <td><?= esc($p['phone'] ?? '—') ?></td>
                <td><?= !empty($p['is_boarded']) ? 'Boarding' : 'Belum' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <p class="small text-muted mt-3 mb-0">Dicetak: <?= date('d/m/Y H:i') ?></p>
</div>
<div class="text-center no-print mt-3">
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-4" onclick="window.print()">Cetak</button>
    <a href="javascript:window.close()" class="btn btn-outline-secondary btn-sm rounded-pill px-4 ms-2">Tutup</a>
</div>
<script>window.onload = function() { }</script>
</body>
</html>

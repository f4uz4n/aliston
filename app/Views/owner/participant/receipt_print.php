<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .receipt-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 50px;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
            border-radius: 20px;
            position: relative;
            overflow: hidden;
        }
        .receipt-header {
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        .company-logo {
            max-height: 70px;
        }
        .receipt-title {
            font-weight: 800;
            color: #ef3338;
            letter-spacing: -0.5px;
        }
        .info-label {
            color: #94a3b8;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            margin-bottom: 2px;
        }
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        .table thead {
            background-color: #f8fafc;
        }
        .table thead th {
            border: none;
            color: #64748b;
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            padding: 15px;
        }
        .table tbody td {
            padding: 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .total-section {
            background-color: #f8fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 8rem;
            font-weight: 900;
            color: rgba(0,0,0,0.03);
            pointer-events: none;
            z-index: 0;
            white-space: nowrap;
        }
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .receipt-container { 
                box-shadow: none; 
                margin: 0; 
                width: 100%; 
                max-width: 100%;
                padding: 0;
            }
            .btn-print { display: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container no-print mt-4 mb-2 text-center">
    <button onclick="window.print()" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
        <i class="bi bi-printer-fill me-2"></i> Cetak Sekarang
    </button>
    <a href="javascript:window.close()" class="btn btn-light btn-lg rounded-pill px-4 ms-2 border">Tutup</a>
</div>

<div class="receipt-container">
    <div class="watermark">LUNAS</div>
    
    <div class="receipt-header">
        <div class="row align-items-center">
            <div class="col-6">
                <img src="<?= get_company_logo() ?>" alt="Logo" class="company-logo">
            </div>
            <div class="col-6 text-end">
                <h2 class="receipt-title mb-0">KWITANSI</h2>
                <p class="text-secondary small mb-0">Nomor: REG/<?= date('Ymd', strtotime($participant['created_at'])) ?>/<?= str_pad($participant['id'], 4, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-4">
            <div class="info-label">Jamaah</div>
            <div class="info-value"><?= esc($participant['name']) ?></div>
            <div class="small text-secondary"><?= esc($participant['phone']) ?></div>
        </div>
        <div class="col-4">
            <div class="info-label">Paket Perjalanan</div>
            <div class="info-value"><?= esc($participant['package_name']) ?></div>
            <?php $total_target_receipt = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0); ?>
            <div class="small text-secondary">Total: Rp <?= number_format($total_target_receipt, 0, ',', '.') ?><?= (!empty($participant['upgrade_cost']) && (float)$participant['upgrade_cost'] > 0) ? ' <span class="text-muted">(Paket + Upgrade)</span>' : '' ?></div>
        </div>
        <div class="col-4 text-end">
            <div class="info-label">Tanggal Cetak</div>
            <div class="info-value"><?= date('d F Y') ?></div>
        </div>
    </div>

    <?php if(isset($payment)): ?>
        <div class="p-4 border rounded-4 bg-light mb-4 position-relative">
            <div class="row align-items-center">
                <div class="col-8">
                    <div class="info-label text-primary">Pembayaran Tahap Ini</div>
                    <h1 class="fw-800 text-dark mb-0">Rp <?= number_format($payment['amount'], 0, ',', '.') ?></h1>
                    <div class="mt-2 fw-bold text-secondary italic"><?= esc($payment['notes'] ?: 'Pembayaran Angsuran') ?></div>
                </div>
                <div class="col-4 text-end">
                    <div class="info-label">Tanggal Bayar</div>
                    <div class="info-value"><?= date('d M Y', strtotime($payment['payment_date'])) ?></div>
                </div>
            </div>
        </div>
        
        <h6 class="fw-bold mb-3 small text-secondary text-uppercase">Ringkasan Saldo</h6>
    <?php else: ?>
        <h6 class="fw-bold mb-3">Rincian Pembayaran Terverifikasi</h6>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Keterangan</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(isset($payment)): ?>
                    <tr class="table-primary">
                        <td><?= date('d/m/Y', strtotime($payment['payment_date'])) ?></td>
                        <td><span class="badge bg-primary me-2">TAHAP INI</span> <?= esc($payment['notes'] ?: 'Pembayaran Angsuran') ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($payment['amount'], 0, ',', '.') ?></td>
                    </tr>
                <?php else: 
                    $total_paid = 0;
                    foreach($payments as $pay): 
                        $total_paid += $pay['amount'];
                    ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($pay['payment_date'])) ?></td>
                        <td><?= esc($pay['notes'] ?: 'Pembayaran Angsuran') ?></td>
                        <td class="text-end fw-bold">Rp <?= number_format($pay['amount'], 0, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if(!isset($payment) && empty($payments)): ?>
                <tr>
                    <td colspan="3" class="text-center py-4 text-muted small">Belum ada pembayaran terverifikasi</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="total-section">
        <div class="row align-items-center text-uppercase">
            <div class="col-6">
                <h6 class="fw-800 text-secondary mb-0" style="font-size: 0.7rem;">Total Terbayar</h6>
                <h2 class="fw-800 text-dark mb-0">Rp <?= number_format($total_paid, 0, ',', '.') ?></h2>
            </div>
            <div class="col-6 text-end border-start">
                <h6 class="fw-800 text-secondary mb-0" style="font-size: 0.7rem;">Sisa Tagihan</h6>
                <h4 class="fw-800 <?= (($total_target_receipt ?? (($participant['package_price'] ?? 0) + ($participant['upgrade_cost'] ?? 0))) - $total_paid > 0) ? 'text-danger' : 'text-success' ?> mb-0">
                    Rp <?= number_format(max(0, ($total_target_receipt ?? (($participant['package_price'] ?? 0) + ($participant['upgrade_cost'] ?? 0))) - $total_paid), 0, ',', '.') ?>
                </h4>
            </div>
        </div>
    </div>

    <div class="row mt-5 pt-4">
        <div class="col-8">
            <p class="small text-secondary mb-0">Catatan:</p>
            <ul class="small text-secondary ps-3">
                <li>Kwitansi ini adalah bukti pembayaran yang sah.</li>
                <li>Harap simpan kwitansi ini sebagai referensi di masa mendatang.</li>
                <li>Semua dana yang sudah dibayarkan mengikuti kebijakan pembatalan yang berlaku.</li>
            </ul>
        </div>
        <div class="col-4 text-center">
            <div class="mb-5 pb-3">
                <div class="info-label">Petugas Finance,</div>
            </div>
            <div class="mt-4 pt-2">
                <div class="h5 fw-bold mb-0"><?= session()->get('full_name') ?></div>
                <div class="info-label">Aliston Tour & Travel</div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

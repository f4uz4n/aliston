<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<?php
$allow = $allow_ubah_jadwal_hotel ?? false;
$days = $days_until_departure ?? null;
$isCancelled = ($participant['status'] ?? '') === 'cancelled';
$verifiedPayments = $verified_payments ?? [];
$pembayaranLunas = $pembayaran_lunas ?? false;
$totalTargetKelola = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);

// Catatan pemberangkatan: simpan sebagai Y-m-d\TH:i (datetime-local); tampilkan d/m/Y H:i
$dn = trim((string) ($participant['departure_note'] ?? ''));
$departureNoteDisplayLabel = $dn;
if ($dn !== '') {
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $dn)) {
        $ts = strtotime(str_replace('T', ' ', substr($dn, 0, 16)));
        if ($ts !== false) {
            $departureNoteDisplayLabel = date('d/m/Y H:i', $ts);
        }
    } else {
        $ts = strtotime($dn);
        if ($ts !== false) {
            $departureNoteDisplayLabel = date('d/m/Y H:i', $ts);
        }
    }
}
$oDn = old('departure_note');
$dnForInput = $oDn === null ? $dn : trim((string) $oDn);
$departureDatetimeLocalValue = '';
if ($dnForInput !== '') {
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', $dnForInput)) {
        $departureDatetimeLocalValue = substr($dnForInput, 0, 16);
    } else {
        $tsIn = strtotime($dnForInput);
        if ($tsIn !== false) {
            $departureDatetimeLocalValue = date('Y-m-d\TH:i', $tsIn);
        }
    }
}

$effectiveBoardingDeparture = participant_effective_departure($participant['package_departure_date'] ?? null, $participant['departure_note'] ?? null);
$boardingScheduleLabel = $effectiveBoardingDeparture ? date('d/m/Y H:i', strtotime($effectiveBoardingDeparture)) : '—';
$boardingFromCustomNote = ! empty($participant['departure_note']) && preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}/', trim((string) $participant['departure_note']));
?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <a href="<?= base_url('owner/participant') ?>" class="btn btn-light border rounded-pill px-3 fw-bold mb-2">
            <i class="bi bi-arrow-left me-2"></i>Kembali ke Daftar Jamaah
        </a>
        <h2 class="fw-800 text-dark mb-1">Kelola Jamaah</h2>
        <p class="text-secondary mb-0"><?= esc($participant['name']) ?> — <?= esc($participant['agency_name']) ?></p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <?php
        $canBoard = $can_boarding ?? false;
        $isBoard = !empty($participant['is_boarded']);
        ?>
        <?php if (is_owner() && $canBoard && !$isBoard): ?>
        <form action="<?= base_url('owner/participant/confirm-boarding') ?>" method="post" class="d-inline-block me-2">
            <?= csrf_field() ?>
            <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
            <button type="submit" class="btn btn-success rounded-pill px-4">
                <i class="bi bi-airplane-engines me-1"></i> Verifikasi Keseluruhan (Boarding)
            </button>
        </form>
        <?php elseif ($isBoard): ?>
        <span class="badge bg-success rounded-pill px-3 py-2 me-2"><i class="bi bi-check-circle me-1"></i> Sudah Boarding</span>
        <?php endif; ?>
        <a href="<?= base_url('owner/participant/registration-form/' . $participant['id']) ?>" target="_blank" class="btn btn-outline-secondary rounded-pill px-4 me-2">
            <i class="bi bi-printer me-1"></i> Cetak Formulir Pendaftaran
        </a>
        <?php if (!$isCancelled): ?>
        <a href="<?= base_url('owner/participant/cancel-form/' . $participant['id']) ?>" class="btn btn-outline-danger rounded-pill px-4 me-2">
            <i class="bi bi-x-circle me-1"></i> Batalkan Jamaah
        </a>
        <?php endif; ?>
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Monitoring Berkas & Pembayaran -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-graph-up me-2"></i>Monitoring Kelengkapan & Pembayaran</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div>
                                <span class="text-secondary small fw-bold">Kelengkapan Berkas</span>
                                <div class="mt-1"><span class="fw-bold <?= $doc_progress >= 100 ? 'text-success' : 'text-warning' ?>"><?= $doc_progress ?>%</span> (<?= $doc_count ?>/<?= $total_goal ?> item)</div>
                            </div>
                            <div class="d-flex flex-column gap-1 align-items-end">
                                <a href="<?= base_url('owner/participant/documents/' . $participant['id']) ?>" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="bi bi-file-earmark-check me-1"></i> Lihat Berkas
                                </a>
                                <?php if ($isCancelled): ?>
                                <form action="<?= base_url('owner/participant/reactivate/' . $participant['id']) ?>" method="post" class="d-inline" onsubmit="return confirm('Aktifkan kembali jamaah ini? Status akan diubah dari batal menjadi pending.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-success btn-sm rounded-pill">
                                        <i class="bi bi-arrow-counterclockwise me-1"></i> Aktifkan Kembali Jamaah
                                    </button>
                                </form>
                                <?php else: ?>
                                <?php if (is_owner()): ?>
                                <a href="<?= base_url('owner/checklist/' . $participant['id']) ?>" class="btn btn-outline-success btn-sm rounded-pill">
                                    <i class="bi bi-patch-check me-1"></i> Verifikasi Jamaah
                                </a>
                                <?php else: ?>
                                <a href="<?= base_url('owner/checklist/' . $participant['id']) ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
                                    <i class="bi bi-clipboard-check me-1"></i> Cek Kelengkapan
                                </a>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $total_target = (float)($participant['package_price'] ?? 0) + (float)($participant['upgrade_cost'] ?? 0);
                    ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3">
                            <div>
                                <span class="text-secondary small fw-bold">Pembayaran</span>
                                <div class="mt-1"><span class="fw-bold">Rp <?= number_format($total_paid, 0, ',', '.') ?></span> / Rp <?= number_format($total_target, 0, ',', '.') ?></div>
                                <?php if (!empty($participant['upgrade_cost']) && (float)$participant['upgrade_cost'] > 0): ?>
                                    <div class="small text-muted">Paket: Rp <?= number_format($participant['package_price'], 0, ',', '.') ?> + Upgrade: Rp <?= number_format($participant['upgrade_cost'], 0, ',', '.') ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex flex-column gap-1 align-items-end">
                                <?php if (!$isCancelled): ?>
                                <a href="<?= base_url('owner/participant/add-payment/' . $participant['id']) ?>" class="btn btn-primary btn-sm rounded-pill">
                                    <i class="bi bi-plus-circle me-1"></i> Tambah Pembayaran (Kantor)
                                </a>
                                <?php if (is_owner()): ?>
                                <a href="<?= base_url('owner/payment-verification?participant_id=' . (int)$participant['id'] . '&tab=pending') ?>" class="btn btn-outline-primary btn-sm rounded-pill">Verifikasi Pembayaran</a>
                                <?php endif; ?>
                                <a href="<?= base_url('owner/participant/receipt/' . (int)$participant['id']) ?>" target="_blank" class="btn btn-outline-success btn-sm rounded-pill">
                                    <i class="bi bi-printer me-1"></i> Cetak Kwitansi
                                </a>
                                <?php if (!empty($verifiedPayments)): ?>
                                <div class="text-end mt-1 pt-1 border-top w-100">
                                    <span class="small text-secondary d-block mb-1">Kwitansi per setoran (sudah diverifikasi pemilik)</span>
                                    <div class="d-flex flex-wrap gap-1 justify-content-end">
                                        <?php foreach ($verifiedPayments as $vp): ?>
                                        <a href="<?= base_url('owner/participant/transaction-receipt/' . (int)($vp['id'] ?? 0)) ?>" target="_blank" class="btn btn-light border btn-sm rounded-pill px-2 py-0" title="Cetak kwitansi">
                                            <i class="bi bi-printer me-1"></i><?= date('d/m/y', strtotime($vp['payment_date'] ?? 'now')) ?> · Rp <?= number_format((float)($vp['amount'] ?? 0), 0, ',', '.') ?>
                                        </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ganti Jadwal Pemberangkatan -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-calendar-event me-2"></i>Ganti Jadwal Pemberangkatan</h6>
            </div>
            <div class="card-body">
                <p class="small text-secondary mb-2">Jadwal saat ini: <strong><?= esc($participant['package_name']) ?></strong> (<?= $participant['package_departure_date'] ? date('d/m/Y', strtotime($participant['package_departure_date'])) : '—' ?>)</p>
                <p class="small text-dark mb-2">Untuk perhitungan H-15 dan boarding dipakai: <strong><?= esc($boardingScheduleLabel) ?></strong><?php if ($boardingFromCustomNote): ?> <span class="text-muted">— mengikuti catatan tanggal di bawah, bukan tanggal paket.</span><?php endif; ?></p>
                <?php if (!empty($participant['departure_note'])): ?>
                <p class="small text-dark mb-3 p-2 rounded-3 bg-light border border-dashed"><span class="text-secondary">Catatan tanggal berangkat:</span> <?= esc($departureNoteDisplayLabel) ?></p>
                <?php else: ?>
                <p class="small text-muted mb-3">Belum ada catatan tanggal berangkat tambahan.</p>
                <?php endif; ?>
                <?php if ($allow): ?>
                <form action="<?= base_url('owner/participant/update-schedule') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Pilih Paket / Jadwal Baru</label>
                        <select name="package_id" class="form-select" required>
                            <?php foreach ($packages as $pkg): ?>
                                <option value="<?= $pkg['id'] ?>" <?= $participant['package_id'] == $pkg['id'] ? 'selected' : '' ?>>
                                    <?= esc($pkg['name']) ?> — <?= date('d/m/Y', strtotime($pkg['departure_date'])) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Catatan tanggal pemberangkatan <span class="text-muted fw-normal">(opsional)</span></label>
                        <input type="datetime-local" name="departure_note" class="form-control bg-light border-0 rounded-3" value="<?= esc($departureDatetimeLocalValue) ?>">
                        <div class="form-text">Jika diisi, tanggal ini yang dipakai untuk hitungan H- dan syarat boarding (menggantikan tanggal paket). Kosongkan untuk kembali memakai tanggal paket.</div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-check2 me-1"></i> Simpan Jadwal</button>
                </form>
                <?php else: ?>
                <p class="text-muted small mb-0">Perubahan jadwal dinonaktifkan karena jamaah sudah dibatalkan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Ganti Hotel & Kamar -->
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-header bg-light border-0 py-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-building me-2"></i>Ganti Hotel & Kamar (di luar paket)</h6>
            </div>
            <div class="card-body">
                <p class="small text-secondary mb-3">Pilih hotel dan kamar dari master. Kamar mengikuti hotel yang dipilih.</p>
                <?php if ($allow): ?>
                <form action="<?= base_url('owner/participant/save-upgrade') ?>" method="post" id="formHotelKamar">
                    <?= csrf_field() ?>
                    <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Hotel</label>
                        <select name="hotel_upgrade_id" id="hotelSelect" class="form-select">
                            <option value="">— Tidak ganti hotel —</option>
                            <?php foreach ($hotels as $h): ?>
                                <option value="<?= $h['id'] ?>" <?= ($participant['hotel_upgrade_id'] ?? '') == $h['id'] ? 'selected' : '' ?>><?= esc($h['name']) ?> (<?= esc($h['city']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Kamar / Tipe</label>
                        <select name="room_upgrade_id" id="roomSelect" class="form-select">
                            <option value="">— Pilih hotel dulu —</option>
                            <?php foreach ($hotels as $h): ?>
                                <?php foreach ($h['rooms'] as $r):
                                    $sel = ($participant['room_upgrade_id'] ?? '') == $r['id'] ? 'selected' : '';
                                ?>
                                <option value="<?= $r['id'] ?>" data-hotel-id="<?= $h['id'] ?>" <?= $sel ?>><?= esc($r['name']) ?> (<?= esc($r['type']) ?>) — Rp <?= number_format($r['price_per_pax'], 0, ',', '.') ?>/pax</option>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3" id="bedQtySection" style="display:none;">
                        <label class="form-label small fw-bold">Tambahan Bed / Kasur (qty)</label>
                        <p class="small text-muted mb-2">Pilih jumlah penambahan bed per tipe; biaya = harga × qty.</p>
                        <div id="bedQtyContainer"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Biaya Upgrade (Rp)</label>
                        <input type="number" name="upgrade_cost" id="upgradeCostInput" class="form-control" value="<?= esc($participant['upgrade_cost'] ?? '') ?>" placeholder="0" min="0" step="0.01" readonly>
                        <div id="rekomendasiHarga" class="small text-success mt-1" style="display:none;"></div>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-check2 me-1"></i> Simpan Hotel & Kamar</button>
                </form>
                <?php else: ?>
                <p class="text-muted small mb-0">Perubahan hotel/kamar dinonaktifkan karena jamaah sudah dibatalkan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var hotelSelect = document.getElementById('hotelSelect');
    var roomSelect = document.getElementById('roomSelect');
    var upgradeCostInput = document.getElementById('upgradeCostInput');
    var rekomendasiEl = document.getElementById('rekomendasiHarga');
    var bedQtySection = document.getElementById('bedQtySection');
    var bedQtyContainer = document.getElementById('bedQtyContainer');

    var roomPrices = <?= json_encode(array_reduce($hotels ?? [], function($carry, $h) {
        foreach ($h['rooms'] as $r) { $carry[$r['id']] = (float)($r['price_per_pax'] ?? 0); }
        return $carry;
    }, [])) ?>;

    var roomBeds = <?= json_encode(array_reduce($hotels ?? [], function($carry, $h) {
        foreach ($h['rooms'] as $r) {
            $carry[$r['id']] = array_map(function($b) {
                return ['id' => (int)$b['id'], 'name' => $b['name'], 'price' => (float)($b['price'] ?? 0)];
            }, $r['beds'] ?? []);
        }
        return $carry;
    }, [])) ?>;

    var participantBedQty = <?= json_encode($participant_upgrade_beds ?? []) ?>;

    function filterRooms() {
        var hid = hotelSelect.value;
        var opts = roomSelect.querySelectorAll('option[data-hotel-id]');
        opts.forEach(function(opt) {
            opt.style.display = opt.getAttribute('data-hotel-id') === hid ? '' : 'none';
            opt.disabled = opt.getAttribute('data-hotel-id') !== hid;
        });
        if (!hid) {
            roomSelect.value = '';
            rekomendasiEl.style.display = 'none';
            bedQtySection.style.display = 'none';
            return;
        }
        var firstRoom = roomSelect.querySelector('option[data-hotel-id="' + hid + '"]');
        if (firstRoom && roomSelect.value !== firstRoom.value) roomSelect.value = '';
        renderBedsForRoom();
        recalcUpgradeCost();
    }

    function renderBedsForRoom() {
        var rid = roomSelect.value;
        if (!rid) {
            bedQtySection.style.display = 'none';
            return;
        }
        var beds = roomBeds[rid] || [];
        bedQtyContainer.innerHTML = '';
        if (beds.length === 0) {
            bedQtySection.style.display = 'block';
            bedQtyContainer.innerHTML = '<p class="small text-muted mb-0">Tidak ada master bed untuk kamar ini.</p>';
            return;
        }
        bedQtySection.style.display = 'block';
        beds.forEach(function(b) {
            var qty = participantBedQty[b.id] || 0;
            var row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 mb-2';
            row.innerHTML =
                '<label class="small mb-0 flex-grow-1">' + escapeHtml(b.name) + ' — Rp ' + Number(b.price).toLocaleString('id-ID') + '</label>' +
                '<input type="number" name="bed_qty[' + b.id + ']" class="form-control form-control-sm bed-qty-input" style="width:80px" min="0" value="' + qty + '" data-bed-id="' + b.id + '" data-bed-price="' + b.price + '">';
            bedQtyContainer.appendChild(row);
        });
        bedQtyContainer.querySelectorAll('.bed-qty-input').forEach(function(inp) {
            inp.addEventListener('input', recalcUpgradeCost);
        });
    }

    function escapeHtml(s) {
        var div = document.createElement('div');
        div.textContent = s;
        return div.innerHTML;
    }

    function recalcUpgradeCost() {
        var rid = roomSelect.value;
        var base = (rid && roomPrices[rid] != null) ? roomPrices[rid] : 0;
        var bedTotal = 0;
        bedQtyContainer.querySelectorAll('.bed-qty-input').forEach(function(inp) {
            var qty = parseInt(inp.value, 10) || 0;
            var price = parseFloat(inp.getAttribute('data-bed-price')) || 0;
            bedTotal += price * qty;
        });
        var total = base + bedTotal;
        upgradeCostInput.value = total;
        if (rid && (base > 0 || bedTotal > 0)) {
            rekomendasiEl.textContent = 'Kamar: Rp ' + Number(base).toLocaleString('id-ID') + (bedTotal > 0 ? ' + Bed: Rp ' + Number(bedTotal).toLocaleString('id-ID') : '') + ' = Rp ' + Number(total).toLocaleString('id-ID');
            rekomendasiEl.style.display = 'block';
        } else {
            rekomendasiEl.style.display = 'none';
        }
    }

    roomSelect.addEventListener('change', function() {
        renderBedsForRoom();
        recalcUpgradeCost();
    });

    hotelSelect.addEventListener('change', function() {
        filterRooms();
        roomSelect.value = '';
        rekomendasiEl.style.display = 'none';
        bedQtySection.style.display = 'none';
    });
    filterRooms();
});
</script>
<?= $this->endSection() ?>

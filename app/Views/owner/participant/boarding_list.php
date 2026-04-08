<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-6">
        <h2 class="fw-800 text-dark mb-1">Boarding</h2>
        <p class="text-secondary mb-0">Verifikasi keseluruhan jamaah sebelum keberangkatan. Berkas lengkap, pembayaran lunas, dan H-15.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end">
        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 rounded-pill px-4 py-2 d-inline-block"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form method="get" action="<?= base_url('owner/participant/boarding-list') ?>" class="row g-3 align-items-end">
            <div class="col-auto">
                <label class="col-form-label small fw-bold">Paket</label>
                <select name="package_id" class="form-select form-select-sm bg-light border-0 rounded-pill" style="min-width: 220px;">
                    <option value="">Semua Paket</option>
                    <?php foreach ($packages as $pkg): ?>
                        <option value="<?= $pkg['id'] ?>" <?= ($selected_package ?? '') == $pkg['id'] ? 'selected' : '' ?>>
                            <?= esc($pkg['name']) ?> — <?= !empty($pkg['departure_date']) ? date('d/m/Y', strtotime($pkg['departure_date'])) : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="col-form-label small fw-bold">Tanggal Berangkat</label>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <input type="date" name="departure_date_from" class="form-control form-control-sm bg-light border-0 rounded-pill" value="<?= esc($departure_date_from ?? '') ?>" placeholder="Dari">
                    <span class="small text-muted">s/d</span>
                    <input type="date" name="departure_date_to" class="form-control form-control-sm bg-light border-0 rounded-pill" value="<?= esc($departure_date_to ?? '') ?>" placeholder="Sampai">
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3"><i class="bi bi-funnel me-1"></i> Filter</button>
                <a href="<?= base_url('owner/participant/boarding-list') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">Reset</a>
                <?php
                $exportQuery = array_filter([
                    'package_id' => $selected_package ?? '',
                    'departure_date_from' => $departure_date_from ?? '',
                    'departure_date_to' => $departure_date_to ?? '',
                ]);
                $exportUrl = base_url('owner/participant/boarding-list-export') . (empty($exportQuery) ? '' : '?' . http_build_query($exportQuery));
                ?>
                <a href="<?= esc($exportUrl) ?>" class="btn btn-success btn-sm rounded-pill px-3" title="Cetak/Export daftar jamaah ke Excel"><i class="bi bi-file-earmark-excel me-1"></i> Cetak Excel</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="border-0 py-3">Tanggal Berangkat</th>
                    <th class="border-0 py-3">Maskapai</th>
                    <th class="border-0 py-3">Nama Jamaah</th>
                    <th class="border-0 py-3">Agensi</th>
                    <th class="border-0 py-3">Paket</th>
                    <th class="border-0 py-3 text-center">Pembayaran</th>
                    <th class="border-0 py-3 text-center">H-</th>
                    <th class="border-0 py-3 text-center">Status</th>
                    <th class="border-0 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($participants)): ?>
                <tr><td colspan="9" class="text-center py-5 text-muted">Tidak ada data jamaah.</td></tr>
                <?php else: ?>
                <?php foreach ($participants as $p): ?>
                <tr>
                    <td><?= !empty($p['departure_date']) ? date('d/m/Y', strtotime($p['departure_date'])) : '—' ?></td>
                    <td><?= esc($p['airline'] ?? '—') ?></td>
                    <td><strong><?= esc($p['name']) ?></strong><br><small class="text-muted"><?= esc($p['nik'] ?? '') ?></small></td>
                    <td class="small"><?= esc($p['agency_name'] ?? '—') ?></td>
                    <td class="small"><?= esc($p['package_name'] ?? '—') ?></td>
                    <td class="text-center">
                        <?php if ($p['pembayaran_lunas']): ?>
                            <span class="badge bg-success rounded-pill">Lunas</span>
                        <?php else: ?>
                            <span class="badge bg-secondary rounded-pill">Rp <?= number_format($p['total_paid'], 0, ',', '.') ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center"><?= $p['days_until'] !== null ? $p['days_until'] : '—' ?></td>
                    <td class="text-center">
                        <?php if (!empty($p['is_boarded'])): ?>
                            <span class="badge bg-success rounded-pill">Boarding</span>
                        <?php else: ?>
                            <span class="badge bg-light text-dark rounded-pill">Belum</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-end">
                        <?php if (empty($p['is_boarded']) && is_owner()): ?>
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" onclick="openBoardingModal(<?= htmlspecialchars(json_encode([
                            'id' => $p['id'],
                            'name' => $p['name'],
                            'departure_date' => $p['departure_date'] ?? '',
                            'airline' => $p['airline'] ?? '—',
                            'berkas_lengkap' => $p['berkas_lengkap'],
                            'pembayaran_lunas' => $p['pembayaran_lunas'],
                            'days_until' => $p['days_until'],
                            'can_boarding' => $p['can_boarding'],
                            'total_paid' => $p['total_paid'],
                            'total_target' => $p['total_target'],
                            'doc_progress' => $p['doc_progress'],
                        ])) ?>)">
                            <i class="bi bi-airplane-engines me-1"></i> Boarding
                        </button>
                        <?php elseif (empty($p['is_boarded'])): ?>
                        <span class="text-muted small">Hanya pemilik</span>
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

<!-- Modal Konfirmasi Boarding -->
<div class="modal fade" id="boardingModal" tabindex="-1" aria-labelledby="boardingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="boardingModalLabel"><i class="bi bi-airplane-engines me-2"></i>Konfirmasi Boarding</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-3 small text-muted" id="modalJamaahName"></p>
                <ul class="list-group list-group-flush rounded-3">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Tanggal Pemberangkatan</span>
                        <strong id="modalDeparture">—</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Maskapai</span>
                        <strong id="modalAirline">—</strong>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Berkas</span>
                        <span id="modalBerkas">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>Pembayaran</span>
                        <span id="modalPembayaran">—</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>H-15</span>
                        <span id="modalH15">—</span>
                    </li>
                </ul>
                <div id="modalAlert" class="alert alert-warning mt-3 small mb-0" style="display:none;">
                    Berkas lengkap, pembayaran lunas, dan minimal H-15 diperlukan untuk konfirmasi boarding.
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                <form id="formConfirmBoarding" action="<?= base_url('owner/participant/confirm-boarding') ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <input type="hidden" name="participant_id" id="modalParticipantId" value="">
                    <button type="submit" class="btn btn-success rounded-pill" id="btnConfirmBoarding">
                        <i class="bi bi-check-circle me-1"></i> Konfirmasi Boarding
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openBoardingModal(data) {
    document.getElementById('modalJamaahName').textContent = data.name;
    document.getElementById('modalDeparture').textContent = data.departure_date ? formatDate(data.departure_date) : '—';
    document.getElementById('modalAirline').textContent = data.airline || '—';
    document.getElementById('modalBerkas').innerHTML = data.berkas_lengkap
        ? '<span class="badge bg-success rounded-pill">Lengkap</span>'
        : '<span class="badge bg-warning text-dark rounded-pill">' + data.doc_progress + '%</span>';
    document.getElementById('modalPembayaran').innerHTML = data.pembayaran_lunas
        ? '<span class="badge bg-success rounded-pill">Lunas</span>'
        : '<span class="badge bg-secondary rounded-pill">Belum lunas</span>';
    document.getElementById('modalH15').innerHTML = data.days_until !== null
        ? 'H-' + data.days_until + (data.days_until <= 15 ? ' <span class="badge bg-success rounded-pill">OK</span>' : '')
        : '—';
    document.getElementById('modalParticipantId').value = data.id;
    var canBoard = data.can_boarding;
    document.getElementById('btnConfirmBoarding').disabled = !canBoard;
    document.getElementById('modalAlert').style.display = canBoard ? 'none' : 'block';
    new bootstrap.Modal(document.getElementById('boardingModal')).show();
}
function formatDate(s) {
    if (!s) return '—';
    var d = new Date(s);
    return ('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth() + 1)).slice(-2) + '/' + d.getFullYear();
}
</script>
<?= $this->endSection() ?>

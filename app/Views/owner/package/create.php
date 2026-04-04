<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 text-dark mb-1">Buat Paket Perjalanan</h2>
                <p class="text-secondary mb-0">Lengkapi detail paket untuk dipromosikan oleh agensi Anda</p>
            </div>
            <a href="<?= base_url('package') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <form action="<?= base_url('package/store') ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?>

            <!-- Section 1: Informasi Dasar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="bg-primary-soft text-primary rounded-circle p-2 me-3">
                            <i class="bi bi-info-circle-fill fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Informasi Umum</h5>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Nama Paket Perjalanan</label>
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" placeholder="Contoh: UMROH SYAWAL 13 HARI" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Durasi Paket</label>
                            <input type="text" name="duration" class="form-control form-control-lg bg-light border-0" placeholder="Contoh: 13 Hari" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Gambar Paket (Brosur/Pemandangan)</label>
                            <input type="file" name="image" class="form-control bg-light border-0" accept="image/*">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Tanggal Berangkat</label>
                            <input type="date" name="departure_date" class="form-control form-control-lg bg-light border-0" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Lokasi Start - End</label>
                            <input type="text" name="location_start_end" class="form-control form-control-lg bg-light border-0" placeholder="KEDIRI" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Akomodasi -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="bg-success-soft text-secondary-color rounded-circle p-2 me-3">
                            <i class="bi bi-building-fill fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Penginapan & Akomodasi</h5>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Hotel <?= esc($city1_name ?? 'Kota 1') ?></label>
                            <select name="hotel_mekkah_id" class="form-select bg-light border-0" <?= !empty($hotels_all) ? 'required' : '' ?>>
                                <option value="">— Pilih hotel dari master —</option>
                                <?php foreach ($hotels_all ?? [] as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= esc($h['name']) ?> (<?= esc($h['city'] ?? '') ?>) — <?= (int)($h['star_rating'] ?? 0) ?> Bintang</option>
                                <?php endforeach; ?>
                            </select>
                            <p class="small text-muted mt-1 mb-0">Bintang hotel mengikuti data master.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Hotel <?= esc($city2_name ?? 'Kota 2') ?></label>
                            <select name="hotel_madinah_id" class="form-select bg-light border-0" <?= !empty($hotels_all) ? 'required' : '' ?>>
                                <option value="">— Pilih hotel dari master —</option>
                                <?php foreach ($hotels_all ?? [] as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= esc($h['name']) ?> (<?= esc($h['city'] ?? '') ?>) — <?= (int)($h['star_rating'] ?? 0) ?> Bintang</option>
                                <?php endforeach; ?>
                            </select>
                            <p class="small text-muted mt-1 mb-0">Bintang hotel mengikuti data master.</p>
                        </div>
                    </div>
                    <?php if (empty($hotels_all)): ?>
                    <p class="small text-muted mt-2 mb-0">Belum ada hotel di master. <a href="<?= base_url('owner/hotels') ?>">Tambah di Master Hotel & Kamar</a>.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Section 3: Inklusi & Freebies -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="bg-warning-soft text-warning rounded-circle p-2 me-3">
                            <i class="bi bi-gift-fill fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Sudah Termasuk & Fasilitas Tambahan</h5>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Sudah Termasuk (Satu per baris)</label>
                            <textarea name="inclusions" class="form-control bg-light border-0" rows="8" placeholder="Tiket Pesawat PP&#10;Visa Umroh..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Gratis / Free (Satu per baris)</label>
                            <textarea name="freebies" class="form-control bg-light border-0" rows="8" placeholder="City Tour Thaif..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Belum Termasuk (Satu per baris)</label>
                            <textarea name="exclusions" class="form-control bg-light border-0" rows="8" placeholder="Biaya keperluan pribadi&#10;Asuransi perjalanan opsional..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Transportasi & Harga -->
            <div class="card border-0 shadow-sm rounded-4 mb-5 overflow-hidden">
                <div class="card-header bg-white border-0 py-4 px-4 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="bg-info-soft text-info rounded-circle p-2 me-3">
                            <i class="bi bi-airplane-engines-fill fs-5"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-0">Transportasi & Harga</h5>
                    </div>
                </div>
                <div class="card-body p-4 p-md-5">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Maskapai</label>
                            <input type="text" name="airline" class="form-control bg-light border-0" placeholder="Lion Air" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Rute</label>
                            <input type="text" name="flight_route" class="form-control bg-light border-0" placeholder="SUB - JED" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Harga Paket (Rupiah)</label>
                            <input type="text" name="price" class="form-control bg-light border-0 format-rupiah" placeholder="31.200.000" data-value="0" required>
                            <small class="text-muted">Input nominal penuh. Tampilan: <strong id="priceDisplayPreview">0 JT</strong></small>
                        </div>
                        <?php if (can_view_commission()): ?>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Komisi Agency (per pax)</label>
                            <input type="text" name="commission_per_pax" class="form-control bg-light border-0 format-rupiah" placeholder="500.000" value="0" data-value="0">
                            <small class="text-muted">Nominal komisi per penumpang untuk agency (format Rupiah, tanpa desimal)</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end mb-5">
                <button type="submit" class="btn-premium rounded-pill px-5">
                    <i class="bi bi-check2-circle me-2"></i>Simpan Paket Perjalanan
                </button>
            </div>
        </form>
    </div>
</div>
<script>
(function() {
    function formatRupiah(el) {
        var v = el.value.replace(/\D/g, '');
        if (v === '') { el.value = ''; el.dataset.value = '0'; return; }
        el.dataset.value = v;
        var n = parseInt(v, 10);
        el.value = n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
    }
    function initRupiah() {
        document.querySelectorAll('.format-rupiah').forEach(function(el) {
            el.addEventListener('input', function() { formatRupiah(this); });
            el.addEventListener('blur', function() { formatRupiah(this); });
            if (el.value && el.value !== '0') formatRupiah(el);
        });
        document.querySelector('form').addEventListener('submit', function() {
            document.querySelectorAll('.format-rupiah').forEach(function(el) {
                el.value = el.dataset.value || el.value.replace(/\D/g, '') || '0';
            });
        });
        var priceInput = document.querySelector('input[name="price"]');
        var pricePreview = document.getElementById('priceDisplayPreview');
        if (priceInput && pricePreview) {
            function updatePricePreview() {
                var v = (priceInput.dataset.value || priceInput.value.replace(/\D/g, '') || '0').replace(/^\0+/, '') || '0';
                var num = parseInt(v, 10) || 0;
                if (num >= 1000000) {
                    var j = num / 1000000;
                    pricePreview.textContent = j.toFixed(1).replace('.', ',') + ' JT';
                } else {
                    pricePreview.textContent = num ? (num / 1000000).toFixed(1).replace('.', ',') + ' JT' : '0 JT';
                }
            }
            priceInput.addEventListener('input', function() { formatRupiah(this); updatePricePreview(); });
            priceInput.addEventListener('blur', function() { updatePricePreview(); });
        }
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initRupiah);
    else initRupiah();
})();
</script>
<?= $this->endSection() ?>
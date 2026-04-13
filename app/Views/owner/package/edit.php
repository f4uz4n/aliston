<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="fw-800 text-dark mb-1">Edit Paket Perjalanan</h2>
                <p class="text-secondary mb-0">Perbarui detail paket "<?= esc($package['name']) ?>"</p>
            </div>
            <a href="<?= base_url('package') ?>" class="btn btn-light border rounded-pill px-4 fw-bold">
                <i class="bi bi-arrow-left me-2"></i>Kembali
            </a>
        </div>

        <form action="<?= base_url('package/update/'.$package['id']) ?>" method="post" enctype="multipart/form-data">
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
                            <input type="text" name="name" class="form-control form-control-lg bg-light border-0" value="<?= esc($package['name']) ?>" required>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Durasi Paket</label>
                            <input type="text" name="duration" class="form-control form-control-lg bg-light border-0" value="<?= esc($package['duration']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Gambar Paket</label>
                            <?php if($package['image']): ?>
                                <div class="mb-2">
                                    <img src="<?= base_url($package['image']) ?>" alt="Current Image" class="rounded-3" style="height: 100px;">
                                    <p class="small text-muted mt-1">Ganti gambar jika ingin memperbarui</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image" class="form-control bg-light border-0" accept="image/*">
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Tanggal Berangkat</label>
                            <input type="date" name="departure_date" class="form-control form-control-lg bg-light border-0" value="<?= esc($package['departure_date']) ?>" required>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Lokasi Start - End</label>
                            <input type="text" name="location_start_end" class="form-control form-control-lg bg-light border-0" value="<?= esc($package['location_start_end']) ?>" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check mt-1">
                                <input class="form-check-input" type="checkbox" value="1" id="showDepartureMonthYearOnlyEdit" name="show_departure_month_year_only" <?= !empty($package['show_departure_month_year_only']) ? 'checked' : '' ?>>
                                <label class="form-check-label small text-secondary" for="showDepartureMonthYearOnlyEdit">
                                    Tampilkan keberangkatan <strong>bulan + tahun saja</strong> pada pamflet publik.
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="bg-light border rounded-3 p-3">
                                <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Bulan Keberangkatan (Pamflet)</label>
                                <?php
                                $bulanMap = [
                                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                                ];
                                $selectedPamphletMonths = [];
                                if (!empty($package['pamphlet_departure_months'])) {
                                    foreach (explode(',', (string) $package['pamphlet_departure_months']) as $m) {
                                        $mi = (int) trim($m);
                                        if ($mi >= 1 && $mi <= 12) {
                                            $selectedPamphletMonths[] = $mi;
                                        }
                                    }
                                }
                                ?>
                                <div class="d-flex flex-wrap gap-3">
                                    <?php foreach ($bulanMap as $bNum => $bNama): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="pamphlet_departure_months[]" id="pamphletMonthEdit<?= $bNum ?>" value="<?= $bNum ?>" <?= in_array($bNum, $selectedPamphletMonths, true) ? 'checked' : '' ?>>
                                        <label class="form-check-label small" for="pamphletMonthEdit<?= $bNum ?>"><?= esc($bNama) ?></label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="mt-3" style="max-width: 220px;">
                                    <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Tahun (Pamflet)</label>
                                    <input type="number" name="pamphlet_departure_year" class="form-control bg-white border" min="2020" max="2100" value="<?= esc((string)($package['pamphlet_departure_year'] ?? '')) ?>" placeholder="2026">
                                </div>
                                <div class="form-text">Contoh hasil pamflet: Juni, Juli, Agustus 2026.</div>
                            </div>
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
                            <select name="hotel_mekkah_id" class="form-select bg-light border-0">
                                <option value="">— Pilih hotel dari master —</option>
                                <?php
                                $list1 = $hotels_all ?? [];
                                $id1 = (int)($package['hotel_mekkah_id'] ?? 0);
                                foreach ($list1 as $h) {
                                    if ($id1 && (int)$h['id'] === $id1) {
                                        $sel1 = 'selected';
                                    } elseif (!$id1 && trim((string)($package['hotel_mekkah'] ?? '')) === trim($h['name'])) {
                                        $sel1 = 'selected';
                                    } else {
                                        $sel1 = '';
                                    }
                                    $star1 = (int)($h['star_rating'] ?? 0);
                                    $cityName1 = esc($h['city'] ?? '');
                                    echo '<option value="' . (int)$h['id'] . '" ' . $sel1 . '>' . esc($h['name']) . ' (' . $cityName1 . ') — ' . $star1 . ' Bintang</option>';
                                }
                                ?>
                            </select>
                            <p class="small text-muted mt-1 mb-0">Bintang hotel mengikuti data master.</p>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Hotel <?= esc($city2_name ?? 'Kota 2') ?></label>
                            <select name="hotel_madinah_id" class="form-select bg-light border-0">
                                <option value="">— Pilih hotel dari master —</option>
                                <?php
                                $list2 = $hotels_all ?? [];
                                $id2 = (int)($package['hotel_madinah_id'] ?? 0);
                                foreach ($list2 as $h) {
                                    if ($id2 && (int)$h['id'] === $id2) {
                                        $sel2 = 'selected';
                                    } elseif (!$id2 && trim((string)($package['hotel_madinah'] ?? '')) === trim($h['name'])) {
                                        $sel2 = 'selected';
                                    } else {
                                        $sel2 = '';
                                    }
                                    $star2 = (int)($h['star_rating'] ?? 0);
                                    $cityName2 = esc($h['city'] ?? '');
                                    echo '<option value="' . (int)$h['id'] . '" ' . $sel2 . '>' . esc($h['name']) . ' (' . $cityName2 . ') — ' . $star2 . ' Bintang</option>';
                                }
                                ?>
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
                            <textarea name="inclusions" class="form-control bg-light border-0" rows="8"><?= esc($package['inclusions_text']) ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Gratis / Free (Satu per baris)</label>
                            <textarea name="freebies" class="form-control bg-light border-0" rows="8"><?= esc($package['freebies_text']) ?></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1 mb-2">Belum Termasuk (Satu per baris)</label>
                            <textarea name="exclusions" class="form-control bg-light border-0" rows="8"><?= esc($package['exclusions_text'] ?? '') ?></textarea>
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
                            <input type="text" name="airline" class="form-control bg-light border-0" value="<?= esc($package['airline']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Rute</label>
                            <input type="text" name="flight_route" class="form-control bg-light border-0" value="<?= esc($package['flight_route']) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Harga Paket (Rupiah)</label>
                            <?php
                            $priceVal = (float)($package['price'] ?? 0);
                            if ($priceVal >= 1_000_000) {
                                $priceRupiah = (int) $priceVal;
                                $priceDisplay = number_format($priceVal / 1e6, 1, ',', '') . ' JT';
                            } else {
                                $priceRupiah = (int) ($priceVal * 1_000_000);
                                $priceDisplay = number_format($priceVal, 1, ',', '') . ' JT';
                            }
                            $priceFormatted = number_format($priceRupiah, 0, '', '.');
                            ?>
                            <input type="text" name="price" class="form-control bg-light border-0 format-rupiah" value="<?= esc($priceFormatted) ?>" data-value="<?= esc((string)$priceRupiah) ?>" placeholder="31.200.000" required>
                            <small class="text-muted">Tampilan: <strong><?= $priceDisplay ?></strong></small>
                        </div>
                        <?php if (can_view_commission()): ?>
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-bold small text-uppercase text-secondary ls-1">Komisi Agency (per pax)</label>
                            <?php $komisi = (float)($package['commission_per_pax'] ?? 0); ?>
                            <input type="text" name="commission_per_pax" class="form-control bg-light border-0 format-rupiah" placeholder="500.000" value="<?= esc(number_format($komisi, 0, '', '.')) ?>" data-value="<?= esc((string)(int)$komisi) ?>">
                            <small class="text-muted">Nominal komisi per penumpang untuk agency (format Rupiah, tanpa desimal)</small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 justify-content-end mb-5">
                <button type="submit" class="btn-premium rounded-pill px-5">
                    <i class="bi bi-arrow-repeat me-2"></i>Perbarui Paket
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
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initRupiah);
    else initRupiah();
})();
</script>
<?= $this->endSection() ?>
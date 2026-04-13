<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>

<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?= base_url('agency') ?>" class="text-decoration-none text-secondary">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Testimoni Jamaah</li>
                    </ol>
                </nav>
                <h2 class="fw-800 text-dark mb-1">Testimoni Jamaah</h2>
                <p class="text-secondary mb-0">Kirim testimoni dari jamaah untuk ditampilkan di halaman depan (setelah diverifikasi admin)</p>
            </div>
        </div>

        <?php if (session()->getFlashdata('msg')): ?>
            <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('msg') ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4"><?= session()->getFlashdata('error') ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0"><i class="bi bi-pencil-square me-2 text-primary"></i>Input Testimoni</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="<?= base_url('agency/testimoni/submit') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control bg-light border-0 input-nama-jamaah-upper" value="<?= esc(old('name')) ?>" required placeholder="Nama jamaah" maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Paket (opsional)</label>
                                <select name="package_id" class="form-select bg-light border-0">
                                    <option value="">-- Pilih paket --</option>
                                    <?php foreach ($packages as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= old('package_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Rating <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-1" id="agencyRatingStars">
                                    <?php $oldR = (int)old('rating') ?: 0; for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star-select text-warning" data-value="<?= $i ?>" style="cursor:pointer;font-size:1.5rem;">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="agencyRatingInput" value="<?= $oldR ?: 5 ?>" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Testimoni <span class="text-danger">*</span></label>
                                <textarea name="testimonial" class="form-control bg-light border-0" rows="4" required placeholder="Tulis testimoni (min. 10 karakter)"><?= esc(old('testimonial')) ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Kirim Testimoni</button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">Testimoni akan dipublikasikan setelah diverifikasi oleh admin.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header bg-transparent border-0 py-4 px-4 border-bottom">
                        <h5 class="fw-bold text-dark mb-0">Riwayat Kiriman Anda</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($testimonials)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-chat-quote fs-1 opacity-25"></i>
                                <p class="mb-0 mt-2">Belum ada testimoni dikirim.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($testimonials as $t): ?>
                                <div class="list-group-item border-0 py-4 px-4">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="fw-bold text-dark mb-0"><?= esc($t['name']) ?></h6>
                                        <?php if ($t['status'] === 'pending'): ?>
                                            <span class="badge bg-warning bg-opacity-10 text-warning rounded-pill">Menunggu verifikasi</span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill">Dipublikasikan</span>
                                        <?php endif; ?>
                                    </div>
                                    <?php $r = (int)($t['rating'] ?? 5); ?>
                                    <div class="text-warning small mb-1" title="<?= $r ?> bintang"><?= str_repeat('★', $r) ?><?= str_repeat('☆', 5 - $r) ?></div>
                                    <?php if (!empty($t['package_name'])): ?>
                                        <small class="text-secondary d-block mb-1"><?= esc($t['package_name']) ?></small>
                                    <?php endif; ?>
                                    <p class="text-secondary small mb-0"><?= nl2br(esc($t['testimonial'])) ?></p>
                                    <small class="text-muted"><?= date('d M Y', strtotime($t['created_at'])) ?></small>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    var c = document.getElementById('agencyRatingStars');
    var input = document.getElementById('agencyRatingInput');
    if (!c || !input) return;
    var stars = c.querySelectorAll('.star-select');
    function up(v) { var n = parseInt(v, 10) || 0; stars.forEach(function(s, i) { s.style.opacity = (i + 1) <= n ? '1' : '0.35'; }); }
    up(input.value);
    stars.forEach(function(s) { s.addEventListener('click', function() { input.value = this.getAttribute('data-value'); up(input.value); }); });
})();
</script>
<?= $this->endSection() ?>

<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5">
            <h1 class="section-title display-6 mb-2">Testimoni Jamaah</h1>
            <p class="text-secondary">Bagikan pengalaman Anda atau baca testimoni jamaah yang telah berangkat</p>
        </div>

        <div class="row g-4">
            <!-- Form input testimoni (publik) -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-4"><i class="bi bi-pencil-square me-2 text-primary"></i>Kirim Testimoni</h5>
                        <?php if (session()->getFlashdata('msg')): ?>
                            <div class="alert alert-success border-0 small"><?= session()->getFlashdata('msg') ?></div>
                        <?php endif; ?>
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger border-0 small"><?= session()->getFlashdata('error') ?></div>
                        <?php endif; ?>

                        <form action="<?= base_url('testimoni-jamaah/submit') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control input-nama-jamaah-upper" value="<?= esc(old('name')) ?>" required placeholder="Nama lengkap atau inisial" maxlength="255">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Paket (opsional)</label>
                                <select name="package_id" class="form-select">
                                    <option value="">-- Pilih paket --</option>
                                    <?php foreach ($packages as $p): ?>
                                        <option value="<?= $p['id'] ?>" <?= old('package_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Rating <span class="text-danger">*</span></label>
                                <div class="d-flex align-items-center gap-1" id="ratingStarsForm">
                                    <?php $oldRating = (int)old('rating') ?: 0; for ($i = 1; $i <= 5; $i++): ?>
                                        <span class="star-select text-warning" data-value="<?= $i ?>" style="cursor:pointer;font-size:1.5rem;" title="<?= $i ?> bintang">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" name="rating" id="ratingInput" value="<?= $oldRating ?: 5 ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Testimoni <span class="text-danger">*</span></label>
                                <textarea name="testimonial" class="form-control" rows="4" required placeholder="Tulis pengalaman Anda (min. 10 karakter)"><?= esc(old('testimonial')) ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label class="form-label small fw-bold">Captcha <span class="text-danger">*</span></label>
                                <p class="small text-secondary mb-1">Berapa <strong><?= (int)$captcha_a ?> + <?= (int)$captcha_b ?></strong> ?</p>
                                <input type="number" name="captcha_answer" class="form-control" required placeholder="Jawaban" min="0" max="99">
                            </div>
                            <button type="submit" class="btn btn-primary-public w-100 rounded-pill fw-bold py-2">Kirim Testimoni</button>
                        </form>
                        <p class="small text-muted mt-3 mb-0">Testimoni akan ditinjau oleh admin sebelum dipublikasikan.</p>
                    </div>
                </div>
            </div>

            <!-- Daftar testimoni yang sudah diverifikasi -->
            <div class="col-lg-7">
                <h5 class="fw-bold text-dark mb-3">Testimoni yang Dipublikasikan</h5>
                <?php if (empty($testimonials)): ?>
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body py-5 text-center text-muted">
                            <i class="bi bi-chat-quote display-4 opacity-25"></i>
                            <p class="mb-0 mt-2">Belum ada testimoni yang dipublikasikan.</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($testimonials as $t): ?>
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-2">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="fw-bold text-dark mb-0"><?= esc($t['name']) ?></h6>
                                        <small class="text-secondary"><?= esc($t['package_name'] ?? 'Paket Umum') ?></small>
                                        <?php $r = (int)($t['rating'] ?? 5); ?>
                                        <div class="text-warning mt-1" style="font-size:0.9rem;" title="<?= $r ?> bintang"><?= str_repeat('★', $r) ?><?= str_repeat('☆', 5 - $r) ?></div>
                                    </div>
                                </div>
                                <p class="text-secondary mb-0 small"><?= nl2br(esc($t['testimonial'])) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <div class="text-center mt-4">
                    <a href="<?= base_url('login') ?>" class="btn btn-primary-public btn-sm rounded-pill px-4">Login Admin / Agen</a>
                    <a href="<?= base_url() ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-4 ms-2">Beranda</a>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
(function() {
    var container = document.getElementById('ratingStarsForm');
    var input = document.getElementById('ratingInput');
    if (!container || !input) return;
    var stars = container.querySelectorAll('.star-select');
    function updateDisplay(v) {
        var v = parseInt(v, 10) || 0;
        stars.forEach(function(s, i) { s.style.opacity = (i + 1) <= v ? '1' : '0.35'; });
    }
    updateDisplay(input.value);
    stars.forEach(function(star) {
        star.addEventListener('click', function() {
            var v = this.getAttribute('data-value');
            input.value = v;
            updateDisplay(v);
        });
    });
})();
</script>
<?= $this->endSection() ?>

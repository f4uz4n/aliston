<?php helper('branding'); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? get_company_name() ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #c41e3a;
            --primary-dark: #9a1830;
            --secondary: #1a1a2e;
            --light: #f8f9fa;
            --logo-green: #00a651;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; color: #333; }
        .navbar-public { background: #fff; box-shadow: 0 2px 20px rgba(0,0,0,.08); }
        .navbar-wrapper { position: sticky; top: 0; z-index: 1030; }
        .navbar-divider { height: 4px; background: var(--logo-green); box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .navbar-public .nav-link { color: #333 !important; font-weight: 600; padding: 0.5rem 1rem !important; }
        .navbar-public .nav-link:hover { color: var(--primary) !important; }
        .btn-primary-public { background: var(--primary); border-color: var(--primary); color: #fff; font-weight: 600; }
        .btn-primary-public:hover { background: var(--primary-dark); color: #fff; }
        .section-title { font-weight: 800; color: var(--secondary); }
        .hero-gradient { background: linear-gradient(135deg, var(--secondary) 0%, #16213e 50%, var(--primary-dark) 100%); min-height: 70vh; }
        .card-package { border: none; border-radius: 1rem; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,.08); transition: transform .25s; }
        .card-package:hover { transform: translateY(-6px); }
        .card-agency { border: none; border-radius: 1rem; box-shadow: 0 8px 30px rgba(0,0,0,.06); transition: transform .25s; }
        .card-agency:hover { transform: translateY(-4px); }
        .agency-avatar { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #fff; box-shadow: 0 4px 15px rgba(0,0,0,.15); }
        .footer-public { background: var(--secondary); color: rgba(255,255,255,.85); }
        .footer-public a { color: rgba(255,255,255,.9); text-decoration: none; }
        .footer-public a:hover { color: #fff; }
        .section-services { position: relative; overflow: hidden; }
        .section-services .services-overlay { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(255,255,255,0.97) 0%, rgba(248,249,250,0.98) 100%); z-index: 0; }
        .section-services .container { position: relative; z-index: 1; }
        .service-icon-circle { width: 80px; height: 80px; border-radius: 50%; background: var(--primary); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="navbar-wrapper">
    <nav class="navbar navbar-expand-lg navbar-public py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= base_url() ?>">
                <img src="<?= get_company_logo() ?>" alt="Logo" style="height: 42px;">
                <span class="fw-bold text-dark d-none d-md-inline"><?= get_company_name() ?></span>
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <i class="bi bi-list fs-3"></i>
            </button>
            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>#hero">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>#layanan">Layanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>#paket">Paket</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('berita') ?>">Berita</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>#kontak">Kontak</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url() ?>#testimoni">Testimoni Jamaah</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="navbar-divider"></div>
    </div>

    <?= $this->renderSection('content') ?>

    <footer class="footer-public py-5 mt-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                    <img src="<?= get_company_logo() ?>" alt="Logo" style="height: 36px;" class="mb-2">
                    <p class="mb-0 small"><?= get_company_name() ?></p>
                </div>
                <div class="col-md-6 text-center text-md-end small">
                    <a href="<?= base_url('login') ?>">Login Admin / Agen</a> &middot;
                    <span>&copy; <?= date('Y') ?> <?= get_company_name() ?></span>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>

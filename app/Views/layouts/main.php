<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aliston Tour & Travel - Premium Dashboard</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --primary-color: #ef3338;
            --primary-soft: rgba(239, 51, 56, 0.1);
            --primary-dark: #d32f2f;
            --secondary-color: #00a651;
            --secondary-soft: rgba(0, 166, 81, 0.1);
            --bg-body: #f4f7fa;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 85px;
            --topbar-height: 70px;
            --card-shadow: 0 10px 30px 0 rgba(0,0,0,0.05);
            --transition-speed: 0.3s;
        }

        body { 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body);
            color: #1a1a1a;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Sidebar Glassmorphism */
                #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0,0,0,0.05);
            z-index: 1000;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }

        #sidebar::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Logo */
                        .sidebar-brand {
            padding: 1rem 0.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            transition: all var(--transition-speed);
        }

                .sidebar-brand img {
            max-height: 80px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 6px rgba(0,0,0,0.08));
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        }

        .collapsed .sidebar-brand {
            padding: 1rem 0.5rem;
        }

        .collapsed .sidebar-brand img {
            height: 35px;
        }

        /* Navigation Menu */
        .nav-menu {
            list-style: none;
            padding: 0;
            margin: 0;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 0.8rem 1rem;
            color: #64748b;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.2s ease;
            white-space: nowrap;
            font-weight: 500;
            cursor: pointer;
        }

        .nav-link i {
            font-size: 1.4rem;
            margin-right: 1rem;
            min-width: 24px;
            text-align: center;
        }

        .nav-link:hover {
            background: var(--primary-soft);
            color: var(--primary-color);
        }

        .nav-link.active {
            background: var(--primary-color);
            color: #fff;
            box-shadow: 0 8px 16px rgba(239, 51, 56, 0.2);
        }

        .collapsed .nav-link span {
            display: none;
        }

        /* Submenu Styles */
        .nav-submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }

        .nav-submenu.show {
            max-height: 500px;
        }

        .nav-submenu .nav-item {
            margin-bottom: 0.25rem;
        }

        .nav-submenu .nav-link {
            padding: 0.6rem 1rem 0.6rem 3rem;
            font-size: 0.9rem;
        }

        .nav-submenu .nav-link i {
            font-size: 1.2rem;
            margin-right: 0.75rem;
        }

        .nav-menu-toggle {
            margin-left: auto;
            transition: transform 0.3s ease;
            font-size: 0.9rem;
        }

        .nav-menu-toggle.rotated {
            transform: rotate(90deg);
        }

        .collapsed .nav-submenu {
            display: none;
        }

        .nav-menu-parent {
            position: relative;
            cursor: pointer;
        }

        .nav-menu-parent:hover {
            background: var(--primary-soft);
            color: var(--primary-color);
        }

        /* Topbar */
        #topbar {
            height: var(--topbar-height);
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.05);
            z-index: 900;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            padding: 0 2rem;
            justify-content: space-between;
        }

        #topbar.expanded {
            left: var(--sidebar-collapsed-width);
        }

        /* Main Content Area */
        main {
            margin-top: var(--topbar-height);
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - var(--topbar-height));
        }

        main.expanded {
            margin-left: var(--sidebar-collapsed-width);
        }

        /* Cards & Components */
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: var(--card-shadow);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn-premium {
            background: var(--primary-color);
            color: #fff;
            border-radius: 12px;
            padding: 0.7rem 1.5rem;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(239, 51, 56, 0.2);
        }

        .btn-premium:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 51, 56, 0.3);
            color: #fff;
        }

        /* User Profile Dropdown */
        .user-profile {
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
        }

        .user-profile:hover {
            background: #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }

        /* Notifikasi */
        #notificationBtn {
            transition: all 0.2s;
        }
        #notificationBtn:hover {
            background: #f0f0f0 !important;
        }
        #notificationIcon {
            transition: color 0.2s;
        }
        #notificationIcon.has-notification {
            color: var(--primary-color, #ef3338);
        }
        #notificationBadge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: translate(-50%, -50%) scale(1); }
            50% { transform: translate(-50%, -50%) scale(1.1); }
        }
        .notification-item {
            padding: 0.75rem;
            border-radius: 0.5rem;
            transition: background 0.2s;
            cursor: pointer;
            text-decoration: none;
            display: block;
            color: inherit;
        }
        .notification-item:hover {
            background: #f8f9fa;
        }
        .notification-item .badge {
            font-size: 0.7rem;
        }

        /* Mobile Responsive */
        @media (max-width: 991.98px) {
            #sidebar {
                left: -100%;
                z-index: 1050;
                box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            }

            #sidebar.mobile-open {
                left: 0;
            }

            #main-content {
                margin-left: 0 !important;
                width: 100% !important;
            }

            #topbar {
                left: 0 !important;
                width: 100% !important;
            }

            /* Overlay untuk mobile */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                backdrop-filter: blur(2px);
            }

            .sidebar-overlay.show {
                display: block;
            }

            /* Pastikan sidebar tidak collapsed di mobile */
            #sidebar.collapsed {
                width: var(--sidebar-width);
            }

            /* Pastikan hamburger button selalu terlihat di mobile */
            #toggle-sidebar {
                display: block !important;
            }

            /* Perbaiki padding untuk mobile */
            #sidebar {
                padding: 1rem 0.75rem;
            }

            .sidebar-brand {
                padding: 0.75rem 0.5rem 1.5rem !important;
            }

            .nav-link {
                padding: 0.7rem 0.75rem;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <?php if(session()->get('isLoggedIn')): ?>
    <!-- Sidebar Overlay untuk Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-brand">
            <img src="<?= get_company_logo() ?>" alt="Aliston Logo">
        </div>

        <ul class="nav-menu">
            <?php if(session()->get('role') == 'owner'): ?>
                <li class="nav-item">
                    <a href="<?= base_url('owner') ?>" class="nav-link <?= current_url() == base_url('owner') ? 'active' : '' ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Layanan -->
                <li class="nav-item border-top my-2 pt-2">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'layanan')">
                        <i class="bi bi-briefcase-fill"></i>
                        <span>Layanan</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-layanan"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-layanan">
                        <li class="nav-item">
                            <a href="<?= base_url('package') ?>" class="nav-link <?= strpos(current_url(), 'package') !== false ? 'active' : '' ?>">
                                <i class="bi bi-briefcase-fill"></i>
                                <span>Paket Perjalanan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/materials') ?>" class="nav-link <?= strpos(current_url(), 'materials') !== false ? 'active' : '' ?>">
                                <i class="bi bi-cloud-arrow-up-fill"></i>
                                <span>Materi Promosi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/tabungan') ?>" class="nav-link <?= strpos(current_url(), 'owner/tabungan') !== false ? 'active' : '' ?>">
                                <i class="bi bi-safe2"></i>
                                <span>Tabungan Jamaah</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Daftar Jamaah -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'daftar-jamaah')">
                        <i class="bi bi-people-fill"></i>
                        <span>Daftar Jamaah</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-daftar-jamaah"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-daftar-jamaah">
                        <li class="nav-item">
                            <a href="<?= base_url('owner/participant') ?>" class="nav-link <?= (strpos(current_url(), 'owner/participant') !== false && strpos(current_url(), 'documents') === false && strpos(current_url(), 'equipment') === false && strpos(current_url(), 'kelola') === false && strpos(current_url(), 'cancellations') === false && strpos(current_url(), 'cancel-form') === false && strpos(current_url(), 'boarding-list') === false && strpos(current_url(), 'register') === false) ? 'active' : '' ?>">
                                <i class="bi bi-people-fill"></i>
                                <span>Semua Jamaah</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/participant/documents') ?>" class="nav-link <?= strpos(current_url(), 'documents') !== false ? 'active' : '' ?>">
                                <i class="bi bi-file-earmark-check-fill"></i>
                                <span>Kelengkapan Berkas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/payment-verification') ?>" class="nav-link <?= strpos(current_url(), 'payment-verification') !== false ? 'active' : '' ?>">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Verifikasi Pembayaran</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/participant/cancellations') ?>" class="nav-link <?= strpos(current_url(), 'cancellations') !== false || strpos(current_url(), 'cancel-form') !== false ? 'active' : '' ?>">
                                <i class="bi bi-x-circle-fill"></i>
                                <span>Pembatalan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/participant/boarding-list') ?>" class="nav-link <?= strpos(current_url(), 'boarding-list') !== false ? 'active' : '' ?>">
                                <i class="bi bi-airplane-engines-fill"></i>
                                <span>Boarding</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Manajemen -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'manajemen')">
                        <i class="bi bi-gear-fill"></i>
                        <span>Manajemen</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-manajemen"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-manajemen">
                        <li class="nav-item">
                            <a href="<?= base_url('owner/cities') ?>" class="nav-link <?= strpos(current_url(), 'owner/cities') !== false ? 'active' : '' ?>">
                                <i class="bi bi-geo-alt"></i>
                                <span>Data Kota</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/hotels') ?>" class="nav-link <?= strpos(current_url(), 'owner/hotels') !== false ? 'active' : '' ?>">
                                <i class="bi bi-building"></i>
                                <span>Hotel & Kamar</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/agency') ?>" class="nav-link <?= strpos(current_url(), 'agency') !== false ? 'active' : '' ?>">
                                <i class="bi bi-person-badge-fill"></i>
                                <span>Daftar Agensi</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Keuangan -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'keuangan')">
                        <i class="bi bi-wallet2"></i>
                        <span>Keuangan</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-keuangan"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-keuangan">
                        <li class="nav-item">
                            <a href="<?= base_url('owner/reports') ?>" class="nav-link <?= strpos(current_url(), 'reports') !== false ? 'active' : '' ?>">
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>Laporan Bisnis</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/commissions') ?>" class="nav-link <?= strpos(current_url(), 'commissions') !== false ? 'active' : '' ?>">
                                <i class="bi bi-wallet2"></i>
                                <span>Komisi Agensi</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Perlengkapan -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'perlengkapan')">
                        <i class="bi bi-box-seam-fill"></i>
                        <span>Perlengkapan</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-perlengkapan"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-perlengkapan">
                        <li class="nav-item">
                            <a href="<?= base_url('owner/equipment') ?>" class="nav-link <?= (strpos(current_url(), 'equipment') !== false && strpos(current_url(), 'participants') === false && strpos(current_url(), 'checklist') === false) ? 'active' : '' ?>">
                                <i class="bi bi-box-seam-fill"></i>
                                <span>Master Atribut</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/equipment/participants') ?>" class="nav-link <?= strpos(current_url(), 'equipment/participants') !== false || strpos(current_url(), 'equipment/checklist') !== false ? 'active' : '' ?>">
                                <i class="bi bi-clipboard-check-fill"></i>
                                <span>Pengambilan Atribut</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Utilities -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'utilities')">
                        <i class="bi bi-tools"></i>
                        <span>Utilities</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-utilities"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-utilities">
                        <li class="nav-item">
                            <a href="<?= base_url('owner/banners') ?>" class="nav-link <?= strpos(current_url(), 'owner/banners') !== false ? 'active' : '' ?>">
                                <i class="bi bi-images"></i>
                                <span>Banner Beranda</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/testimoni') ?>" class="nav-link <?= strpos(current_url(), 'owner/testimoni') !== false ? 'active' : '' ?>">
                                <i class="bi bi-chat-quote"></i>
                                <span>Testimoni Jamaah</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/settings') ?>" class="nav-link <?= strpos(current_url(), 'settings') !== false ? 'active' : '' ?>">
                                <i class="bi bi-gear-fill"></i>
                                <span>Pengaturan Akun</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/print-documents') ?>" class="nav-link <?= strpos(current_url(), 'print-documents') !== false ? 'active' : '' ?>">
                                <i class="bi bi-printer-fill"></i>
                                <span>Cetak Dokumen</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('owner/rubrik-berita') ?>" class="nav-link <?= strpos(current_url(), 'rubrik-berita') !== false ? 'active' : '' ?>">
                                <i class="bi bi-newspaper"></i>
                                <span>Rubrik Berita</span>
                            </a>
                        </li>
                    </ul>
                </li>
                                    <?php else: ?>
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="<?= base_url('agency') ?>" class="nav-link <?= current_url() == base_url('agency') ? 'active' : '' ?>">
                        <i class="bi bi-grid-fill"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Layanan -->
                <li class="nav-item border-top my-2 pt-2">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'layanan-agency')">
                        <i class="bi bi-briefcase-fill"></i>
                        <span>Layanan</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-layanan-agency"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-layanan-agency">
                        <li class="nav-item">
                            <a href="<?= base_url('agency/packages') ?>" class="nav-link <?= strpos(current_url(), 'packages') !== false ? 'active' : '' ?>">
                                <i class="bi bi-airplane-fill"></i>
                                <span>Paket Perjalanan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/materials') ?>" class="nav-link <?= strpos(current_url(), 'agency/materials') !== false ? 'active' : '' ?>">
                                <i class="bi bi-cloud-arrow-down-fill"></i>
                                <span>Materi Promosi</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/tabungan') ?>" class="nav-link <?= strpos(current_url(), 'agency/tabungan') !== false ? 'active' : '' ?>">
                                <i class="bi bi-safe2"></i>
                                <span>Tabungan Jamaah</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Daftar Jamaah -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'daftar-jamaah-agency')">
                        <i class="bi bi-people-fill"></i>
                        <span>Daftar Jamaah</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-daftar-jamaah-agency"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-daftar-jamaah-agency">
                        <li class="nav-item">
                            <a href="<?= base_url('agency/participants') ?>" class="nav-link <?= strpos(current_url(), 'participants') !== false ? 'active' : '' ?>">
                                <i class="bi bi-people-fill"></i>
                                <span>Semua Jamaah</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/cancellations') ?>" class="nav-link <?= strpos(current_url(), 'agency/cancellations') !== false ? 'active' : '' ?>">
                                <i class="bi bi-x-circle"></i>
                                <span>Pembatalan</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/boarding') ?>" class="nav-link <?= strpos(current_url(), 'agency/boarding') !== false ? 'active' : '' ?>">
                                <i class="bi bi-airplane-engines"></i>
                                <span>Boarding</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Menu Laporan -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'laporan-agency')">
                        <i class="bi bi-file-earmark-text-fill"></i>
                        <span>Menu Laporan</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-laporan-agency"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-laporan-agency">
                        <li class="nav-item">
                            <a href="<?= base_url('agency/payments') ?>" class="nav-link <?= strpos(current_url(), 'payments') !== false ? 'active' : '' ?>">
                                <i class="bi bi-wallet2"></i>
                                <span>Laporan Pembayaran</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/income') ?>" class="nav-link <?= strpos(current_url(), 'income') !== false ? 'active' : '' ?>">
                                <i class="bi bi-graph-up-arrow"></i>
                                <span>Laporan Penghasilan</span>
                            </a>
                        </li>
                    </ul>
                </li>

                <!-- Utilities -->
                <li class="nav-item">
                    <a href="#" class="nav-link nav-menu-parent" onclick="toggleSubmenu(event, 'utilities-agency')">
                        <i class="bi bi-tools"></i>
                        <span>Utilities</span>
                        <i class="bi bi-chevron-right nav-menu-toggle" id="toggle-utilities-agency"></i>
                    </a>
                    <ul class="nav-submenu" id="submenu-utilities-agency">
                        <li class="nav-item">
                            <a href="<?= base_url('agency/testimoni') ?>" class="nav-link <?= strpos(current_url(), 'agency/testimoni') !== false ? 'active' : '' ?>">
                                <i class="bi bi-chat-quote"></i>
                                <span>Testimoni Jamaah</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="<?= base_url('agency/profile') ?>" class="nav-link <?= strpos(current_url(), 'agency/profile') !== false ? 'active' : '' ?>">
                                <i class="bi bi-person-gear"></i>
                                <span>Ubah Profil</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <?php endif; ?>
        </ul>

        <div class="mt-auto">
            <a href="<?= base_url('logout') ?>" class="nav-link text-danger">
                <i class="bi bi-box-arrow-left"></i>
                <span>Keluar</span>
            </a>
        </div>
    </aside>

    <!-- Topbar -->
    <header id="topbar">
        <button id="toggle-sidebar" class="btn btn-light rounded-circle p-2">
            <i class="bi bi-list fs-4"></i>
        </button>

        <div class="d-flex align-items-center gap-2">
            <?php if(session()->get('role') == 'owner'): ?>
            <!-- Notifikasi -->
            <div class="dropdown" id="notificationDropdown">
                <button class="btn btn-light rounded-circle p-2 position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBtn" style="width: 40px; height: 40px;">
                    <i class="bi bi-bell fs-5" id="notificationIcon"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationBadge" style="display:none; font-size:0.65rem; padding:0.25rem 0.4rem;">0</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 mt-2" style="min-width: 320px; max-height: 400px; overflow-y: auto;" id="notificationList">
                    <li class="px-3 py-2 border-bottom">
                        <h6 class="mb-0 fw-bold">Notifikasi</h6>
                    </li>
                    <li id="notificationItems" class="p-2">
                        <div class="text-center py-3 text-muted small">Memuat...</div>
                    </li>
                </ul>
            </div>
            <?php endif; ?>

            <div class="dropdown">
                <div class="user-profile d-flex align-items-center" data-bs-toggle="dropdown">
                    <div class="bg-primary-soft text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="d-none d-md-block me-2">
                        <span class="small fw-bold d-block"><?= session()->get('username') ?></span>
                        <span class="text-muted" style="font-size: 0.75rem;"><?= ucfirst(session()->get('role')) ?></span>
                    </div>
                    <i class="bi bi-chevron-down small"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg p-2 rounded-4 mt-2">
                    <li><a class="dropdown-item rounded-3 py-2" href="#"><i class="bi bi-shield-lock me-2"></i>Keamanan</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger rounded-3 py-2" href="<?= base_url('logout') ?>"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Content Area -->
    <main id="main-content">
        <?php if(session()->getFlashdata('msg')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('msg') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if(session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?= $this->renderSection('content') ?>
    </main>

    <script>
        const sidebar = document.getElementById('sidebar');
        const topbar = document.getElementById('topbar');
        const main = document.getElementById('main-content');
        const toggle = document.getElementById('toggle-sidebar');

        toggle.addEventListener('click', () => {
            // Di mobile, toggle mobile menu
            if (window.innerWidth <= 991.98) {
                sidebar.classList.toggle('mobile-open');
                const overlay = document.getElementById('sidebarOverlay');
                if (overlay) {
                    overlay.classList.toggle('show');
                }
            } else {
                // Di desktop, toggle collapsed
                sidebar.classList.toggle('collapsed');
                topbar.classList.toggle('expanded');
                main.classList.toggle('expanded');
            }
        });

        // Tutup sidebar saat klik overlay (mobile)
        const overlay = document.getElementById('sidebarOverlay');
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('mobile-open');
                overlay.classList.remove('show');
            });
        }

        // Tutup sidebar saat resize dari mobile ke desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth > 991.98) {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('show');
            }
        });

        // Fungsi untuk menutup sidebar di mobile
        function closeMobileSidebar() {
            if (window.innerWidth <= 991.98) {
                sidebar.classList.remove('mobile-open');
                if (overlay) overlay.classList.remove('show');
            }
        }

        // Tutup sidebar saat klik link di mobile (menggunakan event delegation)
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 991.98) {
                const navLink = e.target.closest('.nav-link[href]');
                if (navLink && !navLink.classList.contains('nav-menu-parent')) {
                    closeMobileSidebar();
                }
            }
        });

        // Toggle Submenu (untuk semua role) - harus global
        window.toggleSubmenu = function(event, menuId) {
            event.preventDefault();
            event.stopPropagation();
            
            const submenu = document.getElementById('submenu-' + menuId);
            const toggle = document.getElementById('toggle-' + menuId);
            const parent = event.currentTarget.closest('.nav-item');
            
            if (!submenu || !toggle) {
                console.error('Submenu atau toggle tidak ditemukan untuk:', menuId, 'submenu:', submenu, 'toggle:', toggle);
                return;
            }
            
            if (submenu.classList.contains('show')) {
                submenu.classList.remove('show');
                if (parent) parent.classList.remove('active');
                toggle.classList.remove('rotated');
            } else {
                submenu.classList.add('show');
                if (parent) parent.classList.add('active');
                toggle.classList.add('rotated');
            }
        };

        // Auto-expand submenu yang memiliki link aktif
        document.addEventListener('DOMContentLoaded', function() {
            const activeLinks = document.querySelectorAll('.nav-submenu .nav-link.active');
            activeLinks.forEach(function(link) {
                const submenu = link.closest('.nav-submenu');
                if (submenu) {
                    submenu.classList.add('show');
                    const menuId = submenu.id.replace('submenu-', '');
                    const toggle = document.getElementById('toggle-' + menuId);
                    const parent = submenu.closest('.nav-item');
                    if (toggle) toggle.classList.add('rotated');
                    if (parent) parent.classList.add('active');
                }
            });
        });

        <?php if(session()->get('role') == 'owner'): ?>

        // Load Notifikasi
        (function() {
            var badge = document.getElementById('notificationBadge');
            var items = document.getElementById('notificationItems');
            var icon = document.getElementById('notificationIcon');

            function updateNotificationUI(notif) {
                if (!notif) {
                    badge.style.display = 'none';
                    icon.classList.remove('has-notification');
                    items.innerHTML = '<div class="text-center py-4 text-muted">' +
                        '<i class="bi bi-bell-slash display-6 d-block mb-2 opacity-50"></i>' +
                        '<span class="small">Notifikasi kosong</span>' +
                        '</div>';
                    return;
                }

                var total = parseInt(notif.total || 0, 10);
                console.log('Updating UI with total:', total); // Debug

                // Update badge dan icon color
                if (total > 0) {
                    badge.textContent = total > 99 ? '99+' : total.toString();
                    badge.style.display = 'block';
                    icon.classList.add('has-notification');
                } else {
                    badge.style.display = 'none';
                    icon.classList.remove('has-notification');
                }

                // Update dropdown content
                var html = '';
                if (total === 0) {
                    html = '<div class="text-center py-4 text-muted">' +
                        '<i class="bi bi-bell-slash display-6 d-block mb-2 opacity-50"></i>' +
                        '<span class="small">Notifikasi kosong</span>' +
                        '</div>';
                } else {
                    if (notif.participants && parseInt(notif.participants.count || 0, 10) > 0) {
                        html += '<a href="' + notif.participants.url + '" class="notification-item d-flex align-items-center justify-content-between mb-2">' +
                            '<div><i class="bi bi-people text-warning me-2"></i><span class="small">' + notif.participants.label + '</span></div>' +
                            '<span class="badge bg-warning text-dark">' + notif.participants.count + '</span>' +
                            '</a>';
                    }
                    if (notif.payments && parseInt(notif.payments.count || 0, 10) > 0) {
                        html += '<a href="' + notif.payments.url + '" class="notification-item d-flex align-items-center justify-content-between mb-2">' +
                            '<div><i class="bi bi-wallet2 text-info me-2"></i><span class="small">' + notif.payments.label + '</span></div>' +
                            '<span class="badge bg-info">' + notif.payments.count + '</span>' +
                            '</a>';
                    }
                    if (notif.deposits && parseInt(notif.deposits.count || 0, 10) > 0) {
                        html += '<a href="' + notif.deposits.url + '" class="notification-item d-flex align-items-center justify-content-between mb-2">' +
                            '<div><i class="bi bi-safe2 text-success me-2"></i><span class="small">' + notif.deposits.label + '</span></div>' +
                            '<span class="badge bg-success">' + notif.deposits.count + '</span>' +
                            '</a>';
                    }
                }
                items.innerHTML = html || '<div class="text-center py-4 text-muted"><span class="small">Notifikasi kosong</span></div>';
            }

            function loadNotifications() {
                fetch('<?= base_url('owner/notifications') ?>')
                    .then(r => {
                        if (!r.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return r.json();
                    })
                    .then(data => {
                        console.log('Notification data received:', data); // Debug
                        console.log('Data status:', data.status); // Debug
                        console.log('Data notifications:', data.notifications); // Debug
                        
                        if (data && data.status === 'success' && data.notifications) {
                            console.log('Total notifications:', data.notifications.total); // Debug
                            console.log('Deposits count:', data.notifications.deposits ? data.notifications.deposits.count : 'N/A'); // Debug
                            console.log('Participants count:', data.notifications.participants ? data.notifications.participants.count : 'N/A'); // Debug
                            console.log('Payments count:', data.notifications.payments ? data.notifications.payments.count : 'N/A'); // Debug
                            updateNotificationUI(data.notifications);
                        } else {
                            console.log('No notifications or error - data:', data); // Debug
                            updateNotificationUI(null);
                        }
                    })
                    .catch(err => {
                        console.error('Error loading notifications:', err); // Debug
                        updateNotificationUI(null);
                    });
            }

            // Load immediately
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadNotifications);
            } else {
                loadNotifications();
            }
            
            // Refresh setiap 60 detik
            setInterval(loadNotifications, 60000);
        })();
        <?php endif; ?>
    </script>
    <?php else: ?>
        <!-- Public Area (Login Page fallback) -->
        <div class="main-content container py-5">
            <?= $this->renderSection('content') ?>
        </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Format Rupiah: input dengan class .format-rupiah tampil format Indonesia (1.500.000), submit nilai angka -->
    <script>
    (function() {
        function formatRupiahInput(el) {
            var v = (el.value || '').replace(/\D/g, '');
            if (v === '') { el.value = ''; el.dataset.value = '0'; return; }
            el.dataset.value = v;
            var n = parseInt(v, 10);
            if (isNaN(n)) n = 0;
            el.value = n.toLocaleString('id-ID', { maximumFractionDigits: 0 });
        }
        function initFormatRupiah() {
            document.querySelectorAll('.format-rupiah').forEach(function(el) {
                if (el.dataset.rupiahInit) return;
                el.dataset.rupiahInit = '1';
                if (el.value && /^\d+$/.test(el.value.replace(/\D/g, ''))) {
                    el.dataset.value = el.value.replace(/\D/g, '');
                    formatRupiahInput(el);
                } else if (el.dataset.value !== undefined) {
                    el.value = (parseInt(el.dataset.value, 10) || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 });
                }
            });
        }
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('format-rupiah')) formatRupiahInput(e.target);
        }, true);
        document.addEventListener('blur', function(e) {
            if (e.target.classList.contains('format-rupiah')) formatRupiahInput(e.target);
        }, true);
        document.addEventListener('submit', function(e) {
            var form = e.target;
            if (form.tagName === 'FORM') {
                form.querySelectorAll('.format-rupiah').forEach(function(el) {
                    el.value = el.dataset.value !== undefined && el.dataset.value !== '' ? el.dataset.value : (el.value || '').replace(/\D/g, '') || '0';
                });
            }
        }, true);
        if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initFormatRupiah);
        else initFormatRupiah();
    })();
    </script>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- jQuery (required for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>





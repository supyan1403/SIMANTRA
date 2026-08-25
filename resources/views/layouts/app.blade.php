<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMANTRA - {{ $title ?? 'Sistem Monitoring Alokasi Pekerjaan & Honor Mitra' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_kendi_trans.png') }}">
    <script>
        // Anti-BFCache: Force server revalidation on Back/Forward browser navigation after logout
        window.addEventListener('pageshow', function (event) {
            if (event.persisted || (window.performance && window.performance.getEntriesByType && window.performance.getEntriesByType('navigation')[0] && window.performance.getEntriesByType('navigation')[0].type === 'back_forward')) {
                window.location.reload(true);
            }
        });
    </script>
    
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        :root {
            --bs-body-font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            --sidebar-width: 250px;
            --sidebar-collapsed-width: 68px;
            --bg-slate: #f8fafc;
            --sidebar-bg: #0f172a;
        }

        body {
            font-family: var(--bs-body-font-family);
            background-color: var(--bg-slate);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            font-size: 0.875rem;
            margin: 0;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            color: #ffffff;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Brand Header */
        .sidebar-brand {
            padding: 0 1.15rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
            height: 64px;
            flex-shrink: 0;
        }

        #sidebar.collapsed .sidebar-brand {
            padding: 0;
            justify-content: center;
        }

        .brand-logo-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #ffffff;
            overflow: hidden;
            cursor: pointer;
        }

        .brand-kendi-sketch-icon {
            width: 36px;
            height: 36px;
            border-radius: 0.55rem;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: transform 0.15s ease;
        }

        .brand-logo-wrapper:hover .brand-kendi-sketch-icon {
            transform: scale(1.05);
        }

        .brand-text {
            font-weight: 800;
            font-size: 1.15rem;
            letter-spacing: -0.4px;
            color: #ffffff;
            line-height: 1;
        }

        .brand-subtext {
            font-size: 0.65rem;
            color: #94a3b8;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            margin-top: 0.2rem;
        }

        #sidebar.collapsed .brand-text-container,
        #sidebar.collapsed .sidebar-group-title,
        #sidebar.collapsed .nav-text,
        #sidebar.collapsed .user-info-text,
        #sidebar.collapsed .toggle-btn-desktop,
        #sidebar.collapsed .sidebar-copyright-text {
            display: none !important;
        }

        .toggle-btn-desktop {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            width: 28px;
            height: 28px;
            border-radius: 0.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .toggle-btn-desktop:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
        }

        /* Sidebar Navigation Links */
        .sidebar-menu {
            padding: 1rem 0.75rem;
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        #sidebar.collapsed .sidebar-menu {
            padding: 1rem 0.4rem;
        }

        .sidebar-group-title {
            font-size: 0.675rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.5rem 0.85rem 0.35rem 0.85rem;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 0.6rem;
            transition: all 0.2s ease;
            white-space: nowrap;
            margin-bottom: 0.25rem;
        }

        #sidebar.collapsed .sidebar-link {
            justify-content: center;
            padding: 0.65rem 0;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            color: #64748b;
            transition: color 0.2s ease, transform 0.2s ease;
            flex-shrink: 0;
            width: 22px;
            text-align: center;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-link:hover i {
            color: #60a5fa;
            transform: scale(1.1);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        /* Sidebar User Profile Bottom Card */
        .sidebar-footer {
            padding: 0.85rem 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.25);
            display: flex;
            flex-direction: column;
            gap: 0.4rem;
            flex-shrink: 0;
        }

        #sidebar.collapsed .sidebar-footer {
            padding: 0.75rem 0.25rem;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            width: 100%;
        }

        #sidebar.collapsed .user-card {
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
            align-items: center;
        }

        .logout-btn-sidebar {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f87171;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            transition: all 0.2s ease;
        }

        .logout-btn-sidebar:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
        }

        .user-avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 0.55rem;
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #60a5fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .sidebar-copyright-text {
            font-size: 0.675rem;
            color: #64748b;
            text-align: center;
            margin-top: 0.25rem;
        }

        /* Ensure Dropdown Select Arrow never covers text */
        .form-select, .form-select-sm {
            padding-right: 2.25rem !important;
        }

        /* Main Content Wrapper */
        #content-wrapper {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: calc(100% - var(--sidebar-width));
        }

        #content-wrapper.expanded {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }

        /* Top Header Bar */
        .top-header-bar {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
            height: 64px;
        }

        .main-container {
            max-width: 1240px;
            width: 100%;
            margin: 0 auto;
            padding: 1.15rem 1.5rem;
        }

        /* Base Card Styling */
        .card {
            border: 1px solid #e2e8f0;
            border-radius: 0.85rem;
            box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.04);
            background: #ffffff;
        }

        .card-header {
            background-color: transparent;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.85rem 1.2rem;
            font-weight: 700;
            font-size: 0.95rem;
            color: #0f172a;
        }

        /* Stat Metric Cards */
        .metric-card {
            border: none !important;
            border-radius: 0.85rem;
            overflow: hidden;
            position: relative;
        }

        .metric-card .card-body {
            padding: 1.25rem;
            position: relative;
            z-index: 2;
        }

        .metric-card-primary { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%) !important; color: #ffffff !important; }
        .metric-card-success { background: linear-gradient(135deg, #047857 0%, #10b981 100%) !important; color: #ffffff !important; }
        .metric-card-warning { background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%) !important; color: #ffffff !important; }
        .metric-card-danger  { background: linear-gradient(135deg, #991b1b 0%, #ef4444 100%) !important; color: #ffffff !important; }
        .metric-card-purple  { background: linear-gradient(135deg, #6d28d9 0%, #a855f7 100%) !important; color: #ffffff !important; }


        .metric-icon-bg {
            width: 44px;
            height: 44px;
            border-radius: 0.65rem;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #ffffff;
        }

        /* Compact Table Styling */
        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table thead th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 0.65rem 0.8rem;
            border-bottom: 1px solid #cbd5e1;
            white-space: nowrap;
        }

        .table tbody td {
            padding: 0.6rem 0.8rem;
            font-size: 0.85rem;
            color: #334155;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-hover tbody tr:hover {
            background-color: #f8fafc;
        }

        /* Action Buttons */
        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            padding: 0.25rem 0.55rem;
            border-radius: 0.35rem;
            line-height: 1.2;
            text-decoration: none;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-action-edit {
            color: #b45309;
            background-color: #fef3c7;
            border: 1px solid #fde68a;
        }

        .btn-action-edit:hover {
            color: #78350f;
            background-color: #fde68a;
        }

        .btn-action-delete {
            color: #dc2626;
            background-color: #fee2e2;
            border: 1px solid #fca5a5;
        }

        .btn-action-delete:hover {
            color: #991b1b;
            background-color: #fca5a5;
        }

        /* Pagination */
        .pagination {
            margin-bottom: 0;
            gap: 2px;
        }

        .pagination .page-item .page-link {
            border-radius: 0.35rem;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.25rem 0.65rem;
        }

        .pagination .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .btn {
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 0.45rem;
            padding: 0.45rem 0.85rem;
        }

        .btn-primary {
            background: #2563eb;
            border: none;
        }

        .btn-primary:hover {
            background: #1d4ed8;
        }

        .form-control, .form-select {
            border-radius: 0.45rem;
            border: 1px solid #cbd5e1;
            padding: 0.45rem 0.75rem;
            font-size: 0.85rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .badge {
            font-weight: 600;
            font-size: 0.725rem;
            padding: 0.35em 0.65em;
            border-radius: 0.35rem;
        }

        .badge-soft-primary { background: #dbeafe; color: #1e40af; }
        .badge-soft-success { background: #d1fae5; color: #065f46; }
        .badge-soft-warning { background: #fef3c7; color: #92400e; }
        .badge-soft-info { background: #e0f2fe; color: #075985; }
        .badge-soft-danger { background: #fee2e2; color: #991b1b; }

        .page-header {
            margin-bottom: 1.15rem;
        }

        .page-title {
            font-weight: 800;
            font-size: 1.25rem;
            color: #0f172a;
            letter-spacing: -0.3px;
        }

        .page-subtitle {
            font-size: 0.825rem;
            color: #64748b;
            margin-top: 0.1rem;
        }

        @media (max-width: 991.98px) {
            #sidebar {
                left: -250px;
            }
            #sidebar.mobile-open {
                left: 0;
            }
            #content-wrapper {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Sleek Left Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo-wrapper" id="brandLogoWrapper" title="SIMANTRA BPS (Klik untuk toggle sidebar)">
                <div class="brand-kendi-sketch-icon">
                    <img src="{{ asset('images/logo_kendi_trans.png') }}" alt="SIMANTRA" style="width: 28px; height: 28px; object-fit: contain;">
                </div>
                <div class="brand-text-container">
                    <div class="brand-text">SIMANTRA</div>
                    <div class="brand-subtext">BPS KAB. TASIKMALAYA</div>
                </div>
            </div>
            <button class="toggle-btn-desktop" id="sidebarToggleBtn" title="Ciutkan Sidebar">
                <i class="bi bi-chevron-left" id="toggleIcon"></i>
            </button>
        </div>

        <div class="sidebar-menu">
            <div class="sidebar-group-title">Menu Utama</div>
            
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-grid-fill"></i>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="{{ route('mitra.index') }}" class="sidebar-link {{ request()->routeIs('mitra.*') ? 'active' : '' }}" title="Data Mitra">
                <i class="bi bi-people-fill"></i>
                <span class="nav-text">Data Mitra</span>
            </a>

            <a href="{{ route('kegiatan.index') }}" class="sidebar-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" title="Mata Anggaran">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span class="nav-text">Mata Anggaran</span>
            </a>

            <a href="{{ route('periode.index') }}" class="sidebar-link {{ request()->routeIs('periode.*') ? 'active' : '' }}" title="Periode">
                <i class="bi bi-calendar3"></i>
                <span class="nav-text">Periode</span>
            </a>

            <a href="{{ route('monitoring.index') }}" class="sidebar-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}" title="Monitoring Honor">
                <i class="bi bi-eye-fill"></i>
                <span class="nav-text">Monitoring Honor</span>
            </a>

            <a href="{{ route('rekap.index') }}" class="sidebar-link {{ request()->routeIs('rekap.*') ? 'active' : '' }}" title="Rekap Tahunan">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span class="nav-text">Rekap Tahunan</span>
            </a>

            <a href="{{ route('spk.index') }}" class="sidebar-link {{ request()->routeIs('spk.*') ? 'active' : '' }}" title="Cetak SPK">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                <span class="nav-text">Cetak SPK</span>
            </a>

            <div class="sidebar-group-title mt-3">Alat & Pengaturan</div>

            <a href="{{ route('import.index') }}" class="sidebar-link {{ request()->routeIs('import.*') ? 'active' : '' }}" title="Import Excel">
                <i class="bi bi-cloud-arrow-up-fill"></i>
                <span class="nav-text">Import Excel MANTRA</span>
            </a>

            @if(auth()->user()?->role === 'admin')
            <a href="{{ route('master-sbml.index') }}" class="sidebar-link {{ request()->routeIs('master-sbml.*') ? 'active' : '' }}" title="Master Batas Honor (SBML)">
                <i class="bi bi-piggy-bank-fill"></i>
                <span class="nav-text">Master SBML</span>
            </a>

            <a href="{{ route('pengaturan.index') }}" class="sidebar-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}" title="Pengaturan User">
                <i class="bi bi-gear-fill"></i>
                <span class="nav-text">Pengaturan User</span>
            </a>
            @endif
        </div>

        <div class="sidebar-footer">
            <div class="user-card">
                <div class="user-avatar-circle overflow-hidden p-0">
                    @if(auth()->user()->foto_profil_url)
                        <img src="{{ auth()->user()->foto_profil_url }}" alt="{{ auth()->user()->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    @endif
                </div>
                <div class="user-info-text overflow-hidden me-auto">
                    <div class="fw-bold text-white text-truncate small" style="max-width: 120px;" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</div>
                    <span class="badge {{ auth()->user()->role === 'admin' ? 'badge-soft-danger' : 'badge-soft-success' }} py-0.5 px-1.5" style="font-size: 0.65rem;">
                        {{ auth()->user()->role === 'admin' ? 'ADMINISTRATOR' : 'OPERATOR' }}
                    </span>
                </div>
                <button type="button" class="logout-btn-sidebar border-0" data-bs-toggle="modal" data-bs-target="#modalConfirmLogout" title="Logout dari Sistem">
                    <i class="bi bi-box-arrow-right fs-6"></i>
                </button>
            </div>
            <div class="sidebar-copyright-text">
                &copy; {{ date('Y') }} BPS Kab. Tasikmalaya
            </div>
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Clean Top Header Bar -->
        <header class="top-header-bar">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" id="mobileMenuBtn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div class="d-flex align-items-center gap-2">
                    <!-- Logo Kendi BPS in Topbar -->
                    <img src="{{ asset('images/logo_kendi_trans.png') }}" alt="SIMANTRA" style="width: 22px; height: 22px; object-fit: contain;">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2.5 py-1">SIMANTRA BPS</span>
                    <span class="text-muted small">/</span>
                    <span class="fw-bold text-dark small">{{ $title ?? 'Sistem Monitoring Alokasi Pekerjaan & Honor Mitra' }}</span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="d-flex align-items-center gap-2 text-muted small">
                    <i class="bi bi-clock me-1 text-primary"></i>
                    <span>{{ date('d M Y') }}</span>
                </div>
            </div>
        </header>

        <!-- Main Body Container -->
        <main class="main-container flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3 py-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-6 text-success"></i>
                    <div class="small fw-semibold">{{ session('success') }}</div>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3 py-2 text-dark" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-warning me-1"></i>
                    <div class="small fw-bold">{{ session('warning') }}</div>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3 py-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-6 text-danger"></i>
                    <div class="small fw-semibold">{{ session('error') }}</div>
                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            @yield('content')
        </main>
    </div>

    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const sidebar = document.getElementById('sidebar');
        const contentWrapper = document.getElementById('content-wrapper');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const brandLogoWrapper = document.getElementById('brandLogoWrapper');
        const toggleIcon = document.getElementById('toggleIcon');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');

        function setCollapsedState(isCollapsed) {
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                contentWrapper.classList.add('expanded');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-right';
            } else {
                sidebar.classList.remove('collapsed');
                contentWrapper.classList.remove('expanded');
                if (toggleIcon) toggleIcon.className = 'bi bi-chevron-left';
            }
            localStorage.setItem('simantra_sidebar_collapsed', isCollapsed);
        }

        const savedState = localStorage.getItem('simantra_sidebar_collapsed') === 'true';
        setCollapsedState(savedState);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isCollapsed = !sidebar.classList.contains('collapsed');
                setCollapsedState(isCollapsed);
            });
        }

        if (brandLogoWrapper) {
            brandLogoWrapper.addEventListener('click', function(e) {
                if (sidebar.classList.contains('collapsed')) {
                    e.preventDefault();
                    setCollapsedState(false);
                }
            });
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.toggle('mobile-open');
            });
        }

        // Global Animated Counter Effect (Ultra Smooth Ease-Out 1.4s)
        function initAnimatedCounters() {
            const counterElements = document.querySelectorAll('[data-counter-value]');
            counterElements.forEach(el => {
                const rawVal = parseFloat(el.getAttribute('data-counter-value')) || 0;
                const prefix = el.getAttribute('data-counter-prefix') || '';
                const isNegative = rawVal < 0;
                const targetVal = Math.abs(rawVal);
                const duration = 1400;
                const startTime = performance.now();

                function step(currentTime) {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeProgress = 1 - Math.pow(1 - progress, 4);
                    const currentVal = Math.floor(easeProgress * targetVal);

                    const formatted = new Intl.NumberFormat('id-ID').format(currentVal);
                    const sign = isNegative ? '-' : '';
                    el.textContent = `${prefix}${sign}${formatted}`;

                    if (progress < 1) {
                        requestAnimationFrame(step);
                    } else {
                        const finalFormatted = new Intl.NumberFormat('id-ID').format(targetVal);
                        el.textContent = `${prefix}${sign}${finalFormatted}`;
                    }
                }
                requestAnimationFrame(step);
            });
        }
        initAnimatedCounters();
    });
    </script>
    <!-- Modal Konfirmasi Logout (Executive Centered Dialog) -->
    <div class="modal fade" id="modalConfirmLogout" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; overflow: hidden; background: #ffffff;">
                <div class="modal-body p-4 text-center">
                    <div class="mx-auto mb-3 rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center shadow-sm" style="width: 58px; height: 58px;">
                        <i class="bi bi-box-arrow-right fs-3"></i>
                    </div>
                    
                    <h5 class="fw-extrabold text-dark mb-2" style="font-size: 1.2rem; letter-spacing: -0.3px;">Konfirmasi Keluar</h5>
                    <p class="text-secondary small mb-1" style="line-height: 1.5;">
                        Apakah Anda yakin ingin keluar (<em>logout</em>) dari akun<br><strong class="text-dark">{{ auth()->user()->name ?? 'Pengguna' }}</strong>?
                    </p>
                    <span class="text-muted extra-small d-block mb-4">Sesi login Anda akan diakhiri secara aman.</span>

                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-light border text-secondary fw-semibold rounded-pill py-2 w-50" data-bs-dismiss="modal" style="font-size: 0.875rem;">
                            Batal
                        </button>
                        <form method="POST" action="{{ route('logout') }}" class="w-50 m-0">
                            @csrf
                            <button type="submit" class="btn btn-danger fw-bold rounded-pill py-2 w-100 shadow-sm" style="font-size: 0.875rem; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); border: none;">
                                <i class="bi bi-box-arrow-right me-1"></i> Ya, Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
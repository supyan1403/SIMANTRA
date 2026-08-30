<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIMANTRA - {{ $title ?? 'Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra' }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo_kendi_trans.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_kendi_trans.png') }}">
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
            --sidebar-width: 270px;
            --sidebar-collapsed-width: 72px;
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
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            overflow: hidden;
        }

        #sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Sidebar Brand Header */
        .sidebar-brand {
            padding: 10px 8px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            white-space: nowrap;
            height: 74px;
            flex-shrink: 0;
            width: 100%;
            position: relative;
            box-sizing: border-box;
        }

        #sidebar.collapsed .sidebar-brand {
            padding: 10px 8px;
        }

        .brand-logo-wrapper {
            display: flex;
            align-items: center;
            width: 100%;
            height: 100%;
            text-decoration: none;
            color: #ffffff;
            overflow: hidden;
            cursor: pointer;
        }

        .brand-kendi-sketch-icon {
            width: 42px;
            min-width: 42px;
            max-width: 42px;
            height: 42px;
            margin: 0 7px;
            border-radius: 0.65rem;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .brand-logo-wrapper:hover .brand-kendi-sketch-icon {
            transform: scale(1.06);
        }

        .brand-text-container {
            margin-left: 8px;
            opacity: 1;
            overflow: hidden;
            white-space: nowrap;
            transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1);
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

        /* Collapsible text elements: pure opacity (clipping box architecture) */
        .sidebar-group-title,
        .nav-text,
        .user-info-text,
        .sidebar-copyright-text {
            opacity: 1;
            white-space: nowrap;
            overflow: hidden;
            transition: opacity 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        #sidebar.collapsed .brand-text-container,
        #sidebar.collapsed .sidebar-group-title,
        #sidebar.collapsed .nav-text,
        #sidebar.collapsed .user-info-text,
        #sidebar.collapsed .sidebar-copyright-text {
            opacity: 0;
            pointer-events: none;
        }

        .toggle-btn-desktop {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #cbd5e1;
            width: 28px;
            height: 28px;
            border-radius: 0.45rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            z-index: 10;
        }

        .toggle-btn-desktop:hover {
            background: #2563eb;
            color: #ffffff;
            border-color: #2563eb;
            transform: translateY(-50%) scale(1.05);
        }

        #sidebar.collapsed .toggle-btn-desktop {
            opacity: 0;
            pointer-events: none;
            display: none !important;
        }

        /* Sidebar Navigation Links (Eliminate default scrollbars) */
        .sidebar-menu {
            padding: 0.5rem 0;
            flex-grow: 1;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none; /* Firefox */
            -ms-overflow-style: none; /* IE/Edge */
            width: 100%;
        }

        .sidebar-menu::-webkit-scrollbar {
            display: none;
            width: 0;
            height: 0;
        }

        .sidebar-group-title {
            font-size: 0.675rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 0.6rem 16px 0.25rem 16px;
            height: 26px;
            box-sizing: border-box;
        }

        .sidebar-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.08);
            margin: 0.5rem 8px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0;
            margin: 2px 8px;
            width: calc(100% - 16px);
            color: #94a3b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.85rem;
            border-radius: 0.6rem;
            transition: background 0.2s cubic-bezier(0.4, 0, 0.2, 1), color 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            white-space: nowrap;
            position: relative;
            height: 42px;
            overflow: hidden;
            box-sizing: border-box;
        }

        #sidebar.collapsed .sidebar-link {
            margin: 2px 8px;
            width: calc(100% - 16px);
            padding: 0;
            height: 42px;
        }

        .sidebar-link i {
            font-size: 1.18rem;
            color: #64748b;
            transition: color 0.2s ease, transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            flex-shrink: 0;
            width: 56px;
            min-width: 56px;
            max-width: 56px;
            height: 42px;
            text-align: center;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .sidebar-link:hover i {
            color: #60a5fa;
            transform: scale(1.12);
        }

        .sidebar-link.active {
            color: #ffffff;
            background: #2563eb;
            font-weight: 700;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);
        }

        .sidebar-link.active i {
            color: #ffffff;
        }

        /* Sidebar User Profile Bottom Card - Pure 1-Row Horizontal (No Reflow) */
        .sidebar-footer {
            height: 64px;
            padding: 0 8px;
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(0, 0, 0, 0.25);
            display: flex;
            align-items: center;
            flex-shrink: 0;
            overflow: hidden;
            box-sizing: border-box;
        }

        #sidebar.collapsed .sidebar-footer {
            height: 64px;
            padding: 0 8px;
        }

        .user-card {
            display: flex;
            align-items: center;
            width: 100%;
            height: 100%;
            overflow: hidden;
            white-space: nowrap;
        }

        #sidebar.collapsed .user-card {
            display: flex;
            align-items: center;
            width: 100%;
            height: 100%;
        }

        .user-avatar-circle {
            width: 40px;
            min-width: 40px;
            max-width: 40px;
            height: 40px;
            margin: 0 8px;
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
            cursor: pointer;
        }

        .user-info-text {
            margin-left: 4px;
            overflow: hidden;
            white-space: nowrap;
        }

        .logout-btn-sidebar {
            width: 32px;
            min-width: 32px;
            max-width: 32px;
            height: 32px;
            margin-left: auto;
            margin-right: 4px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #f87171;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            transition: opacity 0.2s ease, background 0.2s ease;
            flex-shrink: 0;
        }

        #sidebar.collapsed .logout-btn-sidebar {
            opacity: 0;
            pointer-events: none;
        }

        .logout-btn-sidebar:hover {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.35);
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
            max-width: 1650px;
            width: 100%;
            margin: 0 auto;
            padding: 1.25rem 1.25rem;
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

        .btn-action-view {
            color: #1d4ed8;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
        }

        .btn-action-view:hover {
            color: #1e40af;
            background-color: #dbeafe;
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
            gap: 4px;
        }

        .pagination .page-item .page-link {
            border-radius: 0.35rem;
            border: 1px solid #cbd5e1;
            color: #334155;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.3rem 0.7rem;
            line-height: 1.4;
        }

        .pagination .page-item.active .page-link {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        .pagination .page-item .page-link:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        .pagination .page-item.disabled .page-link {
            color: #94a3b8;
            background-color: #f8fafc;
            border-color: #e2e8f0;
        }

        /* ================================================= */
        /* GLOBAL BUTTON DESIGN SYSTEM (HARMONIZED PALETTE)  */
        /* ================================================= */
        .btn {
            font-weight: 600;
            border-radius: 0.5rem;
            padding: 0.45rem 0.95rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            line-height: 1.45;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .btn:active {
            transform: scale(0.98);
        }

        /* Primary: Royal Blue (Action Utama / Simpan / Filter / Terapkan) */
        .btn-primary {
            background: #2563eb !important;
            border-color: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }
        .btn-primary:hover, .btn-primary:focus {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.32);
        }

        /* Success: Emerald Green (Ekspor Excel / Tambah Data / Konfirmasi Positif) */
        .btn-success {
            background: #059669 !important;
            border-color: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(5, 150, 105, 0.2);
        }
        .btn-success:hover, .btn-success:focus {
            background: #047857 !important;
            border-color: #047857 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.32);
        }

        /* Danger: Crimson Coral (Cetak Massal / Hapus / Reset Kritis) */
        .btn-danger {
            background: #dc2626 !important;
            border-color: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(220, 38, 38, 0.2);
        }
        .btn-danger:hover, .btn-danger:focus {
            background: #b91c1c !important;
            border-color: #b91c1c !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.32);
        }

        /* Info: Ocean Blue (Unduh PDF / Dokumen) */
        .btn-info {
            background: #0284c7 !important;
            border-color: #0284c7 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(2, 132, 199, 0.2);
        }
        .btn-info:hover, .btn-info:focus {
            background: #0369a1 !important;
            border-color: #0369a1 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.32);
        }

        /* Warning: Warm Amber (Edit Data / Perhatian) */
        .btn-warning {
            background: #d97706 !important;
            border-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px rgba(217, 119, 6, 0.2);
        }
        .btn-warning:hover, .btn-warning:focus {
            background: #b45309 !important;
            border-color: #b45309 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.32);
        }

        /* Secondary & Light: Slate Neutral (Batal / Kembali / Reset Filter) */
        .btn-secondary, .btn-light {
            background: #f8fafc !important;
            border-color: #cbd5e1 !important;
            color: #475569 !important;
        }
        .btn-secondary:hover, .btn-light:hover, .btn-secondary:focus, .btn-light:focus {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            color: #0f172a !important;
        }

        /* Clean Outline Variants */
        .btn-outline-primary {
            color: #2563eb !important;
            border-color: #2563eb !important;
            background: transparent !important;
        }
        .btn-outline-primary:hover, .btn-outline-primary:focus {
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
        }

        .btn-outline-secondary {
            color: #64748b !important;
            border-color: #cbd5e1 !important;
            background: transparent !important;
        }
        .btn-outline-secondary:hover, .btn-outline-secondary:focus {
            background: #f1f5f9 !important;
            color: #1e293b !important;
            border-color: #94a3b8 !important;
        }

        .btn-outline-danger {
            color: #dc2626 !important;
            border-color: #dc2626 !important;
            background: transparent !important;
        }
        .btn-outline-danger:hover, .btn-outline-danger:focus {
            background: #dc2626 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
        }

        .btn-outline-success {
            color: #059669 !important;
            border-color: #059669 !important;
            background: transparent !important;
        }
        .btn-outline-success:hover, .btn-outline-success:focus {
            background: #059669 !important;
            color: #ffffff !important;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }

        .btn-sm {
            padding: 0.32rem 0.65rem;
            font-size: 0.775rem;
            border-radius: 0.375rem;
        }

        .btn-lg {
            padding: 0.65rem 1.35rem;
            font-size: 0.95rem;
            border-radius: 0.55rem;
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
            margin-bottom: 1.25rem;
            padding-left: 0.5rem;
        }

        .page-title {
            font-weight: 800;
            font-size: 1.35rem;
            color: #0f172a;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
        }

        .page-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 0.2rem;
            padding-left: 0.1rem;
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
            <div class="sidebar-group-title">Data & Master</div>
            
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-grid-fill"></i>
                <span class="nav-text">Dashboard</span>
            </a>

            <a href="{{ route('mitra.index') }}" class="sidebar-link {{ request()->routeIs('mitra.*') ? 'active' : '' }}" title="Data Mitra">
                <i class="bi bi-people-fill"></i>
                <span class="nav-text">Data Mitra</span>
            </a>

            <a href="{{ route('kegiatan.index') }}" class="sidebar-link {{ request()->routeIs('kegiatan.*') ? 'active' : '' }}" title="Kegiatan & Anggaran">
                <i class="bi bi-cash-stack"></i>
                <span class="nav-text">Kegiatan & Anggaran</span>
            </a>

            <a href="{{ route('periode.index') }}" class="sidebar-link {{ request()->routeIs('periode.*') ? 'active' : '' }}" title="Periode">
                <i class="bi bi-calendar3"></i>
                <span class="nav-text">Periode</span>
            </a>

            <a href="{{ route('import.index') }}" class="sidebar-link {{ request()->routeIs('import.*') ? 'active' : '' }}" title="Import & Template Excel">
                <i class="bi bi-file-earmark-excel-fill"></i>
                <span class="nav-text">Import &amp; Template Excel</span>
            </a>

            <div class="sidebar-divider"></div>

            <div class="sidebar-group-title">Pemanfaatan & Dokumen</div>

            <a href="{{ route('monitoring.index') }}" class="sidebar-link {{ request()->routeIs('monitoring.*') ? 'active' : '' }}" title="Monitoring Alokasi">
                <i class="bi bi-clipboard-data"></i>
                <span class="nav-text">Monitoring Alokasi</span>
            </a>

            <a href="{{ route('spk.penomoran.index') }}" class="sidebar-link {{ request()->routeIs('spk.penomoran.*') ? 'active' : '' }}" title="Penomoran SPK & BAST">
                <i class="bi bi-hash"></i>
                <span class="nav-text">Penomoran SPK & BAST</span>
            </a>

            <a href="{{ route('spk.index') }}" class="sidebar-link {{ request()->routeIs('spk.index') || request()->routeIs('spk.cetak*') ? 'active' : '' }}" title="Cetak & Unduh Dokumen">
                <i class="bi bi-printer-fill"></i>
                <span class="nav-text">Cetak & Unduh Dokumen</span>
            </a>

            <a href="{{ route('rekap.index') }}" class="sidebar-link {{ request()->routeIs('rekap.*') ? 'active' : '' }}" title="Rekap Tahunan">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span class="nav-text">Rekap Tahunan</span>
            </a>

            @if(auth()->user()?->role === 'admin')
            <div class="sidebar-divider"></div>

            <div class="sidebar-group-title">Pengaturan</div>

            <a href="{{ route('master-sbml.index') }}" class="sidebar-link {{ request()->routeIs('master-sbml.*') ? 'active' : '' }}" title="Master SBML">
                <i class="bi bi-piggy-bank-fill"></i>
                <span class="nav-text">Master SBML</span>
            </a>

            <a href="{{ route('pengaturan.index') }}" class="sidebar-link {{ request()->routeIs('pengaturan.*') ? 'active' : '' }}" title="Pengaturan User">
                <i class="bi bi-gear-fill"></i>
                <span class="nav-text">Pengaturan User</span>
            </a>

            <a href="{{ route('database.index') }}" class="sidebar-link {{ request()->routeIs('database.*') ? 'active' : '' }}" title="Manajemen Database">
                <i class="bi bi-database-fill-gear"></i>
                <span class="nav-text">Manajemen Database</span>
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
        </div>
    </aside>

    <!-- Content Wrapper -->
    <div id="content-wrapper">
        <!-- Mobile Hamburger Trigger (Visible only on mobile screen) -->
        <div class="d-lg-none p-3 pb-0 d-flex align-items-center justify-content-between">
            <button class="btn btn-sm btn-white border shadow-sm rounded-3 d-flex align-items-center gap-2 fw-bold text-dark px-3 py-2" id="mobileMenuBtn">
                <i class="bi bi-list fs-5 text-primary"></i> Menu SIMANTRA
            </button>
            <span class="text-muted extra-small"><i class="bi bi-clock me-1 text-primary"></i>{{ date('d M Y') }}</span>
        </div>

        <!-- Main Body Container -->
        <main class="main-container flex-grow-1">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between mb-3 px-3.5 py-2.5" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <span class="small fw-semibold text-dark">{{ session('success') }}</span>
                    </div>
                    <button type="button" class="btn-close position-static p-2 m-0" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between mb-3 px-3.5 py-2.5 text-dark" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                        <span class="small fw-bold">{{ session('warning') }}</span>
                    </div>
                    <button type="button" class="btn-close position-static p-2 m-0" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between mb-3 px-3.5 py-2.5" role="alert">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-x-circle-fill fs-5 text-danger"></i>
                        <span class="small fw-semibold text-dark">{{ session('error') }}</span>
                    </div>
                    <button type="button" class="btn-close position-static p-2 m-0" data-bs-dismiss="alert" aria-label="Close"></button>
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
        // Universal Auto-dismiss for all alerts after 4 seconds with smooth fade
        const autoDismissAlerts = document.querySelectorAll('.alert-dismissible');
        autoDismissAlerts.forEach(function(alertEl) {
            setTimeout(function() {
                try {
                    const bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                    if (bsAlert) bsAlert.close();
                } catch(e) {
                    alertEl.classList.remove('show');
                    setTimeout(() => alertEl.remove(), 300);
                }
            }, 4000);
        });

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

        // Global Clean GET Form submit: strip empty inputs automatically across all modules
        document.querySelectorAll('form[method="GET"], form[method="get"]').forEach(form => {
            form.addEventListener('submit', function() {
                form.querySelectorAll('input:not([type="submit"]):not([type="hidden"]), select').forEach(input => {
                    if (!input.value || input.value.trim() === '') {
                        input.disabled = true;
                    }
                });
            });
        });
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
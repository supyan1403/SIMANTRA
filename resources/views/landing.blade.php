<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANTRA - Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra BPS Kab. Tasikmalaya</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/logo_kendi_trans.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo_kendi_trans.png') }}">

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js & Chart.js DataLabels Plugin -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 0.875rem;
        }

        .glass-pill {
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 50rem;
            padding: 0.35rem 0.85rem;
            font-size: 0.725rem;
            font-weight: 600;
            backdrop-filter: blur(8px);
        }

        .hero-banner {
            background: linear-gradient(135deg, rgba(11, 19, 41, 0.92) 0%, rgba(30, 41, 59, 0.92) 50%, rgba(15, 23, 42, 0.92) 100%);
            color: #ffffff;
            border-radius: 16px;
            padding: 2.5rem 2.5rem;
            margin-top: 0.5rem;
            margin-bottom: 1.75rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.15);
            position: relative;
            overflow: hidden;
        }

        .math-symbol {
            position: absolute;
            font-size: 2.5rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.25);
            pointer-events: none;
            z-index: 0;
            user-select: none;
            font-family: 'Times New Roman', 'Cambria Math', serif;
        }

        .metric-card {
            border: none !important;
            border-radius: 0.85rem;
            overflow: hidden;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .metric-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0, 0, 0, 0.08);
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
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #ffffff;
        }

        .extra-small {
            font-size: 0.75rem;
        }

        /* Spacing for left stat cards inside 3-bar comparison (Modern Clean Floating Pill Style) */
        .stat-item-card {
            padding: 1.1rem 1.25rem;
            border-radius: 1rem;
            background: #ffffff;
            border: 1px solid #edf2f7;
            box-shadow: 0 4px 12px -2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        .stat-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08);
        }
        .stat-badge-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
        }
        .running-text {
            white-space: nowrap;
            animation: scroll-text 20s linear infinite;
        }
        @keyframes scroll-text {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
    </style>
</head>
<body>

    <!-- MAIN CONTAINER -->
    <div class="container py-4" style="max-width: 1240px;">

        <!-- HERO TITLE -->
        <div class="hero-banner">
            <!-- MATH SYMBOLS BACKGROUND (STATIC) -->
            <span class="math-symbol" style="top: 8%; left: 3%; font-size: 1.6rem;">Σ</span>
            <span class="math-symbol" style="top: 50%; left: 2%; font-size: 1.4rem;">±</span>
            <span class="math-symbol" style="bottom: 35%; left: 8%; font-size: 2rem;">μ</span>
            <span class="math-symbol" style="top: 15%; left: 18%; font-size: 1.5rem;">x̄</span>
            <span class="math-symbol" style="bottom: 15%; left: 20%; font-size: 1.8rem;">Δ</span>
            <span class="math-symbol" style="top: 55%; left: 28%; font-size: 1.6rem;">−</span>
            <span class="math-symbol" style="bottom: 40%; left: 35%; font-size: 2.2rem;">+</span>
            <span class="math-symbol" style="top: 20%; left: 45%; font-size: 1.5rem;">√</span>
            <span class="math-symbol" style="bottom: 12%; left: 42%; font-size: 1.8rem;">%</span>
            <span class="math-symbol" style="top: 45%; right: 5%; font-size: 2.8rem;">σ</span>
            <span class="math-symbol" style="bottom: 35%; right: 12%; font-size: 2rem;">Σ</span>
            <span class="math-symbol" style="top: 10%; right: 20%; font-size: 1.6rem;">μ</span>
            <span class="math-symbol" style="bottom: 15%; right: 25%; font-size: 1.5rem;">x̄</span>
            <span class="math-symbol" style="top: 55%; right: 30%; font-size: 1.8rem;">Δ</span>
            <span class="math-symbol" style="bottom: 45%; right: 38%; font-size: 1.4rem;">±</span>
            <span class="math-symbol" style="top: 8%; right: 42%; font-size: 2.2rem;">+</span>

            <div style="position: relative; z-index: 1;">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('images/logo_kendi_bg.png') }}" alt="Logo SIMANTRA" style="width: 80px; height: 80px; border-radius: 14px;">
                    <div>
                        <h1 class="fw-extrabold text-white mb-1" style="letter-spacing: -0.5px; font-size: 2.35rem; line-height: 1.15;">
                            Selamat Datang di <span style="font-weight: 900 !important; color: #ffffff;">SIMANTRA</span>
                        </h1>
                        <h5 class="fw-semibold mb-0" style="font-size: 1.05rem; color: #93c5fd !important;">
                            Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra<br>
                            <span style="font-size: 0.9rem; font-weight: 500; color: rgba(255,255,255,0.8);">Badan Pusat Statistik Kabupaten Tasikmalaya</span>
                        </h5>
                    </div>
                </div>
                <div class="mt-3 pt-2 border-top border-white border-opacity-10">
                    <div class="running-text fw-semibold" style="font-size: 0.8rem; color: rgba(255,255,255,0.7);">
                        <i class="bi bi-geo-alt-fill me-2"></i>Jl. Garut - Tasikmalaya No.103a, Cintaraja, Kec. Singaparna, Kabupaten Tasikmalaya, Jawa Barat 46417
                    </div>
                </div>
            </div>
        </div>

        <!-- MACRO FILTER BAR WITH DROPDOWN CHECKBOX -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3.5">
                <form method="GET" action="{{ route('landing') }}" id="formFilterLanding" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label text-muted extra-small fw-bold mb-1">TAHUN</label>
                        <div class="input-group input-group-sm">
                            <input type="number" name="tahun" class="form-control" value="{{ $tahun }}" min="2020" max="2099" style="height: 38px;" onchange="submitLandingClean(this.form)">
                        </div>
                    </div>

                    <!-- Filter Dropdown Checkbox Bulan Pencairan -->
                    <div class="col-6 col-md-4">
                        <label class="form-label text-muted extra-small fw-bold mb-1">BULAN PENCAIRAN</label>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-white border text-dark w-100 text-start d-flex justify-content-between align-items-center bg-white shadow-none dropdown-toggle" type="button" id="dropdownBulanBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="height: 38px; border-color: #dee2e6 !important;">
                                <span class="text-truncate text-dark fw-medium" id="labelBulanSelected">
                                    <i class="bi bi-calendar-check text-primary me-1"></i>
                                    @if(count($bulanPencairan) === 12)
                                        1 Tahun Penuh (12 Bulan)
                                    @elseif(count($bulanPencairan) === 1)
                                        {{ $monthOptions[$bulanPencairan[0]] }}
                                    @else
                                        {{ count($bulanPencairan) }} Bulan Terpilih
                                    @endif
                                </span>
                                <span class="badge bg-primary rounded-pill ms-2">{{ count($bulanPencairan) }}</span>
                            </button>
                            <div class="dropdown-menu p-3 shadow-lg" aria-labelledby="dropdownBulanBtn" style="min-width: 300px; max-height: 380px; overflow-y: auto;">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                    <span class="fw-bold small text-dark">Pilih Bulan Pencairan</span>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-link p-0 text-primary text-decoration-none extra-small me-2" id="btnSelectAllMonths">Pilih Semua</button>
                                        <button type="button" class="btn btn-link p-0 text-muted text-decoration-none extra-small" id="btnClearAllMonths">Reset</button>
                                    </div>
                                </div>
                                <div class="row g-2">
                                    @foreach($monthOptions as $num => $nama)
                                    <div class="col-6">
                                        <div class="form-check">
                                            <input class="form-check-input check-bulan-item" type="checkbox" name="bulan_pencairan[]" value="{{ $num }}" id="cbBulan_{{ $num }}" {{ in_array($num, $bulanPencairan) ? 'checked' : '' }}>
                                            <label class="form-check-label small" for="cbBulan_{{ $num }}">
                                                {{ $nama }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="mt-3 pt-2 border-top">
                                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-filter"></i> Terapkan Filter Bulan</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted extra-small fw-bold mb-1">BIDANG</label>
                        <select name="bidang_id" class="form-select form-select-sm" style="height: 38px;" onchange="submitLandingClean(this.form)">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangOptions as $b)
                                <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted extra-small fw-bold mb-1">KEGIATAN</label>
                        <select name="kegiatan_id" class="form-select form-select-sm" style="height: 38px;" onchange="submitLandingClean(this.form)">
                            <option value="">Semua Kegiatan</option>
                            @foreach($kegiatanOptions as $k)
                                <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
        </div>

        <!-- Row 1: Key Financial Indicators -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card metric-card-primary shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Pagu Mata Anggaran</span>
                            <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $paguMataAnggaran }}" data-counter-prefix="Rp ">Rp {{ number_format($paguMataAnggaran, 0, ',', '.') }}</h3>
                            <span class="text-white-50 extra-small">Total pagu kegiatan ({{ $tahun }})</span>
                        </div>
                        <div class="metric-icon-bg"><i class="bi bi-journal-bookmark-fill fs-3"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card metric-card-success shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Realisasi Pengeluaran</span>
                            <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $realisasiHonor }}" data-counter-prefix="Rp ">Rp {{ number_format($realisasiHonor, 0, ',', '.') }}</h3>
                            <span class="text-white-50 extra-small">{{ count($bulanPencairan) }} Bulan Terpilih ({{ $tahun }})</span>
                        </div>
                        <div class="metric-icon-bg"><i class="bi bi-wallet2 fs-3"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card {{ $sisaAnggaran < 0 ? 'metric-card-danger' : 'metric-card-warning' }} shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Sisa Anggaran</span>
                            <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $sisaAnggaran }}" data-counter-prefix="Rp ">Rp {{ number_format($sisaAnggaran, 0, ',', '.') }}</h3>
                            <span class="text-white-50 extra-small">Pagu − Realisasi ({{ $paguMataAnggaran > 0 ? round(($realisasiHonor / $paguMataAnggaran) * 100) : 0 }}% terpakai)</span>
                        </div>
                        <div class="metric-icon-bg"><i class="bi bi-graph-down-arrow fs-3"></i></div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card metric-card metric-card-purple shadow-sm h-100">
                    <div class="card-body p-3.5">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Standar SBML / Bulan</span>
                            <div class="metric-icon-bg"><i class="bi bi-piggy-bank-fill fs-3"></i></div>
                        </div>
                        <div class="d-flex gap-2">
                            <div class="flex-fill rounded-3 p-2 text-center" style="background-color: rgba(255,255,255,0.15);">
                                <span class="text-white-50 extra-small fw-bold d-block" style="font-size: 0.65rem;">PENCACAHAN</span>
                                <h5 class="fw-extrabold text-white mb-0" style="font-size: 1rem;">Rp {{ number_format($sbmlPencacahan, 0, ',', '.') }}</h5>
                            </div>
                            <div class="flex-fill rounded-3 p-2 text-center" style="background-color: rgba(255,255,255,0.15);">
                                <span class="text-white-50 extra-small fw-bold d-block" style="font-size: 0.65rem;">PENGOLAHAN</span>
                                <h5 class="fw-extrabold text-white mb-0" style="font-size: 1rem;">Rp {{ number_format($sbmlPengolahan, 0, ',', '.') }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- VISUALISASI 3 DIAGRAM BATANG MITRA BERDAMPINGAN               -->
        <!-- ============================================================== -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Visualisasi Statistik Kemitraan Kabupaten Tasikmalaya ({{ $tahun }})</h6>
                    <span class="text-muted extra-small">Perbandingan total mitra, mitra yang sudah dipekerjakan, dan yang belum dipekerjakan</span>
                </div>
                <span class="badge bg-light text-dark border"><i class="bi bi-info-circle me-1"></i> Data Agregat Publik</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4">
                        <div class="d-flex flex-column">
                            <!-- Card 1: Total Mitra -->
                            <div class="p-3 rounded-3 bg-light border-start border-4 border-primary mb-3">
                                <span class="text-muted extra-small fw-bold text-uppercase">1. Total Mitra (1 Tahun)</span>
                                <h3 class="fw-extrabold text-primary mb-0 mt-1">{{ number_format($totalMitra) }} <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                                <span class="text-muted extra-small">Total basis data mitra BPS Kab. Tasikmalaya</span>
                            </div>

                            <!-- Card 2: Sudah Dipekerjakan -->
                            <div class="p-3 rounded-3 bg-light border-start border-4 border-success mb-3">
                                <span class="text-muted extra-small fw-bold text-uppercase">2. Sudah Dipekerjakan</span>
                                <h3 class="fw-extrabold text-success mb-0 mt-1">{{ number_format($sudahDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Orang ({{ $totalMitra > 0 ? round(($sudahDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%)</span></h3>
                                <span class="text-muted extra-small">Memiliki alokasi honor pada filter terpilih</span>
                            </div>

                            <!-- Card 3: Belum Dipekerjakan -->
                            <div class="p-3 rounded-3 bg-light border-start border-4 border-warning">
                                <span class="text-muted extra-small fw-bold text-uppercase">3. Belum Dipekerjakan</span>
                                <h3 class="fw-extrabold text-warning mb-0 mt-1">{{ number_format($belumDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Orang ({{ $totalMitra > 0 ? round(($belumDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%)</span></h3>
                                <span class="text-muted extra-small">Belum mendapat alokasi tugas pada rentang ini</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div style="min-height: 280px; height: 280px;">
                            <canvas id="chartMitra3BatangCanvas" style="width: 100%; height: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 EXECUTIVE FINANCIAL CHARTS WITH DATA LABELS -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Pengeluaran Anggaran Honor per Bulan (Label Angka Nominal)</h6>
                        <span class="text-muted extra-small">{{ count($bulanPencairan) }} Bulan ({{ $tahun }})</span>
                    </div>
                    <div class="card-body p-3" style="min-height: 340px;">
                        <canvas id="chartBulanCanvas" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Proporsi Honor per Bidang</h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" style="min-height: 340px;">
                        <canvas id="chartBidangCanvas" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <footer class="py-4 text-center text-muted extra-small border-top bg-white mt-5">
        <div class="container">
            &copy; {{ date('Y') }} <strong>SIMANTRA</strong> (Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra) - Badan Pusat Statistik Kabupaten Tasikmalaya.
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Chart.js Scripts -->
    <script>
    (function() {
        // Register DataLabels Plugin
        if (typeof ChartDataLabels !== 'undefined') {
            Chart.register(ChartDataLabels);
        }

        // Setup Dropdown Checkbox Quick Actions
        const btnSelectAll = document.getElementById('btnSelectAllMonths');
        const btnClearAll = document.getElementById('btnClearAllMonths');
        const checkItems = document.querySelectorAll('.check-bulan-item');

        if (btnSelectAll) {
            btnSelectAll.addEventListener('click', function() {
                checkItems.forEach(cb => cb.checked = true);
            });
        }
        if (btnClearAll) {
            btnClearAll.addEventListener('click', function() {
                checkItems.forEach(cb => cb.checked = false);
            });
        }

        // Clean & Short Query String URL Handler on Submit
        const filterForm = document.getElementById('formFilterLanding');
        
        window.submitLandingClean = function(formEl) {
            const form = formEl || filterForm;
            if (!form) return;
            cleanAndCompressForm(form);
            form.submit();
        };

        if (filterForm) {
            filterForm.addEventListener('submit', function(e) {
                cleanAndCompressForm(filterForm);
            });
        }

        function cleanAndCompressForm(form) {
            const selectedMonths = Array.from(form.querySelectorAll('.check-bulan-item:checked')).map(cb => parseInt(cb.value)).sort((a,b) => a-b);
            
            // Disable individual checkbox inputs so they don't produce bulan_pencairan%5B%5D=... in URL
            form.querySelectorAll('.check-bulan-item').forEach(cb => cb.disabled = true);
            
            // Strip empty selects
            form.querySelectorAll('select').forEach(sel => {
                if (!sel.value || sel.value.trim() === '') sel.disabled = true;
            });

            // Create compressed single 'bulan' parameter
            if (selectedMonths.length > 0) {
                let compactValue = '';
                if (selectedMonths.length === 12) {
                    compactValue = '1-12';
                } else if (selectedMonths.length > 1 && isConsecutive(selectedMonths)) {
                    compactValue = `${selectedMonths[0]}-${selectedMonths[selectedMonths.length - 1]}`;
                } else {
                    compactValue = selectedMonths.join(',');
                }

                // Remove previous hidden if exists
                form.querySelectorAll('input[name="bulan"]').forEach(el => el.remove());

                let hiddenBulan = document.createElement('input');
                hiddenBulan.type = 'hidden';
                hiddenBulan.name = 'bulan';
                hiddenBulan.value = compactValue;
                form.appendChild(hiddenBulan);
            }
        }

        function isConsecutive(arr) {
            for (let i = 0; i < arr.length - 1; i++) {
                if (arr[i+1] !== arr[i] + 1) return false;
            }
            return true;
        }

        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 100);
                return;
            }

            // 1. Chart 3 Batang Mitra Berdampingan
            const canvasMitra = document.getElementById('chartMitra3BatangCanvas');
            if (canvasMitra) {
                const totalMitraVal = {{ $totalMitra }};
                const sudahVal = {{ $sudahDipekerjakanCount }};
                const belumVal = {{ $belumDipekerjakanCount }};

                new Chart(canvasMitra.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: ['Status Kemitraan ({{ $tahun }})'],
                        datasets: [
                            {
                                label: 'Total Mitra (1 Tahun)',
                                data: [totalMitraVal],
                                backgroundColor: '#2563eb',
                                borderColor: '#1d4ed8',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                barPercentage: 0.75,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Sudah Dipekerjakan',
                                data: [sudahVal],
                                backgroundColor: '#10b981',
                                borderColor: '#059669',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                barPercentage: 0.75,
                                categoryPercentage: 0.85
                            },
                            {
                                label: 'Belum Dipekerjakan',
                                data: [belumVal],
                                backgroundColor: '#f59e0b',
                                borderColor: '#d97706',
                                borderWidth: 1.5,
                                borderRadius: 8,
                                barPercentage: 0.75,
                                categoryPercentage: 0.85
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' }, boxWidth: 14 }
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#1e293b',
                                font: { weight: 'bold', size: 12, family: 'Plus Jakarta Sans' },
                                formatter: function(val) {
                                    return new Intl.NumberFormat('id-ID').format(val) + ' Org';
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.dataset.label + ': ' + new Intl.NumberFormat('id-ID').format(context.raw) + ' Orang';
                                    }
                                }
                            }
                        },
                        layout: {
                            padding: {
                                left: 15,
                                right: 15,
                                top: 20,
                                bottom: 5
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                grace: '15%',
                                ticks: {
                                    font: { family: 'Plus Jakarta Sans', size: 11 },
                                    padding: 8,
                                    callback: (val) => new Intl.NumberFormat('id-ID').format(val)
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Chart Bar Bulan (With Nominal Labels on Top)
            const canvasBulan = document.getElementById('chartBulanCanvas');
            if (canvasBulan) {
                const data = @json($honorPerBulan);
                const labels = data.map(d => d.bulan);
                const values = data.map(d => d.total);
                const ctx = canvasBulan.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 280);
                gradient.addColorStop(0, 'rgba(37, 99, 235, 0.9)');
                gradient.addColorStop(1, 'rgba(37, 99, 235, 0.2)');

                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Realisasi Honor (Rp)',
                            data: values,
                            backgroundColor: gradient,
                            borderColor: '#2563eb',
                            borderWidth: 2,
                            borderRadius: 6,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                color: '#1e3a8a',
                                font: { weight: 'bold', size: 11, family: 'Plus Jakarta Sans' },
                                formatter: function(value) {
                                    if (!value || value === 0) return 'Rp 0';
                                    if (value >= 1000000000) return 'Rp ' + (value / 1000000000).toFixed(2) + ' M';
                                    if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + ' Jt';
                                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Total: Rp ' + new Intl.NumberFormat('id-ID').format(context.raw || 0);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                grace: '18%',
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value >= 1000000 ? (value / 1000000).toFixed(0) + ' Jt' : value);
                                    }
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 3. Chart Doughnut Bidang
            const canvasBidang = document.getElementById('chartBidangCanvas');
            if (canvasBidang) {
                const data = @json($honorPerBidang);
                const labels = data.map(b => b.nama);
                const values = data.map(b => b.total);
                const ctx = canvasBidang.getContext('2d');
                new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: values,
                            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'],
                            borderWidth: 2,
                            borderColor: '#ffffff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: { boxWidth: 10, padding: 10, font: { family: 'Plus Jakarta Sans', size: 11 } }
                            },
                            datalabels: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' ' + context.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.raw || 0);
                                    }
                                }
                            }
                        },
                        cutout: '68%'
                    }
                });
            }
        }

        function initAnimatedCounters() {
            const counterElements = document.querySelectorAll('[data-counter-value]');
            counterElements.forEach(el => {
                const rawVal = parseFloat(el.getAttribute('data-counter-value')) || 0;
                const prefix = el.getAttribute('data-counter-prefix') || '';
                const isNegative = rawVal < 0;
                const targetVal = Math.abs(rawVal);
                const duration = 1200;
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

        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            initCharts();
            initAnimatedCounters();
        } else {
            document.addEventListener('DOMContentLoaded', function() {
                initCharts();
                initAnimatedCounters();
            });
        }
    })();
    </script>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMANTRA - Sistem Monitoring Alokasi Pekerjaan & Honor Mitra BPS Kab. Tasikmalaya</title>

    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 0.875rem;
        }

        .navbar-brand-logo {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1rem;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
        }

        .glass-pill {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 50rem;
            padding: 0.2rem 0.65rem;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .hero-banner {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            border-radius: 20px;
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
            position: relative;
            overflow: hidden;
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .metric-card {
            border: none !important;
            border-radius: 0.85rem;
            overflow: hidden;
            position: relative;
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

        .badge-soft-primary {
            background-color: rgba(37, 99, 235, 0.1);
            color: #2563eb;
        }

        .extra-small {
            font-size: 0.75rem;
        }
    </style>
</head>
<body>

    <!-- NAVBAR PUBLIC -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top py-2 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('landing') }}">
                <div class="p-1 rounded-3 bg-white border shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                    <svg width="28" height="28" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <ellipse cx="50" cy="50" rx="44" ry="20" transform="rotate(-25 50 50)" stroke="#0284c7" stroke-width="2.5" stroke-dasharray="6 3 18 3" fill="none"/>
                        <path d="M40 15 H60 V24 C60 24 74 34 74 58 C74 78 62 88 50 88 C38 88 26 78 26 58 C26 34 40 24 40 24 Z" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none" opacity="0.25"/>
                        <path d="M42 17 H58 V26 C58 26 72 36 72 60 C72 78 60 86 50 86 C40 86 28 78 28 60 C28 36 42 26 42 26 Z" fill="#f0fdf4" stroke="#0f172a" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M65 44 L80 37 V47 L69 51 Z" fill="#f97316" stroke="#0f172a" stroke-width="2.5" stroke-linejoin="round"/>
                        <path d="M31 35 C20 35 18 54 28 62" stroke="#16a34a" stroke-width="3.5" stroke-linecap="round" fill="none"/>
                        <path d="M43 37 H57 L48 48 L57 59 H43" stroke="#0284c7" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                        <path d="M37 71 H45 M41 67 V75" stroke="#16a34a" stroke-width="3" stroke-linecap="round"/>
                        <path d="M55 71 H63 M59 66 A1 1 0 1 1 59 65 M59 76 A1 1 0 1 1 59 75" stroke="#f97316" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                </div>
                <div>
                    <div class="fw-extrabold text-dark lh-1" style="font-size: 1rem; letter-spacing: -0.3px;">SIMANTRA</div>
                    <div class="text-muted extra-small fw-bold text-uppercase mt-0.5" style="font-size: 0.65rem; letter-spacing: 0.5px;">BPS KABUPATEN TASIKMALAYA</div>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2 ms-auto">
                <a href="{{ route('login') }}" class="btn btn-primary btn-sm fw-semibold rounded-pill px-3 py-1.5 shadow-sm" style="font-size: 0.825rem;">
                    <i class="bi bi-box-arrow-in-right me-1"></i> LOGIN ADMIN / OPERATOR
                </a>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTAINER -->
    <div class="container py-4" style="max-width: 1240px;">

        <!-- HERO TITLE -->
        <div class="hero-banner">
            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                <span class="glass-pill d-inline-flex align-items-center" style="background: rgba(37, 99, 235, 0.25); border: 1px solid rgba(59, 130, 246, 0.4); padding: 0.35rem 0.85rem;">
                    <i class="bi bi-shield-check text-info me-2 fs-6"></i><span>Portal Eksekutif Informasi Publik</span>
                </span>
                <span class="glass-pill d-inline-flex align-items-center" style="background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.35); padding: 0.35rem 0.85rem;">
                    <i class="bi bi-broadcast text-success me-2 fs-6"></i><span>Real-time Database {{ number_format($totalMitra) }} Mitra</span>
                </span>
            </div>
            <h2 class="fw-extrabold text-white mb-2" style="letter-spacing: -0.5px;">Sistem Monitoring Alokasi Pekerjaan & Honor Mitra</h2>
            <p class="text-white-50 mb-0" style="font-size: 0.95rem; max-width: 820px;">
                Ringkasan eksekutif pagu kegiatan, realisasi honor mitra, serta rekapitulasi beban kerja per bidang Badan Pusat Statistik Kabupaten Tasikmalaya.
            </p>
        </div>

        <!-- MACRO FILTER BAR -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3.5">
                <form method="GET" action="{{ route('landing') }}" class="row g-2 align-items-end">
                    <div class="col-6 col-md-2">
                        <label class="form-label text-muted extra-small fw-bold mb-1">TAHUN</label>
                        <select name="tahun" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                            @foreach($tahunList as $t)
                                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label text-muted extra-small fw-bold mb-1">BULAN AWAL</label>
                        <select name="bulan_awal" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                            @foreach($monthOptions as $angka => $nm)
                                <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label text-muted extra-small fw-bold mb-1">BULAN AKHIR</label>
                        <select name="bulan_akhir" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                            @foreach($monthOptions as $angka => $nm)
                                <option value="{{ $angka }}" {{ $bulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label text-muted extra-small fw-bold mb-1">BIDANG</label>
                        <select name="bidang_id" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangOptions as $b)
                                <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label text-muted extra-small fw-bold mb-1">KEGIATAN</label>
                        <select name="kegiatan_id" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
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
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Realisasi Honor</span>
                            <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $realisasiHonor }}" data-counter-prefix="Rp ">Rp {{ number_format($realisasiHonor, 0, ',', '.') }}</h3>
                            <span class="text-white-50 extra-small">{{ $monthOptions[$bulanAwal] }} - {{ $monthOptions[$bulanAkhir] }} {{ $tahun }}</span>
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
                    <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                        <div>
                            <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Kapasitas Honor (SBML)</span>
                            <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $paguSBML }}" data-counter-prefix="Rp ">Rp {{ number_format($paguSBML, 0, ',', '.') }}</h3>
                            <span class="text-white-50 extra-small">Total acuan SBML dalam rentang</span>
                        </div>
                        <div class="metric-icon-bg"><i class="bi bi-piggy-bank-fill fs-3"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Operational Counters -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Mitra</span>
                            <h4 class="fw-extrabold text-dark mb-0" data-counter-value="{{ $totalMitra }}">{{ number_format($totalMitra) }}</h4>
                        </div>
                        <i class="bi bi-people-fill fs-3 text-primary"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Transaksi</span>
                            <h4 class="fw-extrabold text-dark mb-0" data-counter-value="{{ $totalTransaksi }}">{{ number_format($totalTransaksi) }}</h4>
                        </div>
                        <i class="bi bi-card-checklist fs-3 text-info"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Total Operator</span>
                            <h4 class="fw-extrabold text-dark mb-0" data-counter-value="{{ $totalOperator }}">{{ number_format($totalOperator) }}</h4>
                        </div>
                        <i class="bi bi-person-badge-fill fs-3 text-success"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-bold text-uppercase">Bidang Aktif</span>
                            <h4 class="fw-extrabold text-dark mb-0">{{ $bidangId ? ($bidangOptions->where('id', $bidangId)->first()->nama ?? '-') : 'Semua' }}</h4>
                        </div>
                        <i class="bi bi-diagram-3-fill fs-3 text-warning"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2 EXECUTIVE CHARTS -->
        <div class="row g-3 mb-4">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up text-primary me-2"></i>Realisasi Honor per Bulan</h6>
                        <span class="text-muted extra-small">{{ $monthOptions[$bulanAwal] }} - {{ $monthOptions[$bulanAkhir] }} {{ $tahun }}</span>
                    </div>
                    <div class="card-body p-3" style="min-height: 320px;">
                        <canvas id="chartBulanCanvas" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart text-primary me-2"></i>Proporsi Honor per Bidang</h6>
                    </div>
                    <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center" style="min-height: 320px;">
                        <canvas id="chartBidangCanvas" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- FOOTER LOG IN CALLOUT -->
        <div class="card border-0 shadow-sm bg-gradient p-4 text-center text-md-start" style="background: linear-gradient(135deg, #f1f5f9, #e2e8f0); border-radius: 16px;">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-dark mb-1"><i class="bi bi-lock-fill text-primary me-1.5"></i>Akses Manajemen Beban Kerja & Cetak SPK</h6>
                    <p class="text-muted small mb-0">Untuk melakukan pencarian detail beban kerja mitra individu, pengalokasian honor, dan pencetakan SPK, silakan login terlebih dahulu.</p>
                </div>
                <a href="{{ route('login') }}" class="btn btn-primary fw-bold rounded-pill px-4 py-2.5 shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1.5"></i> LOGIN ADMIN / OPERATOR
                </a>
            </div>
        </div>

    </div>

    <!-- FOOTER -->
    <footer class="py-4 text-center text-muted extra-small border-top bg-white mt-5">
        <div class="container">
            &copy; {{ date('Y') }} SIMANTRA - BPS Kabupaten Tasikmalaya. All rights reserved.
        </div>
    </footer>

    <!-- Chart.js Scripts -->
    <script>
    (function() {
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 100);
                return;
            }

            // Chart Bar Bulan
            const canvasBulan = document.getElementById('chartBulanCanvas');
            if (canvasBulan) {
                const data = @json($honorPerBulan);
                const labels = data.map(d => d.bulan);
                const values = data.map(d => d.total);
                const ctx = canvasBulan.getContext('2d');
                const gradient = ctx.createLinearGradient(0, 0, 0, 260);
                gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
                gradient.addColorStop(1, 'rgba(37, 99, 235, 0.15)');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Total Realisasi Honor (Rp)',
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
                                ticks: {
                                    callback: function(value) {
                                        return 'Rp ' + (value >= 1000000 ? (value / 1000000).toFixed(0) + 'M' : value);
                                    }
                                }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Chart Doughnut Bidang
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
                            backgroundColor: ['#2563eb', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'],
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
                                labels: { boxWidth: 10, padding: 12, font: { family: 'Plus Jakarta Sans', size: 11 } }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return ' Rp ' + new Intl.NumberFormat('id-ID').format(context.raw || 0);
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

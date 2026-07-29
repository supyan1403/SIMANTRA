@extends('layouts.app')
@section('content')

@if(auth()->user()->role === 'admin')
<!-- ========================================== -->
<!-- 1. DASHBOARD ADMIN (Poin 4.2)               -->
<!-- ========================================== -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>Dashboard Executive Admin</h2>
        <p class="page-subtitle">Ringkasan pengelolaan mitra, mata anggaran, dan transaksi honor BPS Kab. Tasikmalaya</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('import.index') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-cloud-arrow-up-fill"></i> Import Excel MANTRA
        </a>
        <a href="{{ route('monitoring.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Honor
        </a>
    </div>
</div>

<!-- Metric Cards Admin (4.2) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-primary shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Operator</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0">{{ number_format($totalOperator) }}</h3>
                    <span class="text-white-50 extra-small">Pengguna Hak Akses Operator</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-person-badge-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-success shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Mitra</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0">{{ number_format($totalMitra) }}</h3>
                    <span class="text-white-50 extra-small">Terdaftar di Database Master</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-purple shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Mata Anggaran</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0">{{ number_format($totalKegiatan) }}</h3>
                    <span class="text-white-50 extra-small">Kegiatan Statistik Teralokasi</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-journal-bookmark-fill fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-info shadow-sm h-100" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Total Transaksi Berjalan</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0">{{ number_format($totalTransaksi) }}</h3>
                    <span class="text-white-50 extra-small">Alokasi Honor Terdistribusi</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-card-checklist fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Visual Charts Section (Admin) -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Grafik Transaksi Berdasarkan Tahun</h6>
                    <p class="text-muted small mb-0">Tren volume alokasi honor pekerjaan mitra dari tahun ke tahun</p>
                </div>
                <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-right"></i> Rekap Detail</a>
            </div>
            <div class="card-body pt-2" style="position: relative; height: 300px;">
                <canvas id="chartTahunCanvas" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill text-info me-2"></i>Proporsi Per Bidang</h6>
                <p class="text-muted small mb-0">Distribusi alokasi anggaran honor 5 tim kerja</p>
            </div>
            <div class="card-body pt-2 d-flex align-items-center justify-content-center" style="position: relative; height: 300px;">
                <div style="width: 100%; height: 260px;">
                    <canvas id="chartBidangCanvas" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Detail Transaksi Berjalan (Poin 4.2: Informasi Detail saat data dipilih) -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-list-stars text-primary me-2"></i>Informasi Detail Transaksi Berjalan</h6>
            <p class="text-muted small mb-0">Klik pada data transaksi untuk melihat rincian lengkap</p>
        </div>
        <a href="{{ route('monitoring.index') }}" class="btn btn-sm btn-outline-primary">Lihat Semua Transaksi</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">NO</th>
                        <th>NAMA MITRA</th>
                        <th>PERIODE</th>
                        <th>KEGIATAN / MATA ANGGARAN</th>
                        <th>BIDANG</th>
                        <th class="text-end">NOMINAL HONOR</th>
                        <th class="text-center pe-3">OPSI DETAIL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestTransaksis as $idx => $t)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $t->mitra->nama ?? '-' }}</div>
                            <span class="text-muted small">{{ $t->mitra->pekerjaan ?? 'Mitra' }}</span>
                        </td>
                        <td><span class="badge badge-soft-primary"><i class="bi bi-calendar-event me-1"></i>{{ $t->periode->bulan ?? '' }} {{ $t->periode->tahun ?? '' }}</span></td>
                        <td class="fw-semibold text-slate-700">{{ $t->kegiatan->nama ?? '-' }}</td>
                        <td><span class="badge badge-soft-info">{{ $t->kegiatan->bidang->nama ?? '-' }}</span></td>
                        <td class="text-end fw-extrabold text-success fs-6">Rp {{ number_format($t->nominal, 0, ',', '.') }}</td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-light border px-3" data-bs-toggle="modal" data-bs-target="#modalDetailTrans_{{ $t->id }}">
                                <i class="bi bi-info-circle text-primary me-1"></i> Detail
                            </button>
                        </td>
                    </tr>

                    <!-- Modal Detail Transaksi (Poin 4.2) -->
                    <div class="modal fade" id="modalDetailTrans_{{ $t->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content border-0 shadow">
                                <div class="modal-header bg-primary text-white py-3">
                                    <h6 class="modal-title fw-bold"><i class="bi bi-card-checklist me-2"></i>Rincian Detail Transaksi #{{ $t->id }}</h6>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="bg-light p-3 rounded-3 mb-3 border">
                                        <div class="text-muted small">Nama Mitra</div>
                                        <h5 class="fw-bold text-dark mb-0">{{ $t->mitra->nama ?? '-' }}</h5>
                                        <div class="text-muted small mt-1"><i class="bi bi-geo-alt me-1"></i>{{ $t->mitra->alamat ?? '-' }} (Kode: {{ $t->mitra->kode_alamat ?? '-' }})</div>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <div class="border p-2.5 rounded-3">
                                                <div class="text-muted small">Periode Kerja</div>
                                                <div class="fw-bold text-primary">{{ $t->periode->bulan ?? '-' }} {{ $t->periode->tahun ?? '' }}</div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="border p-2.5 rounded-3">
                                                <div class="text-muted small">Bidang / Tim Kerja</div>
                                                <div class="fw-bold text-info">{{ $t->kegiatan->bidang->nama ?? '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="border p-3 rounded-3 mb-3 bg-white">
                                        <div class="text-muted small">Kegiatan / Mata Anggaran</div>
                                        <div class="fw-bold text-dark">{{ $t->kegiatan->nama ?? '-' }}</div>
                                        @if($t->kegiatan->kode_mata_anggaran)
                                            <code class="px-2 py-0.5 bg-light border rounded text-dark small mt-1 d-inline-block">MAK: {{ $t->kegiatan->kode_mata_anggaran }}</code>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                        <span class="fw-bold text-success">Total Nominal Honor</span>
                                        <h4 class="fw-extrabold text-success mb-0">Rp {{ number_format($t->nominal, 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">Belum ada transaksi berjalan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@else
<!-- ========================================== -->
<!-- 2. DASHBOARD OPERATOR (Poin 4.3)            -->
<!-- ========================================== -->
<!-- Banner Informasi Akun Operator (4.3) -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 54px; height: 54px; font-size: 1.4rem;">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <span class="badge bg-primary bg-opacity-20 text-info px-3 py-1 rounded-pill mb-1 fw-semibold">
                    <i class="bi bi-person-badge-fill me-1"></i> Informasi Akun Operator
                </span>
                <h3 class="fw-extrabold text-white mb-0">{{ $user->name }}</h3>
                <div class="text-slate-300 small d-flex align-items-center gap-3 mt-1 flex-wrap">
                    <span><i class="bi bi-envelope me-1 text-primary"></i>{{ $user->email }}</span>
                    <span><i class="bi bi-diagram-3-fill me-1 text-info"></i>Bidang: <strong>{{ $operatorBidang->nama ?? 'Semua Bidang' }}</strong></span>
                </div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('monitoring.create') }}" class="btn btn-success fw-bold px-3 py-2 text-white"><i class="bi bi-plus-lg me-1"></i> Input Transaksi</a>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-light fw-bold px-3 py-2"><i class="bi bi-person-gear me-1"></i> Ubah Profil</a>
        </div>
    </div>
</div>

<!-- Stat Cards Kewenangan Operator (4.3: Total Honor & Total Transaksi) -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6">
        <div class="card metric-card metric-card-warning shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.75rem;">Total Honor (Kewenangan Bidang {{ $operatorBidang->nama ?? '' }})</span>
                    <h2 class="fw-extrabold text-white mt-1 mb-0">Rp {{ number_format($totalHonorOperator, 0, ',', '.') }}</h2>
                    <span class="text-white-50 extra-small">Total realisasi honor di bidang tempat Anda bertugas</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-wallet2 fs-2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card metric-card metric-card-info shadow-sm h-100" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="card-body d-flex align-items-center justify-content-between p-4">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.75rem;">Total Transaksi Pekerjaan</span>
                    <h2 class="fw-extrabold text-white mt-1 mb-0">{{ number_format($totalTransaksiOperator) }} Pekerjaan</h2>
                    <span class="text-white-50 extra-small">Jumlah alokasi pekerjaan mitra di bidang Anda</span>
                </div>
                <div class="metric-icon-bg">
                    <i class="bi bi-card-checklist fs-2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ringkasan Pekerjaan Operator (Poin 4.3) -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Ringkasan Pekerjaan Mitra (Bidang {{ $operatorBidang->nama ?? 'Utama' }})</h6>
            <p class="text-muted small mb-0">Ringkasan alokasi kegiatan statistik dan jumlah mitra yang bertugas</p>
        </div>
        <a href="{{ route('kegiatan.index') }}" class="btn btn-sm btn-outline-secondary">Kelola Kegiatan</a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">NO</th>
                        <th>NAMA KEGIATAN STATISTIK</th>
                        <th>KODE MAK</th>
                        <th class="text-center">JUMLAH ALOKASI</th>
                        <th class="text-end pe-3">TOTAL HONOR TERALOKASI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kegiatansOperator as $idx => $k)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $k->nama }}</div>
                        </td>
                        <td>
                            @if($k->kode_mata_anggaran)
                                <code class="px-2 py-0.5 bg-light border rounded text-dark small">{{ $k->kode_mata_anggaran }}</code>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge badge-soft-primary px-3 py-1"><i class="bi bi-people me-1"></i>{{ number_format($k->total_alokasi ?? 0) }} Alokasi</span>
                        </td>
                        <td class="text-end pe-3 fw-extrabold text-success fs-6">
                            Rp {{ number_format($k->total_honor ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Belum ada kegiatan terdaftar di bidang Anda.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
(function() {
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initDashboardCharts, 100);
            return;
        }

        // Admin Charts Rendering (4.2)
        const canvasTahun = document.getElementById('chartTahunCanvas');
        if (canvasTahun) {
            const transaksiPerTahunData = {!! json_encode($transaksiPerTahun) !!};
            const labelsTahun = transaksiPerTahunData.map(d => 'Tahun ' + d.tahun);
            const dataNominal = transaksiPerTahunData.map(d => d.total_nominal);

            const ctx = canvasTahun.getContext('2d');
            const gradient = ctx.createLinearGradient(0, 0, 0, 260);
            gradient.addColorStop(0, 'rgba(37, 99, 235, 0.85)');
            gradient.addColorStop(1, 'rgba(37, 99, 235, 0.15)');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labelsTahun,
                    datasets: [{
                        label: 'Total Realisasi Honor (Rp)',
                        data: dataNominal,
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

        const canvasBidang = document.getElementById('chartBidangCanvas');
        if (canvasBidang) {
            const honorPerBidangData = {!! json_encode($honorPerBidang) !!};
            const labelsBidang = honorPerBidangData.map(b => b.nama);
            const dataBidang = honorPerBidangData.map(b => b.total);

            const ctx = canvasBidang.getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labelsBidang,
                    datasets: [{
                        data: dataBidang,
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

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initDashboardCharts();
    } else {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    }
})();
</script>
@endpush
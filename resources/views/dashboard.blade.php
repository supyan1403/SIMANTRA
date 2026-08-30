@extends('layouts.app')
@section('content')

@php
    $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);
    $paguTxt = number_format($paguMataAnggaran, 0, ',', '.');
    $realTxt = number_format($realisasiHonor, 0, ',', '.');
    $sisaTxt = number_format($sisaAnggaran, 0, ',', '.');
    $sisaPct = $paguMataAnggaran > 0 ? round(($realisasiHonor / $paguMataAnggaran) * 100) : 0;
@endphp

<!-- ========================================== -->
<!-- HEADER + TOMBOL CEPAT                        -->
<!-- ========================================== -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>SIMANTRA Kabupaten Tasikmalaya</h2>
        <p class="page-subtitle">Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra - {{ $isAdmin ? 'Dashboard Eksekutif Administrator' : 'Dashboard Operator Bidang' }}</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if($isAdmin)
        <a href="{{ route('import.index') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-cloud-arrow-up-fill"></i> Import Excel MANTRA
        </a>
        @endif
        <a href="{{ route('monitoring.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Honor
        </a>
    </div>
</div>

<!-- ========================================== -->
<!-- BILAH FILTER CASCADE                        -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('dashboard') }}" id="formFilterDashboard" class="row g-2 align-items-end">
            @if($mitraId)<input type="hidden" name="mitra_id" value="{{ $mitraId }}">@endif
            @if($mKegiatanId)<input type="hidden" name="m_kegiatan_id" value="{{ $mKegiatanId }}">@endif
            
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">TAHUN</label>
                <select name="tahun" class="form-select px-2" onchange="submitDashClean(this.form)">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Dropdown Checkbox Bulan Pencairan -->
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">BULAN PENCAIRAN</label>
                <div class="dropdown">
                    <button class="btn btn-white border text-dark dropdown-toggle w-100 text-start d-flex justify-content-between align-items-center bg-white shadow-none" type="button" id="dropdownBulanDashBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" style="border-color: #dee2e6 !important;">
                        <span class="text-truncate text-dark fw-medium">
                            <i class="bi bi-calendar-check text-primary me-1"></i>
                            @if(count($bulanPencairan) === 12)
                                1 Tahun (12 Bulan)
                            @elseif(count($bulanPencairan) === 1)
                                {{ $monthOptions[$bulanPencairan[0]] }}
                            @else
                                {{ count($bulanPencairan) }} Bulan
                            @endif
                        </span>
                        <span class="badge bg-primary rounded-pill ms-2">{{ count($bulanPencairan) }}</span>
                    </button>
                    <div class="dropdown-menu p-3 shadow-lg" aria-labelledby="dropdownBulanDashBtn" style="min-width: 290px; max-height: 380px; overflow-y: auto;">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="fw-bold small text-dark">Bulan Pencairan</span>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-link p-0 text-primary text-decoration-none extra-small me-2" id="btnDashSelectAllMonths">Pilih Semua</button>
                                <button type="button" class="btn btn-link p-0 text-muted text-decoration-none extra-small" id="btnDashClearAllMonths">Reset</button>
                            </div>
                        </div>
                        <div class="row g-2">
                            @foreach($monthOptions as $num => $nama)
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input check-dash-bulan" type="checkbox" name="bulan_pencairan[]" value="{{ $num }}" id="cbDashBulan_{{ $num }}" {{ in_array($num, $bulanPencairan) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="cbDashBulan_{{ $num }}">
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

            @if(!$isOperatorScoped)
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">BIDANG</label>
                <select name="bidang_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Bidang</option>
                    @foreach($bidangOptions as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                <select name="kegiatan_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatanOptions as $k)
                        <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-12 col-md-6">
                <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                <select name="kegiatan_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatanOptions as $k)
                        <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100" title="Cari"><i class="bi bi-search"></i></button>
                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- KARTU METRIK: PAGU / REALISASI / SISA / SBML -->
<!-- ========================================== -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-primary shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Pagu Mata Anggaran</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $paguMataAnggaran }}" data-counter-prefix="Rp ">Rp {{ $paguTxt }}</h3>
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
                    <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $realisasiHonor }}" data-counter-prefix="Rp ">Rp {{ $realTxt }}</h3>
                    <span class="text-white-50 extra-small">{{ $bulanAwal == $bulanAkhir ? $monthOptions[$bulanAwal] : $monthOptions[$bulanAwal] . ' - ' . $monthOptions[$bulanAkhir] }} {{ $tahun }}</span>
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
                    <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $sisaAnggaran }}" data-counter-prefix="Rp ">Rp {{ $sisaTxt }}</h3>
                    <span class="text-white-50 extra-small">Pagu − Realisasi ({{ $sisaPct }}% terpakai)</span>
                </div>
                <div class="metric-icon-bg"><i class="bi bi-graph-down-arrow fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card metric-card metric-card-purple shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3.5">
                <div>
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Standar SBML / Bulan</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0 text-nowrap" style="font-size: 1.3rem;" data-counter-value="{{ $paguSBML }}" data-counter-prefix="Rp ">Rp {{ number_format($paguSBML, 0, ',', '.') }}</h3>
                    <span class="text-white-50 extra-small" style="font-size: 0.68rem;">Cacah: Rp {{ number_format($sbmlPencacahan / 1000000, 1, ',', '.') }}jt • Olah: Rp {{ number_format($sbmlPengolahan / 1000000, 1, ',', '.') }}jt</span>
                </div>
                <div class="metric-icon-bg"><i class="bi bi-piggy-bank-fill fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Mitra</span>
                    <h4 class="fw-extrabold text-dark mt-1 mb-0" data-counter-value="{{ $totalMitra }}">{{ number_format($totalMitra) }}</h4>
                </div>
                <i class="bi bi-people-fill fs-3 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Transaksi</span>
                    <h4 class="fw-extrabold text-dark mt-1 mb-0" data-counter-value="{{ $totalTransaksi }}">{{ number_format($totalTransaksi) }}</h4>
                </div>
                <i class="bi bi-card-checklist fs-3 text-info"></i>
            </div>
        </div>
    </div>
    @if($isAdmin)
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Operator</span>
                    <h4 class="fw-extrabold text-dark mt-1 mb-0" data-counter-value="{{ $totalOperator }}">{{ number_format($totalOperator) }}</h4>
                </div>
                <i class="bi bi-person-badge-fill fs-3 text-success"></i>
            </div>
        </div>
    </div>
    @else
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Total Kegiatan</span>
                    <h4 class="fw-extrabold text-dark mt-1 mb-0" data-counter-value="{{ $totalKegiatan }}">{{ number_format($totalKegiatan) }}</h4>
                </div>
                <i class="bi bi-collection-fill fs-3 text-success"></i>
            </div>
        </div>
    </div>
    @endif
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center justify-content-between p-3">
                <div>
                    <span class="text-muted small fw-bold text-uppercase" style="font-size: 0.72rem;">Bidang Aktif</span>
                    <h4 class="fw-extrabold text-dark mt-1 mb-0 text-truncate" style="max-width: 140px;" title="{{ $bidangId ? ($bidangOptions->firstWhere('id', $bidangId)->nama ?? '-') : ($isAdmin ? 'Semua' : ($user->bidang->nama ?? 'Semua')) }}">
                        {{ $bidangId ? ($bidangOptions->firstWhere('id', $bidangId)->nama ?? '-') : ($isAdmin ? 'Semua' : ($user->bidang->nama ?? 'Semua')) }}
                    </h4>
                </div>
                <i class="bi bi-diagram-3-fill fs-3 text-warning"></i>
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
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Statistik Kemitraan Kabupaten Tasikmalaya ({{ $tahun }})</h6>
            <p class="text-muted small mb-0">Visualisasi 3 diagram batang berdampingan: Total Mitra, Sudah Dipekerjakan, dan Belum Dipekerjakan</p>
        </div>
        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold"><i class="bi bi-check2-circle me-1"></i> Data Real-Time</span>
    </div>
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-4">
                <div class="d-flex flex-column">
                    <div class="p-3 rounded-3 bg-light border-start border-4 border-primary mb-3 shadow-xs">
                        <span class="text-muted extra-small fw-bold text-uppercase">1. Total Mitra (1 Tahun)</span>
                        <h3 class="fw-extrabold text-primary mb-0 mt-1">{{ number_format($totalMitra) }} <span class="fs-6 fw-normal text-muted">Orang</span></h3>
                        <span class="text-muted extra-small">Total basis data mitra BPS Kab. Tasikmalaya</span>
                    </div>
                    <div class="p-3 rounded-3 bg-light border-start border-4 border-success mb-3 shadow-xs">
                        <span class="text-muted extra-small fw-bold text-uppercase">2. Sudah Dipekerjakan</span>
                        <h3 class="fw-extrabold text-success mb-0 mt-1">{{ number_format($sudahDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Orang ({{ $totalMitra > 0 ? round(($sudahDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%)</span></h3>
                        <span class="text-muted extra-small">Memiliki alokasi honor pada filter terpilih</span>
                    </div>
                    <div class="p-3 rounded-3 bg-light border-start border-4 border-warning shadow-xs">
                        <span class="text-muted extra-small fw-bold text-uppercase">3. Belum Dipekerjakan</span>
                        <h3 class="fw-extrabold text-warning mb-0 mt-1">{{ number_format($belumDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Orang ({{ $totalMitra > 0 ? round(($belumDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%)</span></h3>
                        <span class="text-muted extra-small">Belum mendapat alokasi tugas pada rentang ini</span>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div style="min-height: 280px; height: 280px;">
                    <canvas id="chartMitra3BatangDashCanvas" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- GRAFIK KEUANGAN DENGAN LABEL NOMINAL       -->
<!-- ========================================== -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Pengeluaran Anggaran Honor per Bulan (Label Angka Nominal)</h6>
                    <p class="text-muted small mb-0">{{ count($bulanPencairan) }} Bulan ({{ $tahun }})</p>
                </div>
                <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-right"></i> Rekap Detail</a>
            </div>
            <div class="card-body pt-2" style="position: relative; height: 320px;">
                <canvas id="chartBulanCanvas" style="width: 100%; height: 100%;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-pie-chart-fill text-info me-2"></i>Proporsi Honor per Bidang</h6>
                <p class="text-muted small mb-0">Distribusi realisasi honor pada rentang terpilih</p>
            </div>
            <div class="card-body pt-2 d-flex align-items-center justify-content-center" style="position: relative; height: 300px;">
                <div style="width: 100%; height: 260px;">
                    <canvas id="chartBidangCanvas" style="width: 100%; height: 100%;"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- PANEL PENCARIAN & BEBAN KERJA MITRA        -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4" id="mitra-section">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-workspace text-primary me-2"></i>Pencarian & Beban Kerja Mitra</h6>
                <p class="text-muted small mb-0">Sesuaikan filter kegiatan/periode di bawah ini dan pilih nama mitra untuk melihat alokasi honornya</p>
            </div>
            @if($mitraProfile || $mKegiatanId || $mBidangId)
                <a href="{{ route('dashboard', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'bidang_id' => $bidangId, 'kegiatan_id' => $kegiatanId])) }}#mitra-section" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Reset Filter Mitra"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter Mitra</a>
            @endif
        </div>
        
        <form method="GET" action="{{ route('dashboard') }}#mitra-section" id="searchMitraForm">
            <!-- Pass top macro params -->
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="bulan_awal" value="{{ $bulanAwal }}">
            <input type="hidden" name="bulan_akhir" value="{{ $bulanAkhir }}">
            @if($bidangId)<input type="hidden" name="bidang_id" value="{{ $bidangId }}">@endif
            @if($kegiatanId)<input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">@endif

            <!-- Row 1: Cascade Filter Dropdowns -->
            <div class="row g-2 align-items-end mb-3">
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-bold mb-1">TAHUN</label>
                    <select name="m_tahun" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        @foreach($tahunList as $t)
                            <option value="{{ $t }}" {{ $t == $mTahun ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-bold mb-1">BULAN AWAL</label>
                    <select name="m_bulan_awal" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        @foreach($monthOptions as $angka => $nm)
                            <option value="{{ $angka }}" {{ $mBulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-bold mb-1">BULAN AKHIR</label>
                    <select name="m_bulan_akhir" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        @foreach($monthOptions as $angka => $nm)
                            <option value="{{ $angka }}" {{ $mBulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$isOperatorScoped)
                <div class="col-6 col-md-3">
                    <label class="form-label text-muted small fw-bold mb-1">BIDANG</label>
                    <select name="m_bidang_id" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangOptions as $b)
                            <option value="{{ $b->id }}" {{ $mBidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                    <select name="m_kegiatan_id" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        <option value="">Semua Kegiatan</option>
                        @foreach($mKegiatanOptions as $k)
                            <option value="{{ $k->id }}" {{ $mKegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                    <select name="m_kegiatan_id" class="form-select" onchange="this.form.action='{{ route('dashboard') }}#mitra-section'; this.form.submit()">
                        <option value="">Semua Kegiatan</option>
                        @foreach($mKegiatanOptions as $k)
                            <option value="{{ $k->id }}" {{ $mKegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
            </div>

            <!-- Row 2: Prominent Full-Width Live Search Input with Aesthetic Inline Reset -->
            <div class="bg-light p-3 rounded-3 border">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label text-primary fw-bold small mb-0"><i class="bi bi-person-search me-1"></i> CARI NAMA / ID SOBAT MITRA (LIVE SEARCH)</label>
                    @if($mitraProfile)
                        <a href="{{ route('dashboard', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'bidang_id' => $bidangId, 'kegiatan_id' => $kegiatanId, 'm_tahun' => $mTahun, 'm_bulan_awal' => $mBulanAwal, 'm_bulan_akhir' => $mBulanAkhir, 'm_bidang_id' => $mBidangId, 'm_kegiatan_id' => $mKegiatanId])) }}#mitra-section" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" title="Hapus Mitra Terpilih">
                            <i class="bi bi-x-circle-fill"></i> Reset Pencarian Mitra
                        </a>
                    @endif
                </div>
                <select name="mitra_id" id="selectMitraSearch" class="form-select">
                    @if($mitraProfile)
                        <option value="{{ $mitraProfile->id }}" selected>{{ $mitraProfile->nama }}{{ $mitraProfile->id_sobat ? ' (' . $mitraProfile->id_sobat . ')' : '' }}</option>
                    @else
                        <option value="">Ketik nama atau ID Sobat Mitra (contoh: ADE SONI, 3206...)</option>
                    @endif
                </select>
            </div>
        </form>
    </div>
    <div class="card-body p-4">
        @if($mitraProfile)
            <div class="row g-3 mb-4 align-items-stretch">
                <div class="col-md-3">
                    <div class="border p-3 rounded-3 h-100 d-flex flex-column justify-content-center bg-white shadow-sm">
                        <div class="text-muted small text-uppercase fw-bold mb-1">ID Sobat</div>
                        <div class="fw-extrabold text-dark fs-6">{{ $mitraProfile->id_sobat ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded-3 h-100 d-flex flex-column justify-content-center bg-white shadow-sm">
                        <div class="text-muted small text-uppercase fw-bold mb-1">No. HP</div>
                        <div class="fw-extrabold text-dark fs-6">{{ $mitraProfile->no_hp ?? '-' }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded-3 h-100 d-flex flex-column justify-content-center bg-white shadow-sm">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Bidang / Tim</div>
                        <div class="fw-extrabold text-dark fs-6 text-truncate" title="{{ $user->role === 'admin' ? ($workloadKegiatans->first()?->kegiatan->bidang->nama ?? '-') : ($user->bidang->nama ?? '-') }}">{{ $user->role === 'admin' ? ($workloadKegiatans->first()?->kegiatan->bidang->nama ?? '-') : ($user->bidang->nama ?? '-') }}</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="border p-3 rounded-3 h-100 d-flex flex-column justify-content-center bg-success bg-opacity-10 border-success border-opacity-25 shadow-sm">
                        <div class="text-muted small text-uppercase fw-bold mb-1">Estimasi Total Honor</div>
                        <div class="fw-extrabold text-success fs-6">Rp {{ number_format($estimasiHonor, 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            @if($workloadKegiatans->isEmpty())
                <div class="alert alert-light border text-center py-4 mb-0">
                    <i class="bi bi-inbox fs-2 d-block text-muted mb-2"></i>
                    Tidak ada alokasi pekerjaan untuk <strong>{{ $mitraProfile->nama }}</strong> pada rentang terpilih.
                </div>
            @else
            <div class="table-responsive mb-4">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width:50px;">NO</th>
                            <th>NAMA KEGIATAN</th>
                            <th>KODE MAK</th>
                            <th>BIDANG</th>
                            <th class="text-center">BULAN</th>
                            <th class="text-end pe-3">TOTAL HONOR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workloadKegiatans as $i => $wk)
                        <tr>
                            <td class="ps-3 text-muted fw-semibold">{{ $i + 1 }}</td>
                            <td class="fw-bold text-dark">{{ $wk->kegiatan->nama ?? '-' }}</td>
                            <td>
                                @if($wk->kegiatan->kode_mata_anggaran)
                                    <code class="px-2 py-0.5 bg-light border rounded text-dark small">{{ $wk->kegiatan->kode_mata_anggaran }}</code>
                                @else <span class="text-muted small">-</span> @endif
                            </td>
                            <td><span class="badge badge-soft-info">{{ $wk->kegiatan->bidang->nama ?? '-' }}</span></td>
                            <td class="text-center">
                                <span class="badge badge-soft-primary">{{ $wk->list->map(fn($a) => $a->periode->bulan)->implode(', ') }}</span>
                            </td>
                            <td class="text-end pe-3 fw-extrabold text-success">Rp {{ number_format($wk->honor, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="5" class="text-end fw-bold pe-3">TOTAL</td>
                            <td class="text-end pe-3 fw-extrabold text-success">Rp {{ number_format($estimasiHonor, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calendar3 text-primary me-1"></i>Matriks Honor per Bulan</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">BULAN</th>
                            @foreach($workloadMonths as $wm)
                                <th class="text-center">{{ $wm->bulan }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ps-3 fw-bold">Realisasi Honor</td>
                            @foreach($workloadMonths as $wm)
                                <td class="text-center text-success fw-bold">Rp {{ number_format($wm->honor, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="ps-3 fw-bold">Kapasitas SBML</td>
                            @foreach($workloadMonths as $wm)
                                <td class="text-center">Rp {{ number_format($wm->sbml, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td class="ps-3 fw-bold">Sisa</td>
                            @foreach($workloadMonths as $wm)
                                <td class="text-center {{ $wm->sisa < 0 ? 'text-danger fw-bold' : 'text-muted' }}">Rp {{ number_format($wm->sisa, 0, ',', '.') }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        @else
            <div class="text-center py-4 text-muted">
                <i class="bi bi-person-search fs-1 d-block mb-2 text-primary text-opacity-50"></i>
                <div class="fw-bold text-dark">Pencarian Beban Kerja Mitra</div>
                <div class="small">Ketik nama atau ID Sobat mitra pada kotak pencarian di atas untuk melihat detail beban kerja & matriks honor per bulan.</div>
            </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- PANEL STATUS PEKERJAAN & GRAFIK MITRA      -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4" id="status-mitra-section">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-pie-chart-fill text-primary me-2"></i>Status Pekerjaan Mitra & Grafik Visual
                </h6>
                <p class="text-muted small mb-0">
                    Ringkasan real-time mitra yang <strong>Sudah di-Pekerjakan</strong> dan <strong>Belum di-Pekerjakan</strong> pada Tahun {{ $sTahun }}
                </p>
            </div>
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 px-3 py-1.5 rounded-pill fw-semibold">
                <i class="bi bi-database-check me-1"></i> Data Real-Time DB ({{ number_format($totalMitra) }} Total Mitra)
            </span>
        </div>
    </div>
    <div class="card-body p-4">
        <!-- Row 1: Stat Cards + Doughnut Chart -->
        <div class="row g-3 align-items-center mb-4">
            <!-- Left: Stat Cards -->
            <div class="col-md-6">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border border-success border-opacity-25 bg-success bg-opacity-10 shadow-none">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-success fw-bold small text-uppercase mb-1"><i class="bi bi-check-circle-fill me-1"></i> Sudah di-Pekerjakan</div>
                                    <h3 class="fw-extrabold text-success mb-0">{{ number_format($sudahDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Mitra</span></h3>
                                    <div class="text-muted extra-small mt-1">Memiliki alokasi honor pada tahun {{ $sTahun }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-3 fw-extrabold text-success">{{ $totalMitra > 0 ? round(($sudahDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card border border-warning border-opacity-25 bg-warning bg-opacity-10 shadow-none">
                            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-warning fw-bold small text-uppercase mb-1" style="color: #b45309 !important;"><i class="bi bi-clock-history me-1"></i> Belum di-Pekerjakan</div>
                                    <h3 class="fw-extrabold mb-0" style="color: #b45309;">{{ number_format($belumDipekerjakanCount) }} <span class="fs-6 fw-normal text-muted">Mitra</span></h3>
                                    <div class="text-muted extra-small mt-1">Belum menerima alokasi honor pada tahun {{ $sTahun }}</div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-3 fw-extrabold" style="color: #b45309;">{{ $totalMitra > 0 ? round(($belumDipekerjakanCount / $totalMitra) * 100, 1) : 0 }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Doughnut Chart -->
            <div class="col-md-6">
                <div class="border rounded-3 p-3 bg-white d-flex flex-column align-items-center justify-content-center" style="min-height: 220px;">
                    <div class="fw-bold text-muted small mb-2 text-uppercase" style="font-size: 0.725rem;">Proporsi Status Alokasi Mitra</div>
                    <div style="position: relative; width: 100%; max-width: 320px; height: 180px;">
                        <canvas id="chartMitraStatusCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Dropdown & Search Form for Status Table (Clean Independent Filters) -->
        <form method="GET" action="{{ route('dashboard') }}#status-mitra-section" class="row g-2 align-items-end mb-3" id="statusFilterForm">
            <!-- Retain top macro filters -->
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="bulan_awal" value="{{ $bulanAwal }}">
            <input type="hidden" name="bulan_akhir" value="{{ $bulanAkhir }}">
            @if($bidangId)<input type="hidden" name="bidang_id" value="{{ $bidangId }}">@endif
            @if($kegiatanId)<input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">@endif
            @if($mitraId)<input type="hidden" name="mitra_id" value="{{ $mitraId }}">@endif

            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.7rem;">TAHUN</label>
                <select name="s_tahun" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $sTahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.7rem;">STATUS PEKERJAAN</label>
                <select name="s_status" class="form-select form-select-sm" style="height: 35px;" onchange="this.form.submit()">
                    <option value="all" {{ $sStatus === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="sudah" {{ $sStatus === 'sudah' ? 'selected' : '' }}>Sudah di-Pekerjakan</option>
                    <option value="belum" {{ $sStatus === 'belum' ? 'selected' : '' }}>Belum di-Pekerjakan</option>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label text-muted small fw-bold mb-1" style="font-size: 0.7rem;">CARI MITRA (NAMA / ID / WILAYAH)</label>
                <div class="input-group input-group-sm" style="height: 35px;">
                    <span class="input-group-text bg-white border-end-0 text-muted" style="height: 35px;"><i class="bi bi-search"></i></span>
                    <input type="text" name="s_search" class="form-control form-control-sm border-start-0 ps-0" style="height: 35px;" placeholder="Ketik nama, ID, Kecamatan, Desa..." value="{{ $sSearch }}">
                </div>
            </div>
            <div class="col-12 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 d-inline-flex align-items-center justify-content-center" style="height: 35px;" title="Filter Data"><i class="bi bi-funnel-fill"></i></button>
                @if($sStatus !== 'all' || $sSearch !== '' || $sTahun != $tahun)
                    <a href="{{ route('dashboard', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'bidang_id' => $bidangId, 'kegiatan_id' => $kegiatanId, 'mitra_id' => $mitraId])) }}#status-mitra-section" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="height: 35px;" title="Reset Filter Status"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>

        <!-- Status Mitra Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.8rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 45px;">NO</th>
                        <th style="width: 120px;">ID SOBAT</th>
                        <th style="min-width: 180px;">NAMA MITRA</th>
                        <th style="width: 150px;">KABUPATEN / KOTA</th>
                        <th style="width: 130px;">KECAMATAN</th>
                        <th style="width: 130px;">DESA</th>
                        <th class="text-center" style="width: 120px;">TOTAL ALOKASI</th>
                        <th class="text-end" style="width: 140px;">TOTAL HONOR</th>
                        <th class="text-center" style="width: 160px;">STATUS PEKERJAAN</th>
                        <th class="text-center pe-3" style="width: 110px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mitraStatusPaginated as $idx => $mItem)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $mitraStatusPaginated->firstItem() + $idx }}</td>
                        <td><code class="bg-light px-1.5 py-0.5 rounded text-dark fw-bold" style="font-size: 0.75rem;">{{ $mItem->id_sobat ?? '-' }}</code></td>
                        <td class="fw-bold text-dark">{{ $mItem->nama }}</td>
                        <td><span class="badge bg-light text-dark border fw-semibold">{{ $mItem->kabupaten_kota ?? 'Kabupaten Tasikmalaya' }}</span></td>
                        <td class="fw-bold text-slate-800">{{ $mItem->kecamatan ?? '-' }}</td>
                        <td class="text-slate-800">{{ $mItem->desa ?? '-' }}</td>
                        <td class="text-center">
                            @if($mItem->jumlah_alokasi_periode > 0)
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20 fw-bold px-2 py-1">{{ $mItem->jumlah_alokasi_periode }} Kegiatan</span>
                            @else
                                <span class="text-muted extra-small">-</span>
                            @endif
                        </td>
                        <td class="text-end fw-extrabold {{ $mItem->total_honor_periode > 0 ? 'text-success' : 'text-muted' }}">
                            Rp {{ number_format($mItem->total_honor_periode, 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($mItem->jumlah_alokasi_periode > 0)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i> Sudah di-Pekerjakan
                                </span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-2.5 py-1 fw-bold" style="color: #b45309 !important;">
                                    <i class="bi bi-clock-history me-1"></i> Belum di-Pekerjakan
                                </span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-outline-primary py-0.5 px-2 text-nowrap" style="font-size: 0.725rem;" data-bs-toggle="modal" data-bs-target="#modalStatusMitra_{{ $mItem->id }}" title="Lihat Detail Profil & Alokasi">
                                <i class="bi bi-eye-fill"></i> Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted py-4">
                            <i class="bi bi-inbox fs-2 text-muted d-block mb-1"></i>
                            Tidak ada data mitra ditemukan untuk kriteria filter ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($mitraStatusPaginated->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-2" style="font-size: 0.775rem;">
                Menampilkan <strong>{{ $mitraStatusPaginated->firstItem() }}</strong> - <strong>{{ $mitraStatusPaginated->lastItem() }}</strong> dari <strong>{{ number_format($mitraStatusPaginated->total()) }}</strong> mitra
            </div>
            <div class="me-2">
                {{ $mitraStatusPaginated->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL POPUP DETAIL MITRA (STATUS TABLE)    -->
<!-- ========================================== -->
@foreach($mitraStatusPaginated as $mItem)
<div class="modal fade" id="modalStatusMitra_{{ $mItem->id }}" tabindex="-1" aria-labelledby="modalLabel_{{ $mItem->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="avatar-initials bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 0.8rem;">
                        {{ strtoupper(substr($mItem->nama, 0, 2)) }}
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-dark mb-0" id="modalLabel_{{ $mItem->id }}">{{ $mItem->nama }}</h6>
                        <span class="text-muted extra-small">ID SOBAT: {{ $mItem->id_sobat ?? '-' }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Info Cards (Clean Layout without Icons) -->
                <div class="row g-3 align-items-stretch mb-4">
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-white h-100 d-flex flex-column justify-content-between shadow-sm">
                            <div>
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.675rem; letter-spacing: 0.5px;">Wilayah Domisili</div>
                                <div class="fw-extrabold text-dark small">{{ $mItem->kabupaten_kota ?? 'Kabupaten Tasikmalaya' }}</div>
                            </div>
                            <div class="text-muted extra-small mt-2 border-top pt-1.5" style="font-size: 0.725rem;">
                                Kec. {{ $mItem->kecamatan ?? '-' }}, Desa {{ $mItem->desa ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 bg-white h-100 d-flex flex-column justify-content-between shadow-sm">
                            <div>
                                <div class="text-muted text-uppercase fw-bold mb-1" style="font-size: 0.675rem; letter-spacing: 0.5px;">No. Telepon / HP</div>
                                <div class="fw-extrabold text-dark fs-6">{{ $mItem->no_hp ?? '-' }}</div>
                            </div>
                            <div class="text-muted extra-small mt-2 border-top pt-1.5" style="font-size: 0.725rem;">
                                Kontak Aktif Mitra
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 border rounded-3 h-100 d-flex flex-column justify-content-between shadow-sm {{ ($mItem->total_honor_periode ?? 0) > 0 ? 'bg-success bg-opacity-10 border-success border-opacity-25' : 'bg-light' }}">
                            <div>
                                <div class="text-uppercase fw-bold mb-1 {{ ($mItem->total_honor_periode ?? 0) > 0 ? 'text-success' : 'text-muted' }}" style="font-size: 0.675rem; letter-spacing: 0.5px;">
                                    Estimasi Total Honor
                                </div>
                                <div class="fw-extrabold {{ ($mItem->total_honor_periode ?? 0) > 0 ? 'text-success' : 'text-muted' }} fs-5">
                                    Rp {{ number_format($mItem->total_honor_periode ?? 0, 0, ',', '.') }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mt-2 border-top pt-1.5" style="font-size: 0.725rem;">
                                <span class="text-muted extra-small fw-semibold">{{ $mItem->periode_aktif_teks ?? ($monthOptions[$sBulanAwal] ?? '') . ' - ' . ($monthOptions[$sBulanAkhir] ?? '') . ' ' . $sTahun }}</span>
                                <span class="badge {{ ($mItem->total_honor_periode ?? 0) > 0 ? 'bg-success' : 'bg-secondary' }} px-2 py-0.5" style="font-size: 0.675rem;">{{ $mItem->jumlah_alokasi_periode ?? 0 }} Alokasi</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Alokasi Kegiatan -->
                <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-journal-text me-1 text-primary"></i>Rincian Alokasi Kegiatan</h6>
                @if(($mItem->modal_workload_kegiatans ?? collect())->isEmpty())
                    <div class="alert alert-light border text-center py-3 mb-3">
                        <i class="bi bi-clock-history fs-3 d-block text-warning mb-1"></i>
                        <span class="fw-bold text-dark small">Belum di-Pekerjakan</span>
                        <div class="text-muted extra-small">Mitra ini belum memiliki alokasi honor pada Tahun {{ $sTahun }}.</div>
                    </div>
                @else
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered align-middle mb-0" style="font-size: 0.775rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 35px;">NO</th>
                                    <th>KEGIATAN</th>
                                    <th>BIDANG</th>
                                    <th class="text-center">BULAN</th>
                                    <th class="text-end">HONOR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($mItem->modal_workload_kegiatans as $kIdx => $wk)
                                <tr>
                                    <td class="text-center text-muted fw-semibold">{{ $kIdx + 1 }}</td>
                                    <td class="fw-bold text-dark">{{ $wk->kegiatan->nama ?? '-' }}</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25">{{ $wk->kegiatan->bidang->nama ?? '-' }}</span></td>
                                    <td class="text-center"><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-20">{{ $wk->list->map(fn($a) => $a->periode->bulan)->implode(', ') }}</span></td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($wk->honor, 0, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Matriks Honor per Bulan -->
                    <h6 class="fw-bold text-dark small mb-2"><i class="bi bi-calendar3 me-1 text-primary"></i>Matriks Honor Bulanan vs SBML</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered text-center align-middle mb-0" style="font-size: 0.725rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start ps-2">BULAN</th>
                                    @foreach($mItem->modal_workload_months ?? [] as $wm)
                                        <th>{{ $wm->bulan }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="text-start ps-2 fw-bold text-dark">Honor</td>
                                    @foreach($mItem->modal_workload_months ?? [] as $wm)
                                        <td class="{{ $wm->honor > 0 ? 'text-success fw-bold' : 'text-muted' }}">Rp {{ number_format($wm->honor, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                                <tr>
                                    <td class="text-start ps-2 fw-bold text-dark">SBML</td>
                                    @foreach($mItem->modal_workload_months ?? [] as $wm)
                                        <td class="text-muted">Rp {{ number_format($wm->sbml, 0, ',', '.') }}</td>
                                    @endforeach
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
            @endforeach

            @endsection
            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
            <script>
            (function() {
                if (typeof ChartDataLabels !== 'undefined') {
                    Chart.register(ChartDataLabels);
                }

                // Dropdown Checkbox Quick Buttons in Dashboard
                const btnSelectAll = document.getElementById('btnDashSelectAllMonths');
                const btnClearAll = document.getElementById('btnDashClearAllMonths');
                const checkItems = document.querySelectorAll('.check-dash-bulan');

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
                const dashFilterForm = document.getElementById('formFilterDashboard');
                
                window.submitDashClean = function(formEl) {
                    const form = formEl || dashFilterForm;
                    if (!form) return;
                    cleanAndCompressDashForm(form);
                    form.submit();
                };

                if (dashFilterForm) {
                    dashFilterForm.addEventListener('submit', function(e) {
                        cleanAndCompressDashForm(dashFilterForm);
                    });
                }

                function cleanAndCompressDashForm(form) {
                    const selectedMonths = Array.from(form.querySelectorAll('.check-dash-bulan:checked')).map(cb => parseInt(cb.value)).sort((a,b) => a-b);
                    
                    // Disable individual checkbox inputs so they don't produce bulan_pencairan%5B%5D=... in URL
                    form.querySelectorAll('.check-dash-bulan').forEach(cb => cb.disabled = true);
                    
                    // Strip empty inputs and selects
                    form.querySelectorAll('select, input').forEach(el => {
                        if (!el.value && el.type !== 'submit') el.disabled = true;
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

                function initDashboardCharts() {
                    if (typeof Chart === 'undefined') {
                        setTimeout(initDashboardCharts, 100);
                        return;
                    }

                    // 1. Chart 3 Batang Mitra Berdampingan (Dashboard Admin)
                    const ctxMitra3 = document.getElementById('chartMitra3BatangDashCanvas');
                    if (ctxMitra3) {
                        new Chart(ctxMitra3.getContext('2d'), {
                            type: 'bar',
                            data: {
                                labels: ['Status Kemitraan ({{ $tahun }})'],
                                datasets: [
                                    {
                                        label: 'Total Mitra (1 Tahun)',
                                        data: [{{ $totalMitra }}],
                                        backgroundColor: '#2563eb',
                                        borderColor: '#1d4ed8',
                                        borderWidth: 1.5,
                                        borderRadius: 8,
                                        barPercentage: 0.75,
                                        categoryPercentage: 0.85
                                    },
                                    {
                                        label: 'Sudah Dipekerjakan',
                                        data: [{{ $sudahDipekerjakanCount }}],
                                        backgroundColor: '#10b981',
                                        borderColor: '#059669',
                                        borderWidth: 1.5,
                                        borderRadius: 8,
                                        barPercentage: 0.75,
                                        categoryPercentage: 0.85
                                    },
                                    {
                                        label: 'Belum Dipekerjakan',
                                        data: [{{ $belumDipekerjakanCount }}],
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

                    // 2. Chart Bar Bulan (With Nominal Data Labels directly on top)
                    const ctxBulan = document.getElementById('chartBulanCanvas');
                    if (ctxBulan) {
                        const dataHonorPerBulan = @json($honorPerBulan);
                        const labels = dataHonorPerBulan.map(item => item.bulan);
                        const dataValues = dataHonorPerBulan.map(item => item.total);

                        const chartCtx = ctxBulan.getContext('2d');
                        const gradient = chartCtx.createLinearGradient(0, 0, 0, 280);
                        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.9)');
                        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.15)');

                        new Chart(chartCtx, {
                            type: 'bar',
                            data: {
                                labels: labels,
                                datasets: [{
                                    label: 'Realisasi Honor (Rp)',
                                    data: dataValues,
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
                    const ctxBidang = document.getElementById('chartBidangCanvas');
                    if (ctxBidang) {
                        const dataHonorPerBidang = @json($honorPerBidang);
                        const labelsBidang = dataHonorPerBidang.map(item => item.nama);
                        const dataValuesBidang = dataHonorPerBidang.map(item => item.total);

                        new Chart(ctxBidang.getContext('2d'), {
                            type: 'doughnut',
                            data: {
                                labels: labelsBidang,
                                datasets: [{
                                    data: dataValuesBidang,
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
                                        labels: { boxWidth: 10, padding: 8, font: { family: 'Plus Jakarta Sans', size: 11 } }
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

                    // Chart Status Mitra (Doughnut)
                    const ctxMitraStatus = document.getElementById('chartMitraStatusCanvas');
                    if (ctxMitraStatus) {
                        new Chart(ctxMitraStatus, {
                            type: 'doughnut',
                            data: {
                                labels: ['Sudah di-Pekerjakan', 'Belum di-Pekerjakan'],
                                datasets: [{
                                    data: [{{ $sudahDipekerjakanCount }}, {{ $belumDipekerjakanCount }}],
                                    backgroundColor: ['#10b981', '#f59e0b'],
                                    hoverBackgroundColor: ['#059669', '#d97706'],
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
                                                const total = {{ $totalMitra }};
                                                const value = context.raw || 0;
                                                const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                                return ' ' + context.label + ': ' + value.toLocaleString('id-ID') + ' Mitra (' + percentage + '%)';
                                            }
                                        }
                                    }
                                },
                                cutout: '65%'
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

            // Select2 AJAX Live Search Mitra
            $(document).ready(function() {
                // Auto-scroll smooth ke section terkait jika hash URL atau query params terdeteksi
                if (window.location.hash === '#status-mitra-section' || window.location.search.includes('s_')) {
                    setTimeout(function() {
                        const el = document.getElementById('status-mitra-section');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 200);
                } else if (window.location.hash === '#mitra-section' || window.location.search.includes('mitra_id=') || window.location.search.includes('m_')) {
                    setTimeout(function() {
                        const el = document.getElementById('mitra-section');
                        if (el) {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }, 200);
                }

                if ($.fn.select2) {
                    $('#selectMitraSearch').select2({
                        theme: 'bootstrap-5',
                        placeholder: 'Ketik nama / ID Mitra...',
                        allowClear: true,
                        width: '100%',
                        ajax: {
                            url: "{{ route('dashboard.mitra-options') }}",
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    kegiatan_id: $('select[name="m_kegiatan_id"]').val() || $('select[name="kegiatan_id"]').val()
                                };
                            },
                            processResults: function (data) {
                                return {
                                    results: data.results
                                };
                            },
                            cache: true
                        },
                        minimumInputLength: 1
                    }).on('select2:select select2:clear', function () {
                        var form = $(this).closest('form');
                        form.attr('action', "{{ route('dashboard') }}#mitra-section");
                        form.submit();
                    });
                }
            });
            </script>
            @endpush
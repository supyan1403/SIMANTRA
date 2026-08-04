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
        <h2 class="page-title"><i class="bi bi-shield-lock-fill text-primary me-2"></i>{{ $isAdmin ? 'Dashboard Executive Admin' : 'Dashboard Operator' }}</h2>
        <p class="page-subtitle">Ringkasan pagu anggaran, realisasi honor, dan beban kerja mitra BPS Kab. Tasikmalaya</p>
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
        <form method="GET" action="{{ route('dashboard') }}" class="row g-2 align-items-end">
            @if($mitraId)<input type="hidden" name="mitra_id" value="{{ $mitraId }}">@endif
            @if($mKegiatanId)<input type="hidden" name="m_kegiatan_id" value="{{ $mKegiatanId }}">@endif
            
            <div class="col-6 col-md-1">
                <label class="form-label text-muted small fw-bold mb-1">TAHUN</label>
                <select name="tahun" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BULAN AWAL</label>
                <select name="bulan_awal" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BULAN AKHIR</label>
                <select name="bulan_akhir" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            @if(!$isOperatorScoped)
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BIDANG</label>
                <select name="bidang_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Bidang</option>
                    @foreach($bidangOptions as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-4">
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
                <button type="submit" class="btn btn-primary w-100" title="Filter Makro"><i class="bi bi-filter"></i></button>
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
                    <h3 class="fw-extrabold text-white mt-1 mb-0">Rp {{ $paguTxt }}</h3>
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
                    <h3 class="fw-extrabold text-white mt-1 mb-0">Rp {{ $realTxt }}</h3>
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
                    <h3 class="fw-extrabold text-white mt-1 mb-0">Rp {{ $sisaTxt }}</h3>
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
                    <span class="text-white-50 small fw-bold text-uppercase" style="font-size: 0.7rem;">Kapasitas Honor (SBML)</span>
                    <h3 class="fw-extrabold text-white mt-1 mb-0">Rp {{ number_format($paguSBML, 0, ',', '.') }}</h3>
                    <span class="text-white-50 extra-small">Total acuan SBML dalam rentang</span>
                </div>
                <div class="metric-icon-bg"><i class="bi bi-piggy-bank-fill fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Mitra</span>
                    <h4 class="fw-extrabold text-dark mb-0">{{ number_format($totalMitra) }}</h4>
                </div>
                <i class="bi bi-people-fill fs-3 text-primary"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Transaksi</span>
                    <h4 class="fw-extrabold text-dark mb-0">{{ number_format($totalTransaksi) }}</h4>
                </div>
                <i class="bi bi-card-checklist fs-3 text-info"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Total Operator</span>
                    <h4 class="fw-extrabold text-dark mb-0">{{ number_format($totalOperator) }}</h4>
                </div>
                <i class="bi bi-person-badge-fill fs-3 text-success"></i>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-bold text-uppercase">Bidang Aktif</span>
                    <h4 class="fw-extrabold text-dark mb-0">{{ $bidangId ? ($bidangOptions->firstWhere('id', $bidangId)->nama ?? '-') : ($isAdmin ? 'Semua' : ($user->bidang->nama ?? 'Semua')) }}</h4>
                </div>
                <i class="bi bi-diagram-3-fill fs-3 text-warning"></i>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- GRAFIK                                      -->
<!-- ========================================== -->
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-graph-up-arrow text-primary me-2"></i>Realisasi Honor per Bulan</h6>
                    <p class="text-muted small mb-0">{{ $monthOptions[$bulanAwal] }} - {{ $monthOptions[$bulanAkhir] }} {{ $tahun }}</p>
                </div>
                <a href="{{ route('rekap.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-right"></i> Rekap Detail</a>
            </div>
            <div class="card-body pt-2" style="position: relative; height: 300px;">
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
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-workspace text-primary me-2"></i>Pencarian & Beban Kerja Mitra</h6>
                <p class="text-muted small mb-0">Sesuaikan filter kegiatan/periode di bawah ini dan pilih nama mitra untuk melihat alokasi honornya</p>
            </div>
            @if($mitraProfile || $mKegiatanId || $mBidangId)
                <a href="{{ route('dashboard', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'bidang_id' => $bidangId, 'kegiatan_id' => $kegiatanId])) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="Reset Filter Mitra"><i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter Mitra</a>
            @endif
        </div>
        
        <form method="GET" action="{{ route('dashboard') }}" id="searchMitraForm">
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
                    <select name="m_tahun" class="form-select" onchange="this.form.submit()">
                        @foreach($tahunList as $t)
                            <option value="{{ $t }}" {{ $t == $mTahun ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-bold mb-1">BULAN AWAL</label>
                    <select name="m_bulan_awal" class="form-select" onchange="this.form.submit()">
                        @foreach($monthOptions as $angka => $nm)
                            <option value="{{ $angka }}" {{ $mBulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="form-label text-muted small fw-bold mb-1">BULAN AKHIR</label>
                    <select name="m_bulan_akhir" class="form-select" onchange="this.form.submit()">
                        @foreach($monthOptions as $angka => $nm)
                            <option value="{{ $angka }}" {{ $mBulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                @if(!$isOperatorScoped)
                <div class="col-6 col-md-3">
                    <label class="form-label text-muted small fw-bold mb-1">BIDANG</label>
                    <select name="m_bidang_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Bidang</option>
                        @foreach($bidangOptions as $b)
                            <option value="{{ $b->id }}" {{ $mBidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                    <select name="m_kegiatan_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kegiatan</option>
                        @foreach($mKegiatanOptions as $k)
                            <option value="{{ $k->id }}" {{ $mKegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="col-12 col-md-6">
                    <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                    <select name="m_kegiatan_id" class="form-select" onchange="this.form.submit()">
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
                        <a href="{{ route('dashboard', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'bidang_id' => $bidangId, 'kegiatan_id' => $kegiatanId, 'm_tahun' => $mTahun, 'm_bulan_awal' => $mBulanAwal, 'm_bulan_akhir' => $mBulanAkhir, 'm_bidang_id' => $mBidangId, 'm_kegiatan_id' => $mKegiatanId])) }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-bold d-inline-flex align-items-center gap-1 shadow-sm" title="Hapus Mitra Terpilih">
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

@endsection

@push('scripts')
<script>
(function() {
    function initDashboardCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initDashboardCharts, 100);
            return;
        }

        const canvasBulan = document.getElementById('chartBulanCanvas');
        if (canvasBulan) {
            const data = {!! json_encode($honorPerBulan) !!};
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

        const canvasBidang = document.getElementById('chartBidangCanvas');
        if (canvasBidang) {
            const data = {!! json_encode($honorPerBidang) !!};
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

    if (document.readyState === 'complete' || document.readyState === 'interactive') {
        initDashboardCharts();
    } else {
        document.addEventListener('DOMContentLoaded', initDashboardCharts);
    }
})();

// Select2 AJAX Live Search Mitra
$(document).ready(function() {
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
                        kegiatan_id: $('select[name="kegiatan_id"]').val()
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
            $(this).closest('form').submit();
        });
    }
});
</script>
@endpush
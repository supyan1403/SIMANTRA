@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-bar-graph-fill text-primary me-2"></i>Rekap Tahunan Alokasi Honor</h2>
        <p class="page-subtitle">Ringkasan eksekutif realisasi honor per bidang & bulan kerja (Tahun {{ $tahun }})</p>
    </div>
    
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <form method="GET" action="{{ route('rekap.index') }}" class="d-flex align-items-center gap-2">
            <label for="tahun" class="form-label text-muted small fw-bold mb-0 text-nowrap">Tahun:</label>
            <select name="tahun" id="tahun" class="form-select form-select-sm shadow-sm" onchange="this.form.submit()" style="min-width: 135px; padding-right: 2.25rem !important;">
                @foreach($tahunList as $t)
                    <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                @endforeach
            </select>
        </form>

        <a href="{{ route('monitoring.create') }}" class="btn btn-sm btn-primary d-flex align-items-center gap-1 shadow-sm">
            <i class="bi bi-plus-circle-fill"></i> Tambah Honor
        </a>

        <button onclick="window.print()" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
            <i class="bi bi-printer-fill"></i> Cetak / Print
        </button>
    </div>
</div>

@php
    $grandTotalYear = array_sum(array_column($rekap, 'total'));
@endphp

<!-- Grand Total Hero Card -->
<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff;">
    <div class="card-body p-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <span class="badge bg-primary bg-opacity-20 text-info px-3 py-1.5 rounded-pill mb-2 fw-semibold">
                <i class="bi bi-shield-check me-1"></i> Total Alokasi Honor Tahun {{ $tahun }}
            </span>
            <h1 class="fw-extrabold text-white mb-1" style="font-size: 2.2rem;">Rp {{ number_format($grandTotalYear, 0, ',', '.') }}</h1>
            <p class="text-slate-300 small mb-0">Total akumulasi honor teralokasi untuk seluruh 5 tim kerja BPS dalam 12 periode bulanan.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="#card-view" class="btn btn-light btn-sm fw-bold px-3 py-2 text-dark"><i class="bi bi-grid-fill me-1 text-primary"></i> Tampilan Kartu</a>
            <a href="#table-view" class="btn btn-outline-light btn-sm fw-bold px-3 py-2"><i class="bi bi-table me-1"></i> Matriks Tabel</a>
        </div>
    </div>
</div>

<!-- Executive Bidang Cards Grid -->
<h5 class="fw-bold text-dark mb-3" id="card-view"><i class="bi bi-diagram-3-fill text-primary me-2"></i>Rekapitulasi Per Bidang / Tim Kerja</h5>
<div class="row g-3 mb-4">
    @foreach($rekap as $row)
        @php
            $bidangObj = $bidangs->firstWhere('nama', $row['bidang']);
            $percentage = $grandTotalYear > 0 ? round(($row['total'] / $grandTotalYear) * 100, 1) : 0;
            
            $iconMap = [
                'Distribusi' => 'bi-truck',
                'Neraca' => 'bi-calculator',
                'Produksi' => 'bi-gear-wide-connected',
                'Sosial' => 'bi-people',
                'Cadangan' => 'bi-archive',
            ];
            $icon = $iconMap[$row['bidang']] ?? 'bi-building';
            
            $badgeColorMap = [
                'Distribusi' => 'primary',
                'Neraca' => 'success',
                'Produksi' => 'warning',
                'Sosial' => 'info',
                'Cadangan' => 'secondary',
            ];
            $color = $badgeColorMap[$row['bidang']] ?? 'primary';
        @endphp
        
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card h-100 border-0 shadow-sm hover-shadow transition-all">
                <div class="card-body p-3.5 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle bg-{{ $color }} bg-opacity-10 text-{{ $color }} rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 1.1rem;">
                                <i class="bi {{ $icon }}"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0">{{ $row['bidang'] }}</h6>
                                <span class="text-muted small">Porsi: <strong>{{ $percentage }}%</strong> anggaran</span>
                            </div>
                        </div>
                        <span class="badge badge-soft-{{ $color }} fw-bold">{{ $percentage }}%</span>
                    </div>

                    <div class="my-2">
                        <span class="text-muted small fw-semibold text-uppercase">Total Honor Bidang</span>
                        <h3 class="fw-extrabold text-dark mb-1">Rp {{ number_format($row['total'], 0, ',', '.') }}</h3>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Monthly Quick Grid -->
                    <div class="bg-light rounded-3 p-2 my-2 flex-grow-1">
                        <span class="text-muted small fw-bold d-block mb-1 px-1">Rincian Bulanan:</span>
                        <div class="row g-1">
                            @foreach($periodes as $p)
                                @php
                                    $val = $row[$p->id] ?? 0;
                                    if ($val >= 1000000) {
                                        $formattedVal = 'Rp ' . number_format($val / 1000000, 1, ',', '.') . ' Jt';
                                    } elseif ($val >= 1000) {
                                        $formattedVal = 'Rp ' . number_format($val / 1000, 0, ',', '.') . ' Rb';
                                    } else {
                                        $formattedVal = '-';
                                    }
                                @endphp
                                <div class="col-4">
                                    <a href="{{ $val > 0 ? route('monitoring.index', ['periode_id' => $p->id, 'bidang_id' => $bidangObj?->id]) : route('monitoring.create', ['periode_id' => $p->id, 'bidang_id' => $bidangObj?->id]) }}" 
                                       class="d-block text-decoration-none p-1.5 rounded text-center border bg-white hover-bg-light transition-all">
                                        <div class="text-muted" style="font-size: 0.68rem; font-weight: 700;">{{ strtoupper(substr($p->bulan, 0, 3)) }}</div>
                                        <div class="{{ $val > 0 ? 'text-primary fw-bold' : 'text-muted opacity-50' }}" style="font-size: 0.72rem;">
                                            {{ $formattedVal }}
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-2 d-flex gap-2">
                        <a href="{{ route('monitoring.index', ['bidang_id' => $bidangObj?->id]) }}" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-pencil-square"></i> Kelola Honor
                        </a>
                        <a href="{{ route('monitoring.create', ['bidang_id' => $bidangObj?->id]) }}" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                            <i class="bi bi-plus-lg"></i> Tambah
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Compact Print Matriks Table -->
<div class="card border-0 shadow-sm mt-4" id="table-view">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i>Matriks Detail 12 Bulan (Tahun {{ $tahun }})</h6>
        <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> Tampilan tabel cetak resmi</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-nowrap mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="min-width: 160px;">BIDANG / TIM KERJA</th>
                        @foreach($periodes as $p)
                            <th class="text-center" style="min-width: 100px;">{{ strtoupper($p->bulan) }}</th>
                        @endforeach
                        <th class="text-end pe-3 bg-primary text-white" style="min-width: 140px;">TOTAL (RP)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rekap as $row)
                    @php
                        $bidangObj = $bidangs->firstWhere('nama', $row['bidang']);
                    @endphp
                    <tr>
                        <td class="ps-3 fw-bold text-dark">{{ $row['bidang'] }}</td>
                        @foreach($periodes as $p)
                            <td class="text-end">
                                @if(isset($row[$p->id]) && $row[$p->id] > 0)
                                    <a href="{{ route('monitoring.index', ['periode_id' => $p->id, 'bidang_id' => $bidangObj?->id]) }}" class="text-decoration-none fw-semibold text-primary" title="Klik untuk edit {{ $row['bidang'] }} bulan {{ $p->bulan }}">
                                        Rp {{ number_format($row[$p->id], 0, ',', '.') }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="text-end pe-3 fw-extrabold text-success bg-light fs-6">
                            Rp {{ number_format($row['total'], 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td class="ps-3 text-dark text-uppercase">TOTAL KESELURUHAN</td>
                        @php
                            $grandTotal = 0;
                        @endphp
                        @foreach($periodes as $p)
                            @php
                                $colSum = array_sum(array_column($rekap, $p->id));
                                $grandTotal += $colSum;
                            @endphp
                            <td class="text-end">Rp {{ number_format($colSum, 0, ',', '.') }}</td>
                        @endforeach
                        <td class="text-end pe-3 text-success fs-6 fw-extrabold bg-success bg-opacity-10">
                            Rp {{ number_format($grandTotal, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal Kustom Export Excel -->
<div class="modal fade" id="modalExportExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-dark text-white py-3">
                <h5 class="modal-title fw-bold fs-6"><i class="bi bi-file-earmark-excel-fill text-success me-2"></i>Export Laporan Excel (Multi-Periode)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="GET" action="{{ route('rekap.export') }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">1. PILIH RENTANG WAKTU / PERIODE LAPORAN</label>
                        <select name="jenis" id="selectJenis" class="form-select fw-semibold" onchange="toggleExportOptions()">
                            <option value="tahun" selected>📅 Laporan Tahunan (Penuh 12 Bulan)</option>
                            <option value="triwulan">📊 Laporan Per Triwulan (3 Bulanan)</option>
                            <option value="semester">📈 Laporan Per Semester (6 Bulanan)</option>
                            <option value="bulan">📆 Laporan Per Bulan Spesifik</option>
                        </select>
                    </div>

                    <!-- Sub-option Triwulan -->
                    <div class="mb-3 d-none" id="optTriwulan">
                        <label class="form-label fw-bold small text-muted text-uppercase">PILIH TRIWULAN</label>
                        <select name="triwulan" class="form-select">
                            <option value="Q1">Triwulan I (Januari - Maret)</option>
                            <option value="Q2">Triwulan II (April - Juni)</option>
                            <option value="Q3">Triwulan III (Juli - September)</option>
                            <option value="Q4">Triwulan IV (Oktober - Desember)</option>
                        </select>
                    </div>

                    <!-- Sub-option Semester -->
                    <div class="mb-3 d-none" id="optSemester">
                        <label class="form-label fw-bold small text-muted text-uppercase">PILIH SEMESTER</label>
                        <select name="semester" class="form-select">
                            <option value="S1">Semester I (Januari - Juni)</option>
                            <option value="S2">Semester II (Juli - Desember)</option>
                        </select>
                    </div>

                    <!-- Sub-option Bulan (Single / Multi-Select) -->
                    <div class="mb-3 d-none" id="optBulan">
                        <label class="form-label fw-bold small text-muted text-uppercase">PILIH BULAN (DAPAT MEMILIH LEBIH DARI SATU BULAN)</label>
                        <div class="card p-2 border bg-light" style="max-height: 180px; overflow-y: auto;">
                            @foreach([1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'] as $num => $namaBln)
                                <div class="form-check me-3 mb-1">
                                    <input class="form-check-input" type="checkbox" name="bulan_multi[]" value="{{ $num }}" id="bln_{{ $num }}">
                                    <label class="form-check-label small fw-semibold" for="bln_{{ $num }}">
                                        {{ $namaBln }} {{ $tahun }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">2. PILIH TIM KERJA / BIDANG</label>
                        <select name="bidang_id" class="form-select">
                            <option value="all" selected>Semua Bidang (Distribusi, Neraca, Produksi, Sosial, Cadangan)</option>
                            @foreach($bidangs as $b)
                                <option value="{{ $b->id }}">{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small py-2 d-flex align-items-center gap-2 mb-0">
                        <i class="bi bi-info-circle-fill fs-6 text-info"></i>
                        <div>File Excel hasil unduhan terdiri dari <strong>Sheet Matriks Ringkasan</strong> dan <strong>Sheet Rincian Detail Mitra</strong>.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-1 shadow-sm">
                        <i class="bi bi-download"></i> Unduh File Excel (.xlsx)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleExportOptions() {
    const jenis = document.getElementById('selectJenis').value;
    document.getElementById('optTriwulan').classList.add('d-none');
    document.getElementById('optSemester').classList.add('d-none');
    document.getElementById('optBulan').classList.add('d-none');

    if (jenis === 'triwulan') {
        document.getElementById('optTriwulan').classList.remove('d-none');
    } else if (jenis === 'semester') {
        document.getElementById('optSemester').classList.remove('d-none');
    } else if (jenis === 'bulan') {
        document.getElementById('optBulan').classList.remove('d-none');
    }
}
</script>
@endpush

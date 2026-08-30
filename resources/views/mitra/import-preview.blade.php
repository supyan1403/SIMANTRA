@extends('layouts.app')
@section('content')

<style>
    /* Table Container with Horizontal Scroll */
    .table-scroll-container {
        overflow-x: auto;
        overflow-y: visible;
        width: 100%;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    /* 100% Solid & Non-Leaking Sticky Columns (Freeze Panes for 4 Columns: NO, ID SOBAT, NIK, NAMA MITRA) */
    .table-sticky th:nth-child(1), .table-sticky td:nth-child(1) {
        position: sticky !important;
        left: 0px !important;
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(2), .table-sticky td:nth-child(2) {
        position: sticky !important;
        left: 50px !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(3), .table-sticky td:nth-child(3) {
        position: sticky !important;
        left: 170px !important;
        width: 160px !important;
        min-width: 160px !important;
        max-width: 160px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(4), .table-sticky td:nth-child(4) {
        position: sticky !important;
        left: 330px !important;
        width: 270px !important;
        min-width: 270px !important;
        max-width: 270px !important;
        box-sizing: border-box;
        border-right: 2px solid #cbd5e1 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08);
    }

    .table-sticky th:nth-child(1),
    .table-sticky th:nth-child(2),
    .table-sticky th:nth-child(3),
    .table-sticky th:nth-child(4) {
        background-color: #f1f5f9 !important;
        z-index: 20 !important;
    }

    .table-sticky td:nth-child(1),
    .table-sticky td:nth-child(2),
    .table-sticky td:nth-child(3),
    .table-sticky td:nth-child(4) {
        background-color: #ffffff !important;
        z-index: 10 !important;
    }

    /* Synchronized Row Hover Effect Across All Sticky and Non-Sticky Cells */
    .table-sticky tbody tr:hover td {
        background-color: #e2e8f0 !important;
    }
</style>

<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check text-primary me-2"></i>Pratinjau Import Data Mitra</h2>
        <p class="page-subtitle">Tinjau baris calon data mitra yang terbaca dari berkas Excel sebelum disimpan ke database</p>
    </div>
    <a href="{{ route('mitra.import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-success me-2"></i>Daftar Baris Calon Mitra Terdeteksi</h6>
        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 fw-semibold">{{ count($rows) }} Baris Siap Di-import</span>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('mitra.import.process') }}">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">

            @if(empty($rows))
                <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>Tidak ditemukan baris data yang valid (butuh kolom Nama Lengkap / ID Sobat) pada file ini.</div>
                </div>
            @else
                <div class="table-scroll-container">
                    <table class="table table-hover table-sticky align-middle mb-0" style="min-width: 2150px;">
                        <thead>
                            <tr>
                                <th class="text-center ps-3">NO</th>
                                <th>ID SOBAT</th>
                                <th>NIK</th>
                                <th>NAMA MITRA</th>
                                <th style="width: 150px;">POSISI MITRA</th>
                                <th style="width: 150px;">KABUPATEN / KOTA</th>
                                <th style="width: 130px;">KECAMATAN</th>
                                <th style="width: 130px;">DESA</th>
                                <th style="min-width: 220px;">ALAMAT DETAIL</th>
                                <th style="width: 140px;">PEKERJAAN</th>
                                <th class="text-center" style="width: 120px;">JENIS KELAMIN</th>
                                <th>PENDIDIKAN</th>
                                <th>TANGGAL LAHIR</th>
                                <th>NPWP</th>
                                <th>KONTAK / EMAIL</th>
                                <th class="text-center">PENGALAMAN BPS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="text-center ps-3 text-muted fw-semibold">{{ $row['no'] ?: ($i + 1) }}</td>
                                <td>
                                    <span class="badge font-monospace fw-bold" style="font-size: 0.775rem; padding: 0.35rem 0.65rem; background-color: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 0.375rem;">
                                        {{ $row['id_sobat'] ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace text-slate-700 fw-semibold" style="font-size: 0.8rem;">
                                        {{ $row['nik'] ?: '-' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <div class="avatar-initials bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.75rem;">
                                            {{ strtoupper(substr($row['nama'], 0, 2)) }}
                                        </div>
                                        <span class="fw-bold text-dark text-nowrap">{{ $row['nama'] }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($row['posisi']))
                                        <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.725rem; font-weight: 600; padding: 0.25rem 0.5rem;">{{ $row['posisi'] }}</span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.5rem;">
                                        {{ $row['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya' }}
                                    </span>
                                </td>
                                <td class="text-slate-800 fw-bold text-nowrap">{{ $row['kecamatan'] ?: '-' }}</td>
                                <td class="text-slate-800 text-nowrap">{{ $row['desa'] ?: '-' }}</td>
                                <td>
                                    <div class="text-truncate small" style="max-width: 250px;" title="{{ $row['alamat_detail'] }}">
                                        {{ $row['alamat_detail'] ?: '-' }}
                                    </div>
                                </td>
                                <td><span class="text-slate-700 fw-semibold text-nowrap small">{{ $row['pekerjaan'] ?: '-' }}</span></td>
                                <td class="text-center">
                                    @if($row['jk'] === 'L')
                                        <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.725rem; font-weight: 600; padding: 0.25rem 0.5rem;"><i class="bi bi-gender-male me-0.5"></i> Laki-laki</span>
                                    @elseif($row['jk'] === 'P')
                                        <span class="badge" style="background-color: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; font-size: 0.725rem; font-weight: 600; padding: 0.25rem 0.5rem;"><i class="bi bi-gender-female me-0.5"></i> Perempuan</span>
                                    @else
                                        <span class="badge bg-light text-muted" style="font-size: 0.725rem;">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border border-secondary-subtle px-2 py-1 text-nowrap small">
                                        <i class="bi bi-mortarboard-fill text-primary me-1"></i>{{ $row['pendidikan'] ?: '-' }}
                                    </span>
                                </td>
                                <td class="text-nowrap small text-slate-700 font-monospace">
                                    {{ $row['tanggal_lahir'] ?: '-' }}
                                </td>
                                <td class="text-nowrap small font-monospace text-slate-700">
                                    {{ $row['npwp'] ?: '-' }}
                                </td>
                                <td>
                                    <div>
                                        @if(!empty($row['no_hp']))
                                            <div class="small fw-semibold text-slate-800 text-nowrap">
                                                <i class="bi bi-telephone text-primary me-1"></i>{{ $row['no_hp'] }}
                                            </div>
                                        @endif
                                        @if(!empty($row['email']))
                                            <div class="small text-muted text-nowrap" style="font-size: 0.75rem;">
                                                <i class="bi bi-envelope text-secondary me-1"></i>{{ $row['email'] }}
                                            </div>
                                        @endif
                                        @if(empty($row['no_hp']) && empty($row['email']))
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center flex-wrap" style="max-width: 180px;">
                                        @if(!empty($row['exp_sp'])) <span class="badge" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Sensus Penduduk">SP</span> @endif
                                        @if(!empty($row['exp_st'])) <span class="badge" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Sensus Pertanian">ST</span> @endif
                                        @if(!empty($row['exp_se'])) <span class="badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Sensus Ekonomi">SE</span> @endif
                                        @if(!empty($row['exp_susenas'])) <span class="badge" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Survei Susenas">Susenas</span> @endif
                                        @if(!empty($row['exp_sakernas'])) <span class="badge" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Survei Sakernas">Sakernas</span> @endif
                                        @if(!empty($row['exp_sbh'])) <span class="badge" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.7rem; font-weight: 700; padding: 0.2rem 0.4rem;" title="Survei Biaya Hidup">SBH</span> @endif
                                        @if(empty($row['exp_sp']) && empty($row['exp_st']) && empty($row['exp_se']) && empty($row['exp_susenas']) && empty($row['exp_sakernas']) && empty($row['exp_sbh']))
                                            <span class="text-muted" style="font-size: 0.725rem;">-</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 border-top bg-light">
                    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i><strong>{{ count($rows) }}</strong> baris calon data mitra akan disimpan ke database.</span>
                    <button type="submit" class="btn btn-success btn-lg shadow-sm px-4 fw-bold">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan ke Database Data Mitra
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncPreviewStickyColumns() {
        const table = document.querySelector('.table-sticky');
        if (!table) return;
        const ths = table.querySelectorAll('thead tr th');
        if (ths.length < 4) return;

        const w1 = Math.round(ths[0].getBoundingClientRect().width);
        const w2 = Math.round(ths[1].getBoundingClientRect().width);
        const w3 = Math.round(ths[2].getBoundingClientRect().width);

        const left1 = 0;
        const left2 = w1;
        const left3 = w1 + w2;
        const left4 = w1 + w2 + w3;

        table.querySelectorAll('tr').forEach(row => {
            const cells = row.children;
            if (cells.length >= 4) {
                cells[0].style.left = left1 + 'px';
                cells[1].style.left = left2 + 'px';
                cells[2].style.left = left3 + 'px';
                cells[3].style.left = left4 + 'px';
            }
        });
    }

    syncPreviewStickyColumns();
    setTimeout(syncPreviewStickyColumns, 150);
    window.addEventListener('resize', syncPreviewStickyColumns);
});
</script>

@endsection
@extends('layouts.app')
@section('content')

<style>
    /* Filter Form Pixel-Perfect Height Alignment */
    #filterForm .form-select-sm,
    #filterForm .form-control-sm,
    #filterForm .input-group-text,
    #filterForm .btn-sm {
        height: 34px !important;
        font-size: 0.8rem;
    }
    #filterForm .form-label {
        margin-bottom: 0.35rem !important;
        font-size: 0.7rem !important;
        line-height: 1.2;
    }

    .table-scroll-container {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: 0 0 0.5rem 0.5rem;
    }

    .table-compact-scroll th, .table-compact-scroll td {
        padding: 0.5rem 0.65rem;
        font-size: 0.8rem;
        vertical-align: middle;
    }
    .table-compact-scroll th {
        font-size: 0.725rem;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #475569;
        background-color: #f1f5f9;
        font-weight: 700;
        border-bottom: 1px solid #cbd5e1;
        white-space: nowrap;
    }
    .badge-kabupaten-kota {
        background-color: #f1f5f9;
        color: #0f172a !important;
        border: 1px solid #cbd5e1;
        font-weight: 600;
        font-size: 0.725rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.35rem;
        display: inline-block;
        white-space: nowrap;
    }
    .badge-jk-l {
        background-color: #eff6ff;
        color: #1d4ed8 !important;
        border: 1px solid #bfdbfe;
        font-weight: 600;
        font-size: 0.725rem;
        padding: 0.2rem 0.45rem;
        border-radius: 0.35rem;
        display: inline-block;
        white-space: nowrap;
    }
    .badge-jk-p {
        background-color: #fff7ed;
        color: #c2410c !important;
        border: 1px solid #fed7aa;
        font-weight: 600;
        font-size: 0.725rem;
        padding: 0.2rem 0.45rem;
        border-radius: 0.35rem;
        display: inline-block;
        white-space: nowrap;
    }
    .avatar-initials {
        width: 24px;
        height: 24px;
        font-size: 0.65rem;
    }
    .alamat-detail-scroll {
        font-size: 0.775rem;
        line-height: 1.3;
        color: #334155;
        white-space: normal;
        word-break: break-word;
        max-width: 280px;
    }

    /* 100% Solid & Non-Leaking Sticky Columns (Freeze Panes) */
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
        width: 130px !important;
        min-width: 130px !important;
        max-width: 130px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(3), .table-sticky td:nth-child(3) {
        position: sticky !important;
        left: 180px !important; /* 50 + 130 = 180px exact */
        width: 270px !important; /* Widened to 270px for full long names */
        min-width: 270px !important;
        max-width: 270px !important;
        box-sizing: border-box;
        border-right: 2px solid #cbd5e1 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08);
    }

    .table-sticky th:nth-child(1),
    .table-sticky th:nth-child(2),
    .table-sticky th:nth-child(3) {
        background-color: #f1f5f9 !important;
        z-index: 20 !important;
    }

    .table-sticky td:nth-child(1),
    .table-sticky td:nth-child(2),
    .table-sticky td:nth-child(3) {
        background-color: #ffffff !important;
        z-index: 10 !important;
    }

    /* Synchronized Row Hover Effect Across All Sticky and Non-Sticky Cells */
    .table-sticky tbody tr:hover td {
        background-color: #e2e8f0 !important;
    }
</style>

<!-- Page Header styled exactly like Mata Anggaran -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-people-fill text-primary me-2"></i>Data Mitra</h2>
        <p class="page-subtitle">Daftar {{ number_format($mitras->total()) }} mitra statistik BPS terdaftar dalam sistem SIMANTRA</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap">
        <a href="{{ route('mitra.import.template') }}" class="btn btn-outline-success d-flex align-items-center gap-2 shadow-sm text-nowrap">
            <i class="bi bi-download"></i> Download Template
        </a>
        <a href="{{ route('mitra.import.index') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm text-nowrap">
            <i class="bi bi-cloud-arrow-up-fill"></i> Upload Template
        </a>
        <a href="{{ route('mitra.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm text-nowrap">
            <i class="bi bi-plus-circle-fill"></i> Tambah Mitra Baru
        </a>
    </div>
</div>

<!-- Filter Panel with Pixel-Perfect Mapped Heights -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('mitra.index') }}" class="row g-2 align-items-end" id="filterForm">
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">KABUPATEN / KOTA</label>
                <select name="kabupaten_kota" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kab / Kota</option>
                    @foreach($kabupatenKotaList as $kab)
                        <option value="{{ $kab }}" {{ request('kabupaten_kota') == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">KECAMATAN</label>
                <select name="kecamatan" class="form-select form-select-sm" id="filterKecamatan" onchange="this.form.submit()">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->nama }}" {{ request('kecamatan') == $kec->nama ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">DESA / KELURAHAN</label>
                <select name="desa" class="form-select form-select-sm" id="filterDesa" onchange="this.form.submit()">
                    <option value="">Semua Desa</option>
                    @foreach($desasList as $ds)
                        <option value="{{ $ds }}" {{ request('desa') == $ds ? 'selected' : '' }}>{{ $ds }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">PEKERJAAN</label>
                <select name="pekerjaan" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Pekerjaan</option>
                    @foreach($pekerjaanList as $p)
                        <option value="{{ $p }}" {{ request('pekerjaan') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold">CARI MITRA</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Nama, alamat, atau kode..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-secondary w-100 d-flex align-items-center justify-content-center" title="Filter"><i class="bi bi-funnel-fill"></i></button>
                @if(request()->hasAny(['kabupaten_kota', 'kecamatan', 'desa', 'jk', 'pekerjaan', 'search']))
                    <a href="{{ route('mitra.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Clean Data Table Card with Widened Name Column & Synchronized Hover -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-scroll-container">
            <table class="table table-hover table-compact-scroll table-sticky align-middle mb-0" style="min-width: 1380px;">
                <thead>
                    <tr>
                        <th class="text-center ps-3">NO</th>
                        <th>ID SOBAT</th>
                        <th>NAMA MITRA</th>
                        <th style="width: 150px;">KABUPATEN / KOTA</th>
                        <th style="width: 130px;">KECAMATAN</th>
                        <th style="width: 130px;">DESA</th>
                        <th style="min-width: 220px;">ALAMAT DETAIL</th>
                        <th class="text-center" style="width: 110px;">KODE ALAMAT</th>
                        <th style="width: 130px;">PEKERJAAN</th>
                        <th class="text-center" style="width: 120px;">JENIS KELAMIN</th>
                        <th class="text-center pe-3" style="width: 130px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mitras as $i => $m)
                    <tr>
                        <td class="text-center ps-3 text-muted fw-semibold">{{ $mitras->firstItem() + $i }}</td>
                        <td><span class="badge font-monospace fw-bold" style="font-size: 0.775rem; padding: 0.35rem 0.65rem; background-color: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 0.375rem;">{{ $m->id_sobat ?? '-' }}</span></td>
                        <td>
                            <div class="d-inline-flex align-items-center gap-1.5">
                                <div class="avatar-initials bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                    {{ strtoupper(substr($m->nama, 0, 2)) }}
                                </div>
                                <span class="fw-bold text-dark text-nowrap">{{ $m->nama }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="badge-kabupaten-kota">
                                {{ $m->kabupaten_kota ?? 'Kabupaten Tasikmalaya' }}
                            </span>
                        </td>
                        <td class="text-slate-800 fw-bold text-nowrap">{{ $m->kecamatan ?? '-' }}</td>
                        <td class="text-slate-800 text-nowrap">{{ $m->desa ?? '-' }}</td>
                        <td>
                            <div class="alamat-detail-scroll">
                                {{ $m->alamat_detail ?? $m->alamat ?? '-' }}
                            </div>
                        </td>
                        <td class="text-center"><span class="badge font-monospace fw-semibold" style="font-size: 0.775rem; padding: 0.35rem 0.65rem; background-color: #f8fafc; color: #334155; border: 1px solid #cbd5e1; border-radius: 0.375rem;">{{ $m->kode_alamat ?? '-' }}</span></td>
                        <td><span class="text-slate-700 fw-semibold text-nowrap">{{ $m->pekerjaan ?? 'Mitra' }}</span></td>
                        <td class="text-center">
                            @if($m->jk === 'L')
                                <span class="badge-jk-l"><i class="bi bi-gender-male me-0.5"></i> Laki-laki</span>
                            @elseif($m->jk === 'P')
                                <span class="badge-jk-p"><i class="bi bi-gender-female me-0.5"></i> Perempuan</span>
                            @else
                                <span class="badge bg-light text-muted" style="font-size: 0.725rem;">-</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('mitra.edit', $m) }}" class="btn-action btn-action-edit" title="Edit Data">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form method="POST" action="{{ route('mitra.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Hapus mitra {{ $m->nama }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus Data">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-4">
                            <i class="bi bi-people fs-2 text-muted d-block mb-1"></i>
                            @if(request()->hasAny(['kabupaten_kota', 'kecamatan', 'desa', 'jk', 'pekerjaan', 'search']))
                                Tidak ada data mitra cocok dengan filter pencarian
                            @else
                                Belum ada data mitra terdaftar
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($mitras->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-2" style="font-size: 0.775rem;">
                Menampilkan <strong>{{ $mitras->firstItem() }}</strong> - <strong>{{ $mitras->lastItem() }}</strong> dari <strong>{{ number_format($mitras->total()) }}</strong> data
            </div>
            <div class="me-2">
                {{ $mitras->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
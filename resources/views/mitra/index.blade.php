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

    /* 100% Solid & Non-Leaking Sticky Columns (Freeze Panes for 5 Columns: Checkbox, NO, ID SOBAT, NIK, NAMA MITRA) */
    .table-sticky th:nth-child(1), .table-sticky td:nth-child(1) {
        position: sticky !important;
        left: 0px !important;
        width: 44px !important;
        min-width: 44px !important;
        max-width: 44px !important;
        box-sizing: border-box;
        text-align: center !important;
    }
    .table-sticky th:nth-child(2), .table-sticky td:nth-child(2) {
        position: sticky !important;
        left: 44px !important;
        width: 50px !important;
        min-width: 50px !important;
        max-width: 50px !important;
        box-sizing: border-box;
        text-align: center !important;
    }
    .table-sticky th:nth-child(3), .table-sticky td:nth-child(3) {
        position: sticky !important;
        left: 94px !important;
        width: 120px !important;
        min-width: 120px !important;
        max-width: 120px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(4), .table-sticky td:nth-child(4) {
        position: sticky !important;
        left: 214px !important;
        width: 155px !important;
        min-width: 155px !important;
        max-width: 155px !important;
        box-sizing: border-box;
    }
    .table-sticky th:nth-child(5), .table-sticky td:nth-child(5) {
        position: sticky !important;
        left: 369px !important;
        width: 260px !important;
        min-width: 260px !important;
        max-width: 260px !important;
        box-sizing: border-box;
        border-right: 2px solid #cbd5e1 !important;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08);
    }

    .table-sticky th:nth-child(1),
    .table-sticky th:nth-child(2),
    .table-sticky th:nth-child(3),
    .table-sticky th:nth-child(4),
    .table-sticky th:nth-child(5) {
        background-color: #f1f5f9 !important;
        z-index: 20 !important;
    }

    .table-sticky td:nth-child(1),
    .table-sticky td:nth-child(2),
    .table-sticky td:nth-child(3),
    .table-sticky td:nth-child(4),
    .table-sticky td:nth-child(5) {
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
    @if(auth()->user()?->role === 'admin')
    <div class="d-flex gap-2">
        <a href="{{ route('mitra.import.template') }}" class="btn btn-outline-success btn-sm shadow-none" title="Download Template Excel Mitra">
            <i class="bi bi-file-earmark-arrow-down"></i> Template Excel
        </a>
        <a href="{{ route('mitra.import.index') }}" class="btn btn-success btn-sm shadow-none" title="Upload Excel Mitra">
            <i class="bi bi-cloud-arrow-up"></i> Import Excel
        </a>
        <a href="{{ route('mitra.create') }}" class="btn btn-primary btn-sm shadow-sm">
            <i class="bi bi-plus-lg"></i> Tambah Mitra
        </a>
    </div>
    @endif
</div>

<!-- Modern Filter Card -->
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
                <select name="kecamatan" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Kecamatan</option>
                    @foreach($kecamatans as $kec)
                        <option value="{{ $kec->nama }}" {{ request('kecamatan') == $kec->nama ? 'selected' : '' }}>{{ $kec->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">DESA / KELURAHAN</label>
                <select name="desa" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">Semua Desa</option>
                    @foreach($desasList as $desa)
                        <option value="{{ $desa }}" {{ request('desa') == $desa ? 'selected' : '' }}>{{ $desa }}</option>
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
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold">CARI MITRA</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Nama, NIK, Sobat..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary w-100 d-flex align-items-center justify-content-center gap-1.5 fw-semibold" title="Cari"><i class="bi bi-search"></i> Cari</button>
                @if(request()->hasAny(['kabupaten_kota', 'kecamatan', 'desa', 'jk', 'pekerjaan', 'search']))
                    <a href="{{ route('mitra.index') }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

@if(auth()->user()?->role === 'admin')
<!-- Clean Integrated Bulk Action Bar (Appears right above table) -->
<div id="bulkActionBar" class="d-none align-items-center justify-content-between bg-primary bg-opacity-10 border-start border-4 border-primary rounded-3 p-3 mb-3 shadow-sm">
    <div class="d-flex align-items-center gap-2.5">
        <i class="bi bi-check2-circle text-primary fs-4"></i>
        <div>
            <div class="fw-bold text-dark small"><span id="bulkSelectedCount" class="badge bg-primary rounded-pill px-2.5 py-1 fs-6 me-1">0</span> Mitra Dipilih</div>
            <span class="text-muted extra-small">Centang mitra tambahan atau lakukan tindakan penghapusan massal.</span>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-white btn-sm border fw-semibold shadow-none" id="btnCancelBulkSelect">
            <i class="bi bi-x-lg me-1"></i> Batal Pilih
        </button>
        <button type="button" class="btn btn-danger btn-sm fw-bold shadow-sm d-flex align-items-center gap-1.5 px-3" id="btnTriggerBulkDelete">
            <i class="bi bi-trash3-fill"></i> Hapus Mitra Terpilih
        </button>
    </div>
</div>
@endif

<!-- Clean Data Table Card with 5 Sticky Columns & Harmonized Sequence -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-scroll-container">
            <table class="table table-hover table-compact-scroll table-sticky align-middle mb-0" style="min-width: 2150px;">
                <thead>
                    <tr>
                        @if(auth()->user()?->role === 'admin')
                        <th class="text-center px-2" style="width: 44px; min-width: 44px; max-width: 44px;">
                            <input type="checkbox" class="form-check-input my-0" id="checkAllMitra" style="cursor: pointer; width: 16px; height: 16px;" title="Pilih Semua di Halaman Ini">
                        </th>
                        @endif
                        <th class="text-center px-1" style="width: 50px; min-width: 50px; max-width: 50px;">NO</th>
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
                        @if(auth()->user()?->role === 'admin')
                        <th class="text-center pe-3" style="width: 130px;">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($mitras as $i => $m)
                    <tr>
                        @if(auth()->user()?->role === 'admin')
                        <td class="text-center px-2">
                            <input type="checkbox" class="form-check-input mitra-row-check my-0" value="{{ $m->id }}" data-nama="{{ $m->nama }}" style="cursor: pointer; width: 16px; height: 16px;">
                        </td>
                        @endif
                        <td class="text-center px-1 text-muted fw-semibold">{{ $mitras->firstItem() + $i }}</td>
                        <td><span class="badge font-monospace fw-bold" style="font-size: 0.775rem; padding: 0.35rem 0.65rem; background-color: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; border-radius: 0.375rem;">{{ $m->id_sobat ?? '-' }}</span></td>
                        <td><span class="font-monospace text-slate-700 fw-semibold" style="font-size: 0.8rem;">{{ $m->nik ?? '-' }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2" style="max-width: 100%;">
                                <div class="avatar-initials bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0">
                                    {{ strtoupper(substr($m->nama, 0, 2)) }}
                                </div>
                                <span class="fw-bold text-dark text-truncate" style="max-width: 210px;" title="{{ $m->nama }}">{{ $m->nama }}</span>
                            </div>
                        </td>
                        <td>
                            @if(!empty($m->posisi))
                                <span class="badge" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.725rem; font-weight: 600; padding: 0.25rem 0.5rem;">{{ $m->posisi }}</span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
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
                        <td>
                            <span class="badge bg-light text-dark border border-secondary-subtle px-2.5 py-1.5 text-nowrap" style="font-size: 0.75rem;">
                                <i class="bi bi-mortarboard-fill text-primary me-1"></i>{{ $m->pendidikan_clean }}
                            </span>
                        </td>
                        <td class="text-nowrap small text-slate-700 font-monospace">
                            {{ $m->tanggal_lahir_clean }}
                        </td>
                        <td class="text-nowrap small font-monospace text-slate-700">
                            {{ $m->npwp ?: '-' }}
                        </td>
                        <td>
                            <div>
                                @if(!empty($m->no_hp))
                                    <div class="small fw-semibold text-slate-800 text-nowrap">
                                        <i class="bi bi-telephone text-primary me-1"></i>{{ $m->no_hp }}
                                    </div>
                                @endif
                                @if(!empty($m->email))
                                    <div class="small text-muted text-nowrap" style="font-size: 0.75rem;">
                                        <i class="bi bi-envelope text-secondary me-1"></i>{{ $m->email }}
                                    </div>
                                @endif
                                @if(empty($m->no_hp) && empty($m->email))
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center flex-wrap" style="max-width: 190px;">
                                @if($m->exp_sp) <span class="badge" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Sensus Penduduk">SP</span> @endif
                                @if($m->exp_st) <span class="badge" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Sensus Pertanian">ST</span> @endif
                                @if($m->exp_se) <span class="badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Sensus Ekonomi">SE</span> @endif
                                @if($m->exp_susenas) <span class="badge" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Survei Susenas">Susenas</span> @endif
                                @if($m->exp_sakernas) <span class="badge" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Survei Sakernas">Sakernas</span> @endif
                                @if($m->exp_sbh) <span class="badge" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.725rem; font-weight: 700; padding: 0.25rem 0.45rem;" title="Survei Biaya Hidup">SBH</span> @endif
                                @if(!$m->exp_sp && !$m->exp_st && !$m->exp_se && !$m->exp_susenas && !$m->exp_sakernas && !$m->exp_sbh)
                                    <span class="text-muted" style="font-size: 0.725rem;">-</span>
                                @endif
                            </div>
                        </td>
                        @if(auth()->user()?->role === 'admin')
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
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="18" class="text-center text-muted py-4">
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

<!-- ======================================================== -->
<!-- MODAL KONFIRMASI HAPUS MASSAL MITRA                      -->
<!-- ======================================================== -->
<div class="modal fade" id="modalBulkDeleteConfirm" tabindex="-1" aria-labelledby="modalBulkDeleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalBulkDeleteConfirmLabel">
                    <i class="bi bi-shield-exclamation"></i> Konfirmasi Hapus Massal Mitra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('mitra.bulk-delete') }}" id="formBulkDelete">
                @csrf
                <div id="bulkDeleteHiddenInputs"></div>
                
                <div class="modal-body p-4">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 mb-3 p-3">
                        <div class="fw-bold small mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Perhatian Penting!</div>
                        <span class="small">Apakah Anda yakin ingin menghapus <strong id="modalBulkCountText">0</strong> data mitra yang dipilih?</span>
                    </div>
                    <p class="text-muted small mb-2">
                        Data mitra yang dihapus beserta riwayat transaksi alokasi honornya akan dihapus permanen dari sistem.
                    </p>
                    <label class="form-label fw-bold text-dark extra-small mb-1">Daftar Mitra yang Dipilih:</label>
                    <div class="border rounded-3 p-2.5 bg-light" style="max-height: 160px; overflow-y: auto;">
                        <ul class="list-unstyled mb-0 small text-dark" id="modalBulkNamesList"></ul>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-3">
                        <i class="bi bi-trash3-fill me-1"></i> Ya, Hapus Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function syncMitraStickyColumns() {
        const table = document.querySelector('.table-sticky');
        if (!table) return;
        const ths = table.querySelectorAll('thead tr th');
        if (ths.length < 5) return;

        const w1 = Math.round(ths[0].getBoundingClientRect().width);
        const w2 = Math.round(ths[1].getBoundingClientRect().width);
        const w3 = Math.round(ths[2].getBoundingClientRect().width);
        const w4 = Math.round(ths[3].getBoundingClientRect().width);

        const left1 = 0;
        const left2 = w1;
        const left3 = w1 + w2;
        const left4 = w1 + w2 + w3;
        const left5 = w1 + w2 + w3 + w4;

        table.querySelectorAll('tr').forEach(row => {
            const cells = row.children;
            if (cells.length >= 5) {
                cells[0].style.left = left1 + 'px';
                cells[1].style.left = left2 + 'px';
                cells[2].style.left = left3 + 'px';
                cells[3].style.left = left4 + 'px';
                cells[4].style.left = left5 + 'px';
            }
        });
    }

    syncMitraStickyColumns();
    setTimeout(syncMitraStickyColumns, 150);
    window.addEventListener('resize', syncMitraStickyColumns);

    // ==========================================
    // MULTI-SELECT & BULK DELETE LOGIC
    // ==========================================
    const checkAll = document.getElementById('checkAllMitra');
    const rowChecks = document.querySelectorAll('.mitra-row-check');
    const bulkBar = document.getElementById('bulkActionBar');
    const countBadge = document.getElementById('bulkSelectedCount');
    const btnCancel = document.getElementById('btnCancelBulkSelect');
    const btnTrigger = document.getElementById('btnTriggerBulkDelete');
    const hiddenInputsContainer = document.getElementById('bulkDeleteHiddenInputs');
    const modalCountText = document.getElementById('modalBulkCountText');
    const modalNamesList = document.getElementById('modalBulkNamesList');
    const bulkModalEl = document.getElementById('modalBulkDeleteConfirm');

    function updateBulkState() {
        const checkedRows = document.querySelectorAll('.mitra-row-check:checked');
        const count = checkedRows.length;

        if (count > 0) {
            countBadge.textContent = count;
            bulkBar.classList.remove('d-none');
            bulkBar.classList.add('d-flex');
        } else {
            bulkBar.classList.remove('d-flex');
            bulkBar.classList.add('d-none');
        }

        if (checkAll) {
            checkAll.checked = (count > 0 && count === rowChecks.length);
            checkAll.indeterminate = (count > 0 && count < rowChecks.length);
        }
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            rowChecks.forEach(chk => {
                chk.checked = checkAll.checked;
            });
            updateBulkState();
        });
    }

    rowChecks.forEach(chk => {
        chk.addEventListener('change', updateBulkState);
    });

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            rowChecks.forEach(chk => { chk.checked = false; });
            if (checkAll) checkAll.checked = false;
            updateBulkState();
        });
    }

    if (btnTrigger) {
        btnTrigger.addEventListener('click', function() {
            const checkedRows = document.querySelectorAll('.mitra-row-check:checked');
            if (checkedRows.length === 0) return;

            hiddenInputsContainer.innerHTML = '';
            modalNamesList.innerHTML = '';
            modalCountText.textContent = checkedRows.length;

            checkedRows.forEach(chk => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = chk.value;
                hiddenInputsContainer.appendChild(input);

                const li = document.createElement('li');
                li.className = 'py-0.5 d-flex align-items-center gap-1.5';
                li.innerHTML = `<i class="bi bi-person-x-fill text-danger"></i> <span>${chk.dataset.nama}</span>`;
                modalNamesList.appendChild(li);
            });

            const modal = new bootstrap.Modal(bulkModalEl);
            modal.show();
        });
    }
});
</script>

@endsection
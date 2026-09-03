@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Data Kegiatan & Mata Anggaran</h2>
        <p class="page-subtitle">Kelola data mata anggaran kegiatan statistik, kode MAK, volume, harga satuan, dan pagu anggaran per tim kerja</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap flex-wrap">
        @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'operator')
            <a href="{{ route('kegiatan.import.template') }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-2 shadow-none text-nowrap">
                <i class="bi bi-download"></i> Template Excel
            </a>
            <a href="{{ route('kegiatan.import.index') }}" class="btn btn-success btn-sm d-flex align-items-center gap-2 shadow-none text-nowrap">
                <i class="bi bi-cloud-arrow-up-fill"></i> Import Excel
            </a>
        @endif
        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('kegiatan.create') }}" class="btn btn-primary btn-sm d-flex align-items-center gap-2 shadow-sm text-nowrap">
                <i class="bi bi-plus-circle-fill"></i> Tambah Kegiatan & Mata Anggaran
            </a>
        @endif
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('kegiatan.index') }}" class="row g-2 align-items-center">
            <div class="col-6 col-md-2">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white">Tahun</span>
                    <input type="number" name="tahun" class="form-control" value="{{ $tahun && $tahun !== 'all' ? $tahun : '' }}" placeholder="Semua" min="2020" max="2099" onchange="if(!this.value){this.value='';}this.form.submit()">
                </div>
            </div>
            
            <div class="col-6 col-md-3">
                <select name="bidang_id" class="form-select" onchange="this.form.submit()" {{ auth()->user()->role === 'operator' && auth()->user()->bidang_id ? 'disabled' : '' }}>
                    @if(!auth()->user()->bidang_id || auth()->user()->role !== 'operator')
                        <option value="all">Semua Bidang / Tim Kerja</option>
                    @endif
                    @foreach($bidangs as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama kegiatan atau kode MAK..." value="{{ $search }}">
                </div>
            </div>

            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1.5 fw-semibold">
                    <i class="bi bi-search"></i> Cari
                </button>
                @if($search || ($bidangId && $bidangId !== 'all') || ($tahun && $tahun !== 'all'))
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
    .table-kegiatan-scroll {
        overflow-x: auto;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .table-sticky-freeze {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .table-sticky-freeze th,
    .table-sticky-freeze td {
        border-bottom: 1px solid #f1f5f9;
    }

    /* Base Sticky Columns for Kegiatan */
    .sticky-keg-1, .sticky-keg-2, .sticky-keg-3 {
        position: sticky !important;
        background-color: #ffffff;
        z-index: 5;
    }

    th.sticky-keg-1, th.sticky-keg-2, th.sticky-keg-3 {
        z-index: 6 !important;
        background-color: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .sticky-keg-1 {
        left: 0;
        width: 50px;
        min-width: 50px;
        max-width: 50px;
    }

    .sticky-keg-2 {
        left: 50px;
        width: 80px;
        min-width: 80px;
        max-width: 80px;
    }

    .sticky-keg-3 {
        left: 130px;
        width: 320px;
        min-width: 300px;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08) !important;
        border-right: 1px solid #cbd5e1 !important;
    }

    .table-hover tbody tr:hover .sticky-keg-1,
    .table-hover tbody tr:hover .sticky-keg-2,
    .table-hover tbody tr:hover .sticky-keg-3 {
        background-color: #f1f5f9 !important;
    }
</style>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-kegiatan-scroll">
            <table class="table table-hover table-sticky-freeze align-middle mb-0" style="min-width: 1700px;">
                <thead>
                    <tr>
                        <th class="ps-3 text-center sticky-keg-1">NO</th>
                        <th class="text-center sticky-keg-2">TAHUN</th>
                        <th style="width: 140px;">SUMBER</th>
                        <th class="sticky-keg-3">NAMA KEGIATAN</th>
                        <th style="width: 140px;">TIPE KEGIATAN</th>
                        <th style="min-width: 220px;">KODE MAK</th>
                        <th style="width: 130px;">BIDANG / TIM</th>
                        <th style="min-width: 180px;">VOLUME & PAGU ANGGARAN</th>
                        <th style="min-width: 170px;">JADWAL KEGIATAN</th>
                        <th class="text-center" style="width: 120px;">MITRA BERTUGAS</th>
                        <th class="text-end pe-3" style="width: 150px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kegiatans as $index => $kegiatan)
                    <tr>
                        <td class="ps-3 text-center fw-bold text-muted sticky-keg-1">{{ $kegiatans->firstItem() + $index }}</td>
                        <td class="text-center sticky-keg-2">
                            <span class="badge bg-light text-dark border">{{ $kegiatan->tahun ?? '2024' }}</span>
                        </td>
                        <td>
                            @if($kegiatan->revisi_ke)
                                <span class="badge bg-info bg-opacity-10 text-info border border-info-subtle px-2 py-1 extra-small fw-semibold">
                                    <i class="bi bi-file-earmark-text me-1"></i>{{ $kegiatan->revisi_ke }}
                                </span>
                            @else
                                <span class="text-muted extra-small fst-italic">-</span>
                            @endif
                        </td>
                        <td class="sticky-keg-3">
                            <div class="fw-bold text-dark fs-6" style="white-space: normal; word-break: break-word; line-height: 1.4;">{{ $kegiatan->nama }}</div>
                        </td>
                        <td>
                            @if($kegiatan->isPengolahan())
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 extra-small fw-semibold">
                                    <i class="bi bi-cpu-fill me-1"></i>Pengolahan
                                </span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 extra-small fw-semibold">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Pencacahan
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($kegiatan->kode_mata_anggaran)
                                <code class="px-2 py-1 bg-light border rounded text-dark font-monospace fw-bold small d-inline-block">{{ $kegiatan->kode_mata_anggaran }}</code>
                            @else
                                <span class="text-muted extra-small fst-italic">-</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $colorMap = [
                                    'Distribusi' => 'primary',
                                    'Neraca' => 'success',
                                    'Produksi' => 'warning',
                                    'Sosial' => 'info',
                                    'Cadangan' => 'secondary',
                                    'IPDS' => 'danger',
                                ];
                                $color = $colorMap[$kegiatan->bidang->nama ?? ''] ?? 'primary';
                            @endphp
                            <span class="badge badge-soft-{{ $color }}">
                                <i class="bi bi-building me-1"></i>{{ $kegiatan->bidang->nama ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $totalNominalKegiatan = $kegiatan->alokasiHonors->sum('nominal');
                                $paguTotal = $kegiatan->total > 0 ? $kegiatan->total : $totalNominalKegiatan;
                            @endphp
                            <div class="fw-extrabold text-success fs-6">Rp {{ number_format($paguTotal, 0, ',', '.') }}</div>
                            @if($kegiatan->jumlah > 0)
                                <span class="text-muted small">{{ number_format($kegiatan->jumlah) }} {{ $kegiatan->satuan ?? 'Volume' }} @ Rp {{ number_format($kegiatan->harga, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($kegiatan->jadwal_teks !== '-')
                                <span class="badge bg-light text-primary border px-2 py-1 fw-semibold">
                                    <i class="bi bi-calendar3 me-1 text-primary"></i>{{ $kegiatan->jadwal_teks }}
                                </span>
                            @else
                                <span class="text-muted small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($kegiatan->total_alokasi > 0)
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold border-0 bg-info bg-opacity-10 text-info" data-bs-toggle="modal" data-bs-target="#modalMitra_{{ $kegiatan->id }}" title="Klik untuk lihat daftar {{ $kegiatan->total_alokasi }} mitra">
                                    <i class="bi bi-people-fill me-1"></i> {{ number_format($kegiatan->total_alokasi) }} Mitra
                                </button>
                            @else
                                <span class="badge bg-light text-muted border fw-normal">0 Mitra</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                @if($kegiatan->total_alokasi > 0)
                                    <button type="button" class="btn-action bg-info bg-opacity-10 text-info border border-info border-opacity-25" data-bs-toggle="modal" data-bs-target="#modalMitra_{{ $kegiatan->id }}" title="Lihat Detail Mitra">
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </button>
                                @endif
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn-action btn-action-edit">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('kegiatan.destroy', $kegiatan) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata anggaran kegiatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                            Tidak ada data mata anggaran yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($kegiatans->hasPages())
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Menampilkan {{ $kegiatans->firstItem() }} - {{ $kegiatans->lastItem() }} dari {{ $kegiatans->total() }} kegiatan</span>
        {{ $kegiatans->links() }}
    </div>
    @endif
</div>

<!-- Modals Detail Mitra (Placed Outside Table for Valid HTML Rendering) -->
@foreach($kegiatans as $kegiatan)
    @if($kegiatan->total_alokasi > 0)
    <div class="modal fade" id="modalMitra_{{ $kegiatan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <div>
                        <h5 class="modal-title fw-bold fs-6 text-white mb-0"><i class="bi bi-people-fill text-info me-2"></i>Daftar Mitra Bertugas</h5>
                        <span class="small text-slate-300">{{ $kegiatan->nama }} ({{ $kegiatan->bidang->nama ?? '-' }})</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">NO</th>
                                    <th>NAMA MITRA</th>
                                    <th>ALAMAT DETAIL</th>
                                    <th>PERIODE</th>
                                    <th class="text-end pe-3">HONOR ALOKASI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kegiatan->alokasiHonors as $mNo => $alokasi)
                                <tr>
                                    <td class="ps-3 text-muted fw-bold">{{ $mNo + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $alokasi->mitra->nama ?? 'Mitra Dihapus' }}</div>
                                        <span class="text-muted small">{{ $alokasi->mitra->pekerjaan ?? '-' }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $alokasi->mitra->alamat ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $alokasi->periode->bulan ?? '' }} {{ $alokasi->periode->tahun ?? '' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-success">
                                        Rp {{ number_format($alokasi->nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end text-dark">TOTAL KEGIATAN:</td>
                                    <td class="text-end pe-3 text-success fs-6 fw-extrabold">
                                        Rp {{ number_format($kegiatan->alokasiHonors->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <span class="me-auto text-muted small"><i class="bi bi-info-circle me-1"></i> Total {{ $kegiatan->total_alokasi }} alokasi mitra bertugas</span>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <a href="{{ route('monitoring.create', ['kegiatan_id' => $kegiatan->id]) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Tambah Mitra Ke Kegiatan Ini
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function syncKegiatanStickyColumns() {
            const table = document.querySelector('.table-kegiatan-scroll table');
            if (!table) return;
            const ths = table.querySelectorAll('thead tr th');
            if (ths.length < 3) return;

            const w1 = Math.round(ths[0].getBoundingClientRect().width);
            const w2 = Math.round(ths[1].getBoundingClientRect().width);

            const left1 = 0;
            const left2 = w1;
            const left3 = w1 + w2;

            document.querySelectorAll('.sticky-keg-1').forEach(el => el.style.left = left1 + 'px');
            document.querySelectorAll('.sticky-keg-2').forEach(el => el.style.left = left2 + 'px');
            document.querySelectorAll('.sticky-keg-3').forEach(el => el.style.left = left3 + 'px');
        }

        syncKegiatanStickyColumns();
        setTimeout(syncKegiatanStickyColumns, 100);
        window.addEventListener('resize', syncKegiatanStickyColumns);
    });
</script>

@endsection

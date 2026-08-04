@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
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
            <i class="bi bi-person-plus-fill"></i> Tambah Mitra Baru
        </a>
    </div>
</div>

<!-- Filter Panel Matching Monitoring Style -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-2.5">
        <form method="GET" action="{{ route('mitra.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">JENIS KELAMIN</label>
                <select name="jk" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Jenis Kelamin</option>
                    <option value="L" {{ request('jk') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                    <option value="P" {{ request('jk') == 'P' ? 'selected' : '' }}>Perempuan</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">PEKERJAAN</label>
                <select name="pekerjaan" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Pekerjaan</option>
                    @foreach($pekerjaanList as $p)
                        <option value="{{ $p }}" {{ request('pekerjaan') == $p ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold mb-1">CARI MITRA</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik nama, alamat, atau kode..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                @if(request()->hasAny(['jk', 'pekerjaan', 'search']))
                    <a href="{{ route('mitra.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Symmetrical Data Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center ps-3" style="width: 50px;">NO</th>
                        <th style="width: 130px;">ID SOBAT</th>
                        <th style="width: 190px;">NAMA MITRA</th>
                        <th style="width: 150px;">NO. HP</th>
                        <th>ALAMAT DETAIL</th>
                        <th class="text-center" style="width: 110px;">KODE ALAMAT</th>
                        <th style="width: 160px;">PEKERJAAN</th>
                        <th class="text-center" style="width: 115px;">JENIS KELAMIN</th>
                        <th class="text-center pe-3" style="width: 130px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mitras as $i => $m)
                    <tr>
                        <td class="text-center ps-3 text-muted fw-semibold">{{ $mitras->firstItem() + $i }}</td>
                        <td class="text-center"><code class="bg-light px-2 py-0.5 rounded text-dark small">{{ $m->id_sobat ?? '-' }}</code></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 28px; height: 28px; font-size: 0.725rem;">
                                    {{ strtoupper(substr($m->nama, 0, 2)) }}
                                </div>
                                <span class="fw-bold text-dark text-wrap" style="white-space: normal; word-break: break-word;">{{ $m->nama }}</span>
                            </div>
                        </td>
                        <td class="text-slate-600 small">{{ $m->no_hp ?? '-' }}</td>
                        <td class="text-slate-600 small" style="white-space: normal; word-break: break-word;">{{ $m->alamat ?? '-' }}</td>
                        <td class="text-center"><code class="bg-light px-2 py-0.5 rounded text-dark small">{{ $m->kode_alamat ?? '-' }}</code></td>
                        <td><span class="text-slate-700 small" style="white-space: normal; word-break: break-word;">{{ $m->pekerjaan ?? 'Mitra' }}</span></td>
                        <td class="text-center">
                            @if($m->jk === 'L')
                                <span class="badge badge-soft-primary"><i class="bi bi-gender-male me-1"></i> Laki-laki</span>
                            @elseif($m->jk === 'P')
                                <span class="badge badge-soft-warning"><i class="bi bi-gender-female me-1"></i> Perempuan</span>
                            @else
                                <span class="badge bg-light text-muted">-</span>
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
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                            @if(request()->hasAny(['jk', 'pekerjaan', 'search']))
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
        <div class="p-2.5 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-2">
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
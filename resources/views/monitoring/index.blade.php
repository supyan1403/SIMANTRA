@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-eye-fill text-primary me-2"></i>Monitoring Alokasi Honor</h2>
        <p class="page-subtitle">Pantau alokasi honor pekerjaan mitra per periode dan kegiatan statistik</p>
    </div>
    <a href="{{ route('monitoring.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg"></i> Tambah Alokasi Honor
    </a>
</div>

<!-- Filter Panel -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-2.5">
        <form method="GET" action="{{ route('monitoring.index') }}" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">PERIODE</label>
                <select name="periode_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Periode</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>{{ $p->bulan }} {{ $p->tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">BIDANG / TIM</label>
                <select name="bidang_id" class="form-select" onchange="this.form.submit()" {{ auth()->user()->role === 'operator' && auth()->user()->bidang_id ? 'disabled' : '' }}>
                    @if(!auth()->user()->bidang_id || auth()->user()->role !== 'operator')
                        <option value="">Semua Bidang</option>
                    @endif
                    @foreach($bidangs as $b)
                        <option value="{{ $b->id }}" {{ (request('bidang_id') == $b->id || (auth()->user()->role === 'operator' && auth()->user()->bidang_id == $b->id)) ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label text-muted small fw-bold mb-1">CARI MITRA</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik nama mitra..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-funnel-fill"></i> Filter</button>
                @if(request()->hasAny(['periode_id', 'bidang_id', 'search']))
                    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th style="width: 200px;">Nama Mitra</th>
                        <th style="width: 130px;">Periode</th>
                        <th>Kegiatan</th>
                        <th style="width: 140px;">Bidang / Tim</th>
                        <th class="text-end" style="width: 150px;">Nominal Honor</th>
                        <th class="text-end pe-3" style="width: 130px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alokasis as $i => $a)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $alokasis->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word;">{{ $a->mitra->nama ?? '-' }}</div>
                            <span class="text-muted small">{{ $a->mitra->pekerjaan ?? 'Mitra' }}</span>
                        </td>
                        <td>
                            <span class="badge badge-soft-primary"><i class="bi bi-calendar-event me-1"></i>{{ $a->periode->bulan ?? '' }} {{ $a->periode->tahun ?? '' }}</span>
                        </td>
                        <td class="fw-semibold text-slate-700" style="white-space: normal; word-break: break-word;">{{ $a->kegiatan->nama ?? '-' }}</td>
                        <td>
                            <span class="badge badge-soft-info"><i class="bi bi-diagram-3 me-1"></i>{{ $a->kegiatan->bidang->nama ?? '-' }}</span>
                        </td>
                        <td class="text-end fw-extrabold text-success fs-6">
                            Rp {{ number_format($a->nominal, 0, ',', '.') }}
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('monitoring.edit', $a) }}" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <form method="POST" action="{{ route('monitoring.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Hapus alokasi honor ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                            Tidak ada data alokasi honor ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alokasis->hasPages())
        <div class="p-2.5 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-1">
                Menampilkan <strong>{{ $alokasis->firstItem() }}</strong> - <strong>{{ $alokasis->lastItem() }}</strong> dari <strong>{{ number_format($alokasis->total()) }}</strong> data
            </div>
            <div>
                {{ $alokasis->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

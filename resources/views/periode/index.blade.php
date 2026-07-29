@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-calendar3 text-primary me-2"></i>Kelola Periode</h2>
        <p class="page-subtitle">Daftar bulan dan tahun anggaran kegiatan honor mitra BPS</p>
    </div>
    <a href="{{ route('periode.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-circle-fill"></i> Tambah Periode Baru
    </a>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('periode.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama bulan atau tahun..." value="{{ $search }}">
                </div>
            </div>
            
            <div class="col-12 col-md-4">
                <select name="tahun" class="form-select" onchange="this.form.submit()">
                    <option value="all">Semua Tahun Anggaran</option>
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>Tahun {{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-filter"></i> Filter
                </button>
                @if($search || ($tahun && $tahun !== 'all'))
                    <a href="{{ route('periode.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 60px;">NO</th>
                        <th>NAMA BULAN</th>
                        <th>TAHUN ANGGARAN</th>
                        <th>URUTAN BULAN</th>
                        <th class="text-end pe-3" style="width: 140px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periodes as $index => $periode)
                    <tr>
                        <td class="ps-3 fw-bold text-muted">{{ $periodes->firstItem() + $index }}</td>
                        <td class="fw-bold text-dark">
                            <i class="bi bi-calendar-event text-primary me-2"></i>{{ $periode->bulan }}
                        </td>
                        <td>
                            <span class="badge badge-soft-primary">Tahun {{ $periode->tahun }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">Bulan Ke-{{ $periode->bulan_angka }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                <a href="{{ route('periode.edit', $periode) }}" class="btn-action btn-action-edit">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('periode.destroy', $periode) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus periode ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                            Tidak ada data periode yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($periodes->hasPages())
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Menampilkan {{ $periodes->firstItem() }} - {{ $periodes->lastItem() }} dari {{ $periodes->total() }} periode</span>
        {{ $periodes->links() }}
    </div>
    @endif
</div>

@endsection

@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-piggy-bank-fill text-primary me-2"></i>Master Batas Honor (SBML)</h2>
        <p class="page-subtitle">Atur nominal batas maksimal honor per mitra per bulan untuk Pencacahan, Pengolahan, dan Gabungan</p>
    </div>
    <a href="{{ route('master-sbml.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg"></i> Tambah Batas SBML
    </a>
</div>

@if($masters->isEmpty())
<div class="alert alert-warning border">
    <i class="bi bi-info-circle me-1"></i> Belum ada pengaturan batas SBML. Tambahkan batas per tahun agar peringatan kelebihan honor dapat dihitung.
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 55px;">No</th>
                        <th style="width: 140px;">Tahun</th>
                        <th>Batas Pencacahan (Lapangan)</th>
                        <th>Batas Pengolahan (Data)</th>
                        <th>Total Batas Gabungan</th>
                        <th class="text-end pe-3" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($masters as $i => $m)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $i + 1 }}</td>
                        <td>
                            <span class="badge badge-soft-primary fs-6 px-3 py-2"><i class="bi bi-calendar3 me-1"></i>{{ $m->tahun }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1"><i class="bi bi-geo-alt-fill me-1"></i>Lapangan</span>
                                <span class="fw-bold text-dark fs-6">Rp {{ number_format($m->nominal_pencacahan > 0 ? $m->nominal_pencacahan : $m->nominal, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1"><i class="bi bi-cpu-fill me-1"></i>Pengolahan</span>
                                <span class="fw-bold text-dark fs-6">Rp {{ number_format($m->nominal_pengolahan > 0 ? $m->nominal_pengolahan : 0, 0, ',', '.') }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="fw-extrabold text-success fs-6">Rp {{ number_format($m->nominal, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <a href="{{ route('master-sbml.edit', $m) }}" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-square"></i> Edit</a>
                                <form method="POST" action="{{ route('master-sbml.destroy', $m) }}" class="d-inline" onsubmit="return confirm('Hapus batas SBML tahun {{ $m->tahun }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i> Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-piggy-bank fs-1 text-muted d-block mb-2"></i>
                            Belum ada pengaturan batas SBML
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
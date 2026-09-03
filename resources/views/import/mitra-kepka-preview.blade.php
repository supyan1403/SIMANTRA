@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-people-fill text-success me-2"></i>Pratinjau Import Mitra Baru</h2>
        <p class="page-subtitle">Tinjau data mitra yang terbaca dari file sebelum disimpan</p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-people text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Data Baru</p>
                        <p class="fw-bold text-dark mb-0">{{ number_format($stats['total_items']) }} mitra</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-exclamation-triangle text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Duplikat Dilewati</p>
                        <p class="fw-bold text-dark mb-0">{{ number_format($stats['total_duplicate_skipped']) }} mitra</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #3b82f6 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-check-circle text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total di File</p>
                        <p class="fw-bold text-dark mb-0">{{ number_format($stats['total_items'] + $stats['total_duplicate_skipped']) }} baris</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-success me-2"></i>Baris Data Baru ({{ count($rows) }} mitra)</h6>
    </div>
    <div class="card-body p-0">
        @if(empty($rows))
            <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>Semua data sudah ada di database. Tidak ada data baru yang perlu diimport.</div>
            </div>
        @else
            <form method="POST" action="{{ route('import.mitra-kepka.process') }}">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th>Nama</th>
                                <th>Posisi</th>
                                <th>ID SOBAT</th>
                                <th>Alamat</th>
                                <th>Kecamatan</th>
                                <th>No Telp</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td class="fw-bold text-dark">{{ $row['nama'] }}</td>
                                <td><span class="badge badge-soft-primary">{{ $row['posisi'] }}</span></td>
                                <td><code class="small text-muted">{{ $row['id_sobat'] }}</code></td>
                                <td class="small text-muted">{{ $row['alamat_detail'] }}, {{ $row['kecamatan'] }}</td>
                                <td>{{ $row['kecamatan'] }}</td>
                                <td class="small">{{ $row['no_hp'] }}</td>
                                <td class="small text-muted">{{ $row['email'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        {{ count($rows) }} mitra baru akan ditambahkan. Duplikat akan dilewati.
                    </span>
                    <button type="submit" class="btn btn-success btn-lg fw-bold px-5 rounded-3" onclick="return confirm('Import {{ count($rows) }} mitra baru?')">
                        <i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Import Sekarang
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

@endsection
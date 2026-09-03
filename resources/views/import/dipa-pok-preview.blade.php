@extends('layouts.app')
@section('content')

<!-- Page Header -->
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check text-danger me-2"></i>Pratinjau Import {{ $jenisDokumen }}</h2>
        <p class="page-subtitle">Tinjau data anggaran dari file {{ $jenisDokumen }} ({{ $revisiKe }}) sebelum disimpan</p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<!-- Stats Card -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ef4444 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-file-earmark-medical text-danger fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Jenis</p>
                        <p class="fw-bold text-dark mb-0">{{ $jenisDokumen }} {{ $revisiKe }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #3b82f6 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-list-check text-primary fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Item</p>
                        <p class="fw-bold text-dark mb-0">{{ number_format($stats['total_items']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #10b981 !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-cash-stack text-success fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Total Anggaran</p>
                        <p class="fw-bold text-dark mb-0">Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #f59e0b !important;">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 rounded-3 p-2.5">
                        <i class="bi bi-calendar-event text-warning fs-4"></i>
                    </div>
                    <div>
                        <p class="text-muted small mb-0">Tahun</p>
                        <p class="fw-bold text-dark mb-0">{{ $stats['tahun'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Per Bidang Stats -->
@if(count($stats['per_bidang']) > 0)
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bar-chart text-primary me-2"></i>Ringkasan per Bidang</h6>
    </div>
    <div class="card-body p-3">
        <div class="row g-2">
            @foreach($stats['per_bidang'] as $bidang => $data)
            <div class="col-md-2 col-6">
                <div class="p-2.5 bg-light rounded-3 text-center border">
                    <p class="fw-bold text-dark mb-0 small">{{ $bidang }}</p>
                    <p class="text-muted mb-0 extra-small">{{ $data['count'] }} item</p>
                    <p class="text-success fw-bold mb-0 extra-small">Rp {{ number_format($data['total'], 0, ',', '.') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Data Table -->
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-success me-2"></i>Baris Data Terbaca ({{ count($rows) }} item)</h6>
    </div>
    <div class="card-body p-0">
        @if(empty($rows))
            <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>Tidak ditemukan baris data yang valid dari file ini.</div>
            </div>
        @else
            <form method="POST" action="{{ route('import.process') }}">
                @csrf
                <input type="hidden" name="path" value="{{ $path }}">
                <input type="hidden" name="jenis_dokumen" value="{{ $jenisDokumen }}">
                <input type="hidden" name="revisi_ke" value="{{ $revisiKe }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">

                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="ps-3" style="width: 50px;">No</th>
                                <th style="width: 80px;">Bidang</th>
                                <th style="width: 120px;">Kode MAK</th>
                                <th>Nama Kegiatan</th>
                                <th class="text-end" style="width: 80px;">Volume</th>
                                <th style="width: 80px;">Satuan</th>
                                <th class="text-end" style="width: 120px;">Harga</th>
                                <th class="text-end" style="width: 140px;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="ps-3 text-muted">{{ $i + 1 }}</td>
                                <td>
                                    <span class="badge badge-soft-primary">{{ $row['bidang'] }}</span>
                                </td>
                                <td><code class="small text-muted">{{ $row['kode_mata_anggaran'] }}</code></td>
                                <td>{{ $row['nama'] }}</td>
                                <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                <td>{{ $row['satuan'] }}</td>
                                <td class="text-end">Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                                <td class="text-end text-success fw-bold">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Total</td>
                                <td class="text-end fw-bold">{{ number_format(array_sum(array_column($rows, 'jumlah')), 0, ',', '.') }}</td>
                                <td></td>
                                <td></td>
                                <td class="text-end text-success fw-bold">Rp {{ number_format(array_sum(array_column($rows, 'total')), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="p-3 bg-light border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Semua {{ count($rows) }} baris akan diimport sebagai data kegiatan.
                    </span>
                    <button type="submit" class="btn btn-danger btn-lg fw-bold px-5 rounded-3" onclick="return confirm('Yakin ingin import {{ count($rows) }} data {{ $jenisDokumen }} ({{ $revisiKe }})?')">
                        <i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Import Sekarang
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>

@endsection

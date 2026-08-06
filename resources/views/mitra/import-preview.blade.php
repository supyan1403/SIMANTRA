@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check text-primary me-2"></i>Pratinjau Import Mitra</h2>
        <p class="page-subtitle">Tinjau baris yang terbaca dari file yang di-upload sebelum disimpan</p>
    </div>
    <a href="{{ route('mitra.import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-success me-2"></i>Baris Data Mitra Terdeteksi</h6>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('mitra.import.process') }}">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">

            @if(empty($rows))
                <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>Tidak ditemukan baris data yang valid (butuh kolom Nama / ID Sobat) pada file ini.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>ID Sobat</th>
                                <th>Nama</th>
                                <th>No. HP</th>
                                <th>Kabupaten / Kota</th>
                                <th>Kecamatan</th>
                                <th>Desa</th>
                                <th>Alamat Detail</th>
                                <th>Kode Alamat</th>
                                <th>Pekerjaan</th>
                                <th>JK</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="ps-3 text-muted">{{ $row['no'] ?: ($i + 1) }}</td>
                                <td><code class="bg-light px-2 py-0.5 rounded text-dark small">{{ $row['id_sobat'] ?: '-' }}</code></td>
                                <td class="fw-bold text-dark">{{ $row['nama'] }}</td>
                                <td>{{ $row['no_hp'] ?: '-' }}</td>
                                <td><span class="badge bg-light text-dark border fw-normal small">{{ $row['kabupaten_kota'] ?: 'Kabupaten Tasikmalaya' }}</span></td>
                                <td class="text-slate-700 small fw-semibold">{{ $row['kecamatan'] ?: '-' }}</td>
                                <td class="text-slate-700 small">{{ $row['desa'] ?: '-' }}</td>
                                <td class="text-slate-600 small">{{ $row['alamat_detail'] ?: '-' }}</td>
                                <td class="text-center"><code class="bg-light px-2 py-0.5 rounded small">{{ $row['kode_alamat'] ?: '-' }}</code></td>
                                <td>{{ $row['pekerjaan'] ?: '-' }}</td>
                                <td>
                                    @if($row['jk'])
                                        <span class="badge {{ $row['jk'] === 'L' ? 'badge-soft-primary' : 'badge-soft-warning' }}">
                                            <i class="bi bi-gender-{{ $row['jk'] === 'L' ? 'male' : 'female' }} me-1"></i>{{ $row['jk'] === 'L' ? 'Laki-laki' : 'Perempuan' }}
                                        </span>
                                    @elseif($row['jk_raw'] !== '')
                                        <span class="badge badge-soft-warning" title="Format tidak dikenali: {{ $row['jk_raw'] }}">
                                            <i class="bi bi-exclamation-circle me-1"></i>{{ $row['jk_raw'] }}
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small"><i class="bi bi-info-circle me-1"></i>{{ count($rows) }} baris akan diproses. Simpan untuk mengimpor.</span>
                    <button type="submit" class="btn btn-success btn-lg shadow-sm">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan / Proses Import
                    </button>
                </div>
            @endif
        </form>
    </div>
</div>

@endsection
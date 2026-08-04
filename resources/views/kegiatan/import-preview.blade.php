@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check text-primary me-2"></i>Pratinjau Import Mata Anggaran</h2>
        <p class="page-subtitle">Tinjau baris yang terbaca dari file yang di-upload sebelum disimpan</p>
    </div>
    <a href="{{ route('kegiatan.import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-success me-2"></i>Baris Data Terbaca</h6>
    </div>
    <div class="card-body p-0">
        <form method="POST" action="{{ route('kegiatan.import.process') }}">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">

            <div class="p-3 bg-light border-bottom d-flex flex-wrap align-items-center gap-3">
                <label for="tahun" class="form-label fw-bold text-dark mb-0"><i class="bi bi-calendar-event text-primary me-1"></i> Tahun Anggaran (default):</label>
                <select name="tahun" id="tahun" class="form-select w-auto fw-bold text-primary">
                    @foreach($tahunOptions as $t)
                        <option value="{{ $t }}" {{ $t == date('Y') ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <span class="text-muted small">Gunakan jika kolom "Tahun" pada file dikosongkan.</span>
            </div>

            @if(empty($rows))
                <div class="alert alert-warning m-3 d-flex align-items-center gap-2">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>Tidak ditemukan baris data yang valid (butuh kolom Bidang dan Kegiatan) pada file ini.</div>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">No</th>
                                <th>Bidang</th>
                                <th>Kegiatan</th>
                                <th>Akun (MAK)</th>
                                <th>Tahun</th>
                                <th class="text-end">Jumlah</th>
                                <th>Satuan</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                                <th>Jadwal (Bulan)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rows as $i => $row)
                            <tr>
                                <td class="ps-3 text-muted">{{ $row['no'] ?: ($i + 1) }}</td>
                                <td>
                                    @php $valid = in_array(strtolower($row['bidang']), array_map('strtolower', $bidangs->pluck('nama')->toArray())); @endphp
                                    <span class="badge {{ $valid ? 'badge-soft-primary' : 'badge-soft-warning' }}">
                                        {{ $row['bidang'] }}
                                        @if(!$valid) <i class="bi bi-exclamation-circle" title="Bidang belum terdaftar, akan dibuat otomatis"></i> @endif
                                    </span>
                                </td>
                                <td>{{ $row['kegiatan'] }}</td>
                                <td><code class="small text-muted">{{ $row['akun'] }}</code></td>
                                <td><span class="badge bg-light text-dark border">{{ $row['tahun'] ?: '-' }}</span></td>
                                <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                <td>{{ $row['satuan'] }}</td>
                                <td class="text-end">Rp {{ number_format($row['harga'], 0, ',', '.') }}</td>
                                <td class="text-end text-success fw-bold">Rp {{ number_format(($row['jumlah'] * $row['harga']) > 0 ? ($row['jumlah'] * $row['harga']) : $row['total'], 0, ',', '.') }}</td>
                                <td>
                                    @php
                                        $months = collect($row['jadwal'])->map(fn($v, $k) => $v > 0 ? $bulan[$k] : null)->filter();
                                    @endphp
                                    @if($months->isNotEmpty())
                                        <span class="badge badge-soft-info"><i class="bi bi-calendar-event me-1"></i>{{ $months->implode(', ') }}</span>
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
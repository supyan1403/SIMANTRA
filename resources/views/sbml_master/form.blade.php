@extends('layouts.app')
@section('content')

@php
    $isEdit = $master !== null;
@endphp

<div class="page-header">
    <div>
        <h2 class="page-title"><i class="bi bi-piggy-bank-fill text-primary me-2"></i>{{ $isEdit ? 'Edit Batas SBML' : 'Tambah Batas SBML' }}</h2>
        <p class="page-subtitle">{{ $isEdit ? 'Perbarui nominal batas honor Pencacahan, Pengolahan, dan Gabungan untuk tahun ' . $master->tahun : 'Tetapkan nominal batas honor per mitra per bulan untuk suatu tahun' }}</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ $isEdit ? route('master-sbml.update', $master) : route('master-sbml.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Tahun Anggaran <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
                               min="1980" max="2100" value="{{ old('tahun', $isEdit ? $master->tahun : date('Y')) }}" required>
                        @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                <i class="bi bi-geo-alt-fill text-primary me-1"></i>Batas Pencacahan / Lapangan (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="input_pencacahan" name="nominal_pencacahan" step="1" min="0" 
                                   class="form-control @error('nominal_pencacahan') is-invalid @enderror"
                                   value="{{ old('nominal_pencacahan', $isEdit ? ($master->nominal_pencacahan > 0 ? (int)$master->nominal_pencacahan : (int)$master->nominal) : 3326000) }}" required
                                   oninput="calculateTotal()">
                            @error('nominal_pencacahan') <div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-muted">Contoh: 3326000 (2024) atau 4500000 (2025).</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">
                                <i class="bi bi-cpu-fill text-warning-emphasis me-1"></i>Batas Pengolahan / Data (Rp) <span class="text-danger">*</span>
                            </label>
                            <input type="number" id="input_pengolahan" name="nominal_pengolahan" step="1" min="0" 
                                   class="form-control @error('nominal_pengolahan') is-invalid @enderror"
                                   value="{{ old('nominal_pengolahan', $isEdit ? (int)$master->nominal_pengolahan : 3077000) }}" required
                                   oninput="calculateTotal()">
                            @error('nominal_pengolahan') <div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text text-muted">Contoh: 3077000 (2024) atau 3000000 (2025).</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">
                            <i class="bi bi-wallet2 text-success me-1"></i>Total Batas Maksimal Gabungan / Bulan (Rp)
                        </label>
                        <input type="number" id="input_total" name="nominal" step="1" min="0" 
                               class="form-control fw-bold text-success @error('nominal') is-invalid @enderror"
                               value="{{ old('nominal', $isEdit ? (int)$master->nominal : 6403000) }}">
                        @error('nominal') <div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text text-muted">Total otomatis dihitung dari penjumlahan Pencacahan + Pengolahan jika mitra memegang 2 tugas sekaligus.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i> Simpan</button>
                        <a href="{{ route('master-sbml.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function calculateTotal() {
    const p = parseFloat(document.getElementById('input_pencacahan').value) || 0;
    const o = parseFloat(document.getElementById('input_pengolahan').value) || 0;
    document.getElementById('input_total').value = Math.round(p + o);
}
</script>

@endsection
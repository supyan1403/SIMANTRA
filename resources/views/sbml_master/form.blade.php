@extends('layouts.app')
@section('content')

@php
    $isEdit = $master !== null;
@endphp

<div class="page-header">
    <div>
        <h2 class="page-title"><i class="bi bi-piggy-bank-fill text-primary me-2"></i>{{ $isEdit ? 'Edit Batas SBML' : 'Tambah Batas SBML' }}</h2>
        <p class="page-subtitle">{{ $isEdit ? 'Perbarui nominal batas honor untuk tahun ' . $master->tahun : 'Tetapkan nominal batas honor per mitra per bulan untuk suatu tahun' }}</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ $isEdit ? route('master-sbml.update', $master) : route('master-sbml.store') }}">
                    @csrf
                    @if($isEdit) @method('PUT') @endif

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">Tahun <span class="text-danger">*</span></label>
                        <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror"
                               min="1980" max="2100" value="{{ old('tahun', $isEdit ? $master->tahun : '') }}" required>
                        @error('tahun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Batas Maksimal Honor / Bulan (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="nominal" step="0.01" min="0" class="form-control @error('nominal') is-invalid @enderror"
                               value="{{ old('nominal', $isEdit ? $master->nominal : '') }}" required>
                        @error('nominal') <div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text text-muted">Contoh: 4600000 (Rp 4.600.000). Total honor seorang mitra dalam satu bulan yang melebihi nilai ini akan ditandai.</div>
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

@endsection
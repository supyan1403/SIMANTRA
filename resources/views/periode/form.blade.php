@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-calendar-plus text-primary me-2"></i>Tambah Periode Baru</h2>
        <p class="page-subtitle">Formulir penambahan tahun dan bulan kerja periode survei baru</p>
    </div>
    <a href="{{ route('periode.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-calendar-event text-primary me-2"></i>Formulir Data Periode</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('periode.store') }}">
                    @csrf

                    <div class="mb-4">
                        <label for="tahun" class="form-label fw-bold">Tahun <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', date('Y')) }}" min="2020" max="2030" required>
                        @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="bulan_select" class="form-label fw-bold">Bulan <span class="text-danger">*</span></label>
                        <select class="form-select @error('bulan') is-invalid @enderror" id="bulan_select" name="bulan_select" onchange="updateBulanInfo(this)" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach($bulanList as $index => $namaBulan)
                                <option value="{{ $index + 1 }}" data-nama="{{ $namaBulan }}" {{ old('bulan_angka') == ($index + 1) ? 'selected' : '' }}>
                                    {{ $namaBulan }} (Urutan Bulan {{ $index + 1 }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="bulan" id="bulan_nama" value="{{ old('bulan') }}">
                        <input type="hidden" name="bulan_angka" id="bulan_angka" value="{{ old('bulan_angka') }}">
                        @error('bulan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('periode.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Periode
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function updateBulanInfo(select) {
    const selectedOption = select.options[select.selectedIndex];
    if (selectedOption && selectedOption.value) {
        document.getElementById('bulan_nama').value = selectedOption.getAttribute('data-nama');
        document.getElementById('bulan_angka').value = selectedOption.value;
    } else {
        document.getElementById('bulan_nama').value = '';
        document.getElementById('bulan_angka').value = '';
    }
}
</script>
@endpush

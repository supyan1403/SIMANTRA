@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check text-primary me-2"></i>Pratinjau Sheet Import</h2>
        <p class="page-subtitle">Pilih sheet bulanan yang ingin diimpor ke dalam database SIMANTRA</p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Batal / Upload Ulang
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-check-square text-success me-2"></i>Daftar Sheet Bulanan Terdeteksi</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('import.process') }}">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}">

                    @if(empty($sheets))
                        <div class="alert alert-warning d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <div>Tidak ditemukan sheet bulanan standar (JANUARI s/d DESEMBER) pada file ini.</div>
                        </div>
                    @else
                        <div class="mb-3 bg-light p-3 rounded-3">
                            <label for="tahun" class="form-label fw-bold text-dark mb-1"><i class="bi bi-calendar-event text-primary me-1"></i> Pilih Tahun Anggaran Impor:</label>
                            <select name="tahun" id="tahun" class="form-select w-auto fw-bold text-primary">
                                <option value="2025" selected>Tahun 2025</option>
                                <option value="2024">Tahun 2024</option>
                                <option value="2026">Tahun 2026</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 bg-light p-3 rounded-3">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" checked>
                                <label class="form-check-label fw-bold text-dark" for="selectAll">Pilih Semua Sheet Bulanan</label>
                            </div>
                            <span class="badge badge-soft-primary"><i class="bi bi-layers me-1"></i> {{ count($sheets) }} Sheet Ditemukan</span>
                        </div>

                        <div class="row g-3">
                            @foreach($sheets as $sheet)
                            <div class="col-md-4">
                                <div class="card border p-3 card-hover text-center">
                                    <div class="form-check d-flex align-items-center justify-content-center gap-2 mb-0">
                                        <input class="form-check-input sheet-checkbox" type="checkbox" name="sheets[]" value="{{ $sheet }}" id="sheet_{{ $sheet }}" checked>
                                        <label class="form-check-label fw-bold text-dark" for="sheet_{{ $sheet }}">
                                            <i class="bi bi-calendar-check text-primary me-1"></i> {{ $sheet }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="d-grid mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm">
                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Mulai Proses Import Data
                            </button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function toggleSelectAll(master) {
    const checkboxes = document.querySelectorAll('.sheet-checkbox');
    checkboxes.forEach(cb => cb.checked = master.checked);
}
</script>
@endpush

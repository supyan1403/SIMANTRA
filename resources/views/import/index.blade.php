@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Import Excel MANTRA</h2>
        <p class="page-subtitle">Upload file rekapitulasi Excel MANTRA untuk mengimpor data mitra, kegiatan, dan honor secara otomatis</p>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-excel text-success me-2"></i>Upload File Excel (.xlsx / .xls)</h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex gap-3 align-items-start mb-4 rounded-3 p-3">
                    <i class="bi bi-info-circle-fill text-info fs-4 flex-shrink-0 mt-1"></i>
                    <div class="small text-slate-700 w-100">
                        <strong>Petunjuk Import:</strong>
                        <ul class="mb-2 ps-3 mt-1">
                            <li>File Excel harus format standar MANTRA BPS (contoh: <code>1. Input MANTRA...xlsx</code>).</li>
                            <li>Sistem akan membaca sheet bulanan (<strong>JANUARI</strong> s/d <strong>DESEMBER</strong>).</li>
                            <li>Mitra, Kegiatan, Periode, Honor, dan SBML akan diimport/diupdate otomatis.</li>
                        </ul>
                        <div class="pt-2 border-top border-info border-opacity-25 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <span class="fw-bold text-dark"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-1"></i> File Dummy Siap Pakai:</span>
                            <a href="{{ asset('sample_mantra_dummy_2025.xlsx') }}" class="btn btn-sm btn-success fw-bold text-white shadow-sm" download>
                                <i class="bi bi-download me-1"></i> Download Sample Excel Dummy 2025 (.xlsx)
                            </a>
                        </div>
                    </div>
                </div>

                <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="form-label fw-bold">Pilih File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control form-control-lg @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx, .xls" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-search me-1"></i> Pratinjau Sheet Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

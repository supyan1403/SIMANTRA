@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Import Data Mitra (Sobat BPS)</h2>
        <p class="page-subtitle">Download template, lalu upload untuk menambahkan / memperbarui data mitra secara massal</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('mitra.import.template') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-download"></i> Download Template
        </a>
        <a href="{{ route('mitra.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 shadow-sm">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-file-earmark-excel text-success me-2"></i>Upload Template Excel (.xlsx / .xls)</h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info border-0 bg-info bg-opacity-10 d-flex gap-3 align-items-start mb-4 rounded-3 p-3">
                    <i class="bi bi-info-circle-fill text-info fs-4 flex-shrink-0 mt-1"></i>
                    <div class="small text-slate-700 w-100">
                        <strong>Langkah Import:</strong>
                        <ol class="mb-2 ps-3 mt-1">
                            <li>Download tombol <strong>Download Template</strong> untuk mendapat format Excel (sheet Template Mitra + Petunjuk).</li>
                            <li>Isi kolom <strong>ID Sobat</strong> (ID/kode mitra, unik &amp; siap untuk SKP) dan <strong>Nama</strong> (wajib), lalu No. HP, Kabupaten / Kota, Kecamatan, Desa / Kelurahan, Alamat Detail, Kode Alamat, Pekerjaan, Jenis Kelamin.</li>
                            <li>Jenis Kelamin bisa diisi <strong>1/L/Laki-laki</strong> atau <strong>2/P/Perempuan</strong>.</li>
                            <li>Upload file di bawah, pratinjau, lalu simpan. Baris dengan ID Sobat sama akan <strong>diperbarui</strong>, bukan duplikat.</li>
                        </ol>
                    </div>
                </div>

                <form method="POST" action="{{ route('mitra.import.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-4">
                        <label for="file" class="form-label fw-bold">Pilih File Template Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control form-control-lg @error('file') is-invalid @enderror" id="file" name="file" accept=".xlsx, .xls" required>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-primary btn-lg shadow-sm">
                            <i class="bi bi-search me-1"></i> Pratinjau Data Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
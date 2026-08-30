@extends('layouts.app')
@section('content')

<!-- Page Header -->
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-spreadsheet-fill text-success me-2"></i>Pusat Download Template &amp; Import Excel</h2>
        <p class="page-subtitle">Satu pintu untuk mengunduh template resmi berformat aman (Auto-Width &amp; Teks) dan mengunggah data Mitra serta Mata Anggaran</p>
    </div>
</div>

<!-- ========================================================================= -->
<!-- BAGIAN 1: 2 PILAR TEMPLATE RESMI SIMANTRA                                 -->
<!-- ========================================================================= -->
<div class="mb-4">
    <div class="d-flex align-items-center mb-3">
        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold me-2 px-2.5 py-1.5 rounded-pill"><i class="bi bi-download me-1"></i>PILIHAN TEMPLATE</span>
        <span class="text-uppercase small fw-bold text-muted">Unduh Format Standar SIMANTRA (22 Kolom Baku, Auto-Width &amp; Bebas Error NIK)</span>
    </div>

    <div class="row g-4">
        <!-- 1. Master Data Mitra -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-top: 4px solid #3b82f6 !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold px-2.5 py-1.5 rounded-pill">
                                <i class="bi bi-people-fill me-1"></i>Pilar 1: Database Mitra
                            </span>
                            <div class="p-2.5 bg-primary bg-opacity-10 text-primary rounded-3">
                                <i class="bi bi-person-vcard fs-4"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Template Master Data Mitra</h5>
                        <p class="text-secondary small mb-3">
                            Format profil mitra lengkap <strong>22 kolom baku</strong> (NIK 16 Digit Anti-Scientific, ID SOBAT, Posisi, Biodata, Alamat Detail, Pendidikan, Kontak, dan Dropdown Pengalaman Survei BPS).
                        </p>
                        <div class="d-flex flex-wrap gap-1 mb-4">
                            <span class="badge bg-light text-muted border extra-small">22 Kolom Baku</span>
                            <span class="badge bg-light text-muted border extra-small">NIK Format Teks (@)</span>
                            <span class="badge bg-light text-muted border extra-small">Dropdown Survei SP/ST/SE</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('import.template-mitra') }}" class="btn btn-primary w-100 fw-bold py-2.5 rounded-3 shadow-sm">
                            <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> Unduh Template Mitra
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Master Mata Anggaran -->
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white" style="border-top: 4px solid #f59e0b !important;">
                <div class="card-body p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 fw-bold px-2.5 py-1.5 rounded-pill">
                                <i class="bi bi-journal-bookmark-fill me-1"></i>Pilar 2: Mata Anggaran
                            </span>
                            <div class="p-2.5 bg-warning bg-opacity-10 text-warning rounded-3">
                                <i class="bi bi-cash-coin fs-4"></i>
                            </div>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">Template Master Mata Anggaran</h5>
                        <p class="text-secondary small mb-3">
                            Format daftar kegiatan statistik, kode akun MAK, tim kerja/bidang BPS, target volume, satuan, tarif harga satuan, serta total pagu anggaran.
                        </p>
                        <div class="d-flex flex-wrap gap-1 mb-4">
                            <span class="badge bg-light text-muted border extra-small">Kode MAK</span>
                            <span class="badge bg-light text-muted border extra-small">Tarif Satuan</span>
                            <span class="badge bg-light text-muted border extra-small">Pagu Anggaran</span>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('import.template-kegiatan') }}" class="btn btn-warning text-dark w-100 fw-bold py-2.5 rounded-3 shadow-sm">
                            <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> Unduh Template MAK
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- BAGIAN 2: SMART UPLOAD DROPZONE & DRY-RUN PREVIEW                         -->
<!-- ========================================================================= -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
        <div class="d-flex align-items-center">
            <span class="badge bg-success bg-opacity-10 text-success fw-bold me-2 px-2.5 py-1.5 rounded-pill"><i class="bi bi-cloud-arrow-up-fill me-1"></i>UPLOAD DATA</span>
            <h6 class="fw-bold text-dark mb-0">Unggah Berkas Excel (.xlsx / .xls)</h6>
        </div>
    </div>
    <div class="card-body p-4">
        <form method="POST" action="{{ route('import.preview') }}" enctype="multipart/form-data" id="importForm">
            @csrf
            
            <div class="border-2 border-dashed border-primary-subtle rounded-4 p-5 text-center bg-light bg-opacity-50 position-relative mb-3" id="dropzoneContainer" style="border: 2px dashed #93c5fd; transition: all 0.2s ease;">
                <input type="file" class="position-absolute top-0 start-0 w-100 h-100 opacity-0 cursor-pointer" id="fileInput" name="file" accept=".xlsx, .xls" required onchange="onFileSelected(this)" style="cursor: pointer; z-index: 10;">
                
                <div id="dropzonePrompt">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                        <i class="bi bi-cloud-arrow-up fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Pilih atau Seret (Drag &amp; Drop) Berkas Excel ke Sini</h5>
                    <p class="text-muted small mb-3">Mendukung format <strong>.xlsx</strong> dan <strong>.xls</strong>. Sistem otomatis mendeteksi jenis template yang Anda unggah.</p>
                    <span class="btn btn-outline-primary btn-sm px-4 py-2 fw-bold rounded-pill">
                        <i class="bi bi-folder2-open me-1"></i> Jelajahi Komputer Anda
                    </span>
                </div>

                <div id="dropzoneSelected" class="d-none">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-2" style="width: 56px; height: 56px;">
                        <i class="bi bi-file-earmark-excel-fill fs-2"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" id="selectedFileName">Nama_Berkas.xlsx</h6>
                    <span class="badge bg-light text-secondary border font-monospace mb-3" id="selectedFileSize">0 KB</span>
                    <br>
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill position-relative" style="z-index: 20;" onclick="clearFileSelection(event)">
                        <i class="bi bi-x-circle me-1"></i> Ganti Berkas Lain
                    </button>
                </div>
            </div>

            <!-- Fitur Proteksi Cerdas -->
            <div class="row g-2 mb-4">
                <div class="col-md-4">
                    <div class="p-2.5 bg-light rounded-3 d-flex align-items-center gap-2 border">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="extra-small text-secondary"><strong>Auto-Detect Kolom:</strong> Urutan kolom bebas.</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2.5 bg-light rounded-3 d-flex align-items-center gap-2 border">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <span class="extra-small text-secondary"><strong>Formula Cleaner:</strong> Otomatis bersihkan rumus.</span>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-2.5 bg-light rounded-3 d-flex align-items-center gap-2 border">
                        <i class="bi bi-eye-fill text-info fs-5"></i>
                        <span class="extra-small text-secondary"><strong>Dry-Run Safety:</strong> Cek sebelum simpan.</span>
                    </div>
                </div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg shadow-sm rounded-3 fw-bold py-3" id="submitBtn">
                    <i class="bi bi-search me-1.5"></i> Pratinjau &amp; Periksa Data (Dry-Run Preview)
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function onFileSelected(input) {
        if (input.files && input.files[0]) {
            const file = input.files[0];
            document.getElementById('selectedFileName').textContent = file.name;
            document.getElementById('selectedFileSize').textContent = (file.size / 1024).toFixed(1) + ' KB';
            document.getElementById('dropzonePrompt').classList.add('d-none');
            document.getElementById('dropzoneSelected').classList.remove('d-none');
            document.getElementById('dropzoneContainer').style.borderColor = '#10b981';
            document.getElementById('dropzoneContainer').style.backgroundColor = '#f0fdf4';
        }
    }

    function clearFileSelection(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        const input = document.getElementById('fileInput');
        input.value = '';
        document.getElementById('dropzonePrompt').classList.remove('d-none');
        document.getElementById('dropzoneSelected').classList.add('d-none');
        document.getElementById('dropzoneContainer').style.borderColor = '#93c5fd';
        document.getElementById('dropzoneContainer').style.backgroundColor = 'rgba(248, 250, 252, 0.5)';
    }

    // Drag and drop hover effects
    const dropzone = document.getElementById('dropzoneContainer');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            dropzone.style.borderColor = '#2563eb';
            dropzone.style.backgroundColor = '#eff6ff';
        }, false);
    });
    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, () => {
            if (!document.getElementById('fileInput').value) {
                dropzone.style.borderColor = '#93c5fd';
                dropzone.style.backgroundColor = 'rgba(248, 250, 252, 0.5)';
            }
        }, false);
    });
</script>

@endsection

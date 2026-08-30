@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-file-earmark-check-fill text-primary me-2"></i>Pratinjau Berkas Impor (Dry-Run Preview)</h2>
        <p class="page-subtitle">Periksa ringkasan struktur berkas sebelum data disinkronkan ke dalam database SIMANTRA</p>
    </div>
    <a href="{{ route('import.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2 rounded-3 shadow-none">
        <i class="bi bi-arrow-left"></i> Ganti / Upload Berkas Lain
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
                <div class="d-flex align-items-center justify-content-between">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 fw-bold px-3 py-1.5 rounded-pill">
                        <i class="bi bi-shield-check me-1"></i>Berkas Valid &amp; Siap Diproses
                    </span>
                    <span class="text-muted extra-small font-monospace">{{ $path }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('import.process') }}">
                    @csrf
                    <input type="hidden" name="path" value="{{ $path }}">

                    @if($isUniversal)
                        <!-- Universal All-in-One Preview -->
                        <div class="alert alert-success border-0 bg-success bg-opacity-10 d-flex gap-3 align-items-start mb-4 rounded-3 p-3.5">
                            <i class="bi bi-check-circle-fill text-success fs-3 flex-shrink-0 mt-1"></i>
                            <div>
                                <h6 class="fw-bold text-success mb-1">Format Terdeteksi: Template Universal All-in-One</h6>
                                <p class="small text-slate-700 mb-0">
                                    Berkas memuat 3 sheet master lengkap. Seluruh data Mitra, Mata Anggaran, dan Alokasi Penugasan akan otomatis disinkronkan ke sistem.
                                </p>
                            </div>
                        </div>

                        <div class="list-group mb-4 shadow-none rounded-3 border">
                            <div class="list-group-item d-flex align-items-center gap-3 py-3 border-bottom">
                                <div class="bg-primary bg-opacity-10 text-primary p-2.5 rounded-3 fs-4"><i class="bi bi-people-fill"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0.5 text-dark">1. Sheet DATA MITRA</h6>
                                    <small class="text-muted">Sinkronisasi ID SOBAT, NIK, Nama Lengkap, Posisi, No HP/WA, dan Alamat Domisili</small>
                                </div>
                            </div>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3 border-bottom">
                                <div class="bg-info bg-opacity-10 text-info p-2.5 rounded-3 fs-4"><i class="bi bi-journal-bookmark-fill"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0.5 text-dark">2. Sheet MATA ANGGARAN</h6>
                                    <small class="text-muted">Sinkronisasi Nama Kegiatan Statistik, Tim Kerja / Bidang, Kode MAK, dan Pagu Anggaran</small>
                                </div>
                            </div>
                            <div class="list-group-item d-flex align-items-center gap-3 py-3">
                                <div class="bg-success bg-opacity-10 text-success p-2.5 rounded-3 fs-4"><i class="bi bi-wallet2"></i></div>
                                <div>
                                    <h6 class="fw-bold mb-0.5 text-dark">3. Sheet ALOKASI PENUGASAN</h6>
                                    <small class="text-muted">Sinkronisasi Alokasi Honor Bulanan, Volume Tugas, Satuan, dan Tanggal Dokumen SPK</small>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success btn-lg shadow-sm fw-bold py-3 rounded-3">
                                <i class="bi bi-cloud-arrow-up-fill me-1.5"></i> Mulai Sinkronisasi Universal Sekarang
                            </button>
                        </div>

                    @else
                        <!-- Matrix MANTRA Multi-Sheet Preview -->
                        @if(empty($sheets))
                            <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 p-3">
                                <i class="bi bi-exclamation-triangle-fill fs-5 text-warning"></i>
                                <div>Tidak ditemukan sheet bulanan standar (JANUARI s/d DESEMBER) pada file ini.</div>
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-between mb-3 bg-light p-3 rounded-3 border">
                                <div>
                                    <label for="tahun" class="form-label fw-bold text-dark small mb-1"><i class="bi bi-calendar-event text-primary me-1"></i> TAHUN ANGGARAN:</label>
                                    <select name="tahun" id="tahun" class="form-select form-select-sm fw-bold text-primary border-primary-subtle" style="width: auto;">
                                        <option value="{{ date('Y') }}" selected>Tahun {{ date('Y') }}</option>
                                        <option value="2025">Tahun 2025</option>
                                        <option value="2024">Tahun 2024</option>
                                        <option value="2026">Tahun 2026</option>
                                    </select>
                                </div>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold px-3 py-2 rounded-pill">
                                    <i class="bi bi-layers me-1"></i> {{ count($sheets) }} Sheet Terbaca
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" checked style="cursor: pointer;">
                                    <label class="form-check-label fw-bold text-dark" for="selectAll" style="cursor: pointer;">Pilih Semua Sheet Bulanan</label>
                                </div>
                            </div>

                            <div class="row g-2.5 mb-4">
                                @foreach($sheets as $sheet)
                                <div class="col-6 col-md-4">
                                    <div class="card border p-2.5 text-center shadow-none rounded-3 bg-white">
                                        <div class="form-check d-flex align-items-center justify-content-center gap-2 mb-0">
                                            <input class="form-check-input sheet-checkbox" type="checkbox" name="sheets[]" value="{{ $sheet }}" id="sheet_{{ $sheet }}" checked style="cursor: pointer;">
                                            <label class="form-check-label fw-semibold text-dark small" for="sheet_{{ $sheet }}" style="cursor: pointer;">
                                                <i class="bi bi-calendar-check text-primary me-1"></i> {{ $sheet }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm fw-bold py-3 rounded-3">
                                    <i class="bi bi-check-circle-fill me-1.5"></i> Proses Impor Sheet Terpilih
                                </button>
                            </div>
                        @endif
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleSelectAll(source) {
    var checkboxes = document.querySelectorAll('.sheet-checkbox');
    for(var i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}
</script>
@endsection

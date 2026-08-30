@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-database-fill-gear text-primary me-2"></i>Manajemen Database</h2>
        <p class="page-subtitle mb-0">Pantau kesehatan database, cadangkan (backup), pulihkan (restore), optimasi performa, serta reset data sistem.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('database.backup') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm fw-semibold">
            <i class="bi bi-download"></i> Unduh Backup Database (.sqlite)
        </a>
    </div>
</div>

<!-- 1. METRIK RINGKASAN DATABASE -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Total Record Data</span>
                    <h3 class="fw-extrabold text-dark mb-0">{{ number_format($totalRecords) }}</h3>
                    <span class="text-muted extra-small">Dari seluruh tabel sistem</span>
                </div>
                <div class="avatar-initials bg-primary bg-opacity-10 text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                    <i class="bi bi-server"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Ukuran File Database</span>
                    <h3 class="fw-extrabold text-dark mb-0">{{ number_format($dbSize / 1024, 1) }} <span class="fs-6 fw-normal text-muted">KB</span></h3>
                    <span class="text-muted extra-small">Format: {{ strtoupper($connection) }}</span>
                </div>
                <div class="avatar-initials bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                    <i class="bi bi-hdd-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Terakhir Diperbarui</span>
                    <h6 class="fw-extrabold text-dark mb-0 mt-1" style="font-size: 0.95rem;">{{ $dbLastModified }}</h6>
                    <span class="text-muted extra-small">Waktu modifikasi disk</span>
                </div>
                <div class="avatar-initials bg-info bg-opacity-10 text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100 p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Status Koneksi</span>
                    <div class="mt-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.775rem;">
                            <span class="bg-success rounded-circle" style="width: 7px; height: 7px; display: inline-block;"></span>
                            Terhubung
                        </span>
                    </div>
                    <span class="text-muted extra-small d-block mt-1">Driver: {{ strtoupper($connection) }}</span>
                </div>
                <div class="avatar-initials bg-warning bg-opacity-10 text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.35rem;">
                    <i class="bi bi-shield-check"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- KOLOM KIRI: RINCIAN TABEL & ARSIP CADANGAN OTOMATIS -->
    <div class="col-lg-6">
        <!-- Card Rincian Tabel -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-table text-primary me-2"></i>Rincian Isi Data per Tabel</h6>
                <span class="badge bg-light text-muted border">{{ count($tables) }} Tabel Aktif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3 py-2.5 text-nowrap" style="min-width: 175px;">TABEL</th>
                            <th class="py-2.5 text-nowrap">DESKRIPSI</th>
                            <th class="py-2.5 text-nowrap" style="width: 100px;">KATEGORI</th>
                            <th class="text-end pe-3 py-2.5 text-nowrap" style="width: 120px;">TOTAL BARIS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tables as $tbl)
                        <tr>
                            <td class="ps-3 py-2.5 font-monospace text-slate-800 fw-bold text-nowrap">
                                <div class="d-inline-flex align-items-center gap-2">
                                    <i class="bi {{ $tbl['icon'] }} text-primary fs-6 flex-shrink-0"></i>
                                    <span>{{ $tbl['name'] }}</span>
                                </div>
                            </td>
                            <td class="py-2.5 text-dark">{{ $tbl['label'] }}</td>
                            <td class="py-2.5 text-nowrap">
                                <span class="badge {{ $tbl['category'] === 'Master' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle' }}">
                                    {{ $tbl['category'] }}
                                </span>
                            </td>
                            <td class="text-end pe-3 py-2.5 fw-bold text-nowrap {{ $tbl['count'] > 0 ? 'text-dark' : 'text-muted' }}">
                                {{ number_format($tbl['count']) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="ps-3 py-3 fw-bold text-dark text-nowrap">TOTAL SELURUH DATA</td>
                            <td class="text-end pe-3 py-3 fw-extrabold text-primary fs-6 text-nowrap">{{ number_format($totalRecords) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Card Arsip Cadangan Otomatis (Auto-Backup Server) -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-archive-fill text-primary me-2"></i>Arsip Cadangan Otomatis (Server)</h6>
                    <p class="text-muted extra-small mb-0 mt-0.5">Cadangan darurat yang otomatis dibuat sistem sesaat sebelum proses <em>Restore</em> atau <em>Hapus Database</em>.</p>
                </div>
                <span class="badge bg-light text-muted border">{{ count($backups) }} File</span>
            </div>
            <div class="card-body p-0">
                @if(empty($backups))
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-folder2-open fs-2 d-block mb-1 text-muted"></i>
                        <span class="small">Belum ada file cadangan otomatis di folder server.</span>
                    </div>
                @else
                    <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.8rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3 py-2">NAMA FILE</th>
                                    <th class="py-2">UKURAN</th>
                                    <th class="py-2">TANGGAL PEMBUATAN</th>
                                    <th class="text-end pe-3 py-2">AKSI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($backups as $b)
                                <tr>
                                    <td class="ps-3 py-2 font-monospace text-slate-800 fw-semibold">{{ $b['filename'] }}</td>
                                    <td class="py-2"><span class="badge bg-light text-dark border">{{ $b['size'] }}</span></td>
                                    <td class="py-2 text-muted">{{ $b['created_at'] }}</td>
                                    <td class="text-end pe-3 py-2">
                                        <a href="{{ route('database.download-backup', $b['filename']) }}" class="btn btn-xs btn-outline-primary py-0.5 px-2" title="Unduh file cadangan ini">
                                            <i class="bi bi-download me-1"></i> Unduh
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- KOLOM KANAN: AKSI PEMELIHARAAN & ZONA BAHAYA -->
    <div class="col-lg-6">
        <!-- 1. Card Backup & Restore -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cloud-arrow-down-fill text-primary me-2"></i>Cadangkan &amp; Pulihkan Database</h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <!-- Sub-Card 1: Unduh Backup -->
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100 d-flex flex-column justify-content-between bg-white shadow-none" style="padding: 1.25rem; border-color: #e2e8f0 !important;">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar-initials bg-primary bg-opacity-10 text-primary rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                        <i class="bi bi-download"></i>
                                    </div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">Unduh Cadangan</div>
                                </div>
                                <p class="text-muted small mb-4" style="line-height: 1.5;">
                                    Simpan salinan database aktif (<strong>.sqlite</strong>) ke komputer Anda secara mandiri untuk arsip data.
                                </p>
                            </div>
                            <a href="{{ route('database.backup') }}" class="btn btn-outline-primary btn-sm w-100 fw-bold py-2 shadow-none">
                                <i class="bi bi-download me-1.5"></i> Unduh Backup (.sqlite)
                            </a>
                        </div>
                    </div>

                    <!-- Sub-Card 2: Pulihkan Restore -->
                    <div class="col-md-6">
                        <div class="border rounded-3 h-100 d-flex flex-column justify-content-between bg-white shadow-none" style="padding: 1.25rem; border-color: #e2e8f0 !important;">
                            <div>
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="avatar-initials bg-success bg-opacity-10 text-success rounded-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; font-size: 1.1rem;">
                                        <i class="bi bi-upload"></i>
                                    </div>
                                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">Pulihkan Database</div>
                                </div>
                                <p class="text-muted small mb-4" style="line-height: 1.5;">
                                    Unggah file <strong>.sqlite</strong> untuk memulihkan data. Cadangan darurat otomatis dibuat sebelum ditimpa.
                                </p>
                            </div>
                            <button type="button" class="btn btn-outline-success btn-sm w-100 fw-bold py-2 shadow-none" data-bs-toggle="modal" data-bs-target="#modalRestoreDatabase">
                                <i class="bi bi-upload me-1.5"></i> Pulihkan Database
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Card Optimasi (Vacuum & Analyze) -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-speedometer text-success me-2"></i>Pemeliharaan &amp; Optimasi Database</h6>
            </div>
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-start gap-3" style="max-width: 380px;">
                        <div class="avatar-initials bg-success bg-opacity-10 text-success rounded-3 d-flex align-items-center justify-content-center flex-shrink-0 mt-0.5" style="width: 40px; height: 40px; font-size: 1.25rem;">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark mb-1" style="font-size: 0.9rem;">Optimasi Indeks &amp; Kompresi File (VACUUM)</div>
                            <p class="text-muted small mb-0" style="line-height: 1.5;">
                                Membersihkan ruang kosong sisa data lama yang pernah dihapus, menata ulang indeks tabel, dan mempercepat respons kueri aplikasi.
                            </p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('database.optimize') }}">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold px-4 py-2 shadow-sm d-flex align-items-center gap-1.5" onclick="return confirm('Jalankan optimasi database sekarang?')">
                            <i class="bi bi-lightning-charge-fill"></i> Jalankan Optimasi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. ZONA BAHAYA: HAPUS / RESET DATABASE -->
        <div class="card border-danger border-opacity-50 shadow-sm">
            <div class="card-header bg-danger bg-opacity-10 border-bottom border-danger border-opacity-25 py-3">
                <h6 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-2"></i>Zona Bahaya: Hapus / Reset Database</h6>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 mb-4 p-3.5">
                    <div class="fw-bold small mb-1.5"><i class="bi bi-shield-lock-fill me-1.5"></i> Perhatian Penting &amp; Pengamanan Data</div>
                    <div class="small" style="line-height: 1.55;">
                        Tindakan penghapusan tidak dapat dibatalkan. Sistem secara otomatis akan membuat <strong>1 salinan cadangan darurat (.sqlite)</strong> di server sesaat sebelum data dihapus. Diperlukan konfirmasi <strong>Password Administrator</strong> dan <strong>Frasa Persetujuan</strong>.
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold text-dark mb-2.5" style="font-size: 0.875rem;">Pilih Skenario Penghapusan Data:</label>
                    <div class="d-flex flex-column gap-3">
                        <!-- Opsi 1: Reset Transaksi Saja -->
                        <label class="list-group-item d-flex align-items-start gap-3 p-3 rounded-3 border bg-white cursor-pointer shadow-none">
                            <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="selected_wipe_scenario" value="transaksi" checked id="scenario_transaksi" style="width: 1.15rem; height: 1.15rem;">
                            <div class="ps-1">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">1. Hapus Data Transaksi Alokasi Honor &amp; Dokumen SPK (Rekomendasi Pergantian Tahun)</div>
                                <p class="text-muted small mb-0" style="line-height: 1.45;">
                                    Hanya menghapus riwayat transaksi alokasi honor dan nomor SPK/BAST. Data Master Mitra, Kegiatan, Bidang, SBML, serta <strong>seluruh akun Admin &amp; Operator TETAP AMAN</strong>.
                                </p>
                            </div>
                        </label>

                        <!-- Opsi 2: Hapus Data Mitra -->
                        <label class="list-group-item d-flex align-items-start gap-3 p-3 rounded-3 border bg-white cursor-pointer shadow-none">
                            <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="selected_wipe_scenario" value="mitra" id="scenario_mitra" style="width: 1.15rem; height: 1.15rem;">
                            <div class="ps-1">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">2. Hapus Data Master Mitra Statistik (+ Transaksi Terkait)</div>
                                <p class="text-muted small mb-0" style="line-height: 1.45;">
                                    Menghapus seluruh daftar mitra dan riwayat alokasi honornya. Data Kegiatan dan <strong>seluruh akun Admin &amp; Operator TETAP AMAN</strong>.
                                </p>
                            </div>
                        </label>

                        <!-- Opsi 3: Hapus Data Kegiatan -->
                        <label class="list-group-item d-flex align-items-start gap-3 p-3 rounded-3 border bg-white cursor-pointer shadow-none">
                            <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="selected_wipe_scenario" value="kegiatan" id="scenario_kegiatan" style="width: 1.15rem; height: 1.15rem;">
                            <div class="ps-1">
                                <div class="fw-bold text-dark mb-1" style="font-size: 0.875rem;">3. Hapus Data Master Kegiatan &amp; Anggaran (+ Transaksi Terkait)</div>
                                <p class="text-muted small mb-0" style="line-height: 1.45;">
                                    Menghapus seluruh data kegiatan &amp; pagu anggaran. Data Mitra dan <strong>seluruh akun Admin &amp; Operator TETAP AMAN</strong>.
                                </p>
                            </div>
                        </label>

                        <!-- Opsi 4: Factory Reset -->
                        <label class="list-group-item d-flex align-items-start gap-3 p-3 rounded-3 border border-danger border-opacity-40 bg-danger bg-opacity-10 cursor-pointer shadow-none">
                            <input class="form-check-input flex-shrink-0 mt-1" type="radio" name="selected_wipe_scenario" value="factory_reset" id="scenario_factory" style="width: 1.15rem; height: 1.15rem;">
                            <div class="ps-1">
                                <div class="fw-bold text-danger mb-1" style="font-size: 0.875rem;">4. Factory Reset (Kosongkan Seluruh Data Operasional Database)</div>
                                <p class="text-danger text-opacity-85 small mb-0" style="line-height: 1.45;">
                                    Mengosongkan data Mitra, Kegiatan, Alokasi, dan Template SPK. <strong>Seluruh akun Administrator &amp; Operator 100% DIPERTAHANKAN (TIDAK AKAN DIHAPUS)</strong>.
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="button" class="btn btn-danger fw-bold w-100 py-2.5 px-4 shadow-sm d-flex align-items-center justify-content-center gap-2" onclick="openWipeConfirmationModal()">
                    <i class="bi bi-trash3-fill"></i> Lanjutkan Penghapusan Terpilih...
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL RESTORE DATABASE                     -->
<!-- ========================================== -->
<div class="modal fade" id="modalRestoreDatabase" tabindex="-1" aria-labelledby="modalRestoreDatabaseLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalRestoreDatabaseLabel">
                    <i class="bi bi-upload"></i> Pulihkan Database (Restore)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('database.restore') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-warning border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> File database saat ini akan otomatis dicadangkan ke storage server sebelum ditimpa dengan file baru.
                    </div>
                    <div class="mb-3">
                        <label for="backup_file" class="form-label fw-bold small">Pilih File Cadangan (.sqlite) <span class="text-danger">*</span></label>
                        <input type="file" name="backup_file" id="backup_file" class="form-control" accept=".sqlite,.db,.sqlite3" required>
                        <div class="form-text">Maksimal ukuran file: 50MB. Ekstensi: .sqlite</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold px-3" onclick="return confirm('Apakah Anda yakin ingin memulihkan database dari file ini? Database saat ini akan ditimpa.')">
                        <i class="bi bi-upload me-1"></i> Mulai Pulihkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL VERIFIKASI KEAMANAN HAPUS DATABASE   -->
<!-- ========================================== -->
<div class="modal fade" id="modalWipeConfirm" tabindex="-1" aria-labelledby="modalWipeConfirmLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalWipeConfirmLabel">
                    <i class="bi bi-shield-exclamation"></i> Konfirmasi Keamanan Penghapusan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('database.wipe') }}" id="formWipeDatabase">
                @csrf
                <input type="hidden" name="wipe_type" id="input_wipe_type" value="transaksi">

                <div class="modal-body p-4">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger rounded-3 small mb-3">
                        <div class="fw-bold mb-0.5"><i class="bi bi-exclamation-octagon-fill me-1"></i> Tindakan Ini Menghapus Data Secara Permanen!</div>
                        <span id="wipe_scenario_description">Deskripsi skenario penghapusan...</span>
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label fw-bold small text-dark">1. Masukkan Password Akun Administrator Anda <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="confirm_password" class="form-control" placeholder="Ketik password admin..." required autocomplete="current-password">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_phrase" class="form-label fw-bold small text-dark">
                            2. Ketik frasa persetujuan: <code class="text-danger fw-bold">HAPUS-DATABASE-SIMANTRA</code> <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="confirm_phrase" id="confirm_phrase" class="form-control font-monospace" placeholder="Ketik: HAPUS-DATABASE-SIMANTRA" required autocomplete="off">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger btn-sm fw-bold px-4" id="btnSubmitWipe" disabled>
                        <i class="bi bi-trash3-fill me-1"></i> Eksekusi Hapus Database
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function openWipeConfirmationModal() {
        const selectedRadio = document.querySelector('input[name="selected_wipe_scenario"]:checked');
        const wipeType = selectedRadio ? selectedRadio.value : 'transaksi';
        
        document.getElementById('input_wipe_type').value = wipeType;
        const descEl = document.getElementById('wipe_scenario_description');
        
        if (wipeType === 'transaksi') {
            descEl.textContent = "Anda akan menghapus SELURUH data transaksi Alokasi Honor & SPK/BAST. Data Master Mitra, Kegiatan, serta seluruh akun Admin & Operator tetap tersimpan aman.";
        } else if (wipeType === 'mitra') {
            descEl.textContent = "Anda akan menghapus SELURUH database Mitra dan alokasi honor terkait. Seluruh akun Admin & Operator tetap tersimpan aman.";
        } else if (wipeType === 'kegiatan') {
            descEl.textContent = "Anda akan menghapus SELURUH data Kegiatan & Anggaran dan alokasi honor terkait. Seluruh akun Admin & Operator tetap tersimpan aman.";
        } else if (wipeType === 'factory_reset') {
            descEl.textContent = "Anda akan mengosongkan SELURUH data operasional (Mitra, Kegiatan, Alokasi, Template SPK). SELURUH akun Administrator & Operator 100% DIPERTAHANKAN dan TIDAK AKAN DIHAPUS.";
        }

        // Reset form inputs
        document.getElementById('confirm_password').value = '';
        document.getElementById('confirm_phrase').value = '';
        document.getElementById('btnSubmitWipe').disabled = true;

        const modalEl = document.getElementById('modalWipeConfirm');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // Enable button only when phrase matches
    document.addEventListener('DOMContentLoaded', function() {
        const phraseInput = document.getElementById('confirm_phrase');
        const pwdInput = document.getElementById('confirm_password');
        const submitBtn = document.getElementById('btnSubmitWipe');

        function checkValidity() {
            const isPhraseValid = phraseInput.value.trim().toUpperCase() === 'HAPUS-DATABASE-SIMANTRA';
            const isPwdFilled = pwdInput.value.length > 0;
            submitBtn.disabled = !(isPhraseValid && isPwdFilled);
        }

        if (phraseInput && pwdInput && submitBtn) {
            phraseInput.addEventListener('input', checkValidity);
            pwdInput.addEventListener('input', checkValidity);
        }
    });
</script>
@endpush

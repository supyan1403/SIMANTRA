@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-person-lines-fill text-primary me-2"></i>{{ isset($mitra) ? 'Edit Data Mitra' : 'Tambah Mitra Baru' }}</h2>
        <p class="page-subtitle">{{ isset($mitra) ? 'Perbarui informasi profil mitra statistik' : 'Isi formulir untuk menambahkan mitra baru' }}</p>
    </div>
    <a href="{{ route('mitra.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-card-heading text-primary me-2"></i>Formulir Data Mitra</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ isset($mitra) ? route('mitra.update', $mitra) : route('mitra.store') }}">
                    @csrf
                    @if(isset($mitra))
                        @method('PUT')
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="id_sobat" class="form-label fw-bold">ID Sobat</label>
                            <input type="text" class="form-control @error('id_sobat') is-invalid @enderror" id="id_sobat" name="id_sobat" value="{{ old('id_sobat', $mitra->id_sobat ?? '') }}" placeholder="Contoh: 320601234">
                            @error('id_sobat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="nik" class="form-label fw-bold">NIK KTP (16 Digit)</label>
                            <input type="text" maxlength="20" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $mitra->nik ?? '') }}" placeholder="Contoh: 3206010101900001">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="posisi" class="form-label fw-bold mb-0">Posisi Mitra</label>
                                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-bold" style="font-size: 0.75rem;" data-bs-toggle="modal" data-bs-target="#modalKelolaPosisi" id="btnOpenKelolaPosisi">
                                    <i class="bi bi-gear-fill me-1 text-primary"></i>Kelola Posisi
                                </button>
                            </div>
                            <select class="form-select @error('posisi') is-invalid @enderror" id="posisi" name="posisi">
                                <option value="">-- Pilih Posisi --</option>
                                @php
                                    $currentPosisi = old('posisi', $mitra->posisi ?? '');
                                @endphp
                                @foreach($posisiList ?? [] as $p)
                                    <option value="{{ $p->nama }}" {{ trim((string)$currentPosisi) === trim((string)$p->nama) ? 'selected' : '' }}>
                                        {{ $p->nama }}
                                    </option>
                                @endforeach
                                @if(!empty($currentPosisi) && (!isset($posisiList) || !$posisiList->contains('nama', $currentPosisi)))
                                    <option value="{{ $currentPosisi }}" selected>{{ $currentPosisi }}</option>
                                @endif
                            </select>
                            @error('posisi') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="nama" class="form-label fw-bold">Nama Lengkap Mitra <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $mitra->nama ?? '') }}" placeholder="Masukkan nama lengkap mitra..." required>
                            @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="no_hp" class="form-label fw-bold">Nomor HP / WhatsApp</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp', $mitra->no_hp ?? '') }}" placeholder="Contoh: 081234567890">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="email" class="form-label fw-bold">Alamat Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $mitra->email ?? '') }}" placeholder="nama@email.com">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Separated Address Section -->
                    <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill text-danger me-1"></i>Informasi Alamat Domisili</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="kabupaten_kota" class="form-label fw-bold">Kabupaten / Kota</label>
                                <select class="form-select @error('kabupaten_kota') is-invalid @enderror" id="kabupaten_kota" name="kabupaten_kota">
                                    @php $currentKab = old('kabupaten_kota', $mitra->kabupaten_kota ?? 'Kabupaten Tasikmalaya'); @endphp
                                    @foreach($kabupatenKotaList as $kab)
                                        <option value="{{ $kab }}" {{ $currentKab == $kab ? 'selected' : '' }}>{{ $kab }}</option>
                                    @endforeach
                                    @if(!in_array($currentKab, $kabupatenKotaList) && !empty($currentKab))
                                        <option value="{{ $currentKab }}" selected>{{ $currentKab }}</option>
                                    @endif
                                </select>
                                @error('kabupaten_kota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="kecamatan" class="form-label fw-bold">Kecamatan</label>
                                <select class="form-select @error('kecamatan') is-invalid @enderror" id="kecamatan" name="kecamatan">
                                    <option value="">-- Pilih Kecamatan --</option>
                                    @foreach($kecamatans as $kec)
                                        <option value="{{ $kec->nama }}" {{ old('kecamatan', $mitra->kecamatan ?? '') == $kec->nama ? 'selected' : '' }}>
                                            {{ $kec->nama }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kecamatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="desa" class="form-label fw-bold">Desa / Kelurahan</label>
                                <select class="form-select @error('desa') is-invalid @enderror" id="desa" name="desa">
                                    <option value="">-- Pilih Desa / Kelurahan --</option>
                                </select>
                                @error('desa') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="alamat_detail" class="form-label fw-bold">Alamat Detail (RT/RW, Kampung, Jalan)</label>
                            <textarea class="form-control @error('alamat_detail') is-invalid @enderror" id="alamat_detail" name="alamat_detail" rows="2" placeholder="Contoh: Kp. Cisarua RT. 003 RW. 002 / Jl. Raya No. 12">{{ old('alamat_detail', $mitra->alamat_detail ?? $mitra->alamat ?? '') }}</textarea>
                            @error('alamat_detail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Biodata Tambahan -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-2">
                            <label for="jk" class="form-label fw-bold">Jenis Kelamin</label>
                            <select class="form-select @error('jk') is-invalid @enderror" id="jk" name="jk">
                                <option value="">-- Pilih --</option>
                                <option value="L" {{ old('jk', $mitra->jk ?? '') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                <option value="P" {{ old('jk', $mitra->jk ?? '') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                            </select>
                            @error('jk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="pendidikan" class="form-label fw-bold">Pendidikan Terakhir</label>
                            @php 
                                $currPendidikan = old('pendidikan', $mitra->pendidikan ?? '');
                                if (in_array($currPendidikan, ['1','2','3','4','5','6','7'])) {
                                    $map = [
                                        '1' => 'SD / MI / Sederajat',
                                        '2' => 'SMP / MTs / Sederajat',
                                        '3' => 'SMA / SMK / MA / Sederajat',
                                        '4' => 'D1 / D2 / D3 (Diploma)',
                                        '5' => 'S1 / Sarjana (D4/S1)',
                                        '6' => 'S2 / Pascasarjana',
                                        '7' => 'S3 / Doktor',
                                    ];
                                    $currPendidikan = $map[$currPendidikan] ?? $currPendidikan;
                                }
                            @endphp
                            <select class="form-select @error('pendidikan') is-invalid @enderror" id="pendidikan" name="pendidikan">
                                <option value="">-- Pilih Pendidikan --</option>
                                <option value="SMA / SMK / MA / Sederajat" {{ $currPendidikan == 'SMA / SMK / MA / Sederajat' ? 'selected' : '' }}>SMA / SMK / MA / Sederajat</option>
                                <option value="S1 / Sarjana (D4/S1)" {{ $currPendidikan == 'S1 / Sarjana (D4/S1)' ? 'selected' : '' }}>S1 / Sarjana (D4/S1)</option>
                                <option value="D1 / D2 / D3 (Diploma)" {{ $currPendidikan == 'D1 / D2 / D3 (Diploma)' ? 'selected' : '' }}>D1 / D2 / D3 (Diploma)</option>
                                <option value="S2 / Pascasarjana" {{ $currPendidikan == 'S2 / Pascasarjana' ? 'selected' : '' }}>S2 / Pascasarjana</option>
                                <option value="SMP / MTs / Sederajat" {{ $currPendidikan == 'SMP / MTs / Sederajat' ? 'selected' : '' }}>SMP / MTs / Sederajat</option>
                                <option value="SD / MI / Sederajat" {{ $currPendidikan == 'SD / MI / Sederajat' ? 'selected' : '' }}>SD / MI / Sederajat</option>
                                <option value="S3 / Doktor" {{ $currPendidikan == 'S3 / Doktor' ? 'selected' : '' }}>S3 / Doktor</option>
                                <option value="Lainnya" {{ $currPendidikan == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                            </select>
                            @error('pendidikan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="tanggal_lahir" class="form-label fw-bold">Tanggal Lahir</label>
                            <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $mitra->tanggal_lahir_iso ?? $mitra->tanggal_lahir ?? '') }}">
                            @error('tanggal_lahir') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-2">
                            <label for="npwp" class="form-label fw-bold">NPWP</label>
                            <input type="text" class="form-control @error('npwp') is-invalid @enderror" id="npwp" name="npwp" value="{{ old('npwp', $mitra->npwp ?? '') }}" placeholder="Contoh: 12.345.678.9-425.000">
                            @error('npwp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-3">
                            <label for="pekerjaan" class="form-label fw-bold">Pekerjaan Utama</label>
                            <input type="text" class="form-control @error('pekerjaan') is-invalid @enderror" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan', $mitra->pekerjaan ?? '') }}" placeholder="Contoh: Swasta, Petani">
                            @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Rekam Jejak Pengalaman Survei BPS -->
                    <div class="card border border-info border-opacity-25 bg-info bg-opacity-10 p-3 mb-4 rounded-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-award-fill text-primary me-1"></i>Rekam Jejak Pengalaman Sensus & Survei BPS</h6>
                        <p class="small text-muted mb-3">Centang pengalaman survei statistik yang pernah diikuti oleh mitra ini:</p>
                        
                        <div class="row g-2">
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_sp" value="1" id="exp_sp" {{ old('exp_sp', $mitra->exp_sp ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_sp">Sensus Penduduk (SP)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_st" value="1" id="exp_st" {{ old('exp_st', $mitra->exp_st ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_st">Sensus Pertanian (ST)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_se" value="1" id="exp_se" {{ old('exp_se', $mitra->exp_se ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_se">Sensus Ekonomi (SE)</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_susenas" value="1" id="exp_susenas" {{ old('exp_susenas', $mitra->exp_susenas ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_susenas">Survei Susenas</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_sakernas" value="1" id="exp_sakernas" {{ old('exp_sakernas', $mitra->exp_sakernas ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_sakernas">Survei Sakernas</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="exp_sbh" value="1" id="exp_sbh" {{ old('exp_sbh', $mitra->exp_sbh ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold text-dark" for="exp_sbh">Survei Biaya Hidup (SBH)</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="kode_alamat" name="kode_alamat" value="{{ old('kode_alamat', $mitra->kode_alamat ?? '') }}">

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('mitra.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> {{ isset($mitra) ? 'Perbarui Data' : 'Simpan Mitra' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- MODAL KELOLA MASTER POSISI MITRA           -->
<!-- ========================================== -->
<div class="modal fade" id="modalKelolaPosisi" tabindex="-1" aria-labelledby="modalKelolaPosisiLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 880px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalKelolaPosisiLabel">
                    <i class="bi bi-person-badge-fill"></i> Kelola Master Posisi Mitra
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" style="background-color: #f8fafc;">
                <!-- Form Tambah Posisi Baru -->
                <div class="card border rounded-3 bg-white shadow-sm mb-4" style="padding: 1.25rem 1.5rem !important; border-color: #e2e8f0 !important;">
                    <div class="fw-bold text-dark mb-3 d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                        <i class="bi bi-plus-circle-fill text-primary"></i> Tambah Posisi Baru
                    </div>
                    <form id="formTambahPosisi" class="row g-2.5 align-items-center">
                        @csrf
                        <div class="col-md-5">
                            <input type="text" class="form-control" style="padding: 0.5rem 0.85rem; font-size: 0.875rem; border-radius: 0.5rem;" id="input_nama_posisi" name="nama" placeholder="Nama posisi (cth: Supervisor Lapangan)" required>
                        </div>
                        <div class="col-md-4">
                            <input type="text" class="form-control" style="padding: 0.5rem 0.85rem; font-size: 0.875rem; border-radius: 0.5rem;" id="input_ket_posisi" name="keterangan" placeholder="Keterangan singkat (opsional)">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-1.5" style="padding: 0.5rem 1rem; border-radius: 0.5rem;" id="btnSubmitPosisi">
                                <i class="bi bi-plus-lg"></i> Simpan Posisi
                            </button>
                        </div>
                    </form>
                    <div id="alertPosisiModal" class="d-none mt-2.5 alert py-2 px-3 small mb-0 rounded-2"></div>
                </div>

                <!-- Tabel Daftar Posisi Mitra yang Ada -->
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark" style="font-size: 0.925rem;">
                        <i class="bi bi-list-task me-1.5 text-primary"></i>Daftar Posisi Terdaftar
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold" id="totalPosisiBadge">0 Posisi</span>
                </div>
                <div class="table-responsive border rounded-3 bg-white shadow-sm" style="max-height: 360px; overflow-y: auto;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top" style="border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th class="text-muted small fw-bold text-uppercase" style="width: 46%; padding: 0.85rem 1.25rem;">NAMA POSISI</th>
                                <th class="text-muted small fw-bold text-uppercase" style="width: 38%; padding: 0.85rem 1rem;">KETERANGAN</th>
                                <th class="text-center text-muted small fw-bold text-uppercase" style="width: 16%; padding: 0.85rem 1.25rem;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyPosisiMitra">
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">Memuat daftar posisi...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white border-top py-2.5 px-4">
                <button type="button" class="btn btn-secondary px-4 fw-semibold rounded-2" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const kecamatansData = @json($kecamatans);
    const kecamatanSelect = document.getElementById('kecamatan');
    const desaSelect = document.getElementById('desa');
    const kodeAlamatInput = document.getElementById('kode_alamat');

    const currentDesa = "{{ old('desa', $mitra->desa ?? '') }}";

    function updateDesaDropdown(kecName, selectedDesaName) {
        desaSelect.innerHTML = '<option value="">-- Pilih Desa / Kelurahan --</option>';
        if (!kecName) return;

        const kec = kecamatansData.find(k => k.nama === kecName);
        if (kec && kec.desas) {
            kec.desas.forEach(d => {
                const opt = document.createElement('option');
                opt.value = d.nama;
                opt.textContent = d.nama;
                opt.dataset.kodeFull = d.kode_full;
                if (d.nama === selectedDesaName) {
                    opt.selected = true;
                }
                desaSelect.appendChild(opt);
            });
        }
    }

    kecamatanSelect.addEventListener('change', function () {
        updateDesaDropdown(this.value, '');
    });

    desaSelect.addEventListener('change', function () {
        const selectedOpt = this.options[this.selectedIndex];
        if (selectedOpt && selectedOpt.dataset.kodeFull) {
            kodeAlamatInput.value = selectedOpt.dataset.kodeFull;
        }
    });

    // Initial load
    if (kecamatanSelect.value) {
        updateDesaDropdown(kecamatanSelect.value, currentDesa);
    }

    // ==========================================
    // AJAX MASTER POSISI MITRA MANAGEMENT
    // ==========================================
    const posisiSelect = document.getElementById('posisi');
    const tbodyPosisi = document.getElementById('tbodyPosisiMitra');
    const totalPosisiBadge = document.getElementById('totalPosisiBadge');
    const formTambahPosisi = document.getElementById('formTambahPosisi');
    const alertModal = document.getElementById('alertPosisiModal');

    function showAlert(msg, isSuccess = true) {
        alertModal.className = isSuccess 
            ? 'mt-2.5 alert alert-success py-2 px-3 small mb-0 rounded-2 d-block'
            : 'mt-2.5 alert alert-danger py-2 px-3 small mb-0 rounded-2 d-block';
        alertModal.textContent = msg;
        setTimeout(() => { alertModal.className = 'd-none'; }, 4000);
    }

    function refreshPosisiList(autoSelectName = null) {
        fetch("{{ route('posisi-mitra.list-json') }}")
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const data = res.data;
                    totalPosisiBadge.textContent = `${data.length} Posisi`;

                    // 1. Refresh Table in Modal
                    tbodyPosisi.innerHTML = '';
                    if (data.length === 0) {
                        tbodyPosisi.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">Belum ada data posisi.</td></tr>';
                    } else {
                        data.forEach(p => {
                            const tr = document.createElement('tr');
                            tr.id = `row-posisi-${p.id}`;
                            tr.innerHTML = `
                                <td style="padding: 0.9rem 1.25rem;">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <span class="posisi-nama-text fw-bold text-dark" style="font-size: 0.9rem;">${p.nama}</span>
                                        <span class="badge rounded-pill flex-shrink-0" style="background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.75rem; font-weight: 600; padding: 0.35rem 0.65rem;">
                                            <i class="bi bi-people-fill me-1"></i>${p.mitra_count} Mitra
                                        </span>
                                    </div>
                                </td>
                                <td style="padding: 0.9rem 1rem;">
                                    <span class="text-slate-600 small" style="line-height: 1.45;">${p.keterangan || '-'}</span>
                                </td>
                                <td class="text-center" style="padding: 0.9rem 1.25rem;">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <button type="button" class="btn btn-outline-primary btn-edit-posisi d-flex align-items-center justify-content-center shadow-none" style="width: 34px; height: 34px; border-radius: 0.5rem; padding: 0;" data-id="${p.id}" data-nama="${p.nama}" data-ket="${p.keterangan === '-' ? '' : (p.keterangan || '')}" title="Edit Nama Posisi">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-delete-posisi d-flex align-items-center justify-content-center shadow-none" style="width: 34px; height: 34px; border-radius: 0.5rem; padding: 0;" data-id="${p.id}" data-nama="${p.nama}" title="Hapus Posisi">
                                            <i class="bi bi-trash3 fs-6"></i>
                                        </button>
                                    </div>
                                </td>
                            `;
                            tbodyPosisi.appendChild(tr);
                        });
                    }

                    // 2. Refresh Dropdown in Main Form
                    const selectedVal = autoSelectName !== null ? autoSelectName : posisiSelect.value;
                    posisiSelect.innerHTML = '<option value="">-- Pilih Posisi --</option>';
                    data.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.nama;
                        opt.textContent = p.nama;
                        if (p.nama === selectedVal) {
                            opt.selected = true;
                        }
                        posisiSelect.appendChild(opt);
                    });

                    // Attach edit events
                    document.querySelectorAll('.btn-edit-posisi').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const nama = this.dataset.nama;
                            const ket = this.dataset.ket;
                            const tr = document.getElementById(`row-posisi-${id}`);

                            tr.innerHTML = `
                                <td style="padding: 0.75rem 1.25rem;">
                                    <input type="text" class="form-control form-control-sm border-primary" style="border-radius: 0.4rem;" id="edit-nama-${id}" value="${nama}" placeholder="Nama posisi" required>
                                </td>
                                <td style="padding: 0.75rem 1rem;">
                                    <input type="text" class="form-control form-control-sm" style="border-radius: 0.4rem;" id="edit-ket-${id}" value="${ket}" placeholder="Keterangan (opsional)">
                                </td>
                                <td class="text-center" style="padding: 0.75rem 1.25rem;">
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <button type="button" class="btn btn-sm btn-success btn-save-posisi d-flex align-items-center justify-content-center shadow-none" style="width: 34px; height: 34px; border-radius: 0.4rem; padding: 0;" data-id="${id}" title="Simpan Perubahan">
                                            <i class="bi bi-check-lg fs-6"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-secondary btn-cancel-edit d-flex align-items-center justify-content-center shadow-none" style="width: 34px; height: 34px; border-radius: 0.4rem; padding: 0;" title="Batal">
                                            <i class="bi bi-x-lg fs-6"></i>
                                        </button>
                                    </div>
                                </td>
                            `;

                            tr.querySelector('.btn-cancel-edit').addEventListener('click', () => refreshPosisiList());
                            tr.querySelector('.btn-save-posisi').addEventListener('click', function() {
                                const newNama = document.getElementById(`edit-nama-${id}`).value.trim();
                                const newKet = document.getElementById(`edit-ket-${id}`).value.trim();
                                if (!newNama) {
                                    alert('Nama posisi tidak boleh kosong!');
                                    return;
                                }
                                updatePosisi(id, newNama, newKet);
                            });
                        });
                    });

                    // Attach delete events
                    document.querySelectorAll('.btn-delete-posisi').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const id = this.dataset.id;
                            const nama = this.dataset.nama;
                            if (confirm(`Hapus posisi '${nama}' dari master?`)) {
                                deletePosisi(id);
                            }
                        });
                    });
                }
            })
            .catch(err => console.error('Error fetching posisi list:', err));
    }

    function updatePosisi(id, nama, keterangan) {
        fetch(`/posisi-mitra/${id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ nama: nama, keterangan: keterangan })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showAlert(res.message, true);
                refreshPosisiList(nama);
            } else {
                showAlert(res.message || 'Gagal memperbarui posisi.', false);
            }
        })
        .catch(() => showAlert('Gagal memperbarui posisi.', false));
    }

    // Submit form tambah posisi
    if (formTambahPosisi) {
        formTambahPosisi.addEventListener('submit', function(e) {
            e.preventDefault();
            const nama = document.getElementById('input_nama_posisi').value.trim();
            const ket = document.getElementById('input_ket_posisi').value.trim();

            if (!nama) return;

            fetch("{{ route('posisi-mitra.store') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nama: nama, keterangan: ket })
            })
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    showAlert(res.message, true);
                    document.getElementById('input_nama_posisi').value = '';
                    document.getElementById('input_ket_posisi').value = '';
                    refreshPosisiList(nama);
                } else {
                    showAlert(res.message || 'Gagal menyimpan posisi', false);
                }
            })
            .catch(err => {
                showAlert('Gagal menghubungi server.', false);
            });
        });
    }

    function deletePosisi(id) {
        fetch(`/posisi-mitra/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                showAlert(res.message, true);
                refreshPosisiList();
            } else {
                showAlert(res.message || 'Gagal menghapus posisi.', false);
            }
        })
        .catch(() => showAlert('Gagal menghapus posisi.', false));
    }

    // Modal open trigger
    const btnOpenModal = document.getElementById('btnOpenKelolaPosisi');
    if (btnOpenModal) {
        btnOpenModal.addEventListener('click', function() {
            refreshPosisiList();
        });
    }
});
</script>

@endsection
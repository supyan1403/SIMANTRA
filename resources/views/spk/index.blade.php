@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Modul Cetak SPK Multi-Petugas</h4>
        <p class="text-muted small mb-0">Cetak Surat Perjanjian Kerja (SPK) Kolektif Mitra BPS</p>
    </div>
    <a href="{{ route('spk.templates.index') }}" class="btn btn-outline-primary fw-bold shadow-sm">
        <i class="bi bi-folder-symlink-fill me-1"></i> Kelola Template Dokumen
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-octagon-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ========================================== -->
<!-- FORM FILTER & KONFIGURASI DOKUMEN           -->
<!-- ========================================== -->
<form method="POST" action="{{ route('spk.cetak-massal') }}" id="bulkForm" target="_blank">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="jenis_dokumen" value="{{ $selectedTemplate->jenis_dokumen ?? 'spk' }}">
    <input type="hidden" name="bulan_spk" id="formBulanSpk" value="{{ $bulanSpk }}">
    <input type="hidden" name="tahun_spk" id="formTahunSpk" value="{{ $tahunSpk }}">

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-sliders text-primary me-2"></i>Panel Konfigurasi Dokumen & Filter SPK / BAST
            </h6>
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1.5 rounded-pill">
                <i class="bi bi-lightning-charge-fill me-1"></i>Penomoran Otomatis BPS
            </span>
        </div>
        <div class="card-body p-4">
            <!-- SECTION 1: PERIODE & TARGET KEGIATAN -->
            <div class="mb-3">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold me-2 px-2 py-1">LANGKAH 1</span>
                    <span class="text-uppercase small fw-bold text-muted">Filter Periode Kerja & Lingkup Kegiatan Mitra</span>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-2">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-calendar-event text-primary me-1"></i>TAHUN ANGGARAN
                        </label>
                        <select name="tahun" id="filterTahun" class="form-select border-primary-subtle fw-bold py-2" onchange="submitFilter()">
                            @foreach($tahunList as $t)
                                <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-calendar2-month text-primary me-1"></i>BULAN AWAL KERJA
                        </label>
                        <select name="bulan_awal" id="filterBulanAwal" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                            @foreach($monthOptions as $angka => $nm)
                                <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-2">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-calendar2-check text-primary me-1"></i>BULAN AKHIR KERJA
                        </label>
                        <select name="bulan_akhir" id="filterBulanAkhir" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                            @foreach($monthOptions as $angka => $nm)
                                <option value="{{ $angka }}" {{ $bulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-6 col-md-3">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-tag-fill text-primary me-1"></i>KATEGORI KEGIATAN
                        </label>
                        <select name="kategori_kegiatan" id="kategoriKegiatanSelect" class="form-select border-primary-subtle fw-bold py-2" onchange="onKategoriChanged(this.value)">
                            <option value="sensus" {{ $kategoriKegiatan === 'sensus' ? 'selected' : '' }}>📌 Sensus (Pertanian / Ekonomi / Penduduk)</option>
                            <option value="survei" {{ $kategoriKegiatan === 'survei' ? 'selected' : '' }}>📊 Survei (Bulanan / Triwulanan / Rutin)</option>
                            <option value="umum" {{ $kategoriKegiatan === 'umum' ? 'selected' : '' }}>🏢 Umum / Rutin BPS Lainnya</option>
                        </select>
                    </div>

                    @if(auth()->user()->role === 'admin')
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-diagram-3 text-primary me-1"></i>BIDANG / TIM KERJA
                        </label>
                        <select name="bidang_id" id="filterBidang" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                            <option value="">Semua Bidang</option>
                            @foreach($bidangOptions as $b)
                                <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @else
                    <div class="col-12 col-md-3">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-journal-check text-primary me-1"></i>KEGIATAN
                        </label>
                        <select name="kegiatan_id" id="filterKegiatan" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                            <option value="">Semua Kegiatan</option>
                            @foreach($kegiatanOptions as $k)
                                <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>

                @if(auth()->user()->role === 'admin')
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-journal-check text-primary me-1"></i>FILTER SPESIFIK KEGIATAN (OPSIONAL)
                        </label>
                        <select name="kegiatan_id" id="filterKegiatan" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                            <option value="">Semua Kegiatan (Tampilkan seluruh kegiatan pada periode terpilih)</option>
                            @foreach($kegiatanOptions as $k)
                                <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>

            <hr class="my-4 text-muted opacity-25">

            <!-- SECTION 2: TEMPLATE & POLA PENOMORAN SPK DENGAN INPUT BULAN & TAHUN TERSENDIRI -->
            <div class="mb-3">
                <div class="d-flex align-items-center mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold me-2 px-2 py-1">LANGKAH 2</span>
                    <span class="text-uppercase small fw-bold text-muted">Konfigurasi Penomoran Dokumen SPK (Nomor, Bulan & Tahun Surat)</span>
                </div>
                
                <!-- Baris 1: Template Dokumen & Pola Format SPK (Lebar 50% / 50%) -->
                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-file-earmark-text text-primary me-1"></i>TEMPLATE DOKUMEN
                        </label>
                        <select name="template_id_select" id="templateSelect" class="form-select border-primary-subtle fw-bold py-2" onchange="document.getElementById('filterTemplateId').value=this.value; submitFilter();">
                            @forelse($templates as $tmpl)
                                <option value="{{ $tmpl->id }}" {{ $currentTemplateId == $tmpl->id ? 'selected' : '' }}>
                                    [{{ strtoupper($tmpl->jenis_dokumen) }}] {{ $tmpl->nama }}
                                </option>
                            @empty
                                <option value="">Template Baku BPS (Default System)</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label text-secondary fw-bold small mb-0">
                                <i class="bi bi-list-nested text-primary me-1"></i>POLA / FORMAT NO. SPK
                            </label>
                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold text-primary" onclick="editCurrentOption()" title="Klik untuk edit/sesuaikan teks pola yang dipilih">
                                <i class="bi bi-pencil-square me-1"></i>Sesuaikan / Edit
                            </button>
                        </div>
                        <select id="formatSpkSelect" class="form-select border-primary-subtle fw-semibold font-monospace py-2" onchange="onFormatSpkChanged(this.value)">
                            <!-- Populated dynamically via JS based on Kategori Kegiatan -->
                        </select>
                        <input type="hidden" name="format_spk" id="formatSpkInput" value="{{ $formatSpk }}">
                    </div>
                </div>

                <!-- Baris 2: Bulan No. SPK, Tahun No. SPK, dan Nomor Urut Awal (Seimbang 3 Kolom) -->
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-calendar-check text-primary me-1"></i>BULAN PENOMORAN SPK
                        </label>
                        <select id="bulanSpkSelect" class="form-select border-primary-subtle fw-bold py-2" onchange="updateLivePreview()">
                            @foreach($monthOptions as $angka => $nm)
                                <option value="{{ $angka }}" {{ $bulanSpk == $angka ? 'selected' : '' }}>
                                    {{ sprintf('%02d', $angka) }} - {{ $nm }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-calendar text-primary me-1"></i>TAHUN PENOMORAN SPK
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-primary-subtle fw-bold py-2">Tahun</span>
                            <input type="text" id="tahunSpkInput" class="form-control border-primary-subtle fw-bold py-2" value="{{ $tahunSpk }}" placeholder="2026" oninput="updateLivePreview()">
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label text-secondary fw-bold small mb-1">
                            <i class="bi bi-123 text-primary me-1"></i>NOMOR URUT AWAL
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted border-primary-subtle fw-bold py-2">No.</span>
                            <input type="number" name="nomor_awal" id="nomorAwalInput" class="form-control border-primary-subtle fw-bold py-2" value="{{ $nomorAwal }}" min="1" placeholder="1" oninput="updateLivePreview()">
                        </div>
                    </div>
                </div>

                <!-- Custom Format Box (Shown when 'custom' or 'Edit' is triggered) -->
                <div class="row mt-3 d-none" id="customFormatRow">
                    <div class="col-12">
                        <div class="p-3 bg-warning bg-opacity-10 rounded-3 border border-warning border-opacity-50">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-bold text-dark">
                                    <i class="bi bi-pencil-fill text-warning me-1"></i>Kotak Sesuaikan Pola Nomor SPK:
                                </span>
                                <button type="button" class="btn-close btn-close-sm" style="font-size: 0.75rem;" onclick="resetCustomFormat()" title="Tutup / Reset"></button>
                            </div>
                            <input type="text" id="customFormatInput" class="form-control font-monospace fw-bold text-dark border-warning" placeholder="Contoh: B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}" oninput="document.getElementById('formatSpkInput').value=this.value; updateLivePreview();">
                            <div class="text-muted extra-small mt-2">
                                <span class="fw-bold">Variabel Otomatis:</span> 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{nomor}</code> (0001) &bull; 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{nomor_raw}</code> (1) &bull; 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{bulan}</code> (Bulan No. SPK) &bull; 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{bulan_romawi}</code> (Bulan Romawi) &bull; 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{tahun}</code> (Tahun SPK) &bull; 
                                <code class="text-primary bg-white px-1.5 py-0.5 rounded border">{jenis}</code> (SPK/BAST)
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SECTION 3: LIVE PREVIEW & TOMBOL CETAK MASSAL -->
            <div class="row mt-4 pt-3 border-top align-items-center">
                <div class="col-12 col-md-7 col-lg-8">
                    <div class="d-flex align-items-center bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3 px-3 py-2">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 38px; height: 38px; flex-shrink: 0;">
                            <i class="bi bi-eye-fill fs-5"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-muted extra-small text-uppercase fw-bold">Live Preview Penomoran Dokumen Pertama:</div>
                            <div class="font-monospace fw-bold text-primary fs-6 text-truncate" id="livePreviewBadge">B-0001/BPS/3206/SPK/01/2026</div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-5 col-lg-4 text-md-end mt-3 mt-md-0">
                    <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm px-4 py-2 w-100 w-md-auto fs-6">
                        <i class="bi bi-printer-fill me-1"></i> CETAK MASSAL MITRA TERPILIH
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Hidden form for GET filtering -->
<form method="GET" action="{{ route('spk.index') }}" id="getFilterForm" class="d-none">
    <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" id="hiddenBulanAwal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" id="hiddenBulanAkhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="kategori_kegiatan" id="hiddenKategori" value="{{ $kategoriKegiatan }}">
    <input type="hidden" name="template_id" id="filterTemplateId" value="{{ $currentTemplateId }}">
    <input type="hidden" name="bidang_id" id="hiddenBidang" value="{{ $bidangId }}">
    <input type="hidden" name="kegiatan_id" id="hiddenKegiatan" value="{{ $kegiatanId }}">
    <input type="hidden" name="search" id="hiddenSearch" value="{{ $search }}">
</form>

<!-- ========================================== -->
<!-- TABEL DAFTAR MITRA MULTI-PETUGAS           -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <input type="checkbox" id="selectAllMitra" class="form-check-input mt-0" style="width: 20px; height: 20px; cursor: pointer;" onclick="toggleSelectAll(this)">
            <label for="selectAllMitra" class="fw-bold text-dark mb-0 fs-6" style="cursor: pointer;">
                PILIH SEMUA MITRA <span class="badge bg-light text-primary border ms-1">{{ $spkList->count() }} Mitra Terpilih</span>
            </label>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="input-group" style="max-width: 320px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                <input type="text" id="searchInput" class="form-control border-start-0" placeholder="Cari Nama / ID Sobat..." value="{{ $search }}" onkeydown="if(event.key==='Enter'){ searchMitra(); }">
                @if($search)
                    <a href="{{ route('spk.index', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId, 'bidang_id' => $bidangId])) }}" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="bi bi-x"></i></a>
                @endif
                <button type="button" class="btn btn-primary" onclick="searchMitra()">Cari</button>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="ps-4 text-center" style="width: 50px;">PILIH</th>
                        <th>NAMA MITRA</th>
                        <th>ID SOBAT (SPK)</th>
                        <th>NO. HP</th>
                        <th class="text-center">JUMLAH KEGIATAN</th>
                        <th class="text-end">TOTAL HONOR</th>
                        <th class="text-center pe-4" style="width: 260px;">AKSI CETAK INDIVIDUAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spkList as $idx => $spk)
                        <tr>
                            <td class="ps-4 text-center">
                                <input type="checkbox" name="mitra_ids[]" value="{{ $spk->mitra_id }}" form="bulkForm" class="form-check-input mitra-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $spk->mitra->nama }}</div>
                                <div class="text-muted extra-small">{{ $spk->mitra->pekerjaan ?? 'Mitra BPS' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold font-monospace px-2 py-1">
                                    {{ $spk->mitra->id_sobat ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $spk->mitra->no_hp ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1">
                                    {{ $spk->total_kegiatan }} Kegiatan
                                </span>
                            </td>
                            <td class="text-end fw-extrabold text-success">
                                Rp {{ number_format($spk->total_honor, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="javascript:void(0)" onclick="cetakIndividu('{{ $spk->mitra_id }}', {{ $idx }})" class="btn btn-outline-danger fw-bold px-2 py-1" title="Cetak / Simpan PDF Dokumen">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> {{ (isset($selectedTemplate) && $selectedTemplate->jenis_dokumen === 'bast') ? 'BAST' : 'SPK' }}
                                    </a>
                                    <a href="{{ route('spk.cetak-lampiran', array_filter(['mitra' => $spk->mitra_id, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId])) }}" target="_blank" class="btn btn-outline-primary fw-bold px-2 py-1" title="Cetak / Simpan PDF Lampiran SPK">
                                        <i class="bi bi-paperclip me-1"></i> Lampiran
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>
                                Tidak ada data alokasi honorarium mitra pada periode terpilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Format Options murni dan bersih tanpa teks dalam kurung yang membingungkan
const spkOptions = {
    sensus: [
        { label: 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}' },
        { label: 'B-{nomor}/BPS/3206/ST/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/ST/{bulan}/{tahun}' },
        { label: 'B-{nomor}/BPS/3206/SE/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SE/{bulan}/{tahun}' },
        { label: 'B-{nomor}/BPS/3206/SP/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SP/{bulan}/{tahun}' },
        { label: '{nomor}/PPK-SENSUS/SPK/{bulan}/{tahun}', value: '{nomor}/PPK-SENSUS/SPK/{bulan}/{tahun}' },
        { label: '1001/PPK/SPK/{bulan}/{tahun}', value: '1001/PPK/SPK/{bulan}/{tahun}' },
        { label: '⚙️ Format Kustom Lainnya...', value: 'custom' }
    ],
    survei: [
        { label: 'B-{nomor}/BPS/3206/SPK/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SPK/{bulan}/{tahun}' },
        { label: 'B-{nomor}/BPS/3206/SURVEI/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SURVEI/{bulan}/{tahun}' },
        { label: 'B-{nomor}/3206/SPK/{bulan_romawi}/{tahun}', value: 'B-{nomor}/3206/SPK/{bulan_romawi}/{tahun}' },
        { label: '{nomor}/PPK/SPK/{bulan}/{tahun}', value: '{nomor}/PPK/SPK/{bulan}/{tahun}' },
        { label: '1001/PPK/SPK/{bulan}/{tahun}', value: '1001/PPK/SPK/{bulan}/{tahun}' },
        { label: '⚙️ Format Kustom Lainnya...', value: 'custom' }
    ],
    umum: [
        { label: 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}' },
        { label: '{nomor}/PPK/{jenis}/{bulan}/{tahun}', value: '{nomor}/PPK/{jenis}/{bulan}/{tahun}' },
        { label: '1001/PPK/SPK/{bulan}/{tahun}', value: '1001/PPK/SPK/{bulan}/{tahun}' },
        { label: '⚙️ Format Kustom Lainnya...', value: 'custom' }
    ]
};

const romawiMap = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

function submitFilter() {
    document.getElementById('hiddenTahun').value = document.getElementById('filterTahun').value;
    document.getElementById('hiddenBulanAwal').value = document.getElementById('filterBulanAwal').value;
    document.getElementById('hiddenBulanAkhir').value = document.getElementById('filterBulanAkhir').value;
    document.getElementById('hiddenKategori').value = document.getElementById('kategoriKegiatanSelect').value;
    
    const bidangEl = document.getElementById('filterBidang');
    if (bidangEl) document.getElementById('hiddenBidang').value = bidangEl.value;
    
    const kegiatanEl = document.getElementById('filterKegiatan');
    if (kegiatanEl) document.getElementById('hiddenKegiatan').value = kegiatanEl.value;
    
    document.getElementById('getFilterForm').submit();
}

function searchMitra() {
    document.getElementById('hiddenSearch').value = document.getElementById('searchInput').value;
    submitFilter();
}

function onKategoriChanged(kategori) {
    const select = document.getElementById('formatSpkSelect');
    const options = spkOptions[kategori] || spkOptions['sensus'];
    select.innerHTML = '';

    options.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt.value;
        el.textContent = opt.label;
        select.appendChild(el);
    });

    const currentVal = document.getElementById('formatSpkInput').value;
    const matched = options.find(o => o.value === currentVal);
    if (matched) {
        select.value = currentVal;
        document.getElementById('customFormatRow').classList.add('d-none');
    } else if (currentVal && currentVal !== '' && !options.map(o => o.value).includes(currentVal)) {
        select.value = 'custom';
        document.getElementById('customFormatRow').classList.remove('d-none');
        document.getElementById('customFormatInput').value = currentVal;
    } else {
        select.value = options[0].value;
        document.getElementById('formatSpkInput').value = options[0].value;
        document.getElementById('customFormatRow').classList.add('d-none');
    }

    updateLivePreview();
}

function onFormatSpkChanged(val) {
    if (val === 'custom') {
        document.getElementById('customFormatRow').classList.remove('d-none');
        const customVal = document.getElementById('customFormatInput').value || 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
        document.getElementById('formatSpkInput').value = customVal;
    } else {
        document.getElementById('customFormatRow').classList.add('d-none');
        document.getElementById('formatSpkInput').value = val;
    }
    updateLivePreview();
}

function editCurrentOption() {
    const currentVal = document.getElementById('formatSpkInput').value || 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
    document.getElementById('customFormatInput').value = currentVal;
    document.getElementById('customFormatRow').classList.remove('d-none');
    document.getElementById('customFormatInput').focus();
    document.getElementById('formatSpkSelect').value = 'custom';
}

function resetCustomFormat() {
    document.getElementById('customFormatRow').classList.add('d-none');
    const kategori = document.getElementById('kategoriKegiatanSelect').value || 'sensus';
    const defaultOption = spkOptions[kategori] ? spkOptions[kategori][0].value : 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}';
    document.getElementById('formatSpkSelect').value = defaultOption;
    document.getElementById('formatSpkInput').value = defaultOption;
    updateLivePreview();
}

function updateLivePreview() {
    let pattern = document.getElementById('formatSpkInput').value || 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
    const nomorAwal = parseInt(document.getElementById('nomorAwalInput').value) || 1;
    const bulanSpk = parseInt(document.getElementById('bulanSpkSelect').value) || 1;
    const tahunSpk = document.getElementById('tahunSpkInput').value || '{{ $tahun }}';
    const jenis = '{{ $selectedTemplate->jenis_dokumen ?? "spk" }}'.toUpperCase();

    // Update hidden fields for form submit
    if (document.getElementById('formBulanSpk')) document.getElementById('formBulanSpk').value = bulanSpk;
    if (document.getElementById('formTahunSpk')) document.getElementById('formTahunSpk').value = tahunSpk;

    const nomorPad = String(nomorAwal).padStart(4, '0');
    const bulanPad = String(bulanSpk).padStart(2, '0');
    const bulanRom = romawiMap[bulanSpk] || 'I';

    let preview = pattern
        .replace(/{nomor}/g, nomorPad)
        .replace(/{nomor_raw}/g, nomorAwal)
        .replace(/{bulan}/g, bulanPad)
        .replace(/{bulan_romawi}/g, bulanRom)
        .replace(/{tahun}/g, tahunSpk)
        .replace(/{jenis}/g, jenis);

    document.getElementById('livePreviewBadge').textContent = preview;
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.mitra-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function cetakIndividu(mitraId, indexOffset) {
    const nomorStart = parseInt(document.getElementById('nomorAwalInput').value) || 1;
    const calculatedNomor = nomorStart + indexOffset;
    const formatSpk = encodeURIComponent(document.getElementById('formatSpkInput').value);
    const templateId = "{{ $currentTemplateId }}";
    const tahun = document.getElementById('filterTahun').value || "{{ $tahun }}";
    const bulanAwal = document.getElementById('filterBulanAwal').value || "{{ $bulanAwal }}";
    const bulanAkhir = document.getElementById('filterBulanAkhir').value || "{{ $bulanAkhir }}";
    const bulanSpk = document.getElementById('bulanSpkSelect').value || bulanAwal;
    const tahunSpk = document.getElementById('tahunSpkInput').value || tahun;
    const kegiatanId = "{{ $kegiatanId }}";

    let url = `{{ url('spk') }}/${mitraId}/cetak-utama?tahun=${tahun}&bulan_awal=${bulanAwal}&bulan_akhir=${bulanAkhir}&bulan_spk=${bulanSpk}&tahun_spk=${tahunSpk}&nomor_awal=${calculatedNomor}&format_spk=${formatSpk}`;
    if (templateId) url += `&template_id=${templateId}`;
    if (kegiatanId) url += `&kegiatan_id=${kegiatanId}`;

    window.open(url, '_blank');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    const initKategori = document.getElementById('kategoriKegiatanSelect').value || 'sensus';
    onKategoriChanged(initKategori);
});
</script>
@endpush
@endsection

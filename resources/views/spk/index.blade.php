@extends('layouts.app')

@push('styles')
<style>
    /* Absolute Zero-Jitter Sticky Columns on Mitra Table */
    .table-sticky-wrapper {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    
    .sticky-col-1 {
        position: sticky !important;
        left: 0px !important;
        width: 60px !important;
        min-width: 60px !important;
        max-width: 60px !important;
        z-index: 15 !important;
        background-color: #ffffff !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    
    .sticky-col-2 {
        position: sticky !important;
        left: 60px !important;
        width: 300px !important;
        min-width: 300px !important;
        max-width: 300px !important;
        z-index: 15 !important;
        background-color: #ffffff !important;
        box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.08) !important;
        border-bottom: 1px solid #e2e8f0 !important;
    }
    
    thead th.sticky-col-1 {
        z-index: 25 !important;
        background-color: #f8fafc !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    
    thead th.sticky-col-2 {
        z-index: 25 !important;
        background-color: #f8fafc !important;
        width: 300px !important;
        min-width: 300px !important;
        max-width: 300px !important;
        box-shadow: 4px 0 8px -2px rgba(0, 0, 0, 0.08) !important;
        border-bottom: 2px solid #cbd5e1 !important;
    }
    
    tr:hover td.sticky-col-1,
    tr:hover td.sticky-col-2 {
        background-color: #f1f5f9 !important;
    }
</style>
@endpush

@section('content')
<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-printer-fill text-danger me-2"></i>Cetak &amp; Unduh Dokumen SPK / BAST</h2>
        <p class="page-subtitle">Pusat pencetakan massal &amp; individual dokumen Surat Perintah Kerja (SPK), BAST, dan Lampiran Honor Mitra BPS</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('spk.penomoran.index') }}" class="btn btn-outline-primary shadow-sm fw-bold">
            <i class="bi bi-sort-numeric-down me-1"></i> Atur Penomoran Surat
        </a>
        <a href="{{ route('spk.templates.index') }}" class="btn btn-outline-secondary shadow-sm fw-bold">
            <i class="bi bi-folder-symlink-fill me-1"></i> Kelola Template
        </a>
    </div>
</div>

<!-- ========================================== -->
<!-- FORM CETAK MASSAL                           -->
<!-- ========================================== -->
<form method="POST" action="{{ route('spk.cetak-massal') }}" id="bulkForm" target="_blank">
    @csrf
    <input type="hidden" name="mode" value="{{ $mode }}">
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="template_id" value="{{ $currentTemplateId }}">
    <input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">
    <input type="hidden" name="jenis_dokumen" value="{{ $selectedTemplate->jenis_dokumen ?? 'spk' }}">

    <!-- CARD 1: PANEL PILIH TEMPLATE & TOMBOL AKSI MASSAL -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-4 col-lg-5">
                    <label class="form-label text-secondary fw-bold small mb-1.5">
                        <i class="bi bi-file-earmark-text text-primary me-1"></i>PILIH TEMPLATE DOKUMEN YANG AKAN DICETAK
                    </label>
                    <select id="templateSelect" class="form-select border-primary-subtle fw-bold py-2 fs-6" onchange="document.getElementById('filterTemplateId').value=this.value; submitFilter();">
                        @forelse($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" {{ $currentTemplateId == $tmpl->id ? 'selected' : '' }}>
                                [{{ strtoupper($tmpl->jenis_dokumen) }}] {{ $tmpl->nama }}
                            </option>
                        @empty
                            <option value="">Template Baku BPS (Default System)</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-3">
                    <label class="form-label text-secondary fw-bold small mb-1.5">
                        <i class="bi bi-calendar-event text-primary me-1"></i>TANGGAL CETAK DOKUMEN
                    </label>
                    <input type="date" id="filterTanggalDokumen" class="form-control border-primary-subtle fw-bold py-2" value="{{ $tanggalDokumen }}" onchange="document.getElementById('hiddenTanggalDokumen').value=this.value;">
                </div>
                <div class="col-12 col-md-5 col-lg-4 text-md-end mt-3 mt-md-0 pt-md-4 d-flex gap-2 justify-content-md-end flex-wrap">
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm px-3 py-2 fs-6 rounded-3 flex-grow-1 flex-md-grow-0">
                        <i class="bi bi-printer-fill me-1.5"></i> CETAK MASSAL
                    </button>
                    <button type="submit" class="btn btn-primary fw-bold shadow-sm px-3 py-2 fs-6 rounded-3 flex-grow-1 flex-md-grow-0" title="Buka jendela dokumen untuk simpan/download PDF">
                        <i class="bi bi-file-earmark-pdf-fill me-1.5"></i> UNDUH PDF MASSAL
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Hidden form for GET filtering -->
<form method="GET" action="{{ route('spk.index') }}" id="getFilterForm" class="d-none">
    <input type="hidden" name="mode" id="hiddenMode" value="{{ $mode }}">
    <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" id="hiddenBulanAwal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" id="hiddenBulanAkhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="template_id" id="filterTemplateId" value="{{ $currentTemplateId }}">
    <input type="hidden" name="bidang_id" id="hiddenBidang" value="{{ $bidangId }}">
    <input type="hidden" name="kegiatan_id" id="hiddenKegiatan" value="{{ $kegiatanId }}">
    <input type="hidden" name="search" id="hiddenSearch" value="{{ $search }}">
    <input type="hidden" name="tanggal_dokumen" id="hiddenTanggalDokumen" value="{{ $tanggalDokumen }}">
</form>

<!-- CARD 2: FILTER & TABEL DAFTAR MITRA SIAP CETAK -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-4 px-4 rounded-top-4">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold me-2 px-2.5 py-1.5 rounded-pill"><i class="bi bi-funnel-fill me-1"></i>FILTER TABEL</span>
            <span class="text-uppercase small fw-bold text-muted">Saring Periode Kerja &amp; Lingkup Kegiatan Mitra</span>
        </div>

        <!-- Mode Tabs: Mode Per Kegiatan vs Mode Per Bulan -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3 pb-3 border-bottom">
            <div class="btn-group p-1 bg-light rounded-3 shadow-none border" role="group">
                <button type="button" class="btn btn-sm fw-bold px-3 py-1.5 rounded-2 {{ $mode !== 'bulan' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary' }}" onclick="switchMode('kegiatan')">
                    <i class="bi bi-briefcase-fill me-1.5"></i> Mode Per Kegiatan
                </button>
                <button type="button" class="btn btn-sm fw-bold px-3 py-1.5 rounded-2 {{ $mode === 'bulan' ? 'btn-primary shadow-sm text-white' : 'btn-light text-secondary' }}" onclick="switchMode('bulan')">
                    <i class="bi bi-calendar-check-fill me-1.5"></i> Mode Per Bulan (Gabungan)
                </button>
            </div>
            <div class="text-muted extra-small">
                @if($mode === 'bulan')
                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2.5 py-1.5"><i class="bi bi-info-circle me-1"></i>Mode Gabungan: Mencetak 1 SPK yang memuat seluruh kegiatan mitra di bulan tsb</span>
                @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2.5 py-1.5"><i class="bi bi-info-circle me-1"></i>Mode Satuan: Mencetak 1 SPK khusus untuk 1 nama kegiatan</span>
                @endif
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 {{ auth()->user()->role === 'admin' ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-secondary fw-bold small mb-1.5">TAHUN ANGGARAN</label>
                <select name="tahun" id="filterTahun" class="form-select border-primary-subtle fw-bold py-2" onchange="submitFilter()">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 {{ auth()->user()->role === 'admin' ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-secondary fw-bold small mb-1.5">BULAN AWAL KERJA</label>
                <select name="bulan_awal" id="filterBulanAwal" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 {{ auth()->user()->role === 'admin' ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-secondary fw-bold small mb-1.5">BULAN AKHIR KERJA</label>
                <select name="bulan_akhir" id="filterBulanAkhir" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>

            @if(auth()->user()->role === 'admin')
            <div class="col-6 col-md-3">
                <label class="form-label text-secondary fw-bold small mb-1.5">BIDANG / TIM KERJA</label>
                <select name="bidang_id" id="filterBidang" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                    <option value="">Semua Bidang</option>
                    @foreach($bidangOptions as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>

        <!-- Row 2: Searchable Kegiatan Dropdown & Search Mitra -->
        <div class="row g-3 align-items-center mb-3">
            <div class="col-12 col-md-7 position-relative" id="kegiatanDropdownContainer">
                <label class="form-label text-secondary fw-bold small mb-1.5">FILTER SPESIFIK KEGIATAN (OPSIONAL)</label>
                <input type="hidden" name="kegiatan_id" id="hiddenKegiatanIdInput" value="{{ $kegiatanId }}">
                
                <button type="button" class="form-select border-primary-subtle fw-semibold py-2 text-start d-flex justify-content-between align-items-center bg-white shadow-none" 
                        id="kegiatanDropdownTrigger">
                    <span class="text-truncate me-2" id="kegiatanSelectedText">
                        @if($kegiatanId && $kegiatanOptions->firstWhere('id', $kegiatanId))
                            {{ $kegiatanOptions->firstWhere('id', $kegiatanId)->nama }}
                        @else
                            Semua Kegiatan (Tampilkan seluruh kegiatan pada periode terpilih)
                        @endif
                    </span>
                    <i class="bi bi-chevron-down text-primary small" id="kegiatanChevronIcon"></i>
                </button>

                <div id="kegiatanMenuPopup" class="shadow-lg rounded-3 position-absolute w-100 mt-1 d-none bg-white p-2.5" 
                     style="z-index: 1060; border: 1.5px solid #93c5fd; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light text-primary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="kegiatanInnerSearchInput" class="form-control border-start-0 py-1.5" 
                               placeholder="Ketik untuk mencari nama kegiatan..." autocomplete="off">
                    </div>
                    <div id="kegiatanOptionsList" class="list-group list-group-flush" style="max-height: 240px; overflow-y: auto;">
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-md-5">
                <label class="form-label text-secondary fw-bold small mb-1.5">PENCARIAN NAMA / ID SOBAT</label>
                <div class="input-group">
                    <input type="text" id="searchInput" class="form-control border-primary-subtle py-2" placeholder="Ketik nama mitra..." value="{{ $search }}" onkeydown="if(event.key==='Enter'){ searchMitra(); }">
                    @if($search)
                        <a href="{{ route('spk.index', array_filter(['mode' => $mode, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId, 'bidang_id' => $bidangId])) }}" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="bi bi-x"></i></a>
                    @endif
                    <button type="button" class="btn btn-primary px-3 fw-bold" onclick="searchMitra()">Cari</button>
                </div>
            </div>
        </div>

        <hr class="my-3.5 text-muted opacity-25">

        <!-- Baris Checkbox Pilih Semua -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <input type="checkbox" id="selectAllMitra" class="form-check-input mt-0" style="width: 20px; height: 20px; cursor: pointer;" onclick="toggleSelectAll(this)">
                <label for="selectAllMitra" class="fw-bold text-dark mb-0 fs-6" style="cursor: pointer;">
                    PILIH SEMUA MITRA SIAP CETAK <span class="badge bg-light text-primary border ms-1">{{ $spkList->count() }} Mitra Ditemukan</span>
                </label>
            </div>
            <div class="text-muted extra-small">
                <i class="bi bi-info-circle me-1 text-primary"></i>Hanya mitra yang <strong>sudah memiliki nomor sah</strong> yang dapat dicetak.
            </div>
        </div>
    </div>
    
    <!-- TABEL UTAMA -->
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 table-sticky-wrapper">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 text-center py-3 sticky-col-1" style="width: 60px;">
                            <input type="checkbox" id="selectAll" class="form-check-input" onchange="toggleSelectAll(this)" style="width: 18px; height: 18px; cursor: pointer;">
                        </th>
                        <th class="py-3 sticky-col-2" style="width: 300px; min-width: 300px;">NAMA MITRA</th>
                        <th class="py-3" style="min-width: 250px;">{{ $mode === 'bulan' ? 'BULAN & LINGKUP KEGIATAN' : 'KEGIATAN' }}</th>
                        <th class="py-3 text-nowrap" style="min-width: 130px;">ID SOBAT</th>
                        <th class="py-3 text-nowrap" style="min-width: 140px;">NO. TELEPON</th>
                        <th class="py-3" style="min-width: 320px;">NO. SPK / BAST RESMI</th>
                        <th class="text-end py-3" style="min-width: 130px;">TOTAL HONOR</th>
                        <th class="text-center pe-4 py-3" style="min-width: 290px;">AKSI UNDUH DOKUMEN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spkList as $idx => $spk)
                        @php
                            $spkDocNum = $spk->items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;
                            $bastDocNum = $spk->items->firstWhere('nomor_bast', '!=', null)?->nomor_bast;
                            $isReady = !empty($spkDocNum) || !empty($bastDocNum);
                            $targetKegiatanId = $mode === 'bulan' ? '' : ($spk->kegiatan_id ?? '');
                            $targetBulan = $mode === 'bulan' ? ($spk->bulan_angka ?? '') : '';
                        @endphp
                        <tr class="{{ !$isReady ? 'bg-light bg-opacity-40' : '' }}">
                            <td class="ps-4 text-center py-3 sticky-col-1">
                                @if($isReady)
                                    <input type="checkbox" name="mitra_ids[]" value="{{ $spk->mitra_id }}_{{ $mode === 'bulan' ? 'bln_'.$spk->bulan_angka : $spk->kegiatan_id }}" form="bulkForm" class="form-check-input mitra-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                                @else
                                    <input type="checkbox" disabled class="form-check-input opacity-25" style="width: 18px; height: 18px; cursor: not-allowed;" title="Belum memiliki nomor resmi.">
                                @endif
                            </td>
                            <td class="py-3 sticky-col-2">
                                <div class="fw-bold text-dark fs-6">{{ $spk->mitra->nama }}</div>
                                <div class="text-muted extra-small text-truncate" style="max-width: 280px;">{{ $spk->mitra->pekerjaan ?? 'Mitra BPS' }}</div>
                            </td>
                            <td class="py-3">
                                @if($mode === 'bulan')
                                    <div class="fw-bold text-primary"><i class="bi bi-calendar3 me-1"></i>{{ $spk->periode_label }}</div>
                                    <div class="text-secondary small mt-0.5" style="white-space: normal; word-break: break-word;">{{ $spk->kegiatan->nama }}</div>
                                @else
                                    <div class="fw-semibold text-dark">{{ $spk->kegiatan->nama }}</div>
                                    <div class="text-muted extra-small">{{ $spk->kegiatan->bidang->nama ?? '-' }}</div>
                                @endif
                            </td>
                            <td class="py-3 text-nowrap">
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1.5">{{ $spk->mitra->id_sobat ?? '-' }}</span>
                            </td>
                            <td class="py-3 text-nowrap">
                                <span class="small text-muted font-monospace"><i class="bi bi-telephone text-secondary me-1"></i>{{ $spk->mitra->no_hp ?? '-' }}</span>
                            </td>
                            <td class="py-3">
                                @if($spkDocNum)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace fw-bold px-2 py-1.5 d-inline-block mb-1" title="{{ $spkDocNum }}">
                                        <i class="bi bi-file-earmark-check me-1"></i>SPK: {{ $spkDocNum }}
                                    </span>
                                @endif
                                @if($bastDocNum)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 font-monospace fw-bold px-2 py-1.5 d-inline-block" title="{{ $bastDocNum }}">
                                        <i class="bi bi-file-earmark-check me-1"></i>BAST: {{ $bastDocNum }}
                                    </span>
                                @endif
                                @if(!$isReady)
                                    <span class="badge bg-warning bg-opacity-10 text-warning text-dark border border-warning border-opacity-50 font-monospace px-2.5 py-1">
                                        <i class="bi bi-lock-fill me-1 text-danger"></i>Belum Bernomor
                                    </span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-success fs-6 py-3 text-nowrap">
                                Rp {{ number_format($spk->total_honor, 0, ',', '.') }}
                            </td>
                            <td class="text-center pe-4 py-3 text-nowrap">
                                @if($isReady)
                                    <div class="d-inline-flex align-items-center gap-2 shadow-none">
                                        <a href="javascript:void(0)" onclick="cetakIndividu('{{ $spk->mitra_id }}', '{{ $targetKegiatanId }}', '{{ $targetBulan }}')" class="btn btn-sm btn-outline-danger fw-bold px-3 py-1.5 rounded-3" title="Cetak Dokumen Ini (Buka Jendela Print)">
                                            <i class="bi bi-printer-fill me-1"></i> Cetak
                                        </a>
                                        <a href="javascript:void(0)" onclick="unduhWordIndividu('{{ $spk->mitra_id }}', '{{ $targetKegiatanId }}', '{{ $targetBulan }}')" class="btn btn-sm btn-primary fw-bold px-3 py-1.5 rounded-3" title="Download File Word (.docx) Otentik">
                                            <i class="bi bi-file-earmark-word-fill me-1"></i> Unduh Word
                                        </a>
                                        <a href="javascript:void(0)" onclick="unduhPdfIndividu('{{ $spk->mitra_id }}', '{{ $targetKegiatanId }}', '{{ $targetBulan }}')" class="btn btn-sm btn-danger fw-bold px-3 py-1.5 rounded-3" title="Download File PDF Otentik (Hasil Konversi Word)">
                                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh PDF
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('spk.penomoran.index', ['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId]) }}" class="btn btn-sm btn-outline-warning text-dark fw-bold px-2.5 py-1.5 rounded-2" title="Klik untuk atur penomoran terlebih dahulu">
                                        <i class="bi bi-sort-numeric-down me-1"></i> Atur Nomor
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                <h6 class="fw-bold text-dark">Tidak Ada Data Mitra Ditemukan</h6>
                                <p class="small mb-0">Coba sesuaikan filter Tahun Anggaran, Bulan Kerja, atau Kata Kunci Pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($spkList->hasPages())
            <div class="card-footer bg-white border-top py-3 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                <div class="text-muted small">
                    Menampilkan <strong>{{ $spkList->firstItem() ?? 0 }}</strong> sampai <strong>{{ $spkList->lastItem() ?? 0 }}</strong> dari <strong>{{ $spkList->total() }}</strong> item
                </div>
                <div>
                    {{ $spkList->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
const allKegiatansData = {!! json_encode($kegiatanOptions) !!};

document.addEventListener('DOMContentLoaded', function() {
    const triggerBtn = document.getElementById('kegiatanDropdownTrigger');
    const menuPopup = document.getElementById('kegiatanMenuPopup');
    const innerSearch = document.getElementById('kegiatanInnerSearchInput');
    const optionsList = document.getElementById('kegiatanOptionsList');
    const hiddenInput = document.getElementById('hiddenKegiatanIdInput');
    const selectedText = document.getElementById('kegiatanSelectedText');
    const chevronIcon = document.getElementById('kegiatanChevronIcon');
    const container = document.getElementById('kegiatanDropdownContainer');

    if (triggerBtn && menuPopup) {
        function renderOptions(query = '') {
            optionsList.innerHTML = '';
            const q = query.trim().toLowerCase();

            if (q === '') {
                const allItem = document.createElement('a');
                allItem.href = '#';
                allItem.className = `list-group-item list-group-item-action py-2 px-2.5 rounded-2 border-0 mb-1 fw-bold ${!hiddenInput.value ? 'bg-primary text-white' : 'text-primary'}`;
                allItem.innerHTML = `<i class="bi bi-grid-fill me-1.5"></i> Semua Kegiatan`;
                allItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    hiddenInput.value = '';
                    selectedText.textContent = 'Semua Kegiatan';
                    closeDropdown();
                    submitFilter();
                });
                optionsList.appendChild(allItem);
            }

            const filtered = allKegiatansData.filter(k => {
                const nameMatch = k.nama.toLowerCase().includes(q);
                const bidangMatch = k.bidang && k.bidang.nama ? k.bidang.nama.toLowerCase().includes(q) : false;
                return nameMatch || bidangMatch;
            });

            if (filtered.length === 0 && q !== '') {
                const emptyEl = document.createElement('div');
                emptyEl.className = 'text-muted small py-3 text-center';
                emptyEl.innerHTML = `<i class="bi bi-search me-1"></i> Tidak ada kegiatan yang cocok`;
                optionsList.appendChild(emptyEl);
            } else {
                filtered.forEach(k => {
                    const isSelected = String(k.id) === String(hiddenInput.value);
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = `list-group-item list-group-item-action py-2 px-2.5 rounded-2 border-0 mb-1 d-flex align-items-center justify-content-between ${isSelected ? 'bg-primary text-white fw-bold' : 'text-dark'}`;
                    
                    const bidangBadge = k.bidang && k.bidang.nama 
                        ? `<span class="badge ${isSelected ? 'bg-white text-primary' : 'bg-primary bg-opacity-10 text-primary'} extra-small ms-2">${k.bidang.nama}</span>` 
                        : '';

                    item.innerHTML = `
                        <span class="text-truncate small ${isSelected ? 'text-white' : ''}" style="max-width: 80%;">${k.nama}</span>
                        ${bidangBadge}
                    `;

                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        hiddenInput.value = k.id;
                        selectedText.textContent = k.nama;
                        closeDropdown();
                        submitFilter();
                    });
                    optionsList.appendChild(item);
                });
            }
        }

        function openDropdown() {
            menuPopup.classList.remove('d-none');
            chevronIcon.classList.replace('bi-chevron-down', 'bi-chevron-up');
            innerSearch.value = '';
            renderOptions();
            setTimeout(() => innerSearch.focus(), 50);
        }

        function closeDropdown() {
            menuPopup.classList.add('d-none');
            chevronIcon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        }

        triggerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (menuPopup.classList.contains('d-none')) {
                openDropdown();
            } else {
                closeDropdown();
            }
        });

        innerSearch.addEventListener('input', function() {
            renderOptions(innerSearch.value);
        });

        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                closeDropdown();
            }
        });
    }
});

function submitFilter() {
    const hiddenForm = document.getElementById('getFilterForm');
    document.getElementById('hiddenTahun').value = document.getElementById('filterTahun').value;
    document.getElementById('hiddenBulanAwal').value = document.getElementById('filterBulanAwal').value;
    document.getElementById('hiddenBulanAkhir').value = document.getElementById('filterBulanAkhir').value;
    
    const filterBidang = document.getElementById('filterBidang');
    if (filterBidang) {
        document.getElementById('hiddenBidang').value = filterBidang.value;
    }
    
    const hiddenKegiatanInput = document.getElementById('hiddenKegiatanIdInput');
    if (hiddenKegiatanInput) {
        document.getElementById('hiddenKegiatan').value = hiddenKegiatanInput.value;
    }
    
    hiddenForm.submit();
}

function searchMitra() {
    const searchVal = document.getElementById('searchInput').value;
    document.getElementById('hiddenSearch').value = searchVal;
    submitFilter();
}

function switchMode(newMode) {
    document.getElementById('hiddenMode').value = newMode;
    submitFilter();
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.mitra-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function cetakIndividu(mitraId, kegiatanId, bulanSpecific = '') {
    const templateId = "{{ $currentTemplateId }}";
    const bulanAwal = bulanSpecific || "{{ $bulanAwal }}";
    const bulanAkhir = bulanSpecific || "{{ $bulanAkhir }}";
    const tahun = "{{ $tahun }}";
    const tanggalDokumen = document.getElementById('filterTanggalDokumen') ? document.getElementById('filterTanggalDokumen').value : '';

    const url = `{{ url('/spk') }}/${mitraId}/cetak-utama?tahun=${tahun}&bulan_awal=${bulanAwal}&bulan_akhir=${bulanAkhir}&template_id=${templateId}&kegiatan_id=${kegiatanId}&tanggal_spk=${tanggalDokumen}`;
    window.open(url, '_blank');
}

function unduhWordIndividu(mitraId, kegiatanId, bulanSpecific = '') {
    const templateId = "{{ $currentTemplateId }}";
    const bulanAwal = bulanSpecific || "{{ $bulanAwal }}";
    const bulanAkhir = bulanSpecific || "{{ $bulanAkhir }}";
    const tahun = "{{ $tahun }}";
    const tanggalDokumen = document.getElementById('filterTanggalDokumen') ? document.getElementById('filterTanggalDokumen').value : '';

    const url = `{{ url('/spk') }}/${mitraId}/download-pdf?tahun=${tahun}&bulan_awal=${bulanAwal}&bulan_akhir=${bulanAkhir}&template_id=${templateId}&kegiatan_id=${kegiatanId}&tanggal_spk=${tanggalDokumen}&format=docx`;
    window.location.href = url;
}

function unduhPdfIndividu(mitraId, kegiatanId, bulanSpecific = '') {
    const templateId = "{{ $currentTemplateId }}";
    const bulanAwal = bulanSpecific || "{{ $bulanAwal }}";
    const bulanAkhir = bulanSpecific || "{{ $bulanAkhir }}";
    const tahun = "{{ $tahun }}";
    const tanggalDokumen = document.getElementById('filterTanggalDokumen') ? document.getElementById('filterTanggalDokumen').value : '';

    const url = `{{ url('/spk') }}/${mitraId}/download-pdf?tahun=${tahun}&bulan_awal=${bulanAwal}&bulan_akhir=${bulanAkhir}&template_id=${templateId}&kegiatan_id=${kegiatanId}&tanggal_spk=${tanggalDokumen}&format=pdf`;
    window.location.href = url;
}
</script>
@endpush
@endsection

@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h2 class="page-title"><i class="bi bi-sort-numeric-down text-primary me-2"></i>Penomoran SPK &amp; BAST Mitra</h2>
        <p class="page-subtitle">Pusat alokasi, penomoran berurutan otomatis, dan penguncian nomor surat resmi ke database</p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('spk.index') }}" class="btn btn-outline-danger shadow-sm fw-bold">
            <i class="bi bi-printer-fill me-1"></i> Buka Modul Cetak &amp; Unduh
        </a>
    </div>
</div>

<!-- FORM TERAPKAN PENOMORAN -->
<form action="{{ route('spk.penomoran.terapkan') }}" method="POST" id="penomoranForm">
    @csrf
    <input type="hidden" name="tahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">
    <input type="hidden" name="bulan_spk" id="formBulanSpk" value="{{ $bulanSpk }}">
    <input type="hidden" name="tahun_spk" id="formTahunSpk" value="{{ $tahunSpk }}">

    <!-- CARD 1: PANEL KONFIGURASI POLA PENOMORAN -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 rounded-top-4">
            <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-sliders text-primary me-2"></i>Konfigurasi Pola Format Penomoran Surat
            </h6>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary fw-bold small mb-1.5">JENIS DOKUMEN</label>
                    <select name="jenis_dokumen" id="jenisDokumenSelect" class="form-select border-primary-subtle fw-bold py-2" onchange="updateLivePreview()">
                        <option value="spk" {{ $jenisDokumen === 'spk' ? 'selected' : '' }}>Surat Perintah Kerja (SPK)</option>
                        <option value="bast" {{ $jenisDokumen === 'bast' ? 'selected' : '' }}>Berita Acara Serah Terima (BAST)</option>
                    </select>
                </div>

                <div class="col-12 col-md-8">
                    <label class="form-label text-secondary fw-bold small mb-1.5">POLA / FORMAT NOMOR</label>
                    <div class="input-group">
                        <select id="formatSpkSelect" class="form-select border-primary-subtle fw-semibold font-monospace py-2" onchange="onFormatSpkChanged(this.value)">
                        </select>
                        <button type="button" class="btn btn-outline-primary dropdown-toggle fw-bold px-3.5 py-2 d-flex align-items-center shadow-none" data-bs-toggle="dropdown" aria-expanded="false" title="Kelola / Ubah Pola Format Nomor">
                            <i class="bi bi-gear-fill text-primary me-2 fs-6"></i> <span class="d-none d-sm-inline">Kelola Pola</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-2 mt-1" style="min-width: 230px;">
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2 fw-semibold text-success d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); bukaModalTambahPola();">
                                    <i class="bi bi-plus-circle-fill fs-6 text-success"></i> Tambah Pola Baru
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2 fw-semibold text-primary d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); bukaModalEditPola();">
                                    <i class="bi bi-pencil-square fs-6 text-primary"></i> Edit Pola Terpilih
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2 fw-semibold text-danger d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); hapusPolaAktif();">
                                    <i class="bi bi-trash3-fill fs-6 text-danger"></i> Hapus Pola Terpilih
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 px-3 rounded-2 small fw-semibold text-secondary d-flex align-items-center gap-2" href="#" onclick="event.preventDefault(); resetPolaKeDefault();">
                                    <i class="bi bi-arrow-counterclockwise fs-6 text-secondary"></i> Reset ke Pola BPS
                                </a>
                            </li>
                        </ul>
                    </div>
                    <input type="hidden" name="format_spk" id="formatSpkInput" value="{{ $formatSpk }}">
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary fw-bold small mb-1.5">BULAN PENOMORAN</label>
                    <select id="bulanSpkSelect" class="form-select border-primary-subtle fw-semibold py-2" onchange="updateLivePreview()">
                        @foreach($monthOptions as $angka => $nm)
                            <option value="{{ $angka }}" {{ $bulanSpk == $angka ? 'selected' : '' }}>
                                {{ sprintf('%02d', $angka) }} - {{ $nm }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary fw-bold small mb-1.5">TAHUN PENOMORAN</label>
                    <input type="number" id="tahunSpkInput" class="form-control border-primary-subtle fw-bold py-2" value="{{ $tahunSpk }}" min="2020" max="2099" oninput="updateLivePreview()">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label text-secondary fw-bold small mb-1.5">NOMOR URUT AWAL</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold text-muted border-primary-subtle">No.</span>
                        <input type="number" name="nomor_awal" id="nomorAwalInput" class="form-control border-primary-subtle fw-bold py-2" value="{{ $nomorAwal }}" min="1" oninput="updateLivePreview()">
                        <button type="button" class="btn btn-outline-primary fw-semibold px-2.5" style="font-size: 0.82rem;" onclick="setLanjutanNomor()" title="Lanjutkan dari nomor terakhir yang ada di database">
                            Lanjutkan
                        </button>
                        <button type="button" class="btn btn-outline-secondary fw-semibold px-2.5" style="font-size: 0.82rem;" onclick="setResetNomor()" title="Mulai dari nomor urut 1">
                            Reset (1)
                        </button>
                    </div>
                    <div class="mt-1 extra-small text-muted d-flex align-items-center justify-content-between" id="lastNomorInfoText">
                        @php
                            $curMax = ($jenisDokumen === 'bast') ? $maxBastSeq : $maxSpkSeq;
                            $curDoc = ($jenisDokumen === 'bast') ? $lastBastDoc : $lastSpkDoc;
                        @endphp
                        @if($curMax > 0)
                            <span class="text-success fw-semibold text-truncate" title="Nomor terakhir: {{ $curDoc }}">
                                <i class="bi bi-info-circle me-1"></i>Terakhir di DB: <strong>{{ $curDoc }}</strong> (No. {{ $curMax }})
                            </span>
                        @else
                            <span class="text-muted"><i class="bi bi-info-circle me-1"></i>Belum ada nomor di DB</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- LIVE PREVIEW & AKSI UTAMA -->
            <div class="row mt-4 pt-3 border-top align-items-center">
                <div class="col-12 col-md-6 col-lg-7">
                    <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3" style="padding: 1rem 1.25rem;">
                        <div class="text-secondary extra-small text-uppercase fw-bold mb-1" style="letter-spacing: 0.04em;">Live Preview Nomor Pertama:</div>
                        <div class="font-monospace fw-bold text-primary fs-6 text-truncate" style="line-height: 1.4;" id="livePreviewBadge">B-0001/BPS/3206/SPK/01/2026</div>
                    </div>
                </div>
                <div class="col-12 col-md-6 col-lg-5 text-md-end mt-3 mt-md-0 d-flex gap-2 justify-content-md-end">
                    <button type="button" class="btn btn-outline-primary btn-lg fw-bold shadow-sm px-3 py-2.5 fs-6 rounded-3 flex-grow-1 flex-md-grow-0" onclick="bukaModalPratinjau()">
                        <i class="bi bi-list-check me-1"></i> Pratinjau &amp; Cek Nomor
                    </button>
                    <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm px-4 py-2.5 fs-6 rounded-3 flex-grow-1 flex-md-grow-0">
                        <i class="bi bi-check-circle-fill me-1"></i> Terapkan ke Database
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL PRATINJAU & EDIT MANUAL DI TEMPAT -->
<div class="modal fade" id="modalPratinjauSpk" tabindex="-1" aria-labelledby="modalPratinjauSpkLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white py-3 px-4 rounded-top-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-check-fill fs-5"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalPratinjauSpkLabel">Pratinjau &amp; Penyesuaian Nomor SPK / BAST</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-3 py-2.5">
                    <i class="bi bi-info-circle-fill fs-5 text-primary me-2"></i>
                    <div class="small">
                        Berikut daftar alokasi nomor yang akan disimpan ke database. Anda dapat <strong>mengedit kotak nomor di bawah ini secara langsung</strong> jika ingin menyesuaikan nomor khusus untuk mitra tertentu.
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 380px; overflow-y: auto;">
                    <table class="table table-bordered table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 50px;">URUT</th>
                                <th>NAMA MITRA</th>
                                <th>KEGIATAN</th>
                                <th>ID SOBAT</th>
                                <th class="text-end">TOTAL HONOR</th>
                                <th style="width: 380px;">NOMOR RESMI YANG AKAN DISIMPAN (BISA DIEDIT)</th>
                            </tr>
                        </thead>
                        <tbody id="pratinjauTabelBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-between rounded-bottom-4">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-success btn-lg px-4 py-2 fw-bold shadow" onclick="submitFinalFromPreview()">
                    <i class="bi bi-save-fill me-1"></i> SIMPAN NOMOR KE DATABASE
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL TAMBAH / EDIT POLA FORMAT -->
<div class="modal fade" id="modalTambahPola" tabindex="-1" aria-labelledby="modalTambahPolaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header text-white py-3 px-4 rounded-top-4" id="modalPolaHeader">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-diff-fill fs-5" id="modalPolaIcon"></i>
                    <h5 class="modal-title fw-bold mb-0" id="modalTambahPolaLabel">Tambah Pola Nomor Baru</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="editPolaOriginalValue" value="">
                <div class="mb-3">
                    <label class="form-label text-secondary fw-bold small mb-1">NAMA / LABEL POLA</label>
                    <input type="text" id="inputNamaPolaBaru" class="form-control fw-semibold" placeholder="Contoh: Pola Susenas 2026">
                </div>
                <div class="mb-3">
                    <label class="form-label text-secondary fw-bold small mb-1">STRUKTUR POLA FORMAT</label>
                    <input type="text" id="inputFormatPolaBaru" class="form-control font-monospace fw-bold text-dark border-primary-subtle" placeholder="Contoh: B-{nomor}/BPS/3206/SUSENAS/{bulan}/{tahun}">
                </div>
                <div class="p-3 bg-light rounded-3 border extra-small text-muted">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle me-1"></i>Tag Dinamis yang Tersedia:</div>
                    <ul class="mb-0 ps-3">
                        <li><code>{nomor}</code> : Nomor urut 4 digit (misal: 0001)</li>
                        <li><code>{nomor_raw}</code> : Nomor urut tanpa nol (misal: 1)</li>
                        <li><code>{jenis}</code> : Short name kegiatan (SUSENAS, SAKERNAS, dll)</li>
                        <li><code>{bulan}</code> : Dua digit bulan (01, 02, ...)</li>
                        <li><code>{bulan_romawi}</code> : Bulan romawi (I, II, III, ...)</li>
                        <li><code>{tahun}</code> : Tahun berjalan (2026)</li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 px-4 d-flex justify-content-between rounded-bottom-4">
                <button type="button" class="btn btn-secondary px-3 fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow-sm" id="btnSimpanPolaModal" onclick="simpanPolaSubmit()">
                    <i class="bi bi-check-lg me-1"></i> Simpan Pola
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden form for GET filtering -->
<form method="GET" action="{{ route('spk.penomoran.index') }}" id="getFilterForm">
    <input type="hidden" name="tahun" id="hiddenTahun" value="{{ $tahun }}">
    <input type="hidden" name="bulan_awal" id="hiddenBulanAwal" value="{{ $bulanAwal }}">
    <input type="hidden" name="bulan_akhir" id="hiddenBulanAkhir" value="{{ $bulanAkhir }}">
    <input type="hidden" name="bidang_id" id="hiddenBidang" value="{{ $bidangId }}">
    <input type="hidden" name="kegiatan_id" id="hiddenKegiatan" value="{{ $kegiatanId }}">
    <input type="hidden" name="search" id="hiddenSearch" value="{{ $search }}">
</form>

<!-- CARD 2: FILTER & TABEL STATUS PENOMORAN MITRA -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-header bg-white border-bottom py-4 px-4 rounded-top-4">
        <div class="d-flex align-items-center mb-3">
            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold me-2 px-2.5 py-1.5 rounded-pill"><i class="bi bi-funnel-fill me-1"></i>FILTER MITRA</span>
            <span class="text-uppercase small fw-bold text-muted">Saring Berdasarkan Periode &amp; Lingkup Kegiatan</span>
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
                <label class="form-label text-secondary fw-bold small mb-1.5">BULAN AWAL</label>
                <select name="bulan_awal" id="filterBulanAwal" class="form-select border-primary-subtle fw-semibold py-2" onchange="submitFilter()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 {{ auth()->user()->role === 'admin' ? 'col-md-3' : 'col-md-4' }}">
                <label class="form-label text-secondary fw-bold small mb-1.5">BULAN AKHIR</label>
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
                        <a href="{{ route('spk.penomoran.index', array_filter(['tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId, 'bidang_id' => $bidangId])) }}" class="btn btn-outline-secondary" title="Hapus Pencarian"><i class="bi bi-x"></i></a>
                    @endif
                    <button type="button" class="btn btn-primary px-3 fw-bold" onclick="searchMitra()">Cari</button>
                </div>
            </div>
        </div>

        <hr class="my-3.5 text-muted opacity-25">

        <!-- Checkbox Pilih Semua & Tombol Reset -->
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <input type="checkbox" id="selectAllMitra" class="form-check-input mt-0" style="width: 20px; height: 20px; cursor: pointer;" onclick="toggleSelectAll(this)">
                <label for="selectAllMitra" class="fw-bold text-dark mb-0 fs-6" style="cursor: pointer;">
                    PILIH SEMUA MITRA <span class="badge bg-light text-primary border ms-1">{{ $spkList->count() }} Item Ditemukan</span>
                </label>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="jenisResetSelect" class="form-select form-select-sm fw-bold border-warning py-1.5" style="width: auto; font-size: 0.82rem;">
                    <option value="semua">Reset SPK & BAST</option>
                    <option value="spk">Reset No. SPK Saja</option>
                    <option value="bast">Reset No. BAST Saja</option>
                </select>
                <button type="button" class="btn btn-outline-warning text-dark fw-bold btn-sm px-3 py-1.5 shadow-sm rounded-3" onclick="eksekusiResetNomor()">
                    <i class="bi bi-arrow-counterclockwise text-danger me-1"></i> Reset Nomor Terpilih
                </button>
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
                        <th>KEGIATAN</th>
                        <th>ID SOBAT</th>
                        <th class="text-end">TOTAL HONOR</th>
                        <th>STATUS NOMOR SPK SAAT INI</th>
                        <th>STATUS NOMOR BAST SAAT INI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spkList as $idx => $spk)
                        <tr>
                            <td class="ps-4 text-center">
                                <input type="checkbox" name="mitra_ids[]" value="{{ $spk->mitra_id }}_{{ $spk->kegiatan_id }}" form="penomoranForm" class="form-check-input mitra-checkbox" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $spk->mitra->nama }}</div>
                                <div class="text-muted extra-small">{{ $spk->mitra->pekerjaan ?? 'Mitra BPS' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $spk->kegiatan->nama }}</div>
                                <div class="text-muted extra-small">{{ $spk->kegiatan->bidang->nama ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace">{{ $spk->mitra->id_sobat ?? '-' }}</span>
                            </td>
                            <td class="text-end fw-bold text-success fs-6">
                                Rp {{ number_format($spk->total_honor, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($spk->nomor_spk)
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 font-monospace fw-bold px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ $spk->nomor_spk }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-muted border font-monospace px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i>Belum Ada Nomor
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($spk->nomor_bast)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 font-monospace fw-bold px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i>{{ $spk->nomor_bast }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-muted border font-monospace px-2 py-1">
                                        <i class="bi bi-dash-circle me-1"></i>Belum Ada Nomor
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                <h6 class="fw-bold text-dark">Tidak Ada Data Mitra Ditemukan</h6>
                                <p class="small mb-0">Sesuaikan filter periode atau kata kunci pencarian.</p>
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
const romawiMap = {
    1: 'I', 2: 'II', 3: 'III', 4: 'IV', 5: 'V', 6: 'VI',
    7: 'VII', 8: 'VIII', 9: 'IX', 10: 'X', 11: 'XI', 12: 'XII'
};

const allKegiatansData = {!! json_encode($kegiatanOptions) !!};
const kegiatanMetaMap = {!! json_encode($kegiatanMeta) !!};
const allSpkMitraList = {!! json_encode($allSpkList ?? $spkList) !!};

document.addEventListener('DOMContentLoaded', function() {
    initFormatOptions();

    // Dropdown Kegiatan Searchable
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
                    item.className = `list-group-item list-group-item-action py-2 px-2.5 rounded-2 border-0 mb-1 d-flex align-items-center justify-content-between ${isSelected ? 'bg-primary text-white' : 'text-dark'}`;
                    
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

const defaultBpsPatterns = [
    { label: 'Pola Standar  ➔  B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}' },
    { label: 'Pola Sensus  ➔  B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SENSUS/{bulan}/{tahun}' },
    { label: 'Pola Survei  ➔  B-{nomor}/BPS/3206/SURVEI/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/SURVEI/{bulan}/{tahun}' },
    { label: 'Pola Sensus (Romawi)  ➔  B-{nomor}/BPS/3206/SENSUS/{bulan_romawi}/{tahun}', value: 'B-{nomor}/BPS/3206/SENSUS/{bulan_romawi}/{tahun}' },
    { label: 'Pola Survei (Romawi)  ➔  B-{nomor}/BPS/3206/SURVEI/{bulan_romawi}/{tahun}', value: 'B-{nomor}/BPS/3206/SURVEI/{bulan_romawi}/{tahun}' },
    { label: 'Pola Surat Tugas  ➔  B-{nomor}/BPS/3206/ST/{bulan}/{tahun}', value: 'B-{nomor}/BPS/3206/ST/{bulan}/{tahun}' },
];

function getAllActivePatterns() {
    try {
        const data = localStorage.getItem('simantra_active_spk_patterns');
        if (data) {
            const parsed = JSON.parse(data);
            if (Array.isArray(parsed) && parsed.length > 0) return parsed;
        }
    } catch (e) {}
    return [...defaultBpsPatterns];
}

function saveActivePatterns(patterns) {
    try {
        localStorage.setItem('simantra_active_spk_patterns', JSON.stringify(patterns));
    } catch (e) {}
}

function bukaModalTambahPola() {
    document.getElementById('modalPolaHeader').className = 'modal-header bg-success text-white py-3 px-4 rounded-top-4';
    document.getElementById('modalTambahPolaLabel').textContent = 'Tambah Pola Nomor Baru';
    document.getElementById('btnSimpanPolaModal').className = 'btn btn-success px-4 fw-bold shadow-sm';
    document.getElementById('btnSimpanPolaModal').innerHTML = '<i class="bi bi-plus-lg me-1"></i> Tambahkan Pola';
    document.getElementById('editPolaOriginalValue').value = '';
    document.getElementById('inputNamaPolaBaru').value = '';
    document.getElementById('inputFormatPolaBaru').value = 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';

    const modalEl = new bootstrap.Modal(document.getElementById('modalTambahPola'));
    modalEl.show();
}

function bukaModalEditPola() {
    const currentVal = document.getElementById('formatSpkInput').value;
    const allPatterns = getAllActivePatterns();
    const item = allPatterns.find(p => p.value === currentVal) || allPatterns[0];

    if (!item) return;

    document.getElementById('modalPolaHeader').className = 'modal-header bg-primary text-white py-3 px-4 rounded-top-4';
    document.getElementById('modalTambahPolaLabel').textContent = 'Edit Pola Nomor Terpilih';
    document.getElementById('btnSimpanPolaModal').className = 'btn btn-primary px-4 fw-bold shadow-sm';
    document.getElementById('btnSimpanPolaModal').innerHTML = '<i class="bi bi-save-fill me-1"></i> Simpan Perubahan';
    document.getElementById('editPolaOriginalValue').value = item.value;
    
    // Clean label from separator for cleaner edit input
    let cleanLabel = item.label;
    if (cleanLabel.includes('➔')) {
        cleanLabel = cleanLabel.split('➔')[0].trim();
    } else if (cleanLabel.includes(':')) {
        cleanLabel = cleanLabel.split(':')[0].trim();
    }
    document.getElementById('inputNamaPolaBaru').value = cleanLabel;
    document.getElementById('inputFormatPolaBaru').value = item.value;

    const modalEl = new bootstrap.Modal(document.getElementById('modalTambahPola'));
    modalEl.show();
}

function simpanPolaSubmit() {
    const nama = document.getElementById('inputNamaPolaBaru').value.trim();
    const format = document.getElementById('inputFormatPolaBaru').value.trim();
    const originalVal = document.getElementById('editPolaOriginalValue').value;

    if (!nama || !format) {
        alert('Silakan isi Nama Pola dan Struktur Format Pola terlebih dahulu!');
        return;
    }

    let allPatterns = getAllActivePatterns();

    if (originalVal) {
        // MODE EDIT
        allPatterns = allPatterns.map(p => {
            if (p.value === originalVal) {
                return { label: `${nama}  ➔  ${format}`, value: format };
            }
            return p;
        });
    } else {
        // MODE TAMBAH BARU
        allPatterns.push({ label: `${nama}  ➔  ${format}`, value: format });
    }

    saveActivePatterns(allPatterns);

    const modalEl = bootstrap.Modal.getInstance(document.getElementById('modalTambahPola'));
    if (modalEl) modalEl.hide();

    // Select the new / edited format
    document.getElementById('formatSpkInput').value = format;
    initFormatOptions();
}

function hapusPolaAktif() {
    let allPatterns = getAllActivePatterns();
    if (allPatterns.length <= 1) {
        alert('Minimal harus ada 1 pola format nomor di dalam sistem!');
        return;
    }

    const currentVal = document.getElementById('formatSpkInput').value;
    const item = allPatterns.find(p => p.value === currentVal);
    
    const labelToDelete = item ? item.label : currentVal;
    if (!confirm(`Apakah Anda yakin ingin menghapus pola ini dari daftar pilihan:\n"${labelToDelete}"?`)) return;

    allPatterns = allPatterns.filter(p => p.value !== currentVal);
    saveActivePatterns(allPatterns);

    // Set selection to first available pattern
    document.getElementById('formatSpkInput').value = allPatterns[0].value;
    initFormatOptions();
}

function resetPolaKeDefault() {
    if (!confirm('Apakah Anda ingin memulihkan seluruh daftar pola ke setelan awal standar BPS?')) return;
    saveActivePatterns(defaultBpsPatterns);
    document.getElementById('formatSpkInput').value = defaultBpsPatterns[0].value;
    initFormatOptions();
}

function initFormatOptions() {
    const select = document.getElementById('formatSpkSelect');
    select.innerHTML = '';

    const allPatterns = getAllActivePatterns();

    allPatterns.forEach(opt => {
        const el = document.createElement('option');
        el.value = opt.value;
        el.textContent = opt.label;
        select.appendChild(el);
    });

    let currentVal = document.getElementById('formatSpkInput').value;
    let matched = allPatterns.find(o => o.value === currentVal);

    if (matched) {
        select.value = currentVal;
    } else {
        select.value = allPatterns[0].value;
        document.getElementById('formatSpkInput').value = allPatterns[0].value;
    }

    updateLivePreview();
}

function onFormatSpkChanged(val) {
    document.getElementById('formatSpkInput').value = val;
    checkDeleteButtonVisibility();
    updateLivePreview();
}

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

function updateLivePreview() {
    let pattern = document.getElementById('formatSpkInput').value || 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
    const nomorAwal = parseInt(document.getElementById('nomorAwalInput').value) || 1;
    const bulanSpk = parseInt(document.getElementById('bulanSpkSelect').value) || 1;
    const tahunSpk = document.getElementById('tahunSpkInput').value || '{{ $tahun }}';

    // Coba ambil short_name dari kegiatan pertama yang dicentang
    const firstChecked = document.querySelector('.mitra-checkbox:checked');
    let shortName = '';
    if (firstChecked) {
        const parts = firstChecked.value.split('_');
        const kId = parts.slice(1).join('_');
        const meta = kegiatanMetaMap[kId];
        if (meta) {
            shortName = meta.short_name || '';
            if (meta.format_spk) pattern = meta.format_spk;
        }
    }

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
        .replace(/{jenis}/g, (shortName || 'SPK').toUpperCase());

    document.getElementById('livePreviewBadge').textContent = preview;
    updateLastNomorInfo();
}

const maxSpkSeq = {{ $maxSpkSeq ?? 0 }};
const maxBastSeq = {{ $maxBastSeq ?? 0 }};
const lastSpkDoc = @json($lastSpkDoc ?? '');
const lastBastDoc = @json($lastBastDoc ?? '');

function setLanjutanNomor() {
    const jenis = (document.getElementById('jenisDokumenSelect').value || 'spk').toLowerCase();
    const curMax = (jenis === 'bast') ? maxBastSeq : maxSpkSeq;
    const nextNomor = (curMax > 0) ? (curMax + 1) : 1;
    
    document.getElementById('nomorAwalInput').value = nextNomor;
    updateLivePreview();
}

function setResetNomor() {
    document.getElementById('nomorAwalInput').value = 1;
    updateLivePreview();
}

function updateLastNomorInfo() {
    const jenis = (document.getElementById('jenisDokumenSelect').value || 'spk').toLowerCase();
    const curMax = (jenis === 'bast') ? maxBastSeq : maxSpkSeq;
    const curDoc = (jenis === 'bast') ? lastBastDoc : lastSpkDoc;
    const infoContainer = document.getElementById('lastNomorInfoText');
    
    if (!infoContainer) return;
    
    if (curMax > 0) {
        infoContainer.innerHTML = `<span class="text-success fw-semibold text-truncate" title="Nomor terakhir: ${curDoc}">
            <i class="bi bi-info-circle me-1"></i>Terakhir di DB: <strong>${curDoc}</strong> (No. ${curMax})
        </span>`;
    } else {
        infoContainer.innerHTML = `<span class="text-muted"><i class="bi bi-info-circle me-1"></i>Belum ada nomor di DB</span>`;
    }
}

function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.mitra-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}

function searchMitra() {
    const searchVal = document.getElementById('searchInput').value;
    document.getElementById('hiddenSearch').value = searchVal;
    submitFilter();
}

function bukaModalPratinjau() {
    const checkedBoxes = document.querySelectorAll('.mitra-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Silakan pilih (centang) minimal 1 mitra pada tabel di bawah terlebih dahulu!');
        return;
    }

    const globalPattern = document.getElementById('formatSpkInput').value || 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
    let nomorStart = parseInt(document.getElementById('nomorAwalInput').value) || 1;
    const bulanSpk = parseInt(document.getElementById('bulanSpkSelect').value) || 1;
    const tahunSpk = document.getElementById('tahunSpkInput').value || '{{ $tahun }}';

    const bulanPad = String(bulanSpk).padStart(2, '0');
    const bulanRom = romawiMap[bulanSpk] || 'I';

    const tbody = document.getElementById('pratinjauTabelBody');
    tbody.innerHTML = '';

    let currentCounter = nomorStart;

    checkedBoxes.forEach((cb, idx) => {
        const parts = cb.value.split('_');
        const mitraId = parts[0];
        const kegiatanId = parts.slice(1).join('_');
        const mitraData = allSpkMitraList.find(m => String(m.mitra_id) === String(mitraId) && String(m.kegiatan_id) === String(kegiatanId));
        if (!mitraData) return;

        // Ambil format & short_name dari kegiatan
        const meta = kegiatanMetaMap[mitraData.kegiatan_id] || {};
        const fmt = meta.format_spk || globalPattern;
        const shortName = meta.short_name || '';

        const nomorPad = String(currentCounter).padStart(4, '0');
        const defaultNomor = fmt
            .replace(/{nomor}/g, nomorPad)
            .replace(/{nomor_raw}/g, currentCounter)
            .replace(/{bulan}/g, bulanPad)
            .replace(/{bulan_romawi}/g, bulanRom)
            .replace(/{tahun}/g, tahunSpk)
            .replace(/{jenis}/g, shortName.toUpperCase());

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="text-center fw-bold text-muted">${idx + 1}</td>
            <td>
                <div class="fw-bold text-dark">${mitraData.mitra.nama}</div>
                <div class="text-muted extra-small">${mitraData.mitra.pekerjaan || 'Mitra BPS'}</div>
            </td>
            <td>
                <div class="fw-semibold text-dark small">${mitraData.kegiatan.nama}</div>
                <div class="text-muted extra-small">${mitraData.kegiatan.bidang ? mitraData.kegiatan.bidang.nama : '-'}</div>
            </td>
            <td>
                <span class="badge bg-light text-dark border font-monospace">${mitraData.mitra.id_sobat || '-'}</span>
            </td>
            <td class="text-end fw-bold text-success">
                Rp ${new Intl.NumberFormat('id-ID').format(mitraData.total_honor)}
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-pencil-fill text-warning"></i></span>
                    <input type="text" name="preview_custom_nomor[${mitraId}_${kegiatanId}]" 
                           data-mitra-id="${mitraId}" 
                           data-kegiatan-id="${kegiatanId}"
                           class="form-control font-monospace fw-bold text-primary border-primary preview-nomor-input" 
                           value="${defaultNomor}">
                </div>
            </td>
        `;
        tbody.appendChild(row);
        currentCounter++;
    });

    const modal = new bootstrap.Modal(document.getElementById('modalPratinjauSpk'));
    modal.show();
}

function submitFinalFromPreview() {
    const mainForm = document.getElementById('penomoranForm');
    document.querySelectorAll('.hidden-custom-nomor-input').forEach(el => el.remove());

    const inputs = document.querySelectorAll('.preview-nomor-input');
    inputs.forEach(inp => {
        const mitraId = inp.getAttribute('data-mitra-id');
        const kegiatanId = inp.getAttribute('data-kegiatan-id');
        const val = inp.value;
        const hiddenEl = document.createElement('input');
        hiddenEl.type = 'hidden';
        hiddenEl.name = `custom_nomors[${mitraId}_${kegiatanId}]`;
        hiddenEl.value = val;
        hiddenEl.className = 'hidden-custom-nomor-input';
        mainForm.appendChild(hiddenEl);
    });

    mainForm.submit();
}

function eksekusiResetNomor() {
    const checkedBoxes = document.querySelectorAll('.mitra-checkbox:checked');
    if (checkedBoxes.length === 0) {
        alert('Silakan pilih (centang) minimal 1 mitra yang ingin direset nomor SPK & BAST-nya!');
        return;
    }

    const jenisReset = document.getElementById('jenisResetSelect').value;
    const labelMap = { 'spk': 'nomor SPK', 'bast': 'nomor BAST', 'semua': 'nomor SPK & BAST' };
    const resetLabel = labelMap[jenisReset] || 'nomor SPK & BAST';

    if (!confirm(`Apakah Anda yakin ingin MERESET (MENGOSONGKAN) ${resetLabel} untuk ${checkedBoxes.length} item terpilih?`)) {
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = "{{ route('spk.penomoran.reset') }}";

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = "{{ csrf_token() }}";
    form.appendChild(csrfInput);

    const tahunInput = document.createElement('input');
    tahunInput.type = 'hidden';
    tahunInput.name = 'tahun';
    tahunInput.value = "{{ $tahun }}";
    form.appendChild(tahunInput);

    const bAwalInput = document.createElement('input');
    bAwalInput.type = 'hidden';
    bAwalInput.name = 'bulan_awal';
    bAwalInput.value = "{{ $bulanAwal }}";
    form.appendChild(bAwalInput);

    const bAkhirInput = document.createElement('input');
    bAkhirInput.type = 'hidden';
    bAkhirInput.name = 'bulan_akhir';
    bAkhirInput.value = "{{ $bulanAkhir }}";
    form.appendChild(bAkhirInput);

    const jenisInput = document.createElement('input');
    jenisInput.type = 'hidden';
    jenisInput.name = 'jenis_reset';
    jenisInput.value = jenisReset;
    form.appendChild(jenisInput);

    checkedBoxes.forEach(cb => {
        const parts = cb.value.split('_');
        const mId = parts[0];
        const kId = parts.slice(1).join('_');

        const mInput = document.createElement('input');
        mInput.type = 'hidden';
        mInput.name = 'mitra_ids[]';
        mInput.value = mId;
        form.appendChild(mInput);

        const kInput = document.createElement('input');
        kInput.type = 'hidden';
        kInput.name = 'kegiatan_ids[]';
        kInput.value = kId;
        form.appendChild(kInput);
    });

    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
@endsection

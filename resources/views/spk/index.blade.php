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
    <input type="hidden" name="jenis_dokumen" value="spk">
    @if($bidangId)<input type="hidden" name="bidang_id" value="{{ $bidangId }}">@endif
    @if($kegiatanId)<input type="hidden" name="kegiatan_id" value="{{ $kegiatanId }}">@endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders text-primary me-2"></i>Pengaturan Dokumen & Penomoran BPS</h6>
        </div>
        <div class="card-body p-3">
            <!-- Row 1: Document Parameters with Template Name Dropdown -->
            <div class="row g-3 align-items-end mb-2">
                <div class="col-12 col-md-5">
                    <label class="form-label text-primary fw-bold small mb-1">NAMA TEMPLATE DOKUMEN</label>
                    <select name="template_id" class="form-select border-primary fw-bold" onchange="this.form.submit()">
                        @forelse($templates as $tmpl)
                            <option value="{{ $tmpl->id }}" {{ (request('template_id') == $tmpl->id || ($loop->first && !request('template_id'))) ? 'selected' : '' }}>
                                [{{ strtoupper($tmpl->jenis_dokumen) }}] {{ $tmpl->nama }}
                            </option>
                        @empty
                            <option value="">Template Baku BPS (Default System)</option>
                        @endforelse
                    </select>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label text-primary fw-bold small mb-1">KATEGORI KEGIATAN</label>
                    <select name="kategori_kegiatan" class="form-select border-primary">
                        <option value="umum" {{ $kategoriKegiatan === 'umum' ? 'selected' : '' }}>Umum / Rutin</option>
                        <option value="sensus" {{ $kategoriKegiatan === 'sensus' ? 'selected' : '' }}>Kegiatan Sensus (Sensus Pertanian/Ekonomi/dsb)</option>
                        <option value="survei" {{ $kategoriKegiatan === 'survei' ? 'selected' : '' }}>Kegiatan Survei (Survei Bulanan/Triwulanan)</option>
                    </select>
                </div>
                <div class="col-12 col-md-3">
                    <label class="form-label text-primary fw-bold small mb-1">NOMOR URUT AWAL DOKUMEN</label>
                    <input type="number" name="nomor_awal" class="form-select border-primary fw-bold" value="{{ $nomorAwal }}" min="1" placeholder="Contoh: 1 / 1001">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-danger fw-bold shadow-sm px-4 py-2">
                        <i class="bi bi-printer-fill me-1"></i> CETAK MASSAL MITRA TERPILIH
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Filter Pencarian Tabel -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('spk.index') }}" class="row g-2 align-items-end">
            <div class="col-6 col-md-1">
                <label class="form-label text-muted small fw-bold mb-1">TAHUN</label>
                <select name="tahun" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BULAN AWAL</label>
                <select name="bulan_awal" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAwal == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BULAN AKHIR</label>
                <select name="bulan_akhir" class="form-select px-2" onchange="this.form.submit()">
                    @foreach($monthOptions as $angka => $nm)
                        <option value="{{ $angka }}" {{ $bulanAkhir == $angka ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->role === 'admin')
            <div class="col-6 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BIDANG</label>
                <select name="bidang_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Bidang</option>
                    @foreach($bidangOptions as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                <select name="kegiatan_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatanOptions as $k)
                        <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div class="col-12 col-md-4">
                <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                <select name="kegiatan_id" class="form-select px-2" onchange="this.form.submit()">
                    <option value="">Semua Kegiatan</option>
                    @foreach($kegiatanOptions as $k)
                        <option value="{{ $k->id }}" {{ $kegiatanId == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-12 col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">CARI NAMA / ID SOBAT</label>
                <div class="input-group">
                    <input type="text" name="search" class="form-select px-2" placeholder="Nama / ID Sobat..." value="{{ $search }}">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                    <a href="{{ route('spk.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- TABEL DAFTAR MITRA MULTI-PETUGAS           -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <input type="checkbox" id="selectAllMitra" class="form-check-input mt-0" style="width: 20px; height: 20px; cursor: pointer;" onclick="toggleSelectAll(this)">
            <label for="selectAllMitra" class="fw-bold text-dark mb-0" style="cursor: pointer;">PILIH SEMUA MITRA (Total: {{ $spkList->count() }} Mitra)</label>
        </div>
        <span class="badge bg-light text-dark border px-3 py-2">
            Periode: <strong>{{ $monthOptions[$bulanAwal] }} {{ $bulanAwal !== $bulanAkhir ? '- ' . $monthOptions[$bulanAkhir] : '' }} {{ $tahun }}</strong>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="ps-3 text-center" style="width: 50px;">PILIH</th>
                        <th>NAMA MITRA</th>
                        <th>ID SOBAT (SPK)</th>
                        <th>NO. HP</th>
                        <th class="text-center">JUMLAH KEGIATAN</th>
                        <th class="text-end">TOTAL HONOR</th>
                        <th class="text-center pe-3" style="width: 260px;">AKSI CETAK INDIVIDUAL</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spkList as $idx => $spk)
                        <tr>
                            <td class="ps-3 text-center">
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
                            <td class="text-center pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('spk.cetak-utama', array_filter(['mitra' => $spk->mitra_id, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId, 'nomor_awal' => $nomorAwal + $idx, 'template_id' => request('template_id')])) }}" target="_blank" class="btn btn-outline-danger fw-bold" title="Cetak / Simpan PDF Dokumen">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> {{ (isset($selectedTemplate) && $selectedTemplate->jenis_dokumen === 'bast') ? 'BAST' : 'Utama' }}
                                    </a>
                                    <a href="{{ route('spk.cetak-lampiran', array_filter(['mitra' => $spk->mitra_id, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId])) }}" target="_blank" class="btn btn-outline-primary fw-bold" title="Cetak / Simpan PDF Lampiran SPK">
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
function toggleSelectAll(source) {
    const checkboxes = document.querySelectorAll('.mitra-checkbox');
    checkboxes.forEach(cb => cb.checked = source.checked);
}
</script>
@endpush
@endsection

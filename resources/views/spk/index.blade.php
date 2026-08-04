@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Modul Cetak SPK (Surat Perjanjian Kerja)</h4>
        <p class="text-muted small mb-0">Cetak Surat Perjanjian Kerja & Lampiran Rincian Tugas Mitra BPS</p>
    </div>
</div>

<!-- ========================================== -->
<!-- BILAH FILTER SPK                           -->
<!-- ========================================== -->
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
                    <input type="text" name="search" class="form-select px-2" placeholder="NIP / Nama Mitra..." value="{{ $search }}">
                    <button type="submit" class="btn btn-primary" title="Cari"><i class="bi bi-search"></i></button>
                    <a href="{{ route('spk.index') }}" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- TABEL DAFTAR MITRA SIAP CETAK SPK          -->
<!-- ========================================== -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill text-primary me-2"></i>Daftar Mitra & Alokasi Tugas (Total: {{ $spkList->count() }} Mitra)</h6>
        <span class="badge bg-light text-dark border px-3 py-2">
            Periode: <strong>{{ $monthOptions[$bulanAwal] }} {{ $bulanAwal !== $bulanAkhir ? '- ' . $monthOptions[$bulanAkhir] : '' }} {{ $tahun }}</strong>
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light border-bottom">
                    <tr>
                        <th class="ps-3 py-3 text-center" style="width: 50px;">NO</th>
                        <th>NAMA MITRA</th>
                        <th>ID SOBAT (SPK)</th>
                        <th>NO. HP</th>
                        <th class="text-center">JUMLAH KEGIATAN</th>
                        <th class="text-end">TOTAL HONOR</th>
                        <th class="text-center pe-3" style="width: 280px;">AKSI CETAK SPK</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($spkList as $idx => $spk)
                        <tr>
                            <td class="ps-3 text-center text-muted fw-bold">{{ $idx + 1 }}</td>
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
                                    <a href="{{ route('spk.cetak-utama', array_filter(['mitra' => $spk->mitra_id, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId])) }}" target="_blank" class="btn btn-outline-danger fw-bold" title="Cetak File Utama SPK">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Utama
                                    </a>
                                    <a href="{{ route('spk.cetak-lampiran', array_filter(['mitra' => $spk->mitra_id, 'tahun' => $tahun, 'bulan_awal' => $bulanAwal, 'bulan_akhir' => $bulanAkhir, 'kegiatan_id' => $kegiatanId])) }}" target="_blank" class="btn btn-outline-primary fw-bold" title="Cetak Lampiran SPK">
                                        <i class="bi bi-paperclip me-1"></i> Lampiran
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block text-secondary mb-2"></i>
                                Tidak ada data transaksi alokasi honor mitra pada periode terpilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

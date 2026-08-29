@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-journal-plus text-primary me-2"></i>{{ isset($kegiatan) ? 'Edit Kegiatan' : 'Tambah Kegiatan Baru' }}</h2>
        <p class="page-subtitle">{{ isset($kegiatan) ? 'Perbarui rincian informasi kegiatan statistik' : 'Formulir pendaftaran kegiatan survei/sensus baru' }}</p>
    </div>
    <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-card-checklist text-primary me-2"></i>Formulir Data Kegiatan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ isset($kegiatan) ? route('kegiatan.update', $kegiatan) : route('kegiatan.store') }}">
                    @csrf
                    @if(isset($kegiatan))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label for="nama" class="form-label fw-bold">Nama Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $kegiatan->nama ?? '') }}" placeholder="Contoh: Survei Angkatan Kerja Nasional (Sakernas)" required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="short_name" class="form-label fw-bold">Short Name (Untuk Nomor SPK)</label>
                            <input type="text" class="form-control @error('short_name') is-invalid @enderror" id="short_name" name="short_name" value="{{ old('short_name', $kegiatan->short_name ?? '') }}" placeholder="Contoh: SUSENAS, SAKERNAS, SURKON">
                            <div class="form-text">Nama pendek yang otomatis muncul di nomor SPK/BAST. Contoh: SUSENAS</div>
                            @error('short_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="bidang_id" class="form-label fw-bold">Bidang / Tim Kerja <span class="text-danger">*</span></label>
                            <select class="form-select @error('bidang_id') is-invalid @enderror" id="bidang_id" name="bidang_id" required>
                                <option value="">-- Pilih Bidang --</option>
                                @foreach($bidangs as $b)
                                    <option value="{{ $b->id }}" {{ old('bidang_id', $kegiatan->bidang_id ?? '') == $b->id ? 'selected' : '' }}>
                                        {{ $b->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bidang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="format_spk" class="form-label fw-bold">Format Pola Nomor SPK</label>
                        <input type="text" class="form-control font-monospace @error('format_spk') is-invalid @enderror" id="format_spk" name="format_spk" value="{{ old('format_spk', $kegiatan->format_spk ?? 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}') }}" placeholder="B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}">
                        <div class="form-text">
                            Tag tersedia: <code>{nomor}</code> = nomor urut 4 digit, <code>{jenis}</code> = short name kegiatan, <code>{bulan}</code> = bulan (01-12), <code>{tahun}</code> = tahun. 
                            Kosongkan untuk pakai format default.
                        </div>
                        @error('format_spk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="tahun" class="form-label fw-bold">Tahun Anggaran</label>
                            <input type="text" class="form-control @error('tahun') is-invalid @enderror" id="tahun" name="tahun" value="{{ old('tahun', $kegiatan->tahun ?? date('Y')) }}" placeholder="2026">
                            @error('tahun') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="kode_mata_anggaran" class="form-label fw-bold">Kode Mata Anggaran (Akun/MAK)</label>
                            <input type="text" class="form-control @error('kode_mata_anggaran') is-invalid @enderror" id="kode_mata_anggaran" name="kode_mata_anggaran" value="{{ old('kode_mata_anggaran', $kegiatan->kode_mata_anggaran ?? '') }}" placeholder="Contoh: 2894.BMA.001.051.A">
                            @error('kode_mata_anggaran') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="jumlah" class="form-label fw-bold">Jumlah (Volume)</label>
                            <input type="number" class="form-control @error('jumlah') is-invalid @enderror" id="jumlah" name="jumlah" value="{{ old('jumlah', $kegiatan->jumlah ?? 0) }}" min="0" oninput="calculateTotal()">
                            @error('jumlah') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="satuan" class="form-label fw-bold">Satuan</label>
                            <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" name="satuan" value="{{ old('satuan', $kegiatan->satuan ?? '') }}" placeholder="Dokumen / OB / Sampel">
                            @error('satuan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="harga" class="form-label fw-bold">Harga Satuan (Rp)</label>
                            <input type="number" class="form-control @error('harga') is-invalid @enderror" id="harga" name="harga" value="{{ old('harga', $kegiatan->harga ?? 0) }}" min="0" oninput="calculateTotal()">
                            @error('harga') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-4">
                            <label for="total" class="form-label fw-bold">Total Pagu (Rp)</label>
                            <input type="text" class="form-control bg-light" id="total_display" value="{{ number_format(old('total', $kegiatan->total ?? 0), 0, ',', '.') }}" readonly>
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="tgl_mulai" class="form-label fw-bold">Tanggal Mulai</label>
                            <input type="date" class="form-control @error('tgl_mulai') is-invalid @enderror" id="tgl_mulai" name="tgl_mulai" value="{{ old('tgl_mulai', $kegiatan->tgl_mulai ?? '') }}">
                            @error('tgl_mulai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-4">
                            <label for="tgl_selesai" class="form-label fw-bold">Tanggal Selesai</label>
                            <input type="date" class="form-control @error('tgl_selesai') is-invalid @enderror" id="tgl_selesai" name="tgl_selesai" value="{{ old('tgl_selesai', $kegiatan->tgl_selesai ?? '') }}">
                            @error('tgl_selesai') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <script>
                        function calculateTotal() {
                            const jml = parseFloat(document.getElementById('jumlah').value) || 0;
                            const hrg = parseFloat(document.getElementById('harga').value) || 0;
                            const tot = jml * hrg;
                            document.getElementById('total_display').value = new Intl.NumberFormat('id-ID').format(tot);
                        }
                    </script>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('kegiatan.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> {{ isset($kegiatan) ? 'Perbarui Kegiatan' : 'Simpan Kegiatan' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

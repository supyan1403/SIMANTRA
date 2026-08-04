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
    <div class="col-lg-8">
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
                        <div class="col-md-6">
                            <label for="id_sobat" class="form-label fw-bold">ID Sobat / ID Mitra</label>
                            <input type="text" class="form-control @error('id_sobat') is-invalid @enderror" id="id_sobat" name="id_sobat" value="{{ old('id_sobat', $mitra->id_sobat ?? '') }}" placeholder="Kode ID mitra (unik, untuk SKP)">
                            <div class="form-text">Kode unik mitra. Dipakai untuk pencetakan SKP nanti.</div>
                            @error('id_sobat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="no_hp" class="form-label fw-bold">Nomor HP</label>
                            <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp" name="no_hp" value="{{ old('no_hp', $mitra->no_hp ?? '') }}" placeholder="Contoh: 081234567890">
                            @error('no_hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="nama" class="form-label fw-bold">Nama Lengkap Mitra <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama', $mitra->nama ?? '') }}" placeholder="Masukkan nama lengkap mitra..." required>
                        @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="alamat" class="form-label fw-bold">Alamat Lengkap</label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat tempat tinggal mitra...">{{ old('alamat', $mitra->alamat ?? '') }}</textarea>
                        @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label for="pekerjaan" class="form-label fw-bold">Pekerjaan Utama</label>
                            <input type="text" class="form-control @error('pekerjaan') is-invalid @enderror" id="pekerjaan" name="pekerjaan" value="{{ old('pekerjaan', $mitra->pekerjaan ?? '') }}" placeholder="Contoh: Swasta, Petani">
                            @error('pekerjaan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="kode_alamat" class="form-label fw-bold">Kode Alamat / Wilayah</label>
                            <input type="text" class="form-control @error('kode_alamat') is-invalid @enderror" id="kode_alamat" name="kode_alamat" value="{{ old('kode_alamat', $mitra->kode_alamat ?? '') }}" placeholder="Contoh: 3206120001">
                            @error('kode_alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="jk" class="form-label fw-bold">Jenis Kelamin</label>
                            <select class="form-select @error('jk') is-invalid @enderror" id="jk" name="jk">
                                <option value="">-- Pilih Jenis Kelamin --</option>
                                <option value="L" {{ old('jk', $mitra->jk ?? '') == 'L' ? 'selected' : '' }}>Laki-laki (L)</option>
                                <option value="P" {{ old('jk', $mitra->jk ?? '') == 'P' ? 'selected' : '' }}>Perempuan (P)</option>
                            </select>
                            @error('jk') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

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

@endsection
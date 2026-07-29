@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-person-gear text-primary me-2"></i>{{ isset($user) ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h2>
        <p class="page-subtitle">{{ isset($user) ? 'Perbarui profil atau role akses pengguna' : 'Formulir pendaftaran akun pengguna baru' }}</p>
    </div>
    <a href="{{ route('pengaturan.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Pengaturan
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-badge text-primary me-2"></i>Formulir Data Pengguna</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ isset($user) ? route('pengaturan.update', $user) : route('pengaturan.store') }}">
                    @csrf
                    @if(isset($user))
                        @method('PUT')
                    @endif

                    <div class="mb-4">
                        <label for="name" class="form-label fw-bold">Nama Lengkap Pengguna <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" placeholder="Masukkan nama pengguna..." required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-bold">Alamat Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" placeholder="contoh@bps.go.id" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="role" class="form-label fw-bold">Role / Hak Akses <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="operator" {{ old('role', $user->role ?? '') == 'operator' ? 'selected' : '' }}>Operator (Petugas Operasional)</option>
                            <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Administrator (Akses Penuh)</option>
                        </select>
                        @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4" id="bidangGroup">
                        <label for="bidang_id" class="form-label fw-bold">Bidang (Khusus Operator)</label>
                        <select class="form-select @error('bidang_id') is-invalid @enderror" id="bidang_id" name="bidang_id">
                            <option value="">-- Semua Bidang / Admin --</option>
                            @foreach($bidangs as $b)
                                <option value="{{ $b->id }}" {{ old('bidang_id', $user->bidang_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted">Operator hanya akan mengelola transaksi kegiatan untuk bidang yang dipilih.</div>
                        @error('bidang_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-bold">Password {{ isset($user) ? '(Kosongkan jika tidak ingin mengubah)' : '*' }}</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" {{ isset($user) ? '' : 'required' }} placeholder="Minimal 6 karakter">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('pengaturan.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> {{ isset($user) ? 'Perbarui Pengguna' : 'Simpan Pengguna' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

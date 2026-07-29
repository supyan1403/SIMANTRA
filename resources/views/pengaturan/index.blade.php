@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-gear-fill text-primary me-2"></i>Pengaturan Pengguna</h2>
        <p class="page-subtitle">Manajemen akun pengguna sistem (Administrator & Operator)</p>
    </div>
    <a href="{{ route('pengaturan.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-person-plus-fill"></i> Tambah Pengguna Baru
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 55px;">No</th>
                        <th style="width: 200px;">Nama Pengguna</th>
                        <th>Alamat Email</th>
                        <th style="width: 170px;">Role / Hak Akses</th>
                        <th style="width: 150px;">Bidang</th>
                        <th style="width: 140px;">Tanggal Dibuat</th>
                        <th class="text-end pe-3" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $u)
                    <tr>
                        <td class="ps-3 text-muted fw-semibold">{{ $users->firstItem() + $i }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-circle bg-secondary bg-opacity-10 text-dark fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 30px; height: 30px; font-size: 0.75rem;">
                                    {{ strtoupper(substr($u->name, 0, 2)) }}
                                </div>
                                <span class="fw-bold text-dark">{{ $u->name }}</span>
                            </div>
                        </td>
                        <td class="text-slate-600">{{ $u->email }}</td>
                        <td>
                            @if($u->role === 'admin')
                                <span class="badge badge-soft-danger"><i class="bi bi-shield-lock me-1"></i> Administrator</span>
                            @else
                                <span class="badge badge-soft-success"><i class="bi bi-person me-1"></i> Operator</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $u->bidang->nama ?? 'Semua Bidang' }}</span>
                        </td>
                        <td class="text-muted small">{{ $u->created_at ? $u->created_at->format('d M Y H:i') : '-' }}</td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1">
                                <form method="POST" action="{{ route('pengaturan.reset-password', $u) }}" class="d-inline" onsubmit="return confirm('Reset kata sandi pengguna {{ $u->name }} ke default (password123)?')">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-warning text-dark py-0 px-2" title="Reset Password"><i class="bi bi-key"></i> Reset</button>
                                </form>
                                <a href="{{ route('pengaturan.edit', $u) }}" class="btn-action btn-action-edit" title="Edit"><i class="bi bi-pencil-square"></i></a>
                                @if($u->id !== auth()->id())
                                <form method="POST" action="{{ route('pengaturan.destroy', $u) }}" class="d-inline" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?')">
                                    @csrf @method('DELETE')
                                    <button class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            <i class="bi bi-people fs-1 text-muted d-block mb-2"></i>
                            Belum ada pengguna terdaftar
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-2.5 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-1">
                Menampilkan <strong>{{ $users->firstItem() }}</strong> - <strong>{{ $users->lastItem() }}</strong> dari <strong>{{ number_format($users->total()) }}</strong> data
            </div>
            <div>
                {{ $users->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

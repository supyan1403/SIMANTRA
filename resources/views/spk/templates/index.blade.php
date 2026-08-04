@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-folder-symlink-fill text-primary me-2"></i>Kelola Template Dokumen SPK & BAST</h4>
        <p class="text-muted small mb-0">Unggah dan atur berkas template Surat Perjanjian Kerja & Berita Acara Serah Terima BPS</p>
    </div>
    <a href="{{ route('spk.index') }}" class="btn btn-outline-secondary font-monospace fw-bold">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Modul Cetak
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Form Upload Template Baru -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-cloud-arrow-up-fill text-primary me-2"></i>Upload Template Baru</h6>
            </div>
            <div class="card-body p-3">
                <form method="POST" action="{{ route('spk.templates.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">NAMA TEMPLATE</label>
                        <input type="text" name="nama" class="form-control" placeholder="Contoh: Template SPK Sensus Pertanian 2026" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">JENIS DOKUMEN</label>
                        <select name="jenis_dokumen" class="form-select" required>
                            <option value="spk">SPK (Surat Perjanjian Kerja)</option>
                            <option value="bast">BAST (Berita Acara Serah Terima)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">KATEGORI KEGIATAN</label>
                        <select name="kategori_kegiatan" class="form-select" required>
                            <option value="sensus">Kegiatan Sensus</option>
                            <option value="survei">Kegiatan Survei</option>
                            <option value="umum" selected>Umum / Rutin</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">FILE TEMPLATE (DOCX / PDF / TXT)</label>
                        <input type="file" name="file_template" class="form-control" accept=".docx,.doc,.pdf,.txt">
                        <span class="extra-small text-muted">Format: DOCX, PDF (Max: 5MB)</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold mb-1">DESKRIPSI / CATATAN</label>
                        <textarea name="deskripsi" class="form-control" rows="2" placeholder="Catatan peruntukan template..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                        <i class="bi bi-upload me-1"></i> SIMPAN TEMPLATE DOKUMEN
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tabel Daftar Template -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-files text-primary me-2"></i>Daftar Template Dokumen Aktif (Total: {{ $templates->count() }})</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3" style="width: 40px;">NO</th>
                                <th>NAMA TEMPLATE</th>
                                <th>JENIS DOKUMEN</th>
                                <th>KATEGORI</th>
                                <th class="text-center pe-3" style="width: 130px;">AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($templates as $idx => $tmpl)
                                <tr>
                                    <td class="ps-3 text-muted fw-bold">{{ $idx + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $tmpl->nama }}</div>
                                        @if($tmpl->deskripsi)<div class="text-muted extra-small">{{ $tmpl->deskripsi }}</div>@endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $tmpl->jenis_dokumen === 'spk' ? 'bg-danger' : 'bg-primary' }} bg-opacity-10 text-{{ $tmpl->jenis_dokumen === 'spk' ? 'danger' : 'primary' }} fw-bold px-2 py-1">
                                            {{ strtoupper($tmpl->jenis_dokumen) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-2 py-1 text-capitalize">
                                            {{ $tmpl->kategori_kegiatan }}
                                        </span>
                                    </td>
                                    <td class="text-center pe-3">
                                        @if($tmpl->file_path)
                                            <a href="{{ asset('storage/' . $tmpl->file_path) }}" target="_blank" class="btn btn-sm btn-outline-info p-1 me-1" title="Unduh Berkas"><i class="bi bi-download"></i></a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-primary p-1 me-1" data-bs-toggle="modal" data-bs-target="#editTemplateModal{{ $tmpl->id }}" title="Edit Template">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        <form method="POST" action="{{ route('spk.templates.destroy', $tmpl->id) }}" class="d-inline" onsubmit="return confirm('Hapus template ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger p-1" title="Hapus"><i class="bi bi-trash"></i></button>
                                        </form>

                                        <!-- MODAL EDIT TEMPLATE -->
                                        <div class="modal fade text-start" id="editTemplateModal{{ $tmpl->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('spk.templates.update', $tmpl->id) }}" enctype="multipart/form-data">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit Template Dokumen</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small fw-bold mb-1">NAMA TEMPLATE</label>
                                                                <input type="text" name="nama" class="form-control" value="{{ $tmpl->nama }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small fw-bold mb-1">JENIS DOKUMEN</label>
                                                                <select name="jenis_dokumen" class="form-select" required>
                                                                    <option value="spk" {{ $tmpl->jenis_dokumen === 'spk' ? 'selected' : '' }}>SPK (Surat Perjanjian Kerja)</option>
                                                                    <option value="bast" {{ $tmpl->jenis_dokumen === 'bast' ? 'selected' : '' }}>BAST (Berita Acara Serah Terima)</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small fw-bold mb-1">KATEGORI KEGIATAN</label>
                                                                <select name="kategori_kegiatan" class="form-select" required>
                                                                    <option value="sensus" {{ $tmpl->kategori_kegiatan === 'sensus' ? 'selected' : '' }}>Kegiatan Sensus</option>
                                                                    <option value="survei" {{ $tmpl->kategori_kegiatan === 'survei' ? 'selected' : '' }}>Kegiatan Survei</option>
                                                                    <option value="umum" {{ $tmpl->kategori_kegiatan === 'umum' ? 'selected' : '' }}>Umum / Rutin</option>
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small fw-bold mb-1">GANTI FILE TEMPLATE (OPSIONAL)</label>
                                                                <input type="file" name="file_template" class="form-control" accept=".docx,.doc,.pdf,.txt">
                                                                <span class="extra-small text-muted">Biarkan kosong jika tidak ingin mengubah berkas file template.</span>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label class="form-label text-muted small fw-bold mb-1">DESKRIPSI / CATATAN</label>
                                                                <textarea name="deskripsi" class="form-control" rows="2">{{ $tmpl->deskripsi }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder-x fs-1 d-block text-secondary mb-2"></i>
                                        Belum ada template khusus yang diunggah. Sistem menggunakan template standar BPS secara otomatis.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Data Mata Anggaran</h2>
        <p class="page-subtitle">Kelola data mata anggaran kegiatan statistik, kode MAK, volume, harga satuan, dan pagu anggaran per tim kerja</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-shrink-0 text-nowrap">
        @if(auth()->user()?->role === 'admin' || auth()->user()?->role === 'operator')
            <a href="{{ route('kegiatan.import.template') }}" class="btn btn-outline-success d-flex align-items-center gap-2 shadow-sm text-nowrap">
                <i class="bi bi-download"></i> Download Template
            </a>
            <a href="{{ route('kegiatan.import.index') }}" class="btn btn-success d-flex align-items-center gap-2 shadow-sm text-nowrap">
                <i class="bi bi-cloud-arrow-up-fill"></i> Upload Template
            </a>
        @endif
        @if(auth()->user()?->role === 'admin')
            <a href="{{ route('kegiatan.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm text-nowrap">
                <i class="bi bi-plus-circle-fill"></i> Tambah Mata Anggaran
            </a>
        @endif
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('kegiatan.index') }}" class="row g-2 align-items-center">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama kegiatan atau kode MAK..." value="{{ $search }}">
                </div>
            </div>
            
            <div class="col-12 col-md-4">
                <select name="bidang_id" class="form-select" onchange="this.form.submit()" {{ auth()->user()->role === 'operator' && auth()->user()->bidang_id ? 'disabled' : '' }}>
                    @if(!auth()->user()->bidang_id || auth()->user()->role !== 'operator')
                        <option value="all">Semua Bidang / Tim Kerja</option>
                    @endif
                    @foreach($bidangs as $b)
                        <option value="{{ $b->id }}" {{ $bidangId == $b->id ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-filter"></i> Filter
                </button>
                @if($search || ($bidangId && $bidangId !== 'all'))
                    <a href="{{ route('kegiatan.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">NO</th>
                        <th style="width: 80px;">TAHUN</th>
                        <th>NAMA KEGIATAN & MAK</th>
                        <th>BIDANG / TIM</th>
                        <th>VOLUME & PAGU ANGGARAN</th>
                        <th>JADWAL KEGIATAN</th>
                        <th class="text-center">MITRA BERTUGAS</th>
                        <th class="text-end pe-3" style="width: 150px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kegiatans as $index => $kegiatan)
                    <tr>
                        <td class="ps-3 fw-bold text-muted">{{ $kegiatans->firstItem() + $index }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $kegiatan->tahun ?? '2024' }}</span>
                        </td>
                        <td>
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word;">{{ $kegiatan->nama }}</div>
                            @if($kegiatan->kode_mata_anggaran)
                                <code class="px-2 py-0.5 bg-light border rounded text-dark small mt-1 d-inline-block">MAK: {{ $kegiatan->kode_mata_anggaran }}</code>
                            @endif
                        </td>
                        <td>
                            @php
                                $colorMap = [
                                    'Distribusi' => 'primary',
                                    'Neraca' => 'success',
                                    'Produksi' => 'warning',
                                    'Sosial' => 'info',
                                    'Cadangan' => 'secondary',
                                ];
                                $color = $colorMap[$kegiatan->bidang->nama ?? ''] ?? 'primary';
                            @endphp
                            <span class="badge badge-soft-{{ $color }}">
                                <i class="bi bi-building me-1"></i>{{ $kegiatan->bidang->nama ?? '-' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $totalNominalKegiatan = $kegiatan->alokasiHonors->sum('nominal');
                                $paguTotal = $kegiatan->total > 0 ? $kegiatan->total : $totalNominalKegiatan;
                            @endphp
                            <div class="fw-extrabold text-success fs-6">Rp {{ number_format($paguTotal, 0, ',', '.') }}</div>
                            @if($kegiatan->jumlah > 0)
                                <span class="text-muted small">{{ number_format($kegiatan->jumlah) }} {{ $kegiatan->satuan ?? 'Volume' }} @ Rp {{ number_format($kegiatan->harga, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td>
                            @if($kegiatan->jadwal->isNotEmpty())
                                @php
                                    $bulanNama = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                                    $jadwalBulan = $kegiatan->jadwal->pluck('bulan_angka')->map(fn($b) => $bulanNama[$b - 1]);
                                    $jr = $kegiatan->jadwal->sortBy('bulan_angka');
                                    $first = $bulanNama[$jr->first()->bulan_angka - 1];
                                    $last = $bulanNama[$jr->last()->bulan_angka - 1];
                                @endphp
                                <span class="badge badge-soft-info" title="{{ $jadwalBulan->implode(', ') }}"><i class="bi bi-calendar-event me-1"></i>{{ $first == $last ? $first : $first . ' - ' . $last }} {{ $kegiatan->tahun ?? '' }}</span>
                            @elseif($kegiatan->tgl_mulai || $kegiatan->tgl_selesai)
                                <span class="text-muted small"><i class="bi bi-calendar-range me-1 text-primary"></i>{{ $kegiatan->tgl_mulai ? date('d/m/Y', strtotime($kegiatan->tgl_mulai)) : '-' }} s/d {{ $kegiatan->tgl_selesai ? date('d/m/Y', strtotime($kegiatan->tgl_selesai)) : '-' }}</span>
                            @else
                                @php
                                    $periodes = $kegiatan->alokasiHonors->map(fn($a) => $a->periode)->filter()->sortBy('bulan_angka');
                                    $firstBulan = $periodes->first()?->bulan;
                                    $lastBulan = $periodes->last()?->bulan;
                                @endphp
                                @if($firstBulan)
                                    <span class="badge badge-soft-info"><i class="bi bi-calendar-event me-1"></i>{{ $firstBulan == $lastBulan ? $firstBulan : $firstBulan . ' - ' . $lastBulan }} {{ $kegiatan->tahun ?? '2024' }}</span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            @endif
                        </td>
                        <td class="text-center">
                            @if($kegiatan->total_alokasi > 0)
                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold border-0 bg-info bg-opacity-10 text-info" data-bs-toggle="modal" data-bs-target="#modalMitra_{{ $kegiatan->id }}" title="Klik untuk lihat daftar {{ $kegiatan->total_alokasi }} mitra">
                                    <i class="bi bi-people-fill me-1"></i> {{ number_format($kegiatan->total_alokasi) }} Mitra
                                </button>
                            @else
                                <span class="badge bg-light text-muted border fw-normal">0 Mitra</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-flex justify-content-end align-items-center gap-1">
                                @if($kegiatan->total_alokasi > 0)
                                    <button type="button" class="btn-action bg-info bg-opacity-10 text-info border border-info border-opacity-25" data-bs-toggle="modal" data-bs-target="#modalMitra_{{ $kegiatan->id }}" title="Lihat Detail Mitra">
                                        <i class="bi bi-eye-fill"></i> Detail
                                    </button>
                                @endif
                                @if(auth()->user()?->role === 'admin')
                                <a href="{{ route('kegiatan.edit', $kegiatan) }}" class="btn-action btn-action-edit">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </a>
                                <form action="{{ route('kegiatan.destroy', $kegiatan) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata anggaran kegiatan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete">
                                        <i class="bi bi-trash-fill"></i> Hapus
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-slate-300"></i>
                            Tidak ada data mata anggaran yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($kegiatans->hasPages())
    <div class="card-footer bg-white border-top py-3 d-flex justify-content-between align-items-center">
        <span class="text-muted small">Menampilkan {{ $kegiatans->firstItem() }} - {{ $kegiatans->lastItem() }} dari {{ $kegiatans->total() }} kegiatan</span>
        {{ $kegiatans->links() }}
    </div>
    @endif
</div>

<!-- Modals Detail Mitra (Placed Outside Table for Valid HTML Rendering) -->
@foreach($kegiatans as $kegiatan)
    @if($kegiatan->total_alokasi > 0)
    <div class="modal fade" id="modalMitra_{{ $kegiatan->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-dark text-white py-3">
                    <div>
                        <h5 class="modal-title fw-bold fs-6 text-white mb-0"><i class="bi bi-people-fill text-info me-2"></i>Daftar Mitra Bertugas</h5>
                        <span class="small text-slate-300">{{ $kegiatan->nama }} ({{ $kegiatan->bidang->nama ?? '-' }})</span>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-bordered table-hover align-middle mb-0">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">NO</th>
                                    <th>NAMA MITRA</th>
                                    <th>ALAMAT DETAIL</th>
                                    <th>PERIODE</th>
                                    <th class="text-end pe-3">HONOR ALOKASI</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($kegiatan->alokasiHonors as $mNo => $alokasi)
                                <tr>
                                    <td class="ps-3 text-muted fw-bold">{{ $mNo + 1 }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $alokasi->mitra->nama ?? 'Mitra Dihapus' }}</div>
                                        <span class="text-muted small">{{ $alokasi->mitra->pekerjaan ?? '-' }}</span>
                                    </td>
                                    <td class="small text-muted">{{ $alokasi->mitra->alamat ?? '-' }}</td>
                                    <td>
                                        <span class="badge badge-soft-primary">
                                            <i class="bi bi-calendar-event me-1"></i>{{ $alokasi->periode->bulan ?? '' }} {{ $alokasi->periode->tahun ?? '' }}
                                        </span>
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-success">
                                        Rp {{ number_format($alokasi->nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-secondary fw-bold">
                                <tr>
                                    <td colspan="4" class="text-end text-dark">TOTAL KEGIATAN:</td>
                                    <td class="text-end pe-3 text-success fs-6 fw-extrabold">
                                        Rp {{ number_format($kegiatan->alokasiHonors->sum('nominal'), 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <span class="me-auto text-muted small"><i class="bi bi-info-circle me-1"></i> Total {{ $kegiatan->total_alokasi }} alokasi mitra bertugas</span>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <a href="{{ route('monitoring.create', ['kegiatan_id' => $kegiatan->id]) }}" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-plus-lg"></i> Tambah Mitra Ke Kegiatan Ini
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

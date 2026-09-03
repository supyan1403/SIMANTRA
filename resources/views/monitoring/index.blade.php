@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-wallet2 text-primary me-2"></i>Monitoring Alokasi Honor Mitra & SBML</h2>
        <p class="page-subtitle">Kelola alokasi honor mitra, pantau kepatuhan batas SBML, dan kelola alokasi kegiatan</p>
    </div>
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <a href="{{ route('monitoring.create') }}" class="btn btn-primary d-flex align-items-center gap-2 shadow-sm fw-semibold">
            <i class="bi bi-plus-lg"></i> Tambah Alokasi Honor
        </a>
    </div>
</div>

<div class="row mb-3">
<div class="col-md-3">
<div class="card border-0 shadow-sm" style="background-color: #f0f9ff; border-left: 3px solid #0ea5e9;">
    <div class="card-body py-2 px-2.5">
        <div class="d-flex align-items-center justify-content-between mb-1">
            <span class="fw-bold text-uppercase" style="font-size: 0.65rem; color: #0369a1;">Standar SBML / Bulan</span>
            <div class="d-flex align-items-center gap-1">
                <select class="form-select form-select-sm py-0" style="width: auto; font-size: 0.65rem; color: #0369a1; border-color: #bae6fd;" onchange="window.location.href='{{ route('monitoring.index') }}?sbml_tahun='+this.value+'&{{ http_build_query(request()->except('sbml_tahun')) }}'">
                    @foreach($tahunList as $t)
                        <option value="{{ $t }}" style="color: #000;" {{ $sbmlTahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                <i class="bi bi-piggy-bank-fill" style="font-size: 0.9rem; color: #0ea5e9;"></i>
            </div>
        </div>
        <div class="d-flex gap-2">
            <div class="flex-fill rounded-2 py-1 px-1 text-center" style="background-color: #e0f2fe;">
                <span class="fw-bold d-block" style="font-size: 0.6rem; color: #0369a1;">PENCACAHAN</span>
                <span class="fw-bold" style="font-size: 0.8rem; color: #0c4a6e;">Rp {{ number_format($sbmlPencacahan, 0, ',', '.') }}</span>
            </div>
            <div class="flex-fill rounded-2 py-1 px-1 text-center" style="background-color: #e0f2fe;">
                <span class="fw-bold d-block" style="font-size: 0.6rem; color: #0369a1;">PENGOLAHAN</span>
                <span class="fw-bold" style="font-size: 0.8rem; color: #0c4a6e;">Rp {{ number_format($sbmlPengolahan, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>
</div>
</div>

<!-- Filter Panel -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-2.5">
        <form method="GET" action="{{ route('monitoring.index') }}" id="monitoringFilterForm" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">PERIODE</label>
                <select name="periode_id" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Periode</option>
                    @foreach($periodes as $p)
                        <option value="{{ $p->id }}" {{ request('periode_id') == $p->id ? 'selected' : '' }}>{{ $p->bulan }} {{ $p->tahun }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">BIDANG / TIM</label>
                <select name="bidang_id" class="form-select" onchange="this.form.submit()" {{ auth()->user()->role === 'operator' && auth()->user()->bidang_id ? 'disabled' : '' }}>
                    @if(!auth()->user()->bidang_id || auth()->user()->role !== 'operator')
                        <option value="">Semua Bidang</option>
                    @endif
                    @foreach($bidangs as $b)
                        <option value="{{ $b->id }}" {{ (request('bidang_id') == $b->id || (auth()->user()->role === 'operator' && auth()->user()->bidang_id == $b->id)) ? 'selected' : '' }}>{{ $b->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 position-relative" id="monitoringKegiatanContainer">
                <label class="form-label text-muted small fw-bold mb-1">KEGIATAN</label>
                <input type="hidden" name="kegiatan_id" id="hiddenMonitoringKegiatanId" value="{{ request('kegiatan_id') }}">
                
                <!-- Trigger Button with Searchable In-Menu Pop-up -->
                <button type="button" class="form-select text-start d-flex justify-content-between align-items-center bg-white shadow-none" 
                        id="monitoringKegiatanTrigger">
                    <span class="text-truncate me-2" id="monitoringKegiatanSelectedText">
                        @if(request('kegiatan_id') && $kegiatans->firstWhere('id', request('kegiatan_id')))
                            {{ $kegiatans->firstWhere('id', request('kegiatan_id'))->nama }}
                        @else
                            Semua Kegiatan
                        @endif
                    </span>
                    <i class="bi bi-chevron-down text-muted small" id="monitoringKegiatanChevron"></i>
                </button>

                <!-- In-Menu Dropdown Pop-up with Search Input -->
                <div id="monitoringKegiatanPopup" class="shadow-lg rounded-3 position-absolute w-100 mt-1 d-none bg-white p-2.5" 
                     style="z-index: 1060; border: 1.5px solid #93c5fd; min-width: 280px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);">
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-light text-primary border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="monitoringKegiatanInnerSearch" class="form-control border-start-0 py-1.5" 
                               placeholder="Ketik untuk mencari kegiatan..." autocomplete="off">
                    </div>
                    <div id="monitoringKegiatanOptionsList" class="list-group list-group-flush" style="max-height: 220px; overflow-y: auto;">
                        <!-- Dynamically filled -->
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">TARGET / BEBAN</label>
                <select name="volume" class="form-select" onchange="this.form.submit()">
                    <option value="">Semua Beban</option>
                    @foreach($volumeList as $vol)
                        @php
                            $formattedVol = (float)$vol == (int)$vol ? (int)$vol : (float)$vol;
                        @endphp
                        <option value="{{ $vol }}" {{ request('volume') !== null && request('volume') !== '' && request('volume') == (string)$vol ? 'selected' : '' }}>
                            {{ $formattedVol }} Beban / Volume
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label text-muted small fw-bold mb-1">CARI MITRA</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Nama mitra..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center p-2 fw-semibold" title="Cari"><i class="bi bi-search"></i></button>
                @if(request()->hasAny(['periode_id', 'bidang_id', 'kegiatan_id', 'volume', 'search']))
                    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary d-flex align-items-center justify-content-center p-2" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<style>
    .table-monitoring-scroll {
        overflow-x: auto;
        position: relative;
        -webkit-overflow-scrolling: touch;
    }

    .table-sticky-freeze {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    .table-sticky-freeze th,
    .table-sticky-freeze td {
        border-bottom: 1px solid #f1f5f9;
    }

    /* Base Sticky Columns */
    .sticky-col-1, .sticky-col-2, .sticky-col-3, .sticky-col-4 {
        position: sticky !important;
        background-color: #ffffff;
        z-index: 5;
    }

    th.sticky-col-1, th.sticky-col-2, th.sticky-col-3, th.sticky-col-4 {
        z-index: 6 !important;
        background-color: #f8fafc !important;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .sticky-col-1 {
        left: 0;
        width: 48px;
        min-width: 48px;
        max-width: 48px;
    }

    .sticky-col-2 {
        left: 48px;
        width: 220px;
        min-width: 220px;
        max-width: 220px;
    }

    .sticky-col-3 {
        left: 268px;
        width: 210px;
        min-width: 210px;
        max-width: 210px;
    }

    .sticky-col-4 {
        left: 478px;
        width: 135px;
        min-width: 135px;
        max-width: 135px;
        box-shadow: 4px 0 6px -2px rgba(0, 0, 0, 0.08) !important;
        border-right: 1px solid #cbd5e1 !important;
    }

    /* Hover & Danger Row Handling */
    .table-hover tbody tr:hover .sticky-col-1,
    .table-hover tbody tr:hover .sticky-col-2,
    .table-hover tbody tr:hover .sticky-col-3,
    .table-hover tbody tr:hover .sticky-col-4 {
        background-color: #f1f5f9 !important;
    }

    tr.table-danger .sticky-col-1,
    tr.table-danger .sticky-col-2,
    tr.table-danger .sticky-col-3,
    tr.table-danger .sticky-col-4 {
        background-color: #fef2f2 !important;
    }

    .table-hover tbody tr.table-danger:hover .sticky-col-1,
    .table-hover tbody tr.table-danger:hover .sticky-col-2,
    .table-hover tbody tr.table-danger:hover .sticky-col-3,
    .table-hover tbody tr.table-danger:hover .sticky-col-4 {
        background-color: #fee2e2 !important;
    }
</style>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-monitoring-scroll">
            <table class="table table-hover table-sticky-freeze align-middle mb-0" style="min-width: 1550px;">
                <thead>
                    <tr>
                        <th class="ps-3 text-center sticky-col-1">No</th>
                        <th class="sticky-col-2">NAMA MITRA</th>
                        <th class="sticky-col-3">POSISI MITRA</th>
                        <th class="sticky-col-4">PERIODE</th>
                        <th style="min-width: 240px;">NAMA KEGIATAN</th>
                        <th style="width: 140px;">TIPE KEGIATAN</th>
                        <th class="text-center" style="width: 140px;">TARGET / BEBAN</th>
                        <th style="width: 120px;">BIDANG / TIM</th>
                        <th style="min-width: 200px;">DOKUMEN SPK & BAST</th>
                        <th class="text-end" style="width: 200px; min-width: 190px;">NOMINAL HONOR</th>
                        <th class="text-end pe-3" style="width: 115px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alokasis as $i => $a)
                    @php
                        $sbmlKey = $a->mitra_id . '-' . $a->periode_id;
                        $sbmlWarn = $sbmlWarnings[$sbmlKey] ?? null;
                    @endphp
                    <tr class="{{ $sbmlWarn ? 'table-danger' : '' }}">
                        <td class="ps-3 text-center text-muted fw-semibold sticky-col-1">{{ $alokasis->firstItem() + $i }}</td>
                        <td class="sticky-col-2">
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar-initials bg-primary bg-opacity-10 text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                    {{ strtoupper(substr($a->mitra->nama ?? 'M', 0, 2)) }}
                                </div>
                                <div class="text-truncate">
                                    <a href="#" class="fw-bold text-dark text-decoration-none hover-primary d-inline-flex align-items-center gap-1 text-truncate" data-bs-toggle="modal" data-bs-target="#modalMitraDetail-{{ $a->mitra_id }}" title="Lihat Detail Profil Mitra">
                                        <span class="text-truncate">{{ $a->mitra->nama ?? '-' }}</span>
                                        <i class="bi bi-box-arrow-up-right extra-small text-muted flex-shrink-0"></i>
                                    </a>
                                    <span class="text-muted extra-small d-block text-truncate">{{ $a->mitra->pekerjaan_clean }}</span>
                                </div>
                            </div>
                        </td>
                        <td class="sticky-col-3">
                            @if(!empty($a->mitra->posisi))
                                <span class="badge d-inline-block text-wrap text-start" style="max-width: 100%; background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.725rem; font-weight: 600; padding: 0.28rem 0.5rem; line-height: 1.25;" title="{{ $a->mitra->posisi }}">
                                    <i class="bi bi-person-badge me-1"></i>{{ $a->mitra->posisi }}
                                </span>
                            @else
                                <span class="text-muted extra-small fst-italic">Mitra Statistik</span>
                            @endif
                        </td>
                        <td class="sticky-col-4">
                            <span class="badge badge-soft-primary"><i class="bi bi-calendar-event me-1"></i>{{ $a->periode->bulan ?? '' }} {{ $a->periode->tahun ?? '' }}</span>
                        </td>
                        <td>
                            <div class="fw-semibold text-slate-800" style="white-space: normal; word-break: break-word;">{{ $a->kegiatan->nama ?? '-' }}</div>
                        </td>
                        <td>
                            @if($a->kegiatan && $a->kegiatan->isPengolahan())
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 extra-small fw-semibold" title="Tugas Pengolahan Data">
                                    <i class="bi bi-cpu-fill me-1"></i>Pengolahan
                                </span>
                            @elseif($a->kegiatan)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 extra-small fw-semibold" title="Tugas Pendataan Lapangan">
                                    <i class="bi bi-geo-alt-fill me-1"></i>Pencacahan
                                </span>
                            @else
                                <span class="text-muted extra-small">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-2.5 py-1.5 fw-bold" style="font-size: 0.775rem;">
                                <i class="bi bi-stack text-primary me-1"></i>{{ number_format(round($a->volume ?? 1)) }} {{ $a->satuan ?? 'dokumen' }}
                            </span>
                        </td>
                        <td>
                            @php
                                $bName = $a->kegiatan->bidang->nama ?? '';
                                $bColor = match($bName) {
                                    'Sosial' => 'info',
                                    'Produksi' => 'warning',
                                    'Distribusi' => 'primary',
                                    'Neraca' => 'success',
                                    'IPDS' => 'danger',
                                    'Bagian Umum' => 'secondary',
                                    default => 'info'
                                };
                            @endphp
                            <span class="badge badge-soft-{{ $bColor }}"><i class="bi bi-diagram-3 me-1"></i>{{ $bName ?: '-' }}</span>
                        </td>
                        <td>
                            @if($a->nomor_spk || $a->nomor_bast)
                                <div class="d-flex flex-column gap-1">
                                    @if($a->nomor_spk)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 extra-small fw-semibold text-start text-truncate" style="max-width: 220px;" title="Nomor SPK: {{ $a->nomor_spk }}">
                                            <i class="bi bi-file-earmark-check-fill me-1"></i>{{ $a->nomor_spk }}
                                        </span>
                                    @endif
                                    @if($a->nomor_bast)
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 extra-small fw-semibold text-start text-truncate" style="max-width: 220px;" title="Nomor BAST: {{ $a->nomor_bast }}">
                                            <i class="bi bi-check-all me-1"></i>{{ $a->nomor_bast }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span class="badge bg-light text-muted border extra-small fw-normal">Belum Bernomor</span>
                            @endif
                        </td>
                        <td class="text-end fw-extrabold fs-6 text-nowrap {{ $sbmlWarn ? 'text-danger' : 'text-success' }}" style="width: 200px; min-width: 190px;">
                            <span class="d-inline-block">Rp {{ number_format($a->nominal, 0, ',', '.') }}</span>
                            @if($sbmlWarn)
                                <div class="mt-1">
                                    <span class="badge badge-soft-danger d-inline-flex align-items-center gap-1 text-wrap text-start" style="font-size: 0.7rem;" title="{{ $sbmlWarn['warning_reason'] ?? '' }}">
                                        <i class="bi bi-exclamation-octagon-fill"></i> Melebihi Rp {{ number_format($sbmlWarn['excess'], 0, ',', '.') }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1 justify-content-end">
                                <button type="button" class="btn-action btn-action-view" data-bs-toggle="modal" data-bs-target="#modalMitraDetail-{{ $a->mitra_id }}" title="Lihat Data Mitra">
                                    <i class="bi bi-person-lines-fill"></i>
                                </button>
                                <a href="{{ route('monitoring.edit', $a) }}" class="btn-action btn-action-edit" title="Edit Alokasi"><i class="bi bi-pencil-square"></i></a>
                                
                                <form method="POST" action="{{ route('monitoring.destroy', $a) }}" class="d-inline" onsubmit="return confirm('Hapus alokasi honor ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-action btn-action-delete" title="Hapus"><i class="bi bi-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="text-center text-muted py-5">
                            <i class="bi bi-search fs-1 text-muted d-block mb-2"></i>
                            Tidak ada data alokasi honor ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($alokasis->hasPages())
        <div class="p-2.5 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small ms-1">
                Menampilkan <strong>{{ $alokasis->firstItem() }}</strong> - <strong>{{ $alokasis->lastItem() }}</strong> dari <strong>{{ number_format($alokasis->total()) }}</strong> data
            </div>
            <div>
                {{ $alokasis->withQueryString()->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    const allMonitoringKegiatans = {!! json_encode($kegiatans) !!};

    document.addEventListener('DOMContentLoaded', function() {
        // Setup Searchable In-Menu Kegiatan Dropdown for Monitoring
        const triggerBtn = document.getElementById('monitoringKegiatanTrigger');
        const menuPopup = document.getElementById('monitoringKegiatanPopup');
        const innerSearch = document.getElementById('monitoringKegiatanInnerSearch');
        const optionsList = document.getElementById('monitoringKegiatanOptionsList');
        const hiddenInput = document.getElementById('hiddenMonitoringKegiatanId');
        const selectedText = document.getElementById('monitoringKegiatanSelectedText');
        const chevronIcon = document.getElementById('monitoringKegiatanChevron');
        const container = document.getElementById('monitoringKegiatanContainer');
        const form = document.getElementById('monitoringFilterForm');

        if (triggerBtn && menuPopup) {
            function renderOptions(query = '') {
                optionsList.innerHTML = '';
                const q = query.trim().toLowerCase();

                // 1. Opsi Default: Semua Kegiatan
                const allItem = document.createElement('a');
                allItem.href = '#';
                allItem.className = `list-group-item list-group-item-action py-2 px-2.5 rounded-2 border-0 mb-1 fw-bold ${!hiddenInput.value ? 'bg-primary text-white' : 'text-primary'}`;
                allItem.innerHTML = `<i class="bi bi-grid-fill me-1.5"></i> Semua Kegiatan`;
                allItem.addEventListener('click', function(e) {
                    e.preventDefault();
                    hiddenInput.value = '';
                    selectedText.textContent = 'Semua Kegiatan';
                    closeDropdown();
                    form.submit();
                });
                optionsList.appendChild(allItem);

                // 2. Filter items
                const filtered = allMonitoringKegiatans.filter(k => {
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
                            form.submit();
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

            // Close when clicking outside
            document.addEventListener('click', function(e) {
                if (!container.contains(e.target)) {
                    closeDropdown();
                }
            });
        }

        // Exact Sticky Freeze Column Synchronizer (Zero Jitter, Zero Shift)
        function syncStickyColumns() {
            const table = document.querySelector('.table-sticky-freeze');
            if (!table) return;
            const ths = table.querySelectorAll('thead tr th');
            if (ths.length < 4) return;

            const w1 = Math.round(ths[0].getBoundingClientRect().width);
            const w2 = Math.round(ths[1].getBoundingClientRect().width);
            const w3 = Math.round(ths[2].getBoundingClientRect().width);

            const left1 = 0;
            const left2 = w1;
            const left3 = w1 + w2;
            const left4 = w1 + w2 + w3;

            document.querySelectorAll('.sticky-col-1').forEach(el => el.style.left = left1 + 'px');
            document.querySelectorAll('.sticky-col-2').forEach(el => el.style.left = left2 + 'px');
            document.querySelectorAll('.sticky-col-3').forEach(el => el.style.left = left3 + 'px');
            document.querySelectorAll('.sticky-col-4').forEach(el => el.style.left = left4 + 'px');
        }

        syncStickyColumns();
        setTimeout(syncStickyColumns, 100);
        window.addEventListener('resize', syncStickyColumns);
    });
</script>

<!-- MODALS DETAIL DATA MITRA (DB MITRA POPUP) -->
@foreach($alokasis->unique('mitra_id') as $alokasiItem)
@php $m = $alokasiItem->mitra; @endphp
@if($m)
<div class="modal fade text-start" id="modalMitraDetail-{{ $m->id }}" tabindex="-1" aria-labelledby="modalMitraDetailLabel-{{ $m->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Header BPS Navy -->
            <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #0B3B60 0%, #1e40af 100%) !important;">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="avatar-initials bg-white text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 52px; height: 52px; font-size: 1.25rem; color: #0B3B60 !important;">
                        {{ strtoupper(substr($m->nama, 0, 2)) }}
                    </div>
                    <div class="flex-grow-1 text-truncate">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalMitraDetailLabel-{{ $m->id }}">{{ $m->nama }}</h5>
                            @if(!empty($m->posisi))
                                <span class="badge bg-white bg-opacity-25 text-white border border-white border-opacity-25 py-1 px-2" style="font-size: 0.725rem;">{{ $m->posisi }}</span>
                            @endif
                        </div>
                        <div class="text-white-50 small mt-1 font-monospace">
                            ID SOBAT: <strong class="text-white">{{ $m->id_sobat ?? '-' }}</strong> &bull; NIK: <strong class="text-white">{{ $m->nik ?? '-' }}</strong>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white align-self-start" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    <!-- 1. Kontak & Pekerjaan -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-chat-dots-fill text-primary me-2"></i>Kontak & Komunikasi</h6>
                            <div class="mb-2.5">
                                <label class="text-muted extra-small d-block fw-semibold">NOMOR HP / WHATSAPP</label>
                                @if(!empty($m->no_hp))
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $m->no_hp) }}" target="_blank" class="text-decoration-none fw-bold text-success d-inline-flex align-items-center gap-1.5 mt-0.5">
                                        <i class="bi bi-whatsapp fs-6"></i> {{ $m->no_hp }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                            <div class="mb-2.5">
                                <label class="text-muted extra-small d-block fw-semibold">EMAIL RESMI</label>
                                @if(!empty($m->email))
                                    <a href="mailto:{{ $m->email }}" class="text-decoration-none fw-semibold text-primary d-inline-flex align-items-center gap-1.5 mt-0.5">
                                        <i class="bi bi-envelope fs-6"></i> {{ $m->email }}
                                    </a>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </div>
                            <div>
                                <label class="text-muted extra-small d-block fw-semibold">PEKERJAAN UTAMA</label>
                                <span class="fw-bold text-slate-800">{{ $m->pekerjaan_clean }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Informasi Alamat Domisili -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-3 p-3 bg-white">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Alamat & Domisili</h6>
                            <div class="mb-2">
                                <label class="text-muted extra-small d-block fw-semibold">KABUPATEN / KOTA</label>
                                <span class="badge badge-kabupaten-kota mt-0.5">{{ $m->kabupaten_kota ?? 'Kabupaten Tasikmalaya' }}</span>
                            </div>
                            <div class="row g-2 mb-2">
                                <div class="col-6">
                                    <label class="text-muted extra-small d-block fw-semibold">KECAMATAN</label>
                                    <strong class="text-slate-800 small">{{ $m->kecamatan ?? '-' }}</strong>
                                </div>
                                <div class="col-6">
                                    <label class="text-muted extra-small d-block fw-semibold">DESA / KELURAHAN</label>
                                    <strong class="text-slate-800 small">{{ $m->desa ?? '-' }}</strong>
                                </div>
                            </div>
                            <div>
                                <label class="text-muted extra-small d-block fw-semibold">ALAMAT DETAIL</label>
                                <div class="small text-slate-700 bg-light p-2 rounded border mt-0.5" style="max-height: 60px; overflow-y: auto;">
                                    {{ $m->alamat_clean }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Rekam Jejak Sensus & Survei BPS -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-award-fill text-warning me-2"></i>Rekam Jejak Pengalaman Survei / Sensus BPS</h6>
                            <div class="d-flex gap-1.5 flex-wrap mt-2">
                                @if($m->exp_sp) <span class="badge" style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>SP (Sensus Penduduk)</span> @endif
                                @if($m->exp_st) <span class="badge" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>ST (Sensus Pertanian)</span> @endif
                                @if($m->exp_se) <span class="badge" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>SE (Sensus Ekonomi)</span> @endif
                                @if($m->exp_susenas) <span class="badge" style="background-color: #ede9fe; color: #6d28d9; border: 1px solid #ddd6fe; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>Survei Susenas</span> @endif
                                @if($m->exp_sakernas) <span class="badge" style="background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>Survei Sakernas</span> @endif
                                @if($m->exp_sbh) <span class="badge" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; font-size: 0.775rem; font-weight: 700; padding: 0.35rem 0.65rem;"><i class="bi bi-check-circle-fill me-1"></i>Survei Biaya Hidup (SBH)</span> @endif
                                @if(!$m->exp_sp && !$m->exp_st && !$m->exp_se && !$m->exp_susenas && !$m->exp_sakernas && !$m->exp_sbh)
                                    <span class="text-muted small fst-italic">Belum ada rekam jejak survei tercatat</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- 4. Biodata & Administrasi Lengkap -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white">
                            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-lines-fill text-info me-2"></i>Biodata & Administrasi Lengkap</h6>
                            <div class="row g-3 text-start small">
                                <div class="col-md-3 col-6">
                                    <span class="text-muted d-block extra-small">JENIS KELAMIN</span>
                                    <strong class="text-dark">{{ $m->jk === 'L' ? 'Laki-laki' : ($m->jk === 'P' ? 'Perempuan' : '-') }}</strong>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="text-muted d-block extra-small">PENDIDIKAN</span>
                                    <strong class="text-dark">{{ $m->pendidikan_clean }}</strong>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="text-muted d-block extra-small">NPWP</span>
                                    <strong class="text-dark">{{ $m->npwp ?? '-' }}</strong>
                                </div>
                                <div class="col-md-3 col-6">
                                    <span class="text-muted d-block extra-small">TANGGAL LAHIR</span>
                                    <strong class="text-dark">{{ $m->tanggal_lahir_clean }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer bg-white border-top p-3 d-flex justify-content-between">
                <a href="{{ route('mitra.index', ['search' => $m->nama]) }}" target="_blank" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-people-fill"></i> Buka di Data Mitra
                </a>
                <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection


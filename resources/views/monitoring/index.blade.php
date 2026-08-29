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

<!-- Filter Panel -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-2.5">
        <form method="GET" action="{{ route('monitoring.index') }}" id="monitoringFilterForm" class="row g-2 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label text-muted small fw-bold mb-1">CARI MITRA</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Ketik nama mitra..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-secondary w-100" title="Filter"><i class="bi bi-funnel-fill"></i></button>
                @if(request()->hasAny(['periode_id', 'bidang_id', 'kegiatan_id', 'search']))
                    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary" title="Reset Filter"><i class="bi bi-arrow-counterclockwise"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3 text-center" style="width: 45px;">No</th>
                        <th style="width: 280px; min-width: 260px;">Nama Mitra</th>
                        <th style="width: 120px;">Periode</th>
                        <th style="max-width: 320px;">Nama Kegiatan</th>
                        <th style="width: 130px;">Bidang / Tim</th>
                        <th class="text-end" style="width: 140px;">Nominal Honor</th>
                        <th class="text-end pe-3" style="width: 100px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alokasis as $i => $a)
                    @php
                        $sbmlKey = $a->mitra_id . '-' . $a->periode_id;
                        $sbmlWarn = $sbmlWarnings[$sbmlKey] ?? null;
                    @endphp
                    <tr class="{{ $sbmlWarn ? 'table-danger' : '' }}">
                        <td class="ps-3 text-muted fw-semibold">{{ $alokasis->firstItem() + $i }}</td>
                        <td>
                            <div class="fw-bold text-dark" style="white-space: normal; word-break: break-word;">{{ $a->mitra->nama ?? '-' }}</div>
                            <span class="text-muted small">{{ $a->mitra->pekerjaan ?? 'Mitra' }}</span>
                            @if($sbmlWarn)
                                <div class="mt-1">
                                    <span class="badge badge-soft-danger d-inline-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-exclamation-octagon-fill"></i> Total Rp {{ number_format($sbmlWarn['total'], 0, ',', '.') }} &gt; SBML (Rp {{ number_format($sbmlWarn['limit'], 0, ',', '.') }})
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-soft-primary"><i class="bi bi-calendar-event me-1"></i>{{ $a->periode->bulan ?? '' }} {{ $a->periode->tahun ?? '' }}</span>
                        </td>
                        <td>
                            <div class="mb-1">
                                @if($a->kegiatan && $a->kegiatan->isPengolahan())
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 extra-small fw-semibold" title="Tugas Pengolahan Data">
                                        <i class="bi bi-cpu-fill me-1"></i>Pengolahan
                                    </span>
                                @elseif($a->kegiatan)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 extra-small fw-semibold" title="Tugas Pendataan Lapangan">
                                        <i class="bi bi-geo-alt-fill me-1"></i>Pencacahan
                                    </span>
                                @endif
                            </div>
                            <div class="fw-semibold text-slate-800" style="white-space: normal; word-break: break-word;">{{ $a->kegiatan->nama ?? '-' }}</div>
                            @if($a->nomor_spk || $a->nomor_bast)
                                <div class="mt-1 d-flex align-items-center gap-1 flex-wrap">
                                    @if($a->nomor_spk)
                                        <span class="badge bg-light text-dark border extra-small fw-normal" title="Nomor SPK">
                                            <i class="bi bi-file-earmark-check-fill text-success me-1"></i>SPK: <strong>{{ $a->nomor_spk }}</strong>
                                        </span>
                                    @endif
                                    @if($a->nomor_bast)
                                        <span class="badge bg-light text-dark border extra-small fw-normal" title="Nomor BAST">
                                            <i class="bi bi-check-all text-info me-1"></i>BAST: {{ $a->nomor_bast }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-soft-info"><i class="bi bi-diagram-3 me-1"></i>{{ $a->kegiatan->bidang->nama ?? '-' }}</span>
                        </td>
                        <td class="text-end fw-extrabold fs-6 {{ $sbmlWarn ? 'text-danger' : 'text-success' }}">
                            Rp {{ number_format($a->nominal, 0, ',', '.') }}
                            @if($sbmlWarn)
                                <span class="d-block text-danger small fw-bold mt-0.5">Melebihi Rp {{ number_format($sbmlWarn['excess'], 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            <div class="d-inline-flex gap-1 justify-content-end">
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
                        <td colspan="7" class="text-center text-muted py-5">
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
    });
</script>
@endsection

@extends('layouts.app')
@section('content')

<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h2 class="page-title"><i class="bi bi-pencil-square text-primary me-2"></i>{{ isset($alokasi) ? 'Edit Alokasi Honor' : 'Tambah Alokasi Honor Baru' }}</h2>
        <p class="page-subtitle">{{ isset($alokasi) ? 'Perbarui nominal honor mitra' : 'Input alokasi honor pekerjaan mitra pada kegiatan tertentu' }}</p>
    </div>
    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left"></i> Kembali ke Monitoring
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="bi bi-wallet2 text-primary me-2"></i>Formulir Alokasi Honor</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ isset($alokasi) ? route('monitoring.update', $alokasi) : route('monitoring.store') }}">
                    @csrf
                    @if(isset($alokasi))
                        @method('PUT')
                    @endif

                    <!-- 1. Live Searchable Mitra -->
                    <div class="mb-4 position-relative">
                        <label for="mitra_search_input" class="form-label fw-bold">1. Pilih Mitra <span class="text-danger">*</span></label>
                        <input type="hidden" name="mitra_id" id="mitra_id" value="{{ old('mitra_id', $alokasi->mitra_id ?? '') }}" required>
                        
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-person-fill text-primary"></i></span>
                            <input type="text" id="mitra_search_input" class="form-control border-start-0 ps-0 @error('mitra_id') is-invalid @enderror" 
                                   placeholder="Ketik nama mitra untuk mencari..." 
                                   value="{{ old('mitra_name', isset($alokasi) ? ($alokasi->mitra->nama . ' (' . ($alokasi->mitra->pekerjaan ?? 'Mitra') . ')') : '') }}" 
                                   autocomplete="off" required>
                            <button type="button" class="btn btn-outline-secondary border d-none" id="clear_mitra_btn" title="Hapus Pilihan">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        @error('mitra_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                        <div id="mitra_dropdown_list" class="list-group shadow-lg rounded-3 position-absolute w-100 mt-1 d-none" 
                             style="max-height: 220px; overflow-y: auto; z-index: 1050; background: #ffffff; border: 1px solid #cbd5e1;">
                        </div>
                    </div>

                    <!-- 2. Urutan: Pilih Tahun & Pilih Bidang -->
                    <div class="row g-3 mb-4">
                        <!-- 2a. Live Search Tahun -->
                        <div class="col-12 col-md-5 position-relative">
                            <label for="tahun_search_input" class="form-label fw-bold">2. Pilih Tahun Anggaran <span class="text-danger">*</span></label>
                            <input type="hidden" id="tahun_selected_val" value="{{ old('tahun', $alokasi->periode->tahun ?? '') }}">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3 text-primary"></i></span>
                                <input type="text" id="tahun_search_input" class="form-control border-start-0 ps-0" 
                                       placeholder="Ketik/pilih tahun..." 
                                       value="{{ old('tahun', isset($alokasi->periode) ? 'Tahun ' . $alokasi->periode->tahun : '') }}" 
                                       autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary border d-none" id="clear_tahun_btn" title="Hapus Pilihan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div id="tahun_dropdown_list" class="list-group shadow-lg rounded-3 position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 200px; overflow-y: auto; z-index: 1050; background: #ffffff; border: 1px solid #cbd5e1;">
                            </div>
                        </div>

                        <!-- 2b. Live Search Bidang -->
                        <div class="col-12 col-md-7 position-relative">
                            <label for="bidang_search_input" class="form-label fw-bold">3. Pilih Bidang / Tim Kerja <span class="text-danger">*</span></label>
                            <input type="hidden" id="bidang_selected_val" value="{{ old('bidang_id', $alokasi->kegiatan->bidang_id ?? '') }}">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-building text-primary"></i></span>
                                <input type="text" id="bidang_search_input" class="form-control border-start-0 ps-0" 
                                       placeholder="Ketik/pilih bidang..." 
                                       value="{{ old('bidang_name', isset($alokasi->kegiatan->bidang) ? $alokasi->kegiatan->bidang->nama : '') }}" 
                                       autocomplete="off" required>
                                <button type="button" class="btn btn-outline-secondary border d-none" id="clear_bidang_btn" title="Hapus Pilihan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            <div id="bidang_dropdown_list" class="list-group shadow-lg rounded-3 position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 200px; overflow-y: auto; z-index: 1050; background: #ffffff; border: 1px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>

                    <!-- 3. Urutan: Pilih Kegiatan Statistik -> Lalu Pilih Bulan Kerja -->
                    <div class="row g-3 mb-4">
                        <!-- 3a. Live Search Kegiatan -->
                        <div class="col-12 col-md-7 position-relative">
                            <label for="kegiatan_search_input" class="form-label fw-bold">4. Pilih Kegiatan Statistik <span class="text-danger">*</span></label>
                            <input type="hidden" name="kegiatan_id" id="kegiatan_id" value="{{ old('kegiatan_id', $alokasi->kegiatan_id ?? '') }}" required>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-list-task text-primary"></i></span>
                                <input type="text" id="kegiatan_search_input" class="form-control border-start-0 ps-0 @error('kegiatan_id') is-invalid @enderror" 
                                       placeholder="Pilih bidang dahulu..." 
                                       value="{{ old('kegiatan_name', isset($alokasi->kegiatan) ? $alokasi->kegiatan->nama : '') }}" 
                                       autocomplete="off" required {{ isset($alokasi->kegiatan) ? '' : 'disabled' }}>
                                <button type="button" class="btn btn-outline-secondary border d-none" id="clear_kegiatan_btn" title="Hapus Pilihan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            @error('kegiatan_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            
                            <div id="kegiatan_dropdown_list" class="list-group shadow-lg rounded-3 position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 220px; overflow-y: auto; z-index: 1050; background: #ffffff; border: 1px solid #cbd5e1;">
                            </div>
                        </div>

                        <!-- 3b. Live Search Bulan Kerja (Hanya bulan kegiatan aktif) -->
                        <div class="col-12 col-md-5 position-relative">
                            <label for="bulan_search_input" class="form-label fw-bold">5. Pilih Bulan Kerja <span class="text-danger">*</span></label>
                            <input type="hidden" name="periode_id" id="periode_id" value="{{ old('periode_id', $alokasi->periode_id ?? '') }}" required>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-event text-primary"></i></span>
                                <input type="text" id="bulan_search_input" class="form-control border-start-0 ps-0 @error('periode_id') is-invalid @enderror" 
                                       placeholder="Pilih kegiatan dahulu..." 
                                       value="{{ old('periode_name', isset($alokasi->periode) ? $alokasi->periode->bulan : '') }}" 
                                       autocomplete="off" required {{ isset($alokasi->periode) ? '' : 'disabled' }}>
                                <button type="button" class="btn btn-outline-secondary border d-none" id="clear_bulan_btn" title="Hapus Pilihan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                            @error('periode_id') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            
                            <div id="bulan_dropdown_list" class="list-group shadow-lg rounded-3 position-absolute w-100 mt-1 d-none" 
                                 style="max-height: 220px; overflow-y: auto; z-index: 1050; background: #ffffff; border: 1px solid #cbd5e1;">
                            </div>
                        </div>
                    </div>

                    <!-- 4. Beban Tugas: Volume & Satuan -->
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label for="volume" class="form-label fw-bold">Volume Dikerjakan Mitra (Beban Tugas) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-stack text-primary"></i></span>
                                <input type="number" step="any" min="0.01" class="form-control @error('volume') is-invalid @enderror" id="volume" name="volume" value="{{ old('volume', $alokasi->volume ?? 1) }}" placeholder="Contoh: 15 / 1" required>
                            </div>
                            <div class="form-text">Jumlah volume beban tugas yang dikerjakan oleh mitra</div>
                            @error('volume') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="satuan" class="form-label fw-bold">Satuan Pekerjaan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('satuan') is-invalid @enderror" id="satuan" name="satuan" list="satuan_options" value="{{ old('satuan', $alokasi->satuan ?? ($alokasi->kegiatan->satuan ?? 'Dokumen')) }}" placeholder="Contoh: Dokumen / Rumah Tangga / Responden" required>
                            <datalist id="satuan_options">
                                <option value="Dokumen">
                                <option value="Rumah Tangga">
                                <option value="Responden">
                                <option value="Sampel">
                                <option value="Desa">
                                <option value="OB">
                                <option value="Blok Sensus">
                            </datalist>
                            <div class="form-text">Satuan hasil pekerjaan</div>
                            @error('satuan') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Info Tarif Kegiatan Aktif -->
                    <div id="kegiatan_tarif_info_box" class="alert alert-info py-2 px-3 mb-4 rounded-3 d-flex align-items-center justify-content-between {{ isset($alokasi->kegiatan) && $alokasi->kegiatan->harga > 0 ? '' : 'd-none' }}">
                        <div class="small">
                            <i class="bi bi-tag-fill text-primary me-1"></i>
                            <span class="text-muted">Harga Satuan Kegiatan:</span>
                            <strong class="text-dark ms-1" id="kegiatan_tarif_text">
                                Rp {{ number_format($alokasi->kegiatan->harga ?? 0, 0, ',', '.') }} / {{ $alokasi->kegiatan->satuan ?? 'Dokumen' }}
                            </strong>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 extra-small">Auto-Calculate Active</span>
                    </div>

                    <!-- 5. Nominal Honor (Otomatis: Volume Mitra x Harga Satuan) -->
                    <div class="mb-4">
                        <label for="nominal" class="form-label fw-bold">Total Nilai Perjanjian / Honor (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold bg-light text-primary">Rp</span>
                            <input type="number" step="0.01" class="form-control fw-bold fs-6 @error('nominal') is-invalid @enderror" id="nominal" name="nominal" value="{{ old('nominal', $alokasi->nominal ?? '') }}" placeholder="Contoh: 1500000" required>
                        </div>
                        <div class="form-text text-muted">
                            <i class="bi bi-calculator me-1"></i>Otomatis terhitung: <strong>Volume Dikerjakan Mitra &times; Harga Satuan Kegiatan</strong>. Anda tetap dapat menyesuaikan secara manual jika diperlukan.
                        </div>
                        @error('nominal') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <!-- 6. Nomor SPK, BAST & Tanggal -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-text text-primary me-2"></i>Dokumen SPK & BAST (Opsional)</h6>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <label for="nomor_spk" class="form-label fw-semibold small">Nomor SPK</label>
                                <input type="text" class="form-control @error('nomor_spk') is-invalid @enderror" id="nomor_spk" name="nomor_spk" value="{{ old('nomor_spk', $alokasi->nomor_spk ?? '') }}" placeholder="Contoh: B-0001/BPS/3206/SPK/01/2026">
                                @error('nomor_spk') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="nomor_bast" class="form-label fw-semibold small">Nomor BAST</label>
                                <input type="text" class="form-control @error('nomor_bast') is-invalid @enderror" id="nomor_bast" name="nomor_bast" value="{{ old('nomor_bast', $alokasi->nomor_bast ?? '') }}" placeholder="Contoh: B-0002/BPS/3206/BAST/01/2026">
                                @error('nomor_bast') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="tanggal_spk" class="form-label fw-semibold small">Tanggal SPK</label>
                                <input type="date" class="form-control @error('tanggal_spk') is-invalid @enderror" id="tanggal_spk" name="tanggal_spk" value="{{ old('tanggal_spk', $alokasi->tanggal_spk ?? '') }}">
                                @error('tanggal_spk') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-text text-muted"><i class="bi bi-info-circle me-1"></i>Isi nomor SPK/BAST jika sudah ada. Jika kosong, kolom akan tampil "Belum Bernomor" di monitoring.</div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('monitoring.index') }}" class="btn btn-light border">Batal</a>
                        <button type="submit" id="btnSubmitForm" class="btn btn-primary px-4 shadow-sm">
                            <i class="bi bi-save me-1"></i> {{ isset($alokasi) ? 'Perbarui Alokasi Honor' : 'Simpan Alokasi Honor' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Peringatan Honor Penuh & Pengalihan ke Mitra Lain -->
<div class="modal fade" id="sbmlWarningModal" tabindex="-1" aria-labelledby="sbmlWarningModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-warning bg-opacity-20 text-dark border-bottom border-warning py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="sbmlWarningModalLabel">
                    <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i> Peringatan: Honor Mitra Melebihi Batas SBML!
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0 rounded-3 mb-4 text-dark">
                    <div class="fw-bold fs-6 mb-1"><i class="bi bi-info-circle-fill text-warning me-1"></i> Sisa Kuota Honor Mitra Penuh</div>
                    <div>Alokasi honor untuk mitra <strong id="modal_mitra_name">-</strong> pada periode <strong id="modal_periode_name">-</strong> mencapai <span class="badge bg-danger text-white fs-6 px-2" id="modal_new_total">Rp 0</span>, yang mana **MELEBIHI** batas maksimal SBML (<strong id="modal_sbml_limit">Rp 4.500.000</strong>).</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill text-primary me-2"></i>Dialihkan ke Petugas / Mitra Lain (Rekomendasi Honor Masih Longgar):</h6>
                </div>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="bi bi-search text-primary"></i></span>
                    <input type="text" id="modal_search_alt_mitra" class="form-control" placeholder="Cari nama mitra alternatif di sini..." autocomplete="off">
                </div>

                <div id="modal_alternative_mitra_container" class="mb-4" style="max-height: 250px; overflow-y: auto;">
                    <div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm text-primary me-2"></i> Memuat daftar mitra alternatif...</div></div>
                </div>

                <div class="form-check bg-light p-3 rounded-3 border">
                    <input class="form-check-input" type="checkbox" id="check_bypass_override">
                    <label class="form-check-label text-dark fw-bold" for="check_bypass_override">
                        Tetap alokasikan ke <span id="modal_bypass_mitra_name">mitra semula</span> (dengan catatan peringatan SBML)
                    </label>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal / Perbaiki Input</button>
                <button type="button" id="btnConfirmReallocation" class="btn btn-success fw-bold px-4 shadow-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Dialihkan & Simpan Transaksi
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Master Data from Controller
    const mitrasData = {!! json_encode($mitras) !!};
    const periodesData = {!! json_encode($periodes) !!};
    const bidangsData = {!! json_encode($bidangs) !!};
    const tahunListData = {!! json_encode($tahunList) !!};

    // Generic Live Search Dropdown Component Builder
    function setupLiveSearchDropdown({ inputId, hiddenId, listId, clearBtnId, getItemsCallback, onSelectCallback }) {
        const input = document.getElementById(inputId);
        const hidden = hiddenId ? document.getElementById(hiddenId) : null;
        const list = document.getElementById(listId);
        const clearBtn = clearBtnId ? document.getElementById(clearBtnId) : null;

        if (input.value && clearBtn) {
            clearBtn.classList.remove('d-none');
        }

        function render(query = '') {
            list.innerHTML = '';
            const items = getItemsCallback(query);

            if (items.length === 0) {
                list.innerHTML = '<div class="list-group-item text-muted small py-3 text-center">Data tidak ditemukan</div>';
            } else {
                items.forEach(itemData => {
                    const item = document.createElement('a');
                    item.href = '#';
                    item.className = 'list-group-item list-group-item-action py-2 px-3 border-bottom';
                    item.innerHTML = itemData.html;
                    item.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (hidden) hidden.value = itemData.value;
                        input.value = itemData.label;
                        list.classList.add('d-none');
                        if (clearBtn) clearBtn.classList.remove('d-none');
                        if (onSelectCallback) onSelectCallback(itemData);
                    });
                    list.appendChild(item);
                });
            }
            list.classList.remove('d-none');
        }

        input.addEventListener('focus', function() {
            if (!input.disabled) {
                this.select(); // Highlight text so user can retype immediately
                render(this.value);
            }
        });

        input.addEventListener('click', function() {
            if (!input.disabled) render(this.value);
        });

        input.addEventListener('input', function() {
            if (hidden) hidden.value = '';
            if (clearBtn) {
                if (this.value) clearBtn.classList.remove('d-none');
                else clearBtn.classList.add('d-none');
            }
            render(this.value);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (hidden) hidden.value = '';
                input.value = '';
                clearBtn.classList.add('d-none');
                input.focus();
                render('');
                if (onSelectCallback) onSelectCallback(null);
            });
        }

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !list.contains(e.target) && (!clearBtn || !clearBtn.contains(e.target))) {
                list.classList.add('d-none');
            }
        });
    }

    // 1. LIVE SEARCH MITRA
    setupLiveSearchDropdown({
        inputId: 'mitra_search_input',
        hiddenId: 'mitra_id',
        listId: 'mitra_dropdown_list',
        clearBtnId: 'clear_mitra_btn',
        getItemsCallback: function(q) {
            const query = q.toLowerCase().trim();
            return mitrasData.filter(m => 
                m.nama.toLowerCase().includes(query) || 
                (m.pekerjaan && m.pekerjaan.toLowerCase().includes(query)) ||
                (m.alamat && m.alamat.toLowerCase().includes(query))
            ).slice(0, 35).map(m => ({
                value: m.id,
                label: `${m.nama} (${m.pekerjaan || 'Mitra'})`,
                html: `<div class="fw-bold text-dark mb-0">${m.nama}</div><div class="text-muted small">${m.pekerjaan || 'Mitra'} &bull; ${m.alamat || 'Tasikmalaya'}</div>`
            }));
        }
    });

    // 2. LIVE SEARCH TAHUN -> BIDANG -> KEGIATAN -> BULAN CASCADING
    const tahunInput = document.getElementById('tahun_search_input');
    const tahunHidden = document.getElementById('tahun_selected_val');
    const clearTahunBtn = document.getElementById('clear_tahun_btn');

    const bidangInput = document.getElementById('bidang_search_input');
    const bidangHidden = document.getElementById('bidang_selected_val');
    const clearBidangBtn = document.getElementById('clear_bidang_btn');

    const kegiatanInput = document.getElementById('kegiatan_search_input');
    const kegiatanHidden = document.getElementById('kegiatan_id');
    const clearKegiatanBtn = document.getElementById('clear_kegiatan_btn');

    const bulanInput = document.getElementById('bulan_search_input');
    const periodeHidden = document.getElementById('periode_id');
    const clearBulanBtn = document.getElementById('clear_bulan_btn');

    const volumeInput = document.getElementById('volume');
    const satuanInput = document.getElementById('satuan');
    const nominalInput = document.getElementById('nominal');
    const tarifInfoBox = document.getElementById('kegiatan_tarif_info_box');
    const tarifTextEl = document.getElementById('kegiatan_tarif_text');

    let selectedKegiatanObj = null;
    @if(isset($alokasi->kegiatan))
        selectedKegiatanObj = {!! json_encode($alokasi->kegiatan) !!};
    @endif

    function getAllowedMonths(kObj) {
        if (!kObj) return null;
        if (kObj.jadwal_bulan_list && Array.isArray(kObj.jadwal_bulan_list) && kObj.jadwal_bulan_list.length > 0) {
            return kObj.jadwal_bulan_list.map(m => parseInt(m));
        }
        if (kObj.tgl_mulai && kObj.tgl_selesai) {
            try {
                const sM = parseInt(kObj.tgl_mulai.split('-')[1]);
                const eM = parseInt(kObj.tgl_selesai.split('-')[1]);
                if (!isNaN(sM) && !isNaN(eM) && sM <= eM) {
                    const arr = [];
                    for (let m = sM; m <= eM; m++) arr.push(m);
                    return arr;
                }
            } catch(e) {}
        }
        return null;
    }

    function resetBulan() {
        periodeHidden.value = '';
        bulanInput.value = '';
        if (clearBulanBtn) clearBulanBtn.classList.add('d-none');
        bulanInput.disabled = true;
        bulanInput.placeholder = 'Pilih kegiatan dahulu...';
    }

    function resetKegiatan() {
        kegiatanHidden.value = '';
        kegiatanInput.value = '';
        selectedKegiatanObj = null;
        currentKegiatanHarga = 0;
        if (tarifInfoBox) tarifInfoBox.classList.add('d-none');
        if (clearKegiatanBtn) clearKegiatanBtn.classList.add('d-none');
        resetBulan();
    }

    // Step 2: PILIH TAHUN
    setupLiveSearchDropdown({
        inputId: 'tahun_search_input',
        hiddenId: 'tahun_selected_val',
        listId: 'tahun_dropdown_list',
        clearBtnId: 'clear_tahun_btn',
        getItemsCallback: function(q) {
            const query = q.toLowerCase().trim();
            return tahunListData.filter(t => `tahun ${t}`.toLowerCase().includes(query) || `${t}`.includes(query)).map(t => ({
                value: t,
                label: `Tahun ${t}`,
                html: `<div class="fw-bold text-dark py-1"><i class="bi bi-calendar3 text-primary me-2"></i>Tahun ${t}</div>`
            }));
        },
        onSelectCallback: function(selectedItem) {
            resetKegiatan();
            if (bidangHidden.value) {
                kegiatanInput.disabled = false;
                kegiatanInput.placeholder = 'Ketik nama kegiatan...';
            }
        }
    });

    // Step 3: PILIH BIDANG
    setupLiveSearchDropdown({
        inputId: 'bidang_search_input',
        hiddenId: 'bidang_selected_val',
        listId: 'bidang_dropdown_list',
        clearBtnId: 'clear_bidang_btn',
        getItemsCallback: function(q) {
            const query = q.toLowerCase().trim();
            return bidangsData.filter(b => b.nama.toLowerCase().includes(query)).map(b => ({
                value: b.id,
                label: b.nama,
                html: `<div class="fw-bold text-dark py-1"><i class="bi bi-building text-primary me-2"></i>${b.nama}</div>`
            }));
        },
        onSelectCallback: function(selectedItem) {
            resetKegiatan();
            if (selectedItem && selectedItem.value) {
                kegiatanInput.disabled = false;
                kegiatanInput.placeholder = 'Ketik nama kegiatan...';
                kegiatanInput.focus();
            } else {
                kegiatanInput.disabled = true;
                kegiatanInput.placeholder = 'Pilih bidang dahulu...';
            }
        }
    });

    let currentKegiatanHarga = {{ isset($alokasi->kegiatan) ? ((float)($alokasi->kegiatan->harga ?? 0)) : 0 }};
    let currentKegiatanSatuan = "{{ isset($alokasi->kegiatan) ? ($alokasi->kegiatan->satuan ?? 'Dokumen') : 'Dokumen' }}";

    function recalculateHonor() {
        if (currentKegiatanHarga > 0) {
            const vol = parseFloat(volumeInput.value) || 0;
            nominalInput.value = Math.round(vol * currentKegiatanHarga);
        }
    }

    if (volumeInput) {
        volumeInput.addEventListener('input', recalculateHonor);
    }

    // Step 4: PILIH KEGIATAN
    setupLiveSearchDropdown({
        inputId: 'kegiatan_search_input',
        hiddenId: 'kegiatan_id',
        listId: 'kegiatan_dropdown_list',
        clearBtnId: 'clear_kegiatan_btn',
        getItemsCallback: function(q) {
            const currentBidangId = bidangHidden.value;
            if (!currentBidangId) return [];
            const bidangObj = bidangsData.find(b => b.id == currentBidangId);
            if (!bidangObj || !bidangObj.kegiatans) return [];

            const query = q.toLowerCase().trim();
            const currentTahun = tahunHidden.value;

            return bidangObj.kegiatans.filter(k => {
                if (currentTahun && k.tahun && k.tahun != currentTahun) return false;
                return k.nama.toLowerCase().includes(query) || 
                    (k.kode_mata_anggaran && k.kode_mata_anggaran.toLowerCase().includes(query)) ||
                    (k.kode_mak && k.kode_mak.toLowerCase().includes(query));
            }).map(k => {
                const makText = k.kode_mata_anggaran || k.kode_mak || '';
                const hrgText = k.harga > 0 ? 'Tarif: Rp ' + new Intl.NumberFormat('id-ID').format(k.harga) + '/' + (k.satuan || 'dok') : '';
                return {
                    value: k.id,
                    label: k.nama,
                    rawKegiatan: k,
                    html: `<div class="fw-bold text-dark mb-0">${k.nama}</div><div class="d-flex gap-2 mt-1">${makText ? '<span class="badge bg-light text-dark border">MAK: ' + makText + '</span>' : ''}${hrgText ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle">' + hrgText + '</span>' : ''}</div>`
                };
            });
        },
        onSelectCallback: function(selectedItem) {
            if (selectedItem && selectedItem.rawKegiatan) {
                const kObj = selectedItem.rawKegiatan;
                selectedKegiatanObj = kObj;
                currentKegiatanHarga = parseFloat(kObj.harga) || 0;
                currentKegiatanSatuan = kObj.satuan || 'Dokumen';
                
                if (satuanInput) {
                    satuanInput.value = currentKegiatanSatuan;
                }

                // If year wasn't selected yet, auto-set from kegiatan
                const kegTahun = kObj.tahun || '2024';
                if (!tahunHidden.value) {
                    tahunHidden.value = kegTahun;
                    tahunInput.value = `Tahun ${kegTahun}`;
                    if (clearTahunBtn) clearTahunBtn.classList.remove('d-none');
                }

                // Unlock Bulan Input & Focus
                periodeHidden.value = '';
                bulanInput.value = '';
                if (clearBulanBtn) clearBulanBtn.classList.add('d-none');
                bulanInput.disabled = false;
                bulanInput.placeholder = kObj.jadwal_teks ? `Pilih bulan (${kObj.jadwal_teks})...` : 'Pilih bulan kerja...';
                bulanInput.focus();

                if (currentKegiatanHarga > 0) {
                    if (tarifTextEl) {
                        tarifTextEl.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentKegiatanHarga) + ' / ' + currentKegiatanSatuan;
                    }
                    if (tarifInfoBox) {
                        tarifInfoBox.classList.remove('d-none');
                    }
                    recalculateHonor();
                } else {
                    if (tarifInfoBox) {
                        tarifInfoBox.classList.add('d-none');
                    }
                }
            } else {
                resetKegiatan();
            }
        }
    });

    // Step 5: PILIH BULAN KERJA (Menyesuaikan otomatis dengan Kegiatan)
    setupLiveSearchDropdown({
        inputId: 'bulan_search_input',
        hiddenId: 'periode_id',
        listId: 'bulan_dropdown_list',
        clearBtnId: 'clear_bulan_btn',
        getItemsCallback: function(q) {
            const currentTahun = tahunHidden.value;
            if (!currentTahun) return [];
            const query = q.toLowerCase().trim();
            const allowed = getAllowedMonths(selectedKegiatanObj);

            return periodesData.filter(p => {
                if (p.tahun != currentTahun) return false;
                if (allowed && !allowed.includes(parseInt(p.bulan_angka))) return false;
                return p.bulan.toLowerCase().includes(query);
            }).map(p => ({
                value: p.id,
                label: p.bulan,
                html: `<div class="fw-bold text-dark py-1"><i class="bi bi-calendar-event text-primary me-2"></i>${p.bulan}</div>`
            }));
        }
    });

    if (bidangHidden.value) {
        kegiatanInput.disabled = false;
        kegiatanInput.placeholder = 'Ketik nama kegiatan...';
    }

    if (selectedKegiatanObj && tahunHidden.value) {
        bulanInput.disabled = false;
        bulanInput.placeholder = selectedKegiatanObj.jadwal_teks ? `Pilih bulan (${selectedKegiatanObj.jadwal_teks})...` : 'Pilih bulan kerja...';
    }

    // 4. LIVE SBML THRESHOLD CHECK & RE-ALLOCATION MODAL INTERCEPT
    const mainForm = document.querySelector('form[action*="monitoring"]');
    const sbmlModalEl = document.getElementById('sbmlWarningModal');
    const sbmlModal = sbmlModalEl ? new bootstrap.Modal(sbmlModalEl) : null;
    let allowFormSubmit = false;

    if (mainForm) {
        mainForm.addEventListener('submit', function(e) {
            if (allowFormSubmit) return true; // Proceed if user confirmed

            e.preventDefault();

            const mitraId = document.getElementById('mitra_id').value;
            const periodeId = document.getElementById('periode_id').value;
            const nominal = document.getElementById('nominal').value;
            const currentId = "{{ $alokasi->id ?? '' }}";

            if (!mitraId || !periodeId || !nominal) {
                allowFormSubmit = true;
                mainForm.submit();
                return;
            }

            // Fetch AJAX Limit Check
            fetch(`{{ route('monitoring.check-limit') }}?mitra_id=${mitraId}&periode_id=${periodeId}&nominal=${nominal}&current_id=${currentId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.exceeded) {
                        // Render Modal Details
                        document.getElementById('modal_mitra_name').textContent = data.mitra_name;
                        document.getElementById('modal_periode_name').textContent = data.periode_name;
                        document.getElementById('modal_new_total').textContent = data.formatted_new_total;
                        document.getElementById('modal_sbml_limit').textContent = data.formatted_limit;
                        document.getElementById('modal_bypass_mitra_name').textContent = data.mitra_name;
                        document.getElementById('check_bypass_override').checked = false;

                        // Save global modal mitras list for live searching
                        window.currentModalAvailableMitras = data.available_mitras || [];
                        const searchInput = document.getElementById('modal_search_alt_mitra');
                        if (searchInput) searchInput.value = '';

                        function renderModalList(query = '') {
                            const container = document.getElementById('modal_alternative_mitra_container');
                            const q = query.toLowerCase().trim();
                            const filtered = window.currentModalAvailableMitras.filter(m => 
                                m.nama.toLowerCase().includes(q) || 
                                (m.pekerjaan && m.pekerjaan.toLowerCase().includes(q))
                            );

                            if (filtered.length > 0) {
                                let html = '<div class="list-group gap-2">';
                                filtered.forEach((m, idx) => {
                                    const isChecked = idx === 0 ? 'checked' : '';
                                    html += `
                                        <label class="list-group-item list-group-item-action d-flex justify-content-between align-items-center rounded-3 border p-3 cursor-pointer">
                                            <div class="d-flex align-items-center gap-3">
                                                <input class="form-check-input flex-shrink-0 modal-alt-mitra-radio" type="radio" name="alt_mitra_radio" value="${m.id}" data-name="${m.nama} (${m.pekerjaan})" ${isChecked}>
                                                <div>
                                                    <div class="fw-bold text-dark mb-0">${m.nama}</div>
                                                    <span class="text-muted small"><i class="bi bi-briefcase me-1"></i>${m.pekerjaan}</span>
                                                </div>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">Honor Saat Ini: ${m.formatted_current}</span>
                                                <div class="text-muted extra-small mt-1">Sisa Kuota: <strong>${m.formatted_remaining}</strong></div>
                                            </div>
                                        </label>
                                    `;
                                });
                                html += '</div>';
                                container.innerHTML = html;
                            } else {
                                container.innerHTML = `
                                    <div class="alert alert-secondary text-center mb-0 py-3">
                                        <i class="bi bi-exclamation-circle me-1"></i> Tidak ada mitra alternatif "${query}" yang ditemukan.
                                    </div>
                                `;
                            }
                        }

                        renderModalList();

                        if (searchInput) {
                            searchInput.oninput = function() {
                                renderModalList(this.value);
                            };
                        }

                        sbmlModal.show();
                    } else {
                        // Submit Form directly if not exceeded
                        allowFormSubmit = true;
                        mainForm.submit();
                    }
                })
                .catch(err => {
                    console.error("Limit check error:", err);
                    allowFormSubmit = true;
                    mainForm.submit();
                });
        });
    }

    // Handle Confirm Re-allocation Button Click
    const btnConfirm = document.getElementById('btnConfirmReallocation');
    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            const bypassChecked = document.getElementById('check_bypass_override').checked;
            
            if (bypassChecked) {
                // Submit directly to original mitra
                allowFormSubmit = true;
                sbmlModal.hide();
                mainForm.submit();
            } else {
                // Get selected alternative mitra radio
                const selectedRadio = document.querySelector('input[name="alt_mitra_radio"]:checked');
                if (selectedRadio) {
                    const newMitraId = selectedRadio.value;
                    const newMitraName = selectedRadio.getAttribute('data-name');

                    // Update form values to new mitra!
                    document.getElementById('mitra_id').value = newMitraId;
                    document.getElementById('mitra_search_input').value = newMitraName;

                    allowFormSubmit = true;
                    sbmlModal.hide();
                    mainForm.submit();
                } else {
                    alert("Silakan pilih mitra alternatif atau centang opsi tetap alokasikan ke mitra semula.");
                }
            }
        });
    }
});
</script>
@endpush

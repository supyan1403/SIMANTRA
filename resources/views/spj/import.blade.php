@extends('layouts.app')
@section('content')

<div class="page-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
    <div>
        <h2 class="page-title"><i class="bi bi-cloud-upload text-info me-2"></i>Import SPJ (REKAP KUITANSI)</h2>
        <p class="page-subtitle">Upload dokumen REKAP KUITANSI PDF dari BPS untuk import data alokasi honor</p>
    </div>
    <a href="{{ route('monitoring.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Monitoring
    </a>
</div>

@if(!($preview ?? false))
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('spj.import.preview') }}" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-bold">FILE SPJ (PDF)</label>
                    <input type="file" name="file_spj" class="form-control @error('file_spj') is-invalid @enderror" accept=".pdf" required>
                    @error('file_spj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Format: REKAP KUITANSI PDF dari BPS. Maks 10MB.</small>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-info text-white w-100">
                        <i class="bi bi-eye me-1"></i> Preview Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@else
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-success text-white">
        <i class="bi bi-check-circle me-1"></i> Data Berhasil Di-parse - {{ count($parsed['items'] ?? []) }} PPL ditemukan
    </div>
    <div class="card-body p-4">
        @if(!empty($parsed['header']))
        <div class="mb-3">
            <small class="text-muted d-block">
                @foreach($parsed['header'] as $key => $val)
                <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $val }}<br>
                @endforeach
            </small>
        </div>
        @endif

        <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
            <table class="table table-bordered table-sm">
                <thead class="table-dark" style="position:sticky;top:0;">
                    <tr>
                        <th class="text-center" style="width:35px">No</th>
                        <th>Nama</th>
                        <th>Jabatan</th>
                        <th class="text-end">Biaya/Sat</th>
                        <th class="text-center">Vol</th>
                        <th class="text-end">Bruto</th>
                        <th class="text-end">Pajak</th>
                        <th class="text-end">Netto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($parsed['items'] as $item)
                    <tr>
                        <td class="text-center">{{ $item['no'] }}</td>
                        <td>{{ $item['nama'] }}</td>
                        <td>{{ $item['jabatan'] }}</td>
                        <td class="text-end">Rp {{ number_format($item['biaya_satuan'], 0, ',', '.') }}</td>
                        <td class="text-center">{{ $item['volume'] }}</td>
                        <td class="text-end">Rp {{ number_format($item['bruto'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item['pajak'], 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format($item['netto'], 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-warning fw-bold">
                        <td colspan="5" class="text-end">Jumlah</td>
                        <td class="text-end">Rp {{ number_format(collect($parsed['items'])->sum('bruto'), 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format(collect($parsed['items'])->sum('pajak'), 0, ',', '.') }}</td>
                        <td class="text-end">Rp {{ number_format(collect($parsed['items'])->sum('netto'), 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('spj.import.process') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">IMPORT KE KEGIATAN</label>
                    <input type="text" class="form-control mb-1" id="searchKegiatan" placeholder="Ketik untuk cari kegiatan..." autocomplete="off">
                    <select name="kegiatan_id" class="form-select" id="selectKegiatan" size="8" style="max-height:200px;overflow-y:auto" required>
                        @foreach($kegiatans as $k)
                        @php
                            $cleanNama = $k->nama;
                            $cleanNama = preg_replace('/\s*[Mm]aret/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Ss]eptember/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Aa]gustus/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Ff]ebruari/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Jj]anuari/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Aa]pril/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Mm]ei/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Jj]uni/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Jj]uli/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Oo]ktober/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Nn]ovember/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*[Dd]esember/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*\d{4}/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s*\(\s*\)/', '', $cleanNama);
                            $cleanNama = preg_replace('/\s+/', ' ', trim($cleanNama));
                        @endphp
                        <option value="{{ $k->id }}">{{ $cleanNama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">PERIODE</label>
                    <select name="periode_id" class="form-select" required>
                        <option value="">-- Pilih Periode --</option>
                        @foreach($periodes as $p)
                        <option value="{{ $p->id }}">{{ $p->bulan }} {{ $p->tahun }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-success flex-fill" onclick="return confirm('Import {{ count($parsed['items'] ?? []) }} PPL ke alokasi honor? Data yang sudah ada akan ditimpa.')">
                        <i class="bi bi-cloud-upload me-1"></i> Import Sekarang
                    </button>
                </div>
            </div>
            <div class="mt-2">
                <small class="text-muted"><i class="bi bi-info-circle me-1"></i> Mitra baru akan otomatis dibuat. Data alokasi yang sudah ada akan ditimpa.</small>
            </div>
        </form>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('searchKegiatan');
    var selectEl = document.getElementById('selectKegiatan');
    if (!searchInput || !selectEl) return;

    var allOptions = Array.from(selectEl.options).map(function(o) {
        return { el: o, text: o.text.toLowerCase() };
    });

    searchInput.addEventListener('input', function() {
        var q = this.value.toLowerCase();
        selectEl.innerHTML = '';
        allOptions.forEach(function(opt) {
            if (opt.text.includes(q)) {
                selectEl.appendChild(opt.el);
            }
        });
    });
});
</script>

@endsection

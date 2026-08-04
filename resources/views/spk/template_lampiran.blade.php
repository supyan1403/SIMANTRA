<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lampiran SPK - {{ $mitra->nama }} - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 20mm; margin: 10px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; }
        .lampiran-header { border-bottom: 2px solid #000; padding-bottom: 5px; margin-bottom: 15px; }
        .lampiran-title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .lampiran-sub { font-size: 10pt; text-align: center; margin-bottom: 0; }
        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 6px 8px; font-size: 10pt; }
        .ttd-box { margin-top: 40px; }
        @media print {
            body { background: white; }
            .page { width: 100%; min-height: auto; padding: 0; margin: 0; box-shadow: none; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-primary font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK LAMPIRAN SPK (PDF / PRINTER)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

<div class="page">
    <!-- HEADER LAMPIRAN -->
    <div class="lampiran-header text-center">
        <div class="lampiran-title">LAMPIRAN SURAT PERJANJIAN KERJA (SPK) MITRA STATISTIK</div>
        <div class="lampiran-sub">BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA - PERIODE {{ strtoupper($periodeLabel) }}</div>
    </div>

    <!-- PROFIL MITRA RINGKAS -->
    <table class="w-100 mb-3" style="font-size: 10pt;">
        <tr>
            <td style="width: 130px;"><strong>Nama Mitra</strong></td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
            <td style="width: 130px;"><strong>ID Sobat BPS</strong></td>
            <td style="width: 10px;">:</td>
            <td><strong>{{ $mitra->id_sobat ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td><strong>No. HP</strong></td>
            <td>:</td>
            <td>{{ $mitra->no_hp ?? '-' }}</td>
            <td><strong>Pekerjaan</strong></td>
            <td>:</td>
            <td>{{ $mitra->pekerjaan ?? 'Mitra BPS' }}</td>
        </tr>
    </table>

    <div class="fw-bold mb-2">RINCIAN ALOKASI KEGIATAN DAN HONORARIUM:</div>

    <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 40px;">NO</th>
                <th>MATA ANGGARAN / NAMA KEGIATAN</th>
                <th>BIDANG / TIM</th>
                <th>PERIODE / BULAN</th>
                <th class="text-end" style="width: 140px;">HONORARIUM (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->kegiatan->nama }}</div>
                        <div class="text-muted extra-small font-monospace">MAK: {{ $item->kegiatan->kode_mata_anggaran ?? '-' }}</div>
                    </td>
                    <td>{{ $item->kegiatan->bidang->nama ?? '-' }}</td>
                    <td class="text-center">{{ $item->periode->nama_bulan }} {{ $item->periode->tahun }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted">Tidak ada data alokasi honorarium.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="4" class="text-end">TOTAL HONORARIUM MITRA:</td>
                <td class="text-end text-success">Rp {{ number_format($totalHonor, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p class="extra-small text-muted mt-2">Catatan: Seluruh alokasi honorarium di atas telah diverifikasi dan mematuhi batas Standar Biaya Masukan Lainnya (SBML) BPS Kabupaten Tasikmalaya.</p>

    <!-- LEMBAR TTD LAMPIRAN -->
    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>Mitra Statistik BPS</p>
            <br><br><br>
            <p><strong><u>{{ strtoupper($mitra->nama) }}</u></strong><br>ID SOBAT: {{ $mitra->id_sobat ?? '-' }}</p>
        </div>
        <div class="col-6">
            <p>Pejabat Pembuat Komitmen (PPK)</p>
            <br><br><br>
            <p><strong><u>NENG SAFITRI, S.ST., M.Si</u></strong><br>NIP. 19850101 200801 2 001</p>
        </div>
    </div>
</div>

</body>
</html>

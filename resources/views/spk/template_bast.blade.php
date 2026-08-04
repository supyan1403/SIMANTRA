<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak BAST Multi-Petugas ({{ $batchList->count() }} Mitra) - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; page-break-after: always; }
        .bast-header { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 15px; }
        .bast-title { font-size: 13pt; font-weight: bold; text-align: center; text-decoration: underline; margin-top: 10px; margin-bottom: 2px; }
        .bast-nomor { text-align: center; font-size: 10.5pt; margin-bottom: 15px; }
        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 5px 8px; font-size: 10pt; }
        .ttd-box { margin-top: 35px; }
        @media print {
            body { background: white; }
            .page { width: 100%; min-height: auto; padding: 0; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-primary font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK BERITA ACARA SERAH TERIMA (BAST) MULTI-PETUGAS</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

@foreach($batchList as $b)
<div class="page">
    <!-- KOP BAST BPS -->
    <div class="bast-header text-center">
        <div class="fw-bold fs-5">BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
        <div class="small">Jl. Singaparna No. 123, Tasikmalaya - Jawa Barat | Telp: (0265) 123456</div>
    </div>

    <!-- JUDUL BAST -->
    <div class="bast-title">BERITA ACARA SERAH TERIMA (BAST) HASIL PEKERJAAN</div>
    <div class="bast-nomor">Nomor: {{ $b->nomor_dokumen }}</div>

    <p>Pada hari ini, <strong>{{ date('d') }}</strong> bulan <strong>{{ date('F') }}</strong> tahun <strong>{{ date('Y') }}</strong>, telah dilakukan Serah Terima Hasil Pekerjaan Kegiatan Statistik (Kategori {{ strtoupper($kategoriKegiatan) }}) antara:</p>

    <table class="ms-3 mb-2" style="font-size: 10.5pt;">
        <tr>
            <td style="width: 25px;">1.</td>
            <td style="width: 140px;"><strong>Nama Mitra</strong></td>
            <td>: <strong>{{ strtoupper($b->mitra->nama) }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td><strong>ID Sobat BPS</strong></td>
            <td>: <strong>{{ $b->mitra->id_sobat ?? '-' }}</strong></td>
        </tr>
        <tr>
            <td></td>
            <td><strong>No. HP</strong></td>
            <td>: {{ $b->mitra->no_hp ?? '-' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Sebagai Petugas Pelaksana Statistik yang menyerahkan hasil pekerjaan, selanjutnya disebut <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
    </table>

    <table class="ms-3 mb-3" style="font-size: 10.5pt;">
        <tr>
            <td style="width: 25px;">2.</td>
            <td style="width: 140px;"><strong>Nama PPK</strong></td>
            <td>: <strong>NENG SAFITRI, S.ST., M.Si</strong></td>
        </tr>
        <tr>
            <td></td>
            <td><strong>Jabatan</strong></td>
            <td>: Pejabat Pembuat Komitmen (PPK) BPS Kabupaten Tasikmalaya</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Sebagai Penanggung Jawab Kegiatan BPS yang menerima hasil pekerjaan, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p>PIHAK PERTAMA menyerahkan kepada PIHAK KEDUA dan PIHAK KEDUA menerima dari PIHAK PERTAMA hasil pekerjaan statistik untuk periode <strong>{{ $periodeLabel }}</strong> dengan rincian sebagai berikut:</p>

    <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 35px;">NO</th>
                <th>NAMA KEGIATAN STATISTIK</th>
                <th>BIDANG / TIM</th>
                <th>BULAN</th>
                <th class="text-end" style="width: 130px;">NILAI HONOR (RP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($b->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->kegiatan->nama }}</div>
                        <div class="text-muted extra-small font-monospace">KODE: {{ $item->kegiatan->kode_mata_anggaran ?? '-' }}</div>
                    </td>
                    <td>{{ $item->kegiatan->bidang->nama ?? '-' }}</td>
                    <td class="text-center">{{ $item->periode->nama_bulan }} {{ $item->periode->tahun }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="4" class="text-end">TOTAL VALUE HASIL PEKERJAAN:</td>
                <td class="text-end text-success">Rp {{ number_format($b->total_honor, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p>Hasil pekerjaan di atas telah diperiksa dan dinyatakan LENGKAP, SAMA DENGAN TARGET, serta MEMENUHI KUALITAS STANDAR BPS Kabupaten Tasikmalaya.</p>

    <!-- LEMBAR TTD BAST -->
    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>Yang Menyerahkan,<br><strong>PIHAK PERTAMA</strong></p>
            <br><br><br>
            <p><strong><u>{{ strtoupper($b->mitra->nama) }}</u></strong><br>ID SOBAT: {{ $b->mitra->id_sobat ?? '-' }}</p>
        </div>
        <div class="col-6">
            <p>Yang Menerima,<br><strong>PIHAK KEDUA</strong> (PPK)</p>
            <br><br><br>
            <p><strong><u>NENG SAFITRI, S.ST., M.Si</u></strong><br>NIP. 19850101 200801 2 001</p>
        </div>
    </div>
</div>
@endforeach

</body>
</html>

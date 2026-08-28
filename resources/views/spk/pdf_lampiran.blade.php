<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lampiran SPK - {{ $mitra->nama }}</title>
    <style>
        @page {
            size: 210mm 297mm; /* A4 Standar */
            margin: 20mm 20mm 20mm 20mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 10.5pt;
            line-height: 1.4;
            color: #000000;
            margin: 0;
            padding: 0;
        }
        .lampiran-header {
            border-bottom: 2px solid #000;
            padding-bottom: 4px;
            margin-bottom: 12px;
            text-align: center;
        }
        .lampiran-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .lampiran-sub {
            font-size: 9.5pt;
        }
        .profil-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 10pt;
        }
        .profil-table td {
            padding: 2px 0;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            margin-bottom: 12px;
        }
        .table-data th, .table-data td {
            border: 1px solid #000;
            padding: 5px 6px;
            font-size: 9.5pt;
        }
        .table-data th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
    </style>
</head>
<body>

<div class="lampiran-header">
    <div class="lampiran-title">LAMPIRAN SURAT PERJANJIAN KERJA (SPK) MITRA STATISTIK</div>
    <div class="lampiran-sub">BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA - PERIODE {{ strtoupper($periodeLabel) }}</div>
</div>

<table class="profil-table">
    <tr>
        <td style="width: 120px;"><strong>Nama Mitra</strong></td>
        <td style="width: 10px;">:</td>
        <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
        <td style="width: 120px;"><strong>ID Sobat BPS</strong></td>
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

<div style="font-weight: bold; margin-bottom: 4px; font-size: 10pt;">RINCIAN ALOKASI KEGIATAN DAN HONORARIUM:</div>

<table class="table-data">
    <thead>
        <tr>
            <th style="width: 30px;">No</th>
            <th>Nama Kegiatan Sensus / Survei</th>
            <th style="width: 110px;">Bidang / Tim</th>
            <th style="width: 90px;">Periode</th>
            <th style="width: 100px;">Honorarium</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $idx => $item)
            <tr>
                <td style="text-align: center;">{{ $idx + 1 }}</td>
                <td>{{ $item->kegiatan->nama }}</td>
                <td style="font-size: 8.5pt;">{{ $item->kegiatan->bidang->nama ?? '-' }}</td>
                <td style="text-align: center; font-size: 8.5pt;">{{ $item->periode->bulan }} {{ $item->periode->tahun }}</td>
                <td style="text-align: right; font-weight: bold;">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <td colspan="4" style="text-align: right;">TOTAL HONORARIUM:</td>
            <td style="text-align: right; color: #000;">Rp {{ number_format($totalHonor, 0, ',', '.') }}</td>
        </tr>
    </tfoot>
</table>

<table class="ttd-table">
    <tr>
        <td>
            <p><strong>PIHAK KEDUA,</strong></p>
            <div style="height: 55px;"></div>
            <p><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
        </td>
        <td>
            <p><strong>PIHAK PERTAMA,</strong></p>
            <div style="height: 55px;"></div>
            <p><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
        </td>
    </tr>
</table>

</body>
</html>

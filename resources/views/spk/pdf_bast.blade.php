<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>BAST - {{ $periodeLabel }}</title>
    <style>
        @page {
            size: 210mm 297mm; /* A4 Standar */
            margin: 22mm 22mm 20mm 25mm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000000;
            margin: 0;
            padding: 0;
        }
        .bast-header {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .bast-title {
            font-size: 11.5pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            line-height: 1.25;
        }
        .bast-nomor {
            text-align: center;
            font-size: 11pt;
            margin-top: 10px;
            margin-bottom: 18px;
        }
        p {
            text-align: justify;
            text-justify: inter-word;
            margin: 0 0 10px 0;
        }
        .party-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .party-table td {
            vertical-align: top;
            padding: 2px 0;
            font-size: 11pt;
        }
        ol.bast-list {
            padding-left: 25px;
            margin: 0 0 12px 0;
        }
        ol.bast-list li {
            margin-bottom: 4px;
            text-align: justify;
        }
        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

@foreach($batchList as $idx => $b)
@if($idx > 0)
    <div class="page-break"></div>
@endif

<div class="bast-header">
    <div class="bast-title">BERITA ACARA SERAH TERIMA PEKERJAAN</div>
    <div class="bast-title">PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }}</div>
    <div class="bast-title">PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="bast-nomor">Nomor: {{ $b->nomor_dokumen }}</div>
</div>

<p>Pada hari ini, bertempat di Kantor BPS Kabupaten Tasikmalaya dengan alamat Jalan R.A.A Kusumahsubrata Komplek Perkantoran Kertasari, Kabupaten Tasikmalaya, yang bertanda tangan di bawah ini:</p>

<table class="party-table">
    <tr>
        <td style="width: 20px;">1.</td>
        <td style="width: 175px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
        <td style="width: 10px;">:</td>
        <td>Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
    </tr>
    <tr><td colspan="4" style="height: 6px;"></td></tr>
    <tr>
        <td>2.</td>
        <td><strong>{{ strtoupper($b->mitra->nama) }}</strong></td>
        <td>:</td>
        <td>{{ $b->mitra->pekerjaan_clean }}, berkedudukan di {{ $b->mitra->alamat_clean }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
    </tr>
</table>

<p>Menyatakan bahwa:</p>
<ol class="bast-list">
    <li><strong>PIHAK KEDUA</strong> telah menyerahkan seluruh Hasil Pekerjaan Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} kepada <strong>PIHAK PERTAMA</strong> dengan rincian sebagaimana tertuang dalam Lampiran Perjanjian Kerja.</li>
    <li><strong>PIHAK PERTAMA</strong> telah menerima Hasil Pekerjaan tersebut dalam keadaan lengkap, baik, dan memenuhi standar mutu teknis pendataan BPS.</li>
    <li>Atas terselesaikannya pekerjaan tersebut, <strong>PIHAK KEDUA</strong> berhak menerima pembayaran honorarium sebesar <strong>Rp {{ number_format($b->total_honor, 0, ',', '.') }}</strong> sesuai ketentuan yang berlaku.</li>
</ol>

<p>Demikian Berita Acara Serah Terima Pekerjaan ini dibuat dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.</p>

<table class="ttd-table">
    <tr>
        <td>
            <p><strong>PIHAK KEDUA,</strong></p>
            <div style="height: 65px;"></div>
            <p><strong><u>{{ strtoupper($b->mitra->nama) }}</u></strong></p>
        </td>
        <td>
            <p><strong>PIHAK PERTAMA,</strong></p>
            <div style="height: 65px;"></div>
            <p><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
        </td>
    </tr>
</table>
@endforeach

</body>
</html>

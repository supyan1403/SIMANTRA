<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>SPK - {{ $mitra->nama }}</title>
    <style>
        @page {
            size: 215.9mm 330.2mm; /* F4 / Folio: 612pt x 936pt */
            margin: 18mm 18mm 18mm 25.4mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11.5pt;
            line-height: 1.15;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        .header-title-1 {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .header-title-2 {
            font-size: 12pt;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 0;
            line-height: 1.2;
        }

        .header-nomor {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin-top: 0;
            margin-bottom: 14pt;
            line-height: 1.2;
        }

        .pasal-title {
            font-weight: bold;
            text-align: center;
            font-size: 11.5pt;
            margin-top: 8pt;
            margin-bottom: 6pt;
            line-height: 1.2;
        }

        p {
            margin: 0 0 5pt 0;
            text-align: justify;
            text-justify: inter-word;
            font-size: 11.5pt;
            line-height: 1.15;
        }

        .party-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6pt;
        }

        .party-table td {
            vertical-align: top;
            padding: 0;
            font-size: 11.5pt;
            line-height: 1.15;
        }

        .page-break {
            page-break-before: always;
        }

        .ttd-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25pt;
        }

        .ttd-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 0;
            font-size: 11.5pt;
        }

        .materai-box {
            border: 1px dashed #666;
            padding: 6px 10px;
            font-size: 8pt;
            color: #555;
            margin: 0 auto 8px auto;
            width: 75px;
            text-align: center;
        }

        /* Tabel Lampiran Landscape 100% Identik File SPK */
        .lampiran-container {
            page-break-before: always;
        }

        .table-lampiran {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8pt;
            margin-bottom: 8pt;
        }

        .table-lampiran th, .table-lampiran td {
            border: 1px solid #000;
            padding: 4pt 5pt;
            font-size: 9pt;
            font-family: 'Times New Roman', Times, serif;
        }

        .table-lampiran th {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- ========================================== -->
<!-- HALAMAN 1 DARI 4: PEMBUKAAN & PASAL 1 - 4  -->
<!-- ========================================== -->
<div class="header-title-1">PERJANJIAN KERJA</div>
<div class="header-title-2">PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
<div class="header-nomor">NOMOR: {{ $nomorDokumen }}</div>

<p>{{ $tanggalInfo['full_text'] ?? 'Pada hari ini, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini::' }}</p>

<table class="party-table">
    <tr>
        <td style="width: 20pt;">1.</td>
        <td style="width: 175pt;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
        <td style="width: 12pt;">:</td>
        <td>Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
    </tr>
    <tr><td colspan="4" style="height: 5pt;"></td></tr>
    <tr>
        <td>2.</td>
        <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
        <td>:</td>
        <td>{{ $mitra->pekerjaan_clean }}, berkedudukan di {{ $mitra->alamat_clean }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
    </tr>
</table>

<p>bahwa <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> yang secara bersama-sama disebut <strong>PARA PIHAK</strong>, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:</p>

<div class="pasal-title">Pasal 1</div>
<p><strong>PIHAK PERTAMA</strong> memberikan pekerjaan kepada <strong>PIHAK KEDUA</strong> dan <strong>PIHAK KEDUA</strong> menerima pekerjaan dari <strong>PIHAK PERTAMA</strong> sebagai Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dengan lingkup pekerjaan yang ditetapkan oleh <strong>PIHAK PERTAMA</strong>.</p>

<div class="pasal-title">Pasal 2</div>
<p>Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian. Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dan ketentuan-ketentuan yang ditetapkan oleh <strong>PIHAK PERTAMA</strong>.</p>

<div class="pasal-title">Pasal 3</div>
<p>Jangka Waktu Perjanjian terhitung sejak tanggal {{ $periodeLabel }}.</p>

<div class="pasal-title">Pasal 4</div>
<p><strong>PIHAK KEDUA</strong> berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh <strong>PIHAK PERTAMA</strong> sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2, dengan menerapkan protokol kesehatan pencegahan Covid-19 yang berlaku di wilayah kerja masing-masing.</p>

<!-- ========================================== -->
<!-- HALAMAN 2 DARI 4: PASAL 5 - 10             -->
<!-- ========================================== -->
<div class="page-break"></div>

<div class="pasal-title">Pasal 5</div>
<p>(1) <strong>PIHAK KEDUA</strong> berhak untuk mendapatkan honorarium petugas dari <strong>PIHAK PERTAMA</strong> sebesar Rp. {{ number_format($totalHonor, 0, ',', '.') }} ({{ $terbilangHonor }}) untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, termasuk biaya pajak, bea materai, pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</p>
<p>(2) Selain mendapatkan honorarium sebagaimana dimaksud pada ayat (1), <strong>PIHAK KEDUA</strong> berhak mendapatkan asuransi petugas (khusus sensus) dari <strong>PIHAK PERTAMA</strong>.</p>
<p>(3) <strong>PIHAK KEDUA</strong> tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau terdapat tambahan waktu pelaksanaan pekerjaan lapangan.</p>

<div class="pasal-title">Pasal 6</div>
<p>(1) Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah <strong>PIHAK KEDUA</strong> menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada <strong>PIHAK PERTAMA</strong>.</p>
<p>(2) Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh <strong>PIHAK PERTAMA</strong> kepada <strong>PIHAK KEDUA</strong> sesuai dengan ketentuan peraturan perundang-undangan.</p>

<div class="pasal-title">Pasal 7</div>
<p>Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh <strong>PARA PIHAK</strong>.</p>

<div class="pasal-title">Pasal 8</div>
<p><strong>PIHAK PERTAMA</strong> dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal <strong>PIHAK KEDUA</strong> tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, termasuk dalam kondisi terindikasi terinfeksi virus Covid-19, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</p>

<div class="pasal-title">Pasal 9</div>
<p>(1) Apabila <strong>PIHAK KEDUA</strong> mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak menyelesaikan pekerjaan yang menjadi tanggung jawabnya, maka wajib membayar ganti rugi kepada <strong>PIHAK PERTAMA</strong> sebesar Rp. {{ number_format($totalHonor, 0, ',', '.') }},00 ({{ $terbilangHonor }}).</p>
<p>(2) Dikecualikan tidak membayar ganti rugi sebagaimana dimaksud pada ayat (1) kepada <strong>PIHAK PERTAMA</strong>, apabila <strong>PIHAK KEDUA</strong> meninggal dunia, mengundurkan diri karena sakit dengan keterangan rawat inap, terindikasi terinfeksi virus Covid-19, kecelakaan dengan keterangan kepolisian, dan/atau telah diberikan Surat Pemutusan Perjanjian Kerja dari <strong>PIHAK PERTAMA</strong>.</p>
<p>(3) Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (2), <strong>PIHAK PERTAMA</strong> membayarkan honorarium kepada <strong>PIHAK KEDUA</strong> secara proporsional sesuai pekerjaan yang telah dilaksanakan.</p>

<!-- ========================================== -->
<!-- HALAMAN 3 DARI 4: PASAL 10 - 12 & TTD      -->
<!-- ========================================== -->
<div class="page-break"></div>

<div class="pasal-title">Pasal 10</div>
<p>(1) Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, <strong>PIHAK KEDUA</strong> memberitahukan kepada <strong>PIHAK PERTAMA</strong> dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.</p>
<p>(2) Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh <strong>PIHAK KEDUA</strong> dihentikan sementara dan dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan, <strong>PIHAK KEDUA</strong> berhak menerima honorarium secara proporsional sesuai pekerjaan yang telah dilaksanakan.</p>

<div class="pasal-title">Pasal 11</div>
<p>Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian tambahan/addendum dan merupakan bagian tidak terpisahkan dari perjanjian ini.</p>

<div class="pasal-title">Pasal 12</div>
<p>(1) Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat.</p>
<p>(2) Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), <strong>PARA PIHAK</strong> sepakat menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kabupaten Tasikmalaya.</p>

<p style="margin-top: 12pt;">Demikian Perjanjian ini dibuat dan ditandatangani oleh <strong>PARA PIHAK</strong> dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari <strong>PIHAK</strong> manapun dan untuk dilaksanakan oleh <strong>PARA PIHAK</strong>.</p>

<table class="ttd-table" style="margin-top: 35pt;">
    <tr>
        <td>
            <p><strong>PIHAK KEDUA,</strong></p>
            <div style="height: 12pt;"></div>
            <div class="materai-box">Materai<br>10.000</div>
            <div style="height: 12pt;"></div>
            <p><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
        </td>
        <td>
            <p><strong>PIHAK PERTAMA,</strong></p>
            <div style="height: 85pt;"></div>
            <p><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
        </td>
    </tr>
</table>

<!-- ========================================== -->
<!-- HALAMAN 4 DARI 4: LAMPIRAN IDENTIK DOCX    -->
<!-- ========================================== -->
<div class="lampiran-container">
    <div style="font-size: 13pt; font-family: 'Times New Roman', Times, serif; margin-bottom: 6pt;">Lampiran</div>
    <div class="header-title-2" style="font-size: 11pt; text-align: justify; font-weight: normal; margin-bottom: 0;">PERJANJIAN KERJA PETUGAS PENCACAHAN/PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="header-nomor" style="font-size: 11pt; text-align: justify; font-weight: normal; margin-bottom: 6pt;">NOMOR: {{ $nomorDokumen }}</div>
    <div style="text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 8pt;">DAFTAR URAIAN TUGAS, JANGKA WAKTU, NILAI PERJANJIAN, DAN BEBAN ANGGARAN</div>

    <table class="table-lampiran">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25pt;">No</th>
                <th rowspan="2">Uraian Tugas</th>
                <th rowspan="2" style="width: 85pt;">Target Pekerjaan<br><span style="font-weight: normal;">Jangka Waktu</span></th>
                <th colspan="2">Target Pekerjaan</th>
                <th rowspan="2" style="width: 75pt;">Harga Satuan</th>
                <th rowspan="2" style="width: 80pt;">Nilai Perjanjian</th>
                <th rowspan="2" style="width: 110pt;">Beban Anggaran</th>
            </tr>
            <tr>
                <th style="width: 40pt;">Volume</th>
                <th style="width: 45pt;">Satuan</th>
            </tr>
            <tr style="font-size: 7.5pt;">
                <th>(1)</th>
                <th>(2)</th>
                <th>(3)</th>
                <th>(3)</th>
                <th>(4)</th>
                <th>(5)</th>
                <th>(6)</th>
                <th>(7)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
                @php
                    $rentangBulan = \App\Http\Controllers\SpkController::formatRentangTanggalBulan($item->periode->bulan_angka, $item->periode->tahun);
                    $vol = (float)($item->volume ?? 1);
                    $sat = $item->satuan ?? 'dokumen';
                    $hargaSat = ($vol > 0) ? ($item->nominal / $vol) : $item->nominal;
                @endphp
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $item->kegiatan->nama }}</td>
                    <td style="text-align: center;">{{ $rentangBulan }}</td>
                    <td style="text-align: center;">{{ $vol }}</td>
                    <td style="text-align: center;">{{ $sat }}</td>
                    <td style="text-align: right;">Rp. {{ number_format($hargaSat, 0, ',', '.') }},00</td>
                    <td style="text-align: right;">Rp. {{ number_format($item->nominal, 0, ',', '.') }}, 00</td>
                    <td style="font-size: 7pt; text-align: center;">{{ $item->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903.BMA.009.005.A.521213' }}</td>
                </tr>
            @endforeach
            @for($i = count($items) + 1; $i <= 8; $i++)
                <tr>
                    <td style="text-align: center;">{{ $i }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td style="text-align: right;">Rp. 00</td>
                    <td style="text-align: right;">Rp. 00</td>
                    <td></td>
                </tr>
            @endfor
            <tr>
                <td colspan="6" style="font-weight: normal; font-size: 9pt;">
                    Terbilang: {{ $terbilangHonor }}
                </td>
                <td style="text-align: right; font-weight: normal; font-size: 9pt;">
                    Rp. {{ number_format($totalHonor, 0, ',', '.') }}, 00
                </td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <table class="ttd-table" style="margin-top: 25pt;">
        <tr>
            <td>
                <p><strong>PIHAK KEDUA,</strong></p>
                <div style="height: 50pt;"></div>
                <p><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
            </td>
            <td>
                <p><strong>PIHAK PERTAMA,</strong></p>
                <div style="height: 50pt;"></div>
                <p><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Presisi - {{ $mitra->nama }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: 215.9mm 330.2mm; margin: 0; }
        @page landscapePage { size: 330.2mm 215.9mm; margin: 0; }
        
        body { font-family: 'Bookman Old Style', 'Book Antiqua', Georgia, serif; font-size: 11pt; line-height: 1.45; color: #000; background: #f8fafc; margin: 0; padding: 0; }
        
        .page-portrait { width: 215.9mm; min-height: 330.2mm; padding: 18mm 17mm 18mm 25.4mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; box-sizing: border-box; page-break-after: always; position: relative; }
        .page-landscape { page: landscapePage; width: 330.2mm; min-height: 215.9mm; padding: 12.5mm 25.4mm 17mm 25.4mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; box-sizing: border-box; page-break-after: always; position: relative; font-size: 11pt; }
        
        .spk-title { font-size: 11.5pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .spk-nomor { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 14px; }
        .pasal-title { font-weight: bold; text-align: center; margin-top: 10px; margin-bottom: 3px; }
        
        p { margin-bottom: 8px; text-align: justify; font-weight: normal; }
        ol, ul { margin-bottom: 8px; font-weight: normal; }
        li { margin-bottom: 4px; text-align: justify; font-weight: normal; }

        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 4px 6px; font-size: 10pt; }
        
        .ttd-box { margin-top: 25px; }
        
        .ttd-table { width: 100%; border: none !important; margin-top: 30px; border-collapse: collapse; }
        .ttd-table td { width: 50%; text-align: center !important; border: none !important; padding: 0 !important; vertical-align: top; }
        .ttd-table p { text-align: center !important; margin: 0 0 8px 0 !important; font-weight: normal; }
        
        @media print {
            body { background: white; }
            .page-portrait { width: 215.9mm; min-height: 330.2mm; padding: 18mm 17mm 18mm 25.4mm; margin: 0; box-shadow: none; page-break-after: always; }
            .page-landscape { width: 330.2mm; min-height: 215.9mm; padding: 12.5mm 25.4mm 17mm 25.4mm; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-danger font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK SPK (TEKS NORMAL RAPI, HANYA ISTILAH/PASAL/NOMINAL BOLD)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

<!-- ========================================== -->
<!-- HALAMAN 1 DARI 4 (PORTRAIT)                 -->
<!-- ========================================== -->
<div class="page-portrait">
    <div class="spk-title">PERJANJIAN KERJA PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <p class="mb-2">Pada hari ini, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini:</p>

    <table class="ms-2 mb-3" style="width: 100%; font-size: 11pt;">
        <tr valign="top">
            <td style="width: 25px;">1.</td>
            <td style="width: 175px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
            <td>: Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr valign="top">
            <td>2.</td>
            <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
            <td>: {{ $mitra->pekerjaan_clean }}, berkedudukan di {{ $mitra->alamat_clean }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p class="mb-3">bahwa <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> yang secara bersama-sama disebut <strong>PARA PIHAK</strong>, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:</p>

    <div class="pasal-title">Pasal 1</div>
    <p><strong>PIHAK PERTAMA</strong> memberikan pekerjaan kepada <strong>PIHAK KEDUA</strong> dan <strong>PIHAK KEDUA</strong> menerima pekerjaan dari <strong>PIHAK PERTAMA</strong> sebagai Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dengan lingkup pekerjaan yang ditetapkan oleh <strong>PIHAK PERTAMA</strong>.</p>

    <div class="pasal-title">Pasal 2</div>
    <p>Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian. Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dan ketentuan-ketentuan yang ditetapkan oleh <strong>PIHAK PERTAMA</strong>.</p>

    <div class="pasal-title">Pasal 3</div>
    <p>Jangka Waktu Perjanjian terhitung sejak periode <strong>{{ $periodeLabel }}</strong>.</p>

    <div class="pasal-title">Pasal 4</div>
    <p><strong>PIHAK KEDUA</strong> berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh <strong>PIHAK PERTAMA</strong> sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2, dengan menerapkan protokol kesehatan pencegahan Covid-19 yang berlaku di wilayah kerja masing-masing.</p>
</div>

<!-- ========================================== -->
<!-- HALAMAN 2 DARI 4 (PORTRAIT)                 -->
<!-- ========================================== -->
<div class="page-portrait">
    <div class="pasal-title mt-0">Pasal 5</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2"><strong>PIHAK KEDUA</strong> berhak untuk mendapatkan honorarium petugas dari <strong>PIHAK PERTAMA</strong> sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong> untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, termasuk biaya pajak, bea materai, pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</li>
        <li class="mb-2">Selain mendapatkan honorarium sebagaimana dimaksud pada ayat (1), <strong>PIHAK KEDUA</strong> berhak mendapatkan asuransi petugas (khusus sensus) dari <strong>PIHAK PERTAMA</strong>.</li>
        <li><strong>PIHAK KEDUA</strong> tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau terdapat tambahan waktu pelaksanaan pekerjaan lapangan.</li>
    </ol>

    <div class="pasal-title">Pasal 6</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah <strong>PIHAK KEDUA</strong> menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada <strong>PIHAK PERTAMA</strong>.</li>
        <li>Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh <strong>PIHAK PERTAMA</strong> kepada <strong>PIHAK KEDUA</strong> sesuai dengan ketentuan peraturan perundang-undangan.</li>
    </ol>

    <div class="pasal-title">Pasal 7</div>
    <p>Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh <strong>PARA PIHAK</strong>.</p>

    <div class="pasal-title">Pasal 8</div>
    <p><strong>PIHAK PERTAMA</strong> dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal <strong>PIHAK KEDUA</strong> tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, termasuk dalam kondisi terindikasi terinfeksi virus Covid-19, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</p>

    <div class="pasal-title">Pasal 9</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Apabila <strong>PIHAK KEDUA</strong> mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak menyelesaikan pekerjaan yang menjadi tanggung jawabnya, maka wajib membayar ganti rugi kepada <strong>PIHAK PERTAMA</strong> sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong>.</li>
        <li class="mb-2">Dikecualikan tidak membayar ganti rugi sebagaimana dimaksud pada ayat (1) kepada <strong>PIHAK PERTAMA</strong>, apabila <strong>PIHAK KEDUA</strong> meninggal dunia, mengundurkan diri karena sakit dengan keterangan rawat inap, terindikasi terinfeksi virus Covid-19, kecelakaan dengan keterangan kepolisian, dan/atau telah diberikan Surat Pemutusan Perjanjian Kerja dari <strong>PIHAK PERTAMA</strong>.</li>
        <li>Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (2), <strong>PIHAK PERTAMA</strong> membayarkan honorarium kepada <strong>PIHAK KEDUA</strong> secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
    </ol>

    <div class="pasal-title">Pasal 10</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, <strong>PIHAK KEDUA</strong> memberitahukan kepada <strong>PIHAK PERTAMA</strong> dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.</li>
        <li>Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh <strong>PIHAK KEDUA</strong> dihentikan sementara dan dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan, <strong>PIHAK KEDUA</strong> berhak menerima honorarium secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
    </ol>
</div>

<!-- ========================================== -->
<!-- HALAMAN 3 DARI 4 (PORTRAIT)                 -->
<!-- ========================================== -->
<div class="page-portrait">
    <div class="pasal-title mt-0">Pasal 11</div>
    <p>Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian tambahan/<em>addendum</em> dan merupakan bagian tidak terpisahkan dari perjanjian ini.</p>

    <div class="pasal-title">Pasal 12</div>
    <ol class="ps-3 mb-3">
        <li class="mb-2">Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat.</li>
        <li>Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), <strong>PARA PIHAK</strong> sepakat menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kabupaten Tasikmalaya.</li>
    </ol>

    <p class="mb-4">Demikian Perjanjian ini dibuat dan ditandatangani oleh <strong>PARA PIHAK</strong> dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari <strong>PIHAK</strong> manapun dan untuk dilaksanakan oleh <strong>PARA PIHAK</strong>.</p>

    <!-- LEMBAR TTD PASAL HALAMAN 3 -->
    <table class="ttd-table">
        <tr valign="top">
            <td>
                <p><strong>PIHAK KEDUA,</strong></p>
                <div style="border: 1px dashed #777; width: 80px; height: 48px; margin: 0 auto 15px auto; font-size: 8pt; display: flex; align-items: center; justify-content: center; color: #555; text-align: center;">Materai<br>10.000</div>
                <p><u>{{ strtoupper($mitra->nama) }}</u></p>
            </td>
            <td>
                <p><strong>PIHAK PERTAMA,</strong></p>
                <div style="height: 73px;"></div>
                <p><u>Dindin Muldiana, S.ST. MP.</u></p>
            </td>
        </tr>
    </table>
</div>

<!-- ========================================== -->
<!-- HALAMAN 4 DARI 4 (LANDSCAPE MENDATAR)       -->
<!-- ========================================== -->
<div class="page-landscape">
    <div class="text-end extra-small text-muted mb-1">Lampiran: {{ strtoupper($mitra->nama) }}</div>
    <div class="spk-title" style="font-size: 11pt;">PERJANJIAN KERJA PETUGAS PENCACAHAN/PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor" style="font-size: 10.5pt;">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <div class="fw-bold mb-2 small text-center">DAFTAR URAIAN TUGAS, JANGKA WAKTU, NILAI PERJANJIAN, DAN BEBAN ANGGARAN</div>

    <table class="table table-bordered align-middle text-center mb-3">
        <thead class="table-light">
            <tr>
                <th rowspan="2" style="width: 35px;">No</th>
                <th rowspan="2">Uraian Tugas</th>
                <th rowspan="2" style="width: 140px;">Jangka Waktu</th>
                <th colspan="2">Target Pekerjaan</th>
                <th rowspan="2" style="width: 130px;">Harga Satuan</th>
                <th rowspan="2" style="width: 140px;">Nilai Perjanjian</th>
                <th rowspan="2" style="width: 250px;">Beban Anggaran (MAK)</th>
            </tr>
            <tr>
                <th style="width: 60px;">Volume</th>
                <th style="width: 70px;">Satuan</th>
            </tr>
            <tr style="font-size: 8.5pt; background: #f1f5f9;">
                <td>(1)</td>
                <td>(2)</td>
                <td>(3)</td>
                <td>(4)</td>
                <td>(4)</td>
                <td>(5)</td>
                <td>(6)</td>
                <td>(7)</td>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-start fw-bold">{{ $item->kegiatan->nama }}</td>
                    <td>{{ $periodeLabel }}</td>
                    <td>1</td>
                    <td>Dokumen</td>
                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td class="font-monospace extra-small">{{ $item->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903.BMA.009.005.A.521213' }}</td>
                </tr>
            @endforeach
            {{-- Fill empty rows up to 8 rows matching DOCX template --}}
            @for($i = count($items) + 1; $i <= 8; $i++)
                <tr>
                    <td>{{ $i }}</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td class="text-end">Rp 0</td>
                    <td class="text-end">Rp 0</td>
                    <td></td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="6" class="text-end">TOTAL NILAI PERJANJIAN:</td>
                <td class="text-end text-success">Rp {{ number_format($totalHonor, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <table class="ttd-table" style="margin-top: 20px;">
        <tr valign="top">
            <td>
                <p><strong>PIHAK KEDUA,</strong></p>
                <div style="height: 60px;"></div>
                <p><u>{{ strtoupper($mitra->nama) }}</u></p>
            </td>
            <td>
                <p><strong>PIHAK PERTAMA,</strong></p>
                <div style="height: 60px;"></div>
                <p><u>Dindin Muldiana, S.ST. MP.</u></p>
            </td>
        </tr>
    </table>
</div>

</body>
</html>

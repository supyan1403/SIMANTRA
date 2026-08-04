<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK 4-Halaman - {{ $mitra->nama }} - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @page { size: A4; margin: 0; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.45; color: #000; background: #f8fafc; margin: 0; padding: 0; }
        .page { width: 210mm; height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; box-sizing: border-box; page-break-after: always; position: relative; overflow: hidden; }
        .spk-title { font-size: 11.5pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .spk-nomor { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 18px; }
        .pasal-title { font-weight: bold; text-align: center; margin-top: 14px; margin-bottom: 4px; }
        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 4px 6px; font-size: 9.5pt; }
        .ttd-container { position: absolute; bottom: 25mm; left: 25mm; right: 20mm; }
        .page-footer-num { position: absolute; bottom: 10mm; right: 20mm; font-size: 9pt; color: #555; }
        @media print {
            body { background: white; }
            .page { width: 100%; height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-danger font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK SPK RESMI BPS (4 HALAMAN PRESISI)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

<!-- ========================================== -->
<!-- HALAMAN 1 DARI 4: JUDUL, PIHAK 1&2, PASAL 1-4 -->
<!-- ========================================== -->
<div class="page">
    <div class="spk-title">PERJANJIAN KERJA PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <p class="mb-2">Pada hari ini, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini:</p>

    <table class="ms-2 mb-3" style="width: 100%;">
        <tr valign="top">
            <td style="width: 25px;">1.</td>
            <td style="width: 170px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
            <td>: Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr valign="top">
            <td>2.</td>
            <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
            <td>: {{ $mitra->pekerjaan ?? 'Lainnya/ Belum Bekerja' }}, berkedudukan di {{ $mitra->alamat ?? 'Kabupaten Tasikmalaya' }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p class="mb-3">bahwa <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> yang secara bersama-sama disebut <strong>PARA PIHAK</strong>, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:</p>

    <div class="pasal-title">Pasal 1</div>
    <p>PIHAK PERTAMA memberikan pekerjaan kepada PIHAK KEDUA dan PIHAK KEDUA menerima pekerjaan dari PIHAK PERTAMA sebagai Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dengan lingkup pekerjaan yang ditetapkan oleh PIHAK PERTAMA.</p>

    <div class="pasal-title">Pasal 2</div>
    <p>Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian. Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dan ketentuan-ketentuan yang ditetapkan oleh PIHAK PERTAMA.</p>

    <div class="pasal-title">Pasal 3</div>
    <p>Jangka Waktu Perjanjian terhitung sejak periode <strong>{{ $periodeLabel }}</strong>.</p>

    <div class="pasal-title">Pasal 4</div>
    <p>PIHAK KEDUA berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh PIHAK PERTAMA sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2, dengan menerapkan protokol kesehatan pencegahan Covid-19 yang berlaku di wilayah kerja masing-masing.</p>

    <div class="page-footer-num">Halaman 1 dari 4</div>
</div>

<!-- ========================================== -->
<!-- HALAMAN 2 DARI 4: PASAL 5 S.D. PASAL 10     -->
<!-- ========================================== -->
<div class="page">
    <div class="pasal-title mt-0">Pasal 5</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">PIHAK KEDUA berhak untuk mendapatkan honorarium petugas dari PIHAK PERTAMA sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong> untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, termasuk biaya pajak, bea materai, pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</li>
        <li class="mb-2">Selain mendapatkan honorarium sebagaimana dimaksud pada ayat (1), PIHAK KEDUA berhak mendapatkan asuransi petugas (khusus sensus) dari PIHAK PERTAMA.</li>
        <li>PIHAK KEDUA tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau terdapat tambahan waktu pelaksanaan pekerjaan lapangan.</li>
    </ol>

    <div class="pasal-title">Pasal 6</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah PIHAK KEDUA menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada PIHAK PERTAMA.</li>
        <li>Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh PIHAK PERTAMA kepada PIHAK KEDUA sesuai dengan ketentuan peraturan perundang-undangan.</li>
    </ol>

    <div class="pasal-title">Pasal 7</div>
    <p>Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh PARA PIHAK.</p>

    <div class="pasal-title">Pasal 8</div>
    <p>PIHAK PERTAMA dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal PIHAK KEDUA tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, termasuk dalam kondisi terindikasi terinfeksi virus Covid-19, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</p>

    <div class="pasal-title">Pasal 9</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Apabila PIHAK KEDUA mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak menyelesaikan pekerjaan yang menjadi tanggung jawabnya, maka wajib membayar ganti rugi kepada PIHAK PERTAMA sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong>.</li>
        <li class="mb-2">Dikecualikan tidak membayar ganti rugi sebagaimana dimaksud pada ayat (1) kepada PIHAK PERTAMA, apabila PIHAK KEDUA meninggal dunia, mengundurkan diri karena sakit dengan keterangan rawat inap, terindikasi terinfeksi virus Covid-19, kecelakaan dengan keterangan kepolisian, dan/atau telah diberikan Surat Pemutusan Perjanjian Kerja dari PIHAK PERTAMA.</li>
        <li>Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (2), PIHAK PERTAMA membayarkan honorarium kepada PIHAK KEDUA secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
    </ol>

    <div class="pasal-title">Pasal 10</div>
    <ol class="ps-3 mb-2">
        <li class="mb-2">Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, PIHAK KEDUA memberitahukan kepada PIHAK PERTAMA dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.</li>
        <li>Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh PIHAK KEDUA dihentikan sementara dan dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila akibat Keadaan Kahar tidak memungkinkan dilanjutkan/diselesaikannya pelaksanaan pekerjaan, PIHAK KEDUA berhak menerima honorarium secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
    </ol>

    <div class="page-footer-num">Halaman 2 dari 4</div>
</div>

<!-- ========================================== -->
<!-- HALAMAN 3 DARI 4: PASAL 11-12 & TTD SPK     -->
<!-- ========================================== -->
<div class="page">
    <div class="pasal-title mt-0">Pasal 11</div>
    <p>Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian tambahan/addendum dan merupakan bagian tidak terpisahkan dari perjanjian ini.</p>

    <div class="pasal-title">Pasal 12</div>
    <ol class="ps-3 mb-3">
        <li class="mb-2">Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat.</li>
        <li>Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), PARA PIHAK sepakat menyelesaikan perselisihan dengan memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kabupaten Tasikmalaya.</li>
    </ol>

    <p class="mb-4">Demikian Perjanjian ini dibuat dan ditandatangani oleh PARA PIHAK dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari PIHAK manapun dan untuk dilaksanakan oleh PARA PIHAK.</p>

    <!-- LEMBAR TTD PASAL HALAMAN 3 -->
    <div class="ttd-container">
        <div class="row text-center">
            <div class="col-6">
                <p class="mb-1">PIHAK KEDUA,</p>
                <div style="border: 1px dashed #777; width: 85px; height: 50px; margin: 10px auto; font-size: 8pt; display: flex; align-items: center; justify-content: center; color: #555;">Materai<br>10.000</div>
                <p class="mb-0"><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
                <span class="extra-small text-muted">ID SOBAT: {{ $mitra->id_sobat ?? '-' }}</span>
            </div>
            <div class="col-6">
                <p class="mb-1">PIHAK PERTAMA,</p>
                <br><br><br>
                <p class="mb-0"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
                <span class="extra-small text-muted">NIP. 19800101 200212 1 001</span>
            </div>
        </div>
    </div>

    <div class="page-footer-num">Halaman 3 dari 4</div>
</div>

<!-- ========================================== -->
<!-- HALAMAN 4 DARI 4: LAMPIRAN TABEL & TTD     -->
<!-- ========================================== -->
<div class="page">
    <div class="text-end extra-small text-muted mb-2">Lampiran: {{ strtoupper($mitra->nama) }}</div>
    <div class="spk-title" style="font-size: 11pt;">PERJANJIAN KERJA PETUGAS PENCACAHAN/PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor" style="font-size: 10pt;">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <div class="fw-bold mb-3 small text-center">DAFTAR URAIAN TUGAS, JANGKA WAKTU, NILAI PERJANJIAN, DAN BEBAN ANGGARAN</div>

    <table class="table table-bordered align-middle text-center mb-3">
        <thead class="table-light">
            <tr>
                <th rowspan="2" style="width: 30px;">No</th>
                <th rowspan="2">Uraian Tugas</th>
                <th rowspan="2">Jangka Waktu</th>
                <th colspan="2">Target Pekerjaan</th>
                <th rowspan="2" style="width: 95px;">Harga Satuan</th>
                <th rowspan="2" style="width: 105px;">Nilai Perjanjian</th>
                <th rowspan="2" style="width: 140px;">Beban Anggaran (MAK)</th>
            </tr>
            <tr>
                <th style="width: 50px;">Volume</th>
                <th style="width: 60px;">Satuan</th>
            </tr>
            <tr style="font-size: 8pt; background: #f1f5f9;">
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
                    <td class="font-monospace extra-small">{{ $item->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903' }}</td>
                </tr>
            @endforeach
            {{-- Fill empty rows up to 8 rows to match Word docx template --}}
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

    <div class="ttd-container">
        <div class="row text-center">
            <div class="col-6">
                <p class="mb-1">PIHAK KEDUA,</p>
                <br><br><br>
                <p class="mb-0"><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
                <span class="extra-small text-muted">ID SOBAT: {{ $mitra->id_sobat ?? '-' }}</span>
            </div>
            <div class="col-6">
                <p class="mb-1">PIHAK PERTAMA,</p>
                <br><br><br>
                <p class="mb-0"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
                <span class="extra-small text-muted">NIP. 19800101 200212 1 001</span>
            </div>
        </div>
    </div>

    <div class="page-footer-num">Halaman 4 dari 4</div>
</div>

</body>
</html>

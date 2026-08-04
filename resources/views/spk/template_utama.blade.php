<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK - {{ $mitra->nama }} - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.45; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; page-break-after: always; }
        .spk-title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .spk-nomor { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 20px; }
        .pasal-title { font-weight: bold; text-align: center; margin-top: 12px; margin-bottom: 4px; }
        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 5px 6px; font-size: 10pt; }
        .ttd-box { margin-top: 30px; }
        @media print {
            body { background: white; }
            .page { width: 100%; min-height: auto; padding: 0; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-danger font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK SURAT SPK BPS (PDF / PRINTER)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

<div class="page">
    <!-- JUDUL SPK RESMI BPS -->
    <div class="spk-title">PERJANJIAN KERJA PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <p>Pada hari ini, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini:</p>

    <table class="ms-2 mb-3" style="width: 100%;">
        <tr valign="top">
            <td style="width: 25px;">1.</td>
            <td style="width: 160px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
            <td>: Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr valign="top">
            <td>2.</td>
            <td><strong>{{ strtoupper($mitra->nama) }}</strong></td>
            <td>: {{ $mitra->pekerjaan ?? 'Lainnya / Belum Bekerja' }}, berkedudukan di {{ $mitra->alamat ?? 'Kabupaten Tasikmalaya' }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p>bahwa <strong>PIHAK PERTAMA</strong> dan <strong>PIHAK KEDUA</strong> yang secara bersama-sama disebut <strong>PARA PIHAK</strong>, sepakat untuk mengikatkan diri dalam Perjanjian Kerja Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, yang selanjutnya disebut Perjanjian, dengan ketentuan-ketentuan sebagai berikut:</p>

    <div class="pasal-title">Pasal 1</div>
    <p>PIHAK PERTAMA memberikan pekerjaan kepada PIHAK KEDUA dan PIHAK KEDUA menerima pekerjaan dari PIHAK PERTAMA sebagai Petugas Pendataan Lapangan Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dengan lingkup pekerjaan yang ditetapkan oleh PIHAK PERTAMA.</p>

    <div class="pasal-title">Pasal 2</div>
    <p>Ruang lingkup pekerjaan dalam Perjanjian ini mengacu pada wilayah kerja dan beban kerja sebagaimana tertuang dalam lampiran Perjanjian. Pedoman Petugas Pendataan Lapangan Wilayah Kegiatan Survei/Sensus Tahun {{ $tahun }} pada Badan Pusat Statistik Kabupaten Tasikmalaya, dan ketentuan-ketentuan yang ditetapkan oleh PIHAK PERTAMA.</p>

    <div class="pasal-title">Pasal 3</div>
    <p>Jangka Waktu Perjanjian terhitung sejak periode <strong>{{ $periodeLabel }}</strong>.</p>

    <div class="pasal-title">Pasal 4</div>
    <p>PIHAK KEDUA berkewajiban melaksanakan seluruh pekerjaan yang diberikan oleh PIHAK PERTAMA sampai selesai, sesuai ruang lingkup pekerjaan sebagaimana dimaksud dalam Pasal 2, dengan menerapkan protokol kesehatan yang berlaku di wilayah kerja masing-masing.</p>

    <div class="pasal-title">Pasal 5</div>
    <ol class="ps-3 mb-0">
        <li>PIHAK KEDUA berhak untuk mendapatkan honorarium petugas dari PIHAK PERTAMA sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong> untuk pekerjaan sebagaimana dimaksud dalam Pasal 2, termasuk biaya pajak, bea materai, pulsa dan kuota internet untuk komunikasi, dan jasa pelayanan keuangan.</li>
        <li>Selain mendapatkan honorarium sebagaimana dimaksud pada ayat (1), PIHAK KEDUA berhak mendapatkan asuransi petugas (khusus sensus) dari PIHAK PERTAMA.</li>
        <li>PIHAK KEDUA tidak diberikan honorarium tambahan apabila melakukan kunjungan di luar jadwal atau terdapat tambahan waktu pelaksanaan pekerjaan lapangan.</li>
    </ol>

    <div class="pasal-title">Pasal 6</div>
    <ol class="ps-3 mb-0">
        <li>Pembayaran honorarium sebagaimana dimaksud dalam Pasal 5 dilakukan setelah PIHAK KEDUA menyelesaikan dan menyerahkan seluruh hasil pekerjaan sebagaimana dimaksud dalam Pasal 2 kepada PIHAK PERTAMA.</li>
        <li>Pembayaran sebagaimana dimaksud pada ayat (1) dilakukan oleh PIHAK PERTAMA kepada PIHAK KEDUA sesuai dengan ketentuan peraturan perundang-undangan.</li>
    </ol>

    <div class="pasal-title">Pasal 7</div>
    <p>Penyerahan hasil pekerjaan lapangan sebagaimana dimaksud dalam Pasal 2 dilakukan secara bertahap dan selambat-lambatnya seluruh hasil pekerjaan lapangan diserahkan sesuai jadwal yang tercantum dalam Lampiran, yang dinyatakan dalam Berita Acara Serah Terima Hasil Pekerjaan yang ditandatangani oleh PARA PIHAK.</p>

    <div class="pasal-title">Pasal 8</div>
    <p>PIHAK PERTAMA dapat memutuskan Perjanjian ini secara sepihak sewaktu-waktu dalam hal PIHAK KEDUA tidak dapat melaksanakan kewajibannya sebagaimana dimaksud dalam Pasal 4, dengan menerbitkan Surat Pemutusan Perjanjian Kerja.</p>

    <div class="pasal-title">Pasal 9</div>
    <ol class="ps-3 mb-0">
        <li>Apabila PIHAK KEDUA mengundurkan diri pada saat/setelah pelaksanaan pekerjaan lapangan dengan tidak menyelesaikan pekerjaan yang menjadi tanggung jawabnya, maka wajib membayar ganti rugi kepada PIHAK PERTAMA sebesar <strong>Rp {{ number_format($totalHonor, 0, ',', '.') }}</strong>.</li>
        <li>Dikecualikan tidak membayar ganti rugi sebagaimana dimaksud pada ayat (1) kepada PIHAK PERTAMA, apabila PIHAK KEDUA meninggal dunia, mengundurkan diri karena sakit dengan keterangan rawat inap, kecelakaan dengan keterangan kepolisian, dan/atau telah diberikan Surat Pemutusan Perjanjian Kerja dari PIHAK PERTAMA.</li>
        <li>Dalam hal terjadi peristiwa sebagaimana dimaksud pada ayat (2), PIHAK PERTAMA membayarkan honorarium kepada PIHAK KEDUA secara proporsional sesuai pekerjaan yang telah dilaksanakan.</li>
    </ol>

    <div class="pasal-title">Pasal 10</div>
    <ol class="ps-3 mb-0">
        <li>Apabila terjadi Keadaan Kahar, yang meliputi bencana alam dan bencana sosial, PIHAK KEDUA memberitahukan kepada PIHAK PERTAMA dalam waktu paling lambat 7 (tujuh) hari sejak mengetahui atas kejadian Keadaan Kahar dengan menyertakan bukti.</li>
        <li>Pada saat terjadi Keadaan Kahar, pelaksanaan pekerjaan oleh PIHAK KEDUA dihentikan sementara dan dilanjutkan kembali setelah Keadaan Kahar berakhir, namun apabila tidak memungkinkan dilanjutkan, PIHAK KEDUA berhak menerima honorarium secara proporsional.</li>
    </ol>

    <div class="pasal-title">Pasal 11</div>
    <p>Segala sesuatu yang belum atau tidak cukup diatur dalam Perjanjian ini, dituangkan dalam perjanjian tambahan/addendum dan merupakan bagian tidak terpisahkan dari perjanjian ini.</p>

    <div class="pasal-title">Pasal 12</div>
    <ol class="ps-3 mb-0">
        <li>Segala perselisihan atau perbedaan pendapat yang timbul sebagai akibat adanya Perjanjian ini akan diselesaikan secara musyawarah untuk mufakat.</li>
        <li>Apabila perselisihan tidak dapat diselesaikan sebagaimana dimaksud pada ayat (1), PARA PIHAK sepakat memilih kedudukan/domisili hukum di Panitera Pengadilan Negeri Kabupaten Tasikmalaya.</li>
    </ol>

    <p class="mt-3">Demikian Perjanjian ini dibuat dan ditandatangani oleh PARA PIHAK dalam 2 (dua) rangkap asli bermeterai cukup, tanpa paksaan dari PIHAK manapun dan untuk dilaksanakan oleh PARA PIHAK.</p>

    <!-- LEMBAR TTD PASAL -->
    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>PIHAK KEDUA,</p>
            <div style="border: 1px dashed #999; width: 80px; height: 50px; margin: 10px auto; font-size: 8pt; display: flex; align-items: center; justify-content: center; color: #666;">Meterai<br>10.000</div>
            <p class="mb-0"><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
            <span class="extra-small text-muted">ID SOBAT: {{ $mitra->id_sobat ?? '-' }}</span>
        </div>
        <div class="col-6">
            <p>PIHAK PERTAMA,</p>
            <br><br><br>
            <p class="mb-0"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
            <span class="extra-small text-muted">NIP. 19800101 200212 1 001</span>
        </div>
    </div>
</div>

<!-- ========================================== -->
<!-- LAMPIRAN SPK TABEL RINCIAN                -->
<!-- ========================================== -->
<div class="page">
    <div class="text-end extra-small text-muted mb-2">Lampiran SPK: {{ strtoupper($mitra->nama) }}</div>
    <div class="spk-title" style="font-size: 11pt;">PERJANJIAN KERJA PETUGAS PENCACAHAN/PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="spk-nomor" style="font-size: 10pt;">NOMOR: {{ $nomorDokumen ?? '1001/PPK/SPK/03/' . $tahun }}</div>

    <div class="fw-bold mb-2 small text-center">DAFTAR URAIAN TUGAS, JANGKA WAKTU, NILAI PERJANJIAN, DAN BEBAN ANGGARAN</div>

    <table class="table table-bordered align-middle text-center mb-3">
        <thead class="table-light">
            <tr>
                <th style="width: 30px;">NO</th>
                <th>URAIAN TUGAS</th>
                <th>JANGKA WAKTU</th>
                <th style="width: 100px;">TARGET PEKERJAAN</th>
                <th style="width: 110px;">HARGA SATUAN</th>
                <th style="width: 120px;">NILAI PERJANJIAN</th>
                <th style="width: 160px;">BEBAN ANGGARAN (MAK)</th>
            </tr>
            <tr style="font-size: 8pt; background: #f1f5f9;">
                <td>(1)</td>
                <td>(2)</td>
                <td>(3)</td>
                <td>(4)</td>
                <td>(5)</td>
                <td>(6)</td>
                <td>(7)</td>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="text-start fw-bold">{{ $item->kegiatan->nama }}</td>
                    <td>{{ $periodeLabel }}</td>
                    <td>1 Dokumen</td>
                    <td class="text-end">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                    <td class="font-monospace extra-small">{{ $item->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-muted">Tidak ada rincian tugas.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="5" class="text-end">TOTAL NILAI PERJANJIAN:</td>
                <td class="text-end text-success">Rp {{ number_format($totalHonor, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>PIHAK KEDUA,</p>
            <br><br><br>
            <p class="mb-0"><strong><u>{{ strtoupper($mitra->nama) }}</u></strong></p>
            <span class="extra-small text-muted">ID SOBAT: {{ $mitra->id_sobat ?? '-' }}</span>
        </div>
        <div class="col-6">
            <p>PIHAK PERTAMA,</p>
            <br><br><br>
            <p class="mb-0"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
            <span class="extra-small text-muted">NIP. 19800101 200212 1 001</span>
        </div>
    </div>
</div>

</body>
</html>

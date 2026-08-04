<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Berita Acara Serah Terima (BAST) - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.5; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 25mm 25mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; page-break-after: always; }
        .bast-header { text-align: center; font-weight: bold; margin-bottom: 25px; }
        .bast-title { font-size: 11pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 0; line-height: 1.3; }
        .bast-nomor { text-align: center; font-size: 11pt; margin-top: 15px; margin-bottom: 25px; }
        p { text-align: justify; text-justify: inter-word; margin-bottom: 12px; }
        .party-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .party-table td { vertical-align: top; padding: 2px 0; }
        ol.bast-list { padding-left: 25px; margin-bottom: 15px; }
        ol.bast-list li { margin-bottom: 5px; text-align: justify; }
        .ttd-box { margin-top: 60px; text-align: center; }
        .ttd-col { width: 50%; display: inline-block; float: left; }
        .clearfix::after { content: ""; clear: both; display: table; }
        @media print {
            body { background: white; }
            .page { width: 100%; min-height: auto; padding: 0; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-primary font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK BERITA ACARA SERAH TERIMA (BAST)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

@foreach($batchList as $b)
<div class="page">
    <!-- JUDUL BAST BPS PERSIS SEPERTI DOKUMEN ORIGINAL DOCX (IMAGE 2) -->
    <div class="bast-header">
        <div class="bast-title">BERITA ACARA SERAH TERIMA PEKERJAAN</div>
        <div class="bast-title">PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }}</div>
        <div class="bast-title">PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
        <div class="bast-nomor">Nomor: {{ $b->nomor_dokumen }}</div>
    </div>

    <p>Pada hari ini, bertempat di Kantor BPS Kabupaten Tasikmalaya dengan alamat Jalan R.A.A Kusumahsubrata Komplek Perkantoran Kertasari, Kabupaten Tasikmalaya, yang bertanda tangan di bawah ini:</p>

    <table class="party-table ms-1">
        <tr>
            <td style="width: 25px;">1.</td>
            <td style="width: 175px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
            <td style="width: 15px;">:</td>
            <td>Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr><td colspan="4" style="height: 8px;"></td></tr>
        <tr>
            <td>2.</td>
            <td><strong>{{ strtoupper($b->mitra->nama) }}</strong></td>
            <td>:</td>
            <td>{{ $b->mitra->pekerjaan_clean }}, berkedudukan di {{ $b->mitra->alamat_clean }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p style="margin-top: 15px;">Berdasarkan Perjanjian Kerja Nomor <strong>{{ str_replace('BAST', 'SPK', $b->nomor_dokumen) }}</strong>, bersama ini PIHAK KEDUA telah menyerahkan pekerjaan kepada PIHAK PERTAMA, dengan ketentuan sebagai berikut:</p>

    <ol class="bast-list">
        <li>PIHAK PERTAMA menyatakan bahwa pekerjaan dari PIHAK KEDUA telah selesai;</li>
        <li>Hasil pekerjaan PIHAK KEDUA telah sesuai dengan jumlah dan kualitas yang ditetapkan dalam Perjanjian Kerja.</li>
    </ol>

    <p>Demikian Berita Acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <!-- TANDA TANGAN ORIGINAL (IMAGE 2) -->
    <div class="ttd-box clearfix">
        <div class="ttd-col">
            <p style="text-align: center;">PIHAK KEDUA</p>
            <br><br><br><br>
            <p style="text-align: center; margin-bottom: 0;"><strong><u>{{ strtoupper($b->mitra->nama) }}</u></strong></p>
        </div>
        <div class="ttd-col">
            <p style="text-align: center;">PIHAK PERTAMA</p>
            <br><br><br><br>
            <p style="text-align: center; margin-bottom: 0;"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
            <span style="font-size: 10pt;">NIP. 19800101 200212 1 001</span>
        </div>
    </div>
</div>
@endforeach

</body>
</html>

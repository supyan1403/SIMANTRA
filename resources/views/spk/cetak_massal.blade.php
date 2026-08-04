<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Massal SPK Multi-Petugas ({{ $batchList->count() }} Mitra) - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; page-break-after: always; }
        .kop-header { border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 20px; }
        .kop-title { font-size: 14pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .kop-sub { font-size: 11pt; text-align: center; margin-bottom: 0; }
        .spk-title { font-size: 13pt; font-weight: bold; text-align: center; text-decoration: underline; margin-top: 15px; margin-bottom: 2px; }
        .spk-nomor { text-align: center; font-size: 11pt; margin-bottom: 20px; }
        .ttd-box { margin-top: 40px; }
        @media print {
            body { background: white; }
            .page { width: 100%; min-height: auto; padding: 0; margin: 0; box-shadow: none; page-break-after: always; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white sticky-top">
    <button onclick="window.print()" class="btn btn-danger font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK SEMUA SPK MULTI-PETUGAS (PDF / PRINTER)</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

@foreach($batchList as $b)
<div class="page">
    <div class="kop-header text-center">
        <div class="kop-title">BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
        <div class="kop-sub">Jl. Singaparna No. 123, Tasikmalaya - Jawa Barat | Telp: (0265) 123456</div>
    </div>

    <div class="spk-title">SURAT PERJANJIAN KERJA (SPK) MITRA STATISTIK</div>
    <div class="spk-nomor">Nomor: {{ $b->nomor_dokumen }}</div>

    <p>Pada hari ini, <strong>{{ date('d') }}</strong> bulan <strong>{{ date('F') }}</strong> tahun <strong>{{ date('Y') }}</strong>, kami yang bertanda tangan di bawah ini:</p>

    <table class="ms-3 mb-3">
        <tr>
            <td style="width: 30px;">1.</td>
            <td style="width: 140px;"><strong>Nama</strong></td>
            <td>: KEPA TlM BPS KABUPATEN TASIKMALAYA</td>
        </tr>
        <tr>
            <td></td>
            <td><strong>Jabatan</strong></td>
            <td>: Pejabat Pembuat Komitmen (PPK) BPS Kabupaten Tasikmalaya</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, yang selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
    </table>

    <table class="ms-3 mb-3">
        <tr>
            <td style="width: 30px;">2.</td>
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
            <td><strong>Pekerjaan</strong></td>
            <td>: {{ $b->mitra->pekerjaan ?? 'Mitra Statistik BPS' }}</td>
        </tr>
        <tr>
            <td></td>
            <td><strong>Alamat</strong></td>
            <td>: {{ $b->mitra->alamat ?? 'Kabupaten Tasikmalaya' }}</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="2">Bertindak untuk dan atas nama diri sendiri, yang selanjutnya disebut sebagai <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p>PIHAK PERTAMA dan PIHAK KEDUA secara bersama-sama sepakat untuk mengikatkan diri dalam Surat Perjanjian Kerja (SPK) Pelaksanaan Kegiatan Statistik (Kategori {{ strtoupper($kategoriKegiatan) }}) dengan ketentuan sebagai berikut:</p>

    <div class="text-center font-weight-bold my-2"><strong>Pasal 1<br>TUGAS DAN JANGKA WAKTU KERJA</strong></div>
    <ol>
        <li>PIHAK PERTAMA memberikan tugas pekerjaan statistik kepada PIHAK KEDUA, dan PIHAK KEDUA menerima tugas pekerjaan tersebut.</li>
        <li>Jangka waktu pelaksanaan perjanjian kerja ini adalah pada periode <strong>{{ $periodeLabel }}</strong>.</li>
    </ol>

    <div class="text-center font-weight-bold my-2"><strong>Pasal 2<br>HAK DAN KEWAJIBAN</strong></div>
    <ol>
        <li>PIHAK KEDUA berkewajiban menyelesaikan pekerjaan sesuai target dan standar kualitas yang ditetapkan oleh BPS Kabupaten Tasikmalaya.</li>
        <li>PIHAK KEDUA berhak menerima pembayaran honorarium sebesar <strong>Rp {{ number_format($b->total_honor, 0, ',', '.') }}</strong> setelah pekerjaan diselesaikan dan diterima dengan baik oleh PIHAK PERTAMA.</li>
    </ol>

    <div class="text-center font-weight-bold my-2"><strong>Pasal 3<br>PENUTUP</strong></div>
    <p>Demikian Surat Perjanjian Kerja ini dibuat dan ditandatangani oleh kedua belah pihak dalam rangkap 2 (dua) untuk dipergunakan sebagaimana mestinya.</p>

    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>PIHAK KEDUA,<br>Mitra Statistik BPS</p>
            <br><br><br>
            <p><strong><u>{{ strtoupper($b->mitra->nama) }}</u></strong><br>ID SOBAT: {{ $b->mitra->id_sobat ?? '-' }}</p>
        </div>
        <div class="col-6">
            <p>PIHAK PERTAMA,<br>Pejabat Pembuat Komitmen (PPK)</p>
            <br><br><br>
            <p><strong><u>NENG SAFITRI, S.ST., M.Si</u></strong><br>NIP. 19850101 200801 2 001</p>
        </div>
    </div>
</div>
@endforeach

</body>
</html>

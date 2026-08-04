<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak BAST Multi-Petugas ({{ $batchList->count() }} Mitra) - {{ $periodeLabel }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.5; color: #000; background: #f8fafc; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm 20mm 20mm 25mm; margin: 15px auto; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); border-radius: 4px; page-break-after: always; }
        .bast-title { font-size: 12pt; font-weight: bold; text-align: center; text-transform: uppercase; margin-bottom: 2px; }
        .bast-nomor { text-align: center; font-size: 11pt; font-weight: bold; margin-bottom: 20px; }
        table.table-bordered { border-color: #000 !important; }
        table.table-bordered th, table.table-bordered td { border-color: #000 !important; padding: 5px 8px; font-size: 10pt; }
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
    <button onclick="window.print()" class="btn btn-primary font-monospace px-4"><i class="bi bi-printer me-2"></i>CETAK BERITA ACARA SERAH TERIMA (BAST) MULTI-PETUGAS</button>
    <button onclick="window.close()" class="btn btn-outline-light px-3 ms-2">Tutup</button>
</div>

@foreach($batchList as $b)
<div class="page">
    <!-- JUDUL BAST BPS (PERSIS SEPERTI DOCX BAST PETUGAS) -->
    <div class="bast-title">BERITA ACARA SERAH TERIMA PEKERJAAN PETUGAS PENDATAAN LAPANGAN KEGIATAN SURVEI/SENSUS TAHUN {{ $tahun }} PADA BADAN PUSAT STATISTIK KABUPATEN TASIKMALAYA</div>
    <div class="bast-nomor">Nomor: {{ $b->nomor_dokumen }}</div>

    <p>Pada hari ini, bertempat di Kantor BPS Kabupaten Tasikmalaya dengan alamat Jalan R.A.A Kusumahsubrata Komplek Perkantoran Kertasari, Kabupaten Tasikmalaya, yang bertanda tangan di bawah ini:</p>

    <table class="ms-2 mb-3" style="width: 100%;">
        <tr valign="top">
            <td style="width: 25px;">1.</td>
            <td style="width: 160px;"><strong>Dindin Muldiana, S.ST. MP.</strong></td>
            <td>: Pejabat Pembuat Komitmen Badan Pusat Statistik Kabupaten Tasikmalaya; berkedudukan di Jl. Raya Timur Singaparna KM4 Cintaraja Tasikmalaya, bertindak untuk dan atas nama Badan Pusat Statistik Kabupaten Tasikmalaya, selanjutnya disebut sebagai <strong>PIHAK PERTAMA</strong>.</td>
        </tr>
        <tr valign="top">
            <td>2.</td>
            <td><strong>{{ strtoupper($b->mitra->nama) }}</strong></td>
            <td>: {{ $b->mitra->pekerjaan ?? 'Lainnya / Belum Bekerja' }}, berkedudukan di {{ $b->mitra->alamat ?? 'Kabupaten Tasikmalaya' }}, bertindak untuk dan atas nama diri sendiri, selanjutnya disebut <strong>PIHAK KEDUA</strong>.</td>
        </tr>
    </table>

    <p>Berdasarkan Perjanjian Kerja Nomor <strong>{{ str_replace('BAST', 'SPK', $b->nomor_dokumen) }}</strong>, periode <strong>{{ $periodeLabel }}</strong>, bersama ini PIHAK KEDUA telah menyerahkan pekerjaan kepada PIHAK PERTAMA, dengan ketentuan sebagai berikut:</p>

    <ol class="ms-2 mb-3">
        <li class="mb-2">PIHAK PERTAMA menyatakan bahwa pekerjaan dari PIHAK KEDUA telah selesai;</li>
        <li>Hasil pekerjaan PIHAK KEDUA telah sesuai dengan jumlah dan kualitas yang ditetapkan dalam Perjanjian Kerja.</li>
    </ol>

    <div class="fw-bold mb-2">RINCIAN PEKERJAAN YANG DISERAHKAN:</div>
    <table class="table table-bordered align-middle mb-4">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 35px;">NO</th>
                <th>NAMA KEGIATAN STATISTIK</th>
                <th>BIDANG / TIM</th>
                <th>PERIODE</th>
                <th class="text-end" style="width: 140px;">NILAI HONOR (RP)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($b->items as $idx => $item)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>
                        <div class="fw-bold">{{ $item->kegiatan->nama }}</div>
                        <div class="text-muted extra-small font-monospace">KODE: {{ $item->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903' }}</div>
                    </td>
                    <td>{{ $item->kegiatan->bidang->nama ?? '-' }}</td>
                    <td class="text-center">{{ $item->periode->nama_bulan }} {{ $item->periode->tahun }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($item->nominal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="fw-bold table-light">
                <td colspan="4" class="text-end">TOTAL HARGA HASIL PEKERJAAN:</td>
                <td class="text-end text-success">Rp {{ number_format($b->total_honor, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <p>Demikian Berita Acara ini dibuat untuk dipergunakan sebagaimana mestinya.</p>

    <!-- LEMBAR TTD BAST PERSIS SEPERTI DOCX -->
    <div class="row ttd-box text-center">
        <div class="col-6">
            <p>PIHAK KEDUA,</p>
            <br><br><br>
            <p class="mb-0"><strong><u>{{ strtoupper($b->mitra->nama) }}</u></strong></p>
            <span class="extra-small text-muted">ID SOBAT: {{ $b->mitra->id_sobat ?? '-' }}</span>
        </div>
        <div class="col-6">
            <p>PIHAK PERTAMA,</p>
            <br><br><br>
            <p class="mb-0"><strong><u>Dindin Muldiana, S.ST. MP.</u></strong></p>
            <span class="extra-small text-muted">NIP. 19800101 200212 1 001</span>
        </div>
    </div>
</div>
@endforeach

</body>
</html>

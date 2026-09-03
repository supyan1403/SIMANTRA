<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Kegiatan extends Model {
    protected $fillable = [
        'nama', 
        'short_name',
        'format_spk',
        'bidang_id', 
        'kode_mata_anggaran',
        'tahun',
        'jumlah',
        'satuan',
        'harga',
        'total',
        'tgl_mulai',
        'tgl_selesai',
        'source_file',
        'revisi_ke',
        'jenis_dokumen'
    ];
    protected $appends = ['jadwal_bulan_list', 'jadwal_teks', 'harga_satuan', 'target_volume'];

    public function bidang() { return $this->belongsTo(Bidang::class); }
    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }
    public function jadwal() { return $this->hasMany(KegiatanJadwal::class)->orderBy('bulan_angka'); }

    public function getHargaSatuanAttribute(): float
    {
        return (float)($this->harga ?? 0);
    }

    public function getTargetVolumeAttribute(): float
    {
        return (float)($this->jumlah ?? 0);
    }

    /**
     * Ambil daftar angka bulan pelaksanaan kegiatan (1-12) dari tanggal kegiatan atau alokasi honor.
     */
    public function getJadwalBulanListAttribute()
    {
        if (!empty($this->tgl_mulai) && !empty($this->tgl_selesai)) {
            try {
                $startM = (int) \Carbon\Carbon::parse($this->tgl_mulai)->format('n');
                $endM = (int) \Carbon\Carbon::parse($this->tgl_selesai)->format('n');
                if ($startM <= $endM) {
                    return collect(range($startM, $endM))->values();
                }
            } catch (\Throwable $e) {}
        }

        $bulanAngka = $this->alokasiHonors->pluck('periode.bulan_angka')->filter()->unique()->sort()->values();
        if ($bulanAngka->isEmpty() && $this->jadwal->isNotEmpty()) {
            $bulanAngka = $this->jadwal->pluck('bulan_angka')->filter()->unique()->sort()->values();
        }
        return $bulanAngka->isNotEmpty() ? $bulanAngka : collect(range(1, 12))->values();
    }

    /**
     * Format teks jadwal kegiatan (contoh: "Januari - Juni 2024" atau "Maret 2024").
     */
    public function getJadwalTeksAttribute(): string
    {
        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        if (!empty($this->tgl_mulai) && !empty($this->tgl_selesai)) {
            try {
                return \Carbon\Carbon::parse($this->tgl_mulai)->translatedFormat('d M') . ' - ' . \Carbon\Carbon::parse($this->tgl_selesai)->translatedFormat('d M Y');
            } catch (\Throwable $e) {}
        }

        $bulanAngka = $this->jadwal_bulan_list;
        if ($bulanAngka->isEmpty()) {
            return '-';
        }

        $first = $bulanNama[$bulanAngka->first()] ?? '';
        $last = $bulanNama[$bulanAngka->last()] ?? '';
        $tahun = $this->tahun ?? date('Y');

        if ($bulanAngka->count() === 1 || $first === $last) {
            return "{$first} {$tahun}";
        }

        return "{$first} - {$last} {$tahun}";
    }

    /**
     * Klasifikasi jenis tugas kegiatan: 'Pengolahan' atau 'Pencacahan' (Lapangan).
     */
    public function getJenisTugasAttribute(): string
    {
        if (preg_match('/(?:pengolahan|entri|entry|verifikasi|perekaman|editing|coding)/i', (string) $this->nama)) {
            return 'Pengolahan';
        }
        return 'Pencacahan';
    }

    public function isPengolahan(): bool
    {
        return $this->jenis_tugas === 'Pengolahan';
    }

    public function isPencacahan(): bool
    {
        return $this->jenis_tugas === 'Pencacahan';
    }
}

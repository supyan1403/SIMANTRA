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
        'tgl_selesai'
    ];
    public function bidang() { return $this->belongsTo(Bidang::class); }
    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }
    public function jadwal() { return $this->hasMany(KegiatanJadwal::class)->orderBy('bulan_angka'); }

    /**
     * Klasifikasi jenis tugas kegiatan: 'Pengolahan' atau 'Pencacahan' (Lapangan).
     */
    public function getJenisTugasAttribute(): string
    {
        if (preg_match('/(?:pengolahan|entri)/i', (string) $this->nama)) {
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

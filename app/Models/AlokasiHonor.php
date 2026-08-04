<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AlokasiHonor extends Model {
    protected $fillable = ['mitra_id', 'periode_id', 'kegiatan_id', 'nominal', 'nomor_spk', 'nomor_bast'];
    public function mitra() { return $this->belongsTo(Mitra::class); }
    public function periode() { return $this->belongsTo(Periode::class); }
    public function kegiatan() { return $this->belongsTo(Kegiatan::class); }
}

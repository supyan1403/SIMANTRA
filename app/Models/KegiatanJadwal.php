<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KegiatanJadwal extends Model
{
    protected $table = 'kegiatan_jadwal';

    protected $fillable = [
        'kegiatan_id',
        'bulan_angka',
        'jumlah',
    ];

    public $timestamps = true;

    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    protected $fillable = ['nama', 'kode', 'tim_kerja'];

    public function kegiatans()
    {
        return $this->hasMany(Kegiatan::class);
    }

    public static function getNamaBidang()
    {
        return ['Distribusi', 'Neraca', 'Produksi', 'Sosial', 'Cadangan'];
    }
}

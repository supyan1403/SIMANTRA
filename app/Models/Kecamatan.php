<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $fillable = ['kode_kec', 'nama'];

    public function desas()
    {
        return $this->hasMany(Desa::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model {
    protected $fillable = [
        'nama', 
        'id_sobat', 
        'no_hp', 
        'kecamatan', 
        'desa', 
        'kabupaten_kota',
        'alamat_detail', 
        'alamat', 
        'pekerjaan', 
        'kode_alamat', 
        'jk'
    ];

    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }
    public function sbmls() { return $this->hasMany(Sbml::class); }

    public function getPekerjaanCleanAttribute()
    {
        $val = trim((string)$this->pekerjaan);
        if (empty($val) || str_starts_with($val, '=') || str_starts_with($val, '#') || str_contains(strtoupper($val), 'N/A') || str_contains($val, '#REF!')) {
            return 'Lainnya/ Belum Bekerja';
        }
        return $val;
    }

    public function getAlamatCleanAttribute()
    {
        $parts = [];
        if (!empty($this->alamat_detail)) {
            $parts[] = trim($this->alamat_detail);
        }
        if (!empty($this->desa)) {
            $parts[] = 'Desa ' . trim($this->desa);
        }
        if (!empty($this->kecamatan)) {
            $parts[] = 'Kec. ' . trim($this->kecamatan);
        }
        if (!empty($this->kabupaten_kota)) {
            $parts[] = trim($this->kabupaten_kota);
        }

        if (!empty($parts)) {
            return implode(', ', $parts);
        }

        $val = trim((string)$this->alamat);
        if (empty($val) || str_starts_with($val, '=') || str_starts_with($val, '#') || str_contains(strtoupper($val), 'N/A') || str_contains($val, '#REF!')) {
            return 'Kabupaten Tasikmalaya';
        }
        return $val;
    }
}

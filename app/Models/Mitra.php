<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Mitra extends Model {
    protected $fillable = ['nama', 'id_sobat', 'no_hp', 'alamat', 'pekerjaan', 'kode_alamat', 'jk'];
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
        $val = trim((string)$this->alamat);
        if (empty($val) || str_starts_with($val, '=') || str_starts_with($val, '#') || str_contains(strtoupper($val), 'N/A') || str_contains($val, '#REF!')) {
            return 'Kabupaten Tasikmalaya';
        }
        return $val;
    }
}

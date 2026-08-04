<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Mitra extends Model {
    protected $fillable = ['nama', 'id_sobat', 'no_hp', 'alamat', 'pekerjaan', 'kode_alamat', 'jk'];
    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }
    public function sbmls() { return $this->hasMany(Sbml::class); }
}

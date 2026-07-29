<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Periode extends Model {
    protected $fillable = ['tahun', 'bulan', 'bulan_angka'];
    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }
    public function sbmls() { return $this->hasMany(Sbml::class); }
}

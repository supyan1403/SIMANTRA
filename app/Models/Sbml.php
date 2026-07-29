<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Sbml extends Model {
    protected $fillable = ['mitra_id', 'periode_id', 'jenis', 'nominal'];
    public function mitra() { return $this->belongsTo(Mitra::class); }
    public function periode() { return $this->belongsTo(Periode::class); }
}

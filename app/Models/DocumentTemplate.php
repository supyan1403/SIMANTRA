<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jenis_dokumen',
        'kategori_kegiatan',
        'file_path',
        'deskripsi',
        'is_active',
    ];
}

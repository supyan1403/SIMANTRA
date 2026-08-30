<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mitra extends Model {
    protected $fillable = [
        'nama', 
        'id_sobat', 
        'nik',
        'posisi',
        'posisi_daftar',
        'status_seleksi',
        'nilai_ujian',
        'no_hp', 
        'email',
        'npwp',
        'tanggal_lahir',
        'agama',
        'status_perkawinan',
        'pendidikan',
        'kabupaten_kota',
        'kecamatan', 
        'desa', 
        'alamat_detail', 
        'alamat', 
        'pekerjaan', 
        'kode_alamat', 
        'jk',
        'exp_sp',
        'exp_st',
        'exp_se',
        'exp_susenas',
        'exp_sakernas',
        'exp_sbh',
        'catatan_mitra'
    ];

    protected $casts = [
        'nilai_ujian' => 'float',
        'exp_sp' => 'boolean',
        'exp_st' => 'boolean',
        'exp_se' => 'boolean',
        'exp_susenas' => 'boolean',
        'exp_sakernas' => 'boolean',
        'exp_sbh' => 'boolean',
    ];

    public function alokasiHonors() { return $this->hasMany(AlokasiHonor::class); }

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

    public function getPendidikanCleanAttribute()
    {
        $val = trim((string)$this->pendidikan);
        if (empty($val)) {
            return '-';
        }

        $map = [
            '1' => 'SD / MI / Sederajat',
            '2' => 'SMP / MTs / Sederajat',
            '3' => 'SMA / SMK / MA / Sederajat',
            '4' => 'D1 / D2 / D3 (Diploma)',
            '5' => 'S1 / Sarjana (D4/S1)',
            '6' => 'S2 / Pascasarjana',
            '7' => 'S3 / Doktor',
        ];

        return $map[$val] ?? $val;
    }

    public function getTanggalLahirCleanAttribute()
    {
        $val = trim((string)$this->tanggal_lahir);
        if (empty($val)) {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse($val)->translatedFormat('d/m/Y');
        } catch (\Exception $e) {
            return $val;
        }
    }

    public function getTanggalLahirIsoAttribute(): ?string
    {
        $val = trim((string)$this->tanggal_lahir);
        if (empty($val) || $val === '-') {
            return null;
        }

        // Format sudah YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $val)) {
            return $val;
        }

        // Format DD/MM/YYYY atau DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
        }

        try {
            return \Carbon\Carbon::parse($val)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}

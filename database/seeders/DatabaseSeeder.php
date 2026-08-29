<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bidang;
use App\Models\Periode;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\AlokasiHonor;
use App\Models\Sbml;
use App\Models\SbmlMaster;
use App\Models\DocumentTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@bps.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Operator User
        User::firstOrCreate(
            ['email' => 'operator@bps.go.id'],
            [
                'name' => 'Operator SIMANTRA',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // Bidang Default
        $bidangs = ['Distribusi', 'Neraca', 'Produksi', 'Sosial', 'Cadangan'];
        foreach ($bidangs as $nama) {
            Bidang::firstOrCreate(['nama' => $nama]);
        }

        // Seed data from mantra_db.json if available
        $jsonPath = database_path('seeders/mantra_db.json');
        if (file_exists($jsonPath)) {
            $data = json_decode(file_get_contents($jsonPath), true);
            if (!empty($data)) {
                DB::transaction(function () use ($data) {
                    if (!empty($data['bidangs'])) {
                        foreach ($data['bidangs'] as $b) {
                            Bidang::firstOrCreate(['id' => $b['id']], ['nama' => $b['nama']]);
                        }
                    }
                    if (!empty($data['periodes'])) {
                        foreach ($data['periodes'] as $p) {
                            Periode::firstOrCreate(['id' => $p['id']], [
                                'tahun' => $p['tahun'],
                                'bulan' => $p['bulan'],
                                'bulan_angka' => $p['bulan_angka'],
                            ]);
                        }
                    }
                    if (!empty($data['kegiatans'])) {
                        foreach ($data['kegiatans'] as $k) {
                            Kegiatan::firstOrCreate(['id' => $k['id']], [
                                'nama' => $k['nama'],
                                'bidang_id' => $k['bidang_id'],
                                'kode_mata_anggaran' => $k['kode_mata_anggaran'] ?? null,
                                'tahun' => $k['tahun'] ?? '2024',
                            ]);
                        }
                    }
                    if (!empty($data['mitras'])) {
                        foreach ($data['mitras'] as $m) {
                            Mitra::updateOrCreate(['id' => $m['id']], [
                                'nama' => $m['nama'],
                                'id_sobat' => $m['id_sobat'] ?? null,
                                'no_hp' => $m['no_hp'] ?? null,
                                'alamat' => $m['alamat'] ?? null,
                                'pekerjaan' => $m['pekerjaan'] ?? null,
                                'kode_alamat' => $m['kode_alamat'] ?? null,
                                'jk' => $m['jk'] ?? null,
                            ]);
                        }
                    }
                    if (!empty($data['alokasi_honors'])) {
                        foreach ($data['alokasi_honors'] as $h) {
                            AlokasiHonor::updateOrCreate(['id' => $h['id']], [
                                'mitra_id' => $h['mitra_id'],
                                'periode_id' => $h['periode_id'],
                                'kegiatan_id' => $h['kegiatan_id'],
                                'nominal' => $h['nominal'],
                            ]);
                        }
                    }
                    if (!empty($data['sbmls'])) {
                        foreach ($data['sbmls'] as $s) {
                            Sbml::updateOrCreate(['id' => $s['id']], [
                                'mitra_id' => $s['mitra_id'],
                                'periode_id' => $s['periode_id'],
                                'jenis' => $s['jenis'],
                                'nominal' => $s['nominal'],
                            ]);
                        }
                    }
                });
            }
        }

        // Default Document Templates
        $defaultTemplates = [
            [
                'nama' => 'Template SPK (Surat Perintah Kerja) Standar BPS',
                'jenis_dokumen' => 'spk',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format baku Surat Perintah / Perjanjian Kerja Mitra Statistik BPS',
                'is_active' => true,
            ],
            [
                'nama' => 'Template BAST (Berita Acara Serah Terima) Standar BPS',
                'jenis_dokumen' => 'bast',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format baku Berita Acara Serah Terima Hasil Pekerjaan Mitra BPS',
                'is_active' => true,
            ],
            [
                'nama' => 'Template Lampiran Rincian Honor Mitra',
                'jenis_dokumen' => 'spk',
                'kategori_kegiatan' => 'umum',
                'deskripsi' => 'Format lampiran rincian honor dan daftar alokasi kegiatan mitra',
                'is_active' => true,
            ],
        ];

        foreach ($defaultTemplates as $dt) {
            DocumentTemplate::firstOrCreate(
                ['nama' => $dt['nama'], 'jenis_dokumen' => $dt['jenis_dokumen']],
                $dt
            );
        }

        // Default Master SBML Batas Tahunan
        SbmlMaster::firstOrCreate(
            ['tahun' => 2024],
            [
                'nominal_pencacahan' => 3326000,
                'nominal_pengolahan' => 3077000,
                'nominal' => 6403000,
            ]
        );
        SbmlMaster::firstOrCreate(
            ['tahun' => 2025],
            [
                'nominal_pencacahan' => 4500000,
                'nominal_pengolahan' => 3000000,
                'nominal' => 7500000,
            ]
        );

        $this->call(KecamatanDesaSeeder::class);
    }
}

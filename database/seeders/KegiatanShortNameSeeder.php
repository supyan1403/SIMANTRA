<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kegiatan;

class KegiatanShortNameSeeder extends Seeder
{
    public function run(): void
    {
        $mapping = [
            'Pendataan Lapangan Paket Komoditas Shkk' => 'SHKK',
            'Pendataan Lapangan Survei Harga Perdagangan Besar' => 'SHPB',
            'Pendataan Lapangan Updating Ksa Padi' => 'KSA_PADI',
            'Pendataan Lapangan Survei Harga Konsumen Perdesaan' => 'HKD',
            'Pendataan Lapangan Updating Ksa Jagung' => 'KSA_JAGUNG',
            'Pendataan Lapangan Survei Harga Produsen Perdesaan' => 'HD',
            'Pendataan Lapangan Hpt' => 'HPT',
            'Pengolahan Data Vimk23 Triwulanan' => 'VIMK23_TW4',
            'Pendataan Lapangan Vimk23 Triwulanan' => 'VIMK23_TW4',
            'Pendataan Lapangan Sedapp Online' => 'SEDAPP',
            'Pendataan Lapangan Bulanan Pemotongan Ternak' => 'RPH_TPH',
            'Pendataan Lapangan Hpbg' => 'HPBG',
            'Pengolahan Survei Harga Produsen Perdesaan' => 'HD_KABKOT',
            'Pendataan Lapangan Vhts' => 'VHTS',
            'Pendataan Lapangan Updating Listing Bs' => 'SAKERNAS_BS',
            'Pendataan Lapangan Updating Direktori Perusahaan Konstruksi' => 'UDP',
            'Pendataan Lapangan Pemutakhiran Dpa' => 'DPA',
            'Pendataan Lapangan Ubinan Padi' => 'UBINAN',
            'Pendataan Lapangan Updating Direktori Jasa Pariwisata' => 'PARiwisata',
            'Pendataan Lapangan Survei Konstruksi Triwulanan' => 'SKTR',
            'Pendataan Lapangan Sklnpt' => 'SKLNPT',
            'Pendataan Lapangan Pelabuhan Perikanan' => 'PPL_TPI',
            'Pendataan Lapangan (Spak)' => 'SPAK',
            'Pendataan Lapangan (Podes)' => 'PODES',
            'Petugas Lapangan Sensus Papi' => 'PAPI',
            'Pendataan Lapangan Listing Vrest Umk' => 'VREST_UMK',
            'Pendataan Lapangan Survei Ibs Tahunan' => 'STPIM',
            'Pendataan Lapangan Updating Direktori Usaha Perusahaan' => 'DUPE',
            'Pendataan Lapangan Updating Profil Pasar' => 'PROFIL_PASAR',
            'Pendataan Lapangan Keuangan Desa' => 'KEUDES',
            'Pendataan Lapangan Survei Konstruksi Tahunan' => 'SKTH',
            'Pendataan Lapangan Listing Vimk24 Tahunan' => 'VIMK24_LISTING',
            'Pendataan Lapangan Survei Triwulanan Penggalian Badan Hukum' => 'STPBH',
            'Pendataan Lapangan Hpg' => 'HPG',
            'Pengolahan Data Listing Vimk24 Tahunan' => 'VIMK24_LISTING',
            'Pengolahan Ubinan Palawija' => 'UBINAN_PLWJ',
            'Pendataan Lapangan Vimk24 Tahunan' => 'VIMK24',
            'Pendataan Lapangan Sklnp' => 'SKLNP',
            'Pendataan Lapangan Hp Kab' => 'HP_KAB',
            'Pendataan Lapangan Updating Ubinan Palawija' => 'UBINAN_UPD',
            'Pendataan Lapangan Sksppi' => 'SKSPPI',
            'Pendataan Lapangan Skps' => 'SKPS',
            'Pendataan Lapangan Vimk24 Triwulanan' => 'VIMK24_TW',
            'Pendataan Lapangan Susenas' => 'SUSENAS',
            'Pengolahan Data Sensus Ekonomi' => 'SE2025',
            'Survei Konstruksi Triwulanan' => 'SKTR',
            'Survei Angkatan Kerja Nasional' => 'SAKERNAS',
            'Survei Biaya Hidup' => 'SBH',
            'Survei Industri Mikro dan Kecil' => 'VIMK',
            'Penyusunan Tabel Input-Output' => 'IO',
            'Pendataan Potensi Desa' => 'PODES',
            'Survei Komoditas Strategis' => 'SKS',
        ];

        $kegiatans = Kegiatan::whereNull('short_name')->get();
        foreach ($kegiatans as $k) {
            $shortName = null;
            foreach ($mapping as $keyword => $code) {
                if (stripos($k->nama, $keyword) !== false) {
                    $shortName = $code;
                    break;
                }
            }
            if (!$shortName) {
                // Fallback: ambil 3 kata pertama, uppercase
                $words = explode(' ', $k->nama);
                $shortName = strtoupper(implode('_', array_slice($words, -2)));
            }
            $k->update(['short_name' => $shortName]);
        }

        // Set default format_spk for all kegiatan that don't have one
        Kegiatan::whereNull('format_spk')->update([
            'format_spk' => 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}'
        ]);

        $this->command->info('Short names assigned to ' . $kegiatans->count() . ' kegiatan. Format SPK set for all.');
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Kecamatan;
use App\Models\Desa;
use App\Models\Mitra;
use Illuminate\Support\Facades\DB;

class KecamatanDesaSeeder extends Seeder
{
    public function run(): void
    {
        $dataFile = __DIR__ . '/data_master_tasik.php';
        if (!file_exists($dataFile)) {
            $this->command->error('File data_master_tasik.php tidak ditemukan!');
            return;
        }

        $rowsData = require $dataFile;
        $masterMap = [];

        DB::beginTransaction();
        try {
            foreach ($rowsData as $row) {
                $kdkec = trim((string)($row[0] ?? ''));
                $kddesa = trim((string)($row[1] ?? ''));
                $nmkec = trim((string)($row[2] ?? ''));
                $nmdesa = trim((string)($row[3] ?? ''));

                if (empty($kdkec) || empty($kddesa) || empty($nmkec) || empty($nmdesa)) {
                    continue;
                }

                $kdkecPadded = str_pad($kdkec, 3, '0', STR_PAD_LEFT);
                $kddesaPadded = str_pad($kddesa, 3, '0', STR_PAD_LEFT);
                $kodeFull = '3206' . $kdkecPadded . $kddesaPadded;

                $kecamatan = Kecamatan::firstOrCreate(
                    ['kode_kec' => $kdkecPadded],
                    ['nama' => strtoupper($nmkec)]
                );

                Desa::updateOrCreate(
                    ['kode_full' => $kodeFull],
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'kode_desa' => $kddesaPadded,
                        'nama' => strtoupper($nmdesa),
                    ]
                );

                $masterMap["{$kdkecPadded}-{$kddesaPadded}"] = [
                    'kecamatan' => strtoupper($nmkec),
                    'desa' => strtoupper($nmdesa),
                ];
            }

            // Extended BPS Maps for all regions
            $bpsMaps = [
                '78' => [ // Kota Tasikmalaya
                    '010' => ['KAWALU', ['004' => 'GUNUNG GEDE', '005' => 'TALAGASARI', '008' => 'KARSAMENAK']],
                    '020' => ['TAMANSARI', ['001' => 'SETIAMULYA', '003' => 'SUKAHURIP', '005' => 'TAMANJAYA', '006' => 'MULYASARI', '008' => 'SUKAWENING']],
                    '030' => ['CIHIDEUNG', ['003' => 'YUDANAGARA', '004' => 'NAGARAWANGI', '005' => 'ARGASARI', '008' => 'TUGUJAYA']],
                    '031' => ['PURBARATU', ['001' => 'SINGKUP', '002' => 'SUKAMENAK']],
                    '040' => ['TAWANG', ['001' => 'CIKALANG', '002' => 'KAHURIPAN', '003' => 'EMPANGSARI', '004' => 'TAWANGSARI']],
                    '050' => ['CIPEDES', ['001' => 'CIPEDES', '003' => 'NAGARASARI', '005' => 'PANGLAYUNGAN']],
                    '060' => ['MANGKUBUMI', ['002' => 'CIPAWITRA', '003' => 'SAMBONGJAYA', '005' => 'LINGGAJAYA', '006' => 'MANGKUBUMI', '008' => 'KARIKIL']],
                    '070' => ['INDIHIANG', ['007' => 'INDIHIANG', '012' => 'SIRNAGALIH', '013' => 'SUKAMAJUKALER']],
                    '071' => ['BUNGURSARI', ['002' => 'SUKARINDIK', '007' => 'BUNGURSARI']],
                    '080' => ['CIBEUREUM', ['002' => 'CIAKAR', '003' => 'SETIARATU']],
                ],
                '05' => [ // Garut
                    '140' => ['CILAWU', ['004' => 'MEKARSARI']],
                    '160' => ['BAYONGBONG', ['004' => 'BABAKAN GARUT']],
                    '182' => ['TAROGONG KALER', ['009' => 'CIMANGANTEN']],
                    '200' => ['KARANGPAWITAN', ['014' => 'SUKAMULYA', '019' => 'SUCI KALER']],
                    '210' => ['GARUT KOTA', ['005' => 'PAPANDAK']],
                    '310' => ['CIBATU', ['023' => 'CIJARINGAO']],
                ],
                '07' => [ // Ciamis
                    '180' => ['CIAMIS', ['008' => 'JETAK']],
                    '210' => ['CIAMIS', ['008' => 'WARGA', '011' => 'KERTASARI']],
                    '220' => ['CIKONENG', ['003' => 'CIKONENG']],
                    '221' => ['SINDANGKASIH', ['006' => 'SINDANGKASIH']],
                    '230' => ['CIHAURBEUTI', ['001' => 'SUKAMULYA', '003' => 'SUKAHURIP']],
                ],
                '04' => [ // Kab. Bandung
                    '060' => ['CIPARAY', ['006' => 'NAGROG']],
                    '080' => ['PASEH', ['010' => 'CIPEDES']],
                    '280' => ['BOJONGSOANG', ['005' => 'BUAHBATU']],
                    '290' => ['CIMENYAN', ['004' => 'BUAHBATU']],
                ],
                '10' => [ // Majalengka
                    '020' => ['BANTARUJEG', ['016' => 'BANTARUJEG']],
                ],
                '16' => [ // Bekasi
                    '071' => ['CIBITUNG', ['010' => 'TELAGAMURNI']],
                ],
                '01' => [ // Bogor
                    '200' => ['CITEUREUP', ['007' => 'SANJA']],
                ],
                '17' => [ // KBB
                    '090' => ['PADALARANG', ['007' => 'KERTAMULYA']],
                ],
                '09' => [ // Kuningan
                    '010' => ['KADUGEDE', ['015' => 'KADUGEDE']],
                ],
                '12' => [ // Indramayu
                    '080' => ['INDRAMAYU', ['007' => 'MARGADADI']],
                ],
                '15' => [ // Karawang
                    '150' => ['MAJALAYA', ['009' => 'BENGLE']],
                ],
                '73' => [ // Makassar / Gowa / Palopo
                    '060' => ['SOMBA OPU', ['004' => 'TOMBOLO']],
                    '200' => ['WARA', ['001' => 'BARA']],
                ],
            ];

            // Backfill existing Mitra data
            $mitras = Mitra::all();
            foreach ($mitras as $mitra) {
                $code = trim((string)$mitra->kode_alamat);
                $kdkec = null;
                $kddesa = null;

                if (strlen($code) === 8 && str_starts_with($code, '06')) {
                    $kdkec = substr($code, 2, 3);
                    $kddesa = substr($code, 5, 3);
                } elseif (strlen($code) === 10 && str_starts_with($code, '3206')) {
                    $kdkec = substr($code, 4, 3);
                    $kddesa = substr($code, 7, 3);
                }

                $updateData = [];

                if ($kdkec && $kddesa && isset($masterMap["{$kdkec}-{$kddesa}"])) {
                    $match = $masterMap["{$kdkec}-{$kddesa}"];
                    $updateData['kecamatan'] = $match['kecamatan'];
                    $updateData['desa'] = $match['desa'];
                    $updateData['kode_alamat'] = '3206' . $kdkec . $kddesa;
                    $updateData['kabupaten_kota'] = 'Kabupaten Tasikmalaya';
                } else {
                    $prefix2 = substr($code, 0, 2);
                    $prefix4 = substr($code, 0, 4);
                    $subKec = strlen($code) >= 5 ? substr($code, 2, 3) : '';
                    $subDesa = strlen($code) >= 8 ? substr($code, 5, 3) : '';

                    // Match Kabupaten/Kota
                    if ($prefix2 === '78' || $prefix4 === '3278' || str_contains(strtoupper($mitra->alamat ?? ''), 'KOTA TASIKMALAYA')) {
                        $updateData['kabupaten_kota'] = 'Kota Tasikmalaya';
                    } elseif ($prefix2 === '05' || $prefix4 === '3205' || str_contains(strtoupper($mitra->alamat ?? ''), 'GARUT')) {
                        $updateData['kabupaten_kota'] = 'Kabupaten Garut';
                    } elseif ($prefix2 === '07' || $prefix4 === '3207' || str_contains(strtoupper($mitra->alamat ?? ''), 'CIAMIS')) {
                        $updateData['kabupaten_kota'] = 'Kabupaten Ciamis';
                    } elseif ($prefix2 === '04' || $prefix4 === '3204') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Bandung';
                    } elseif ($prefix2 === '01' || $prefix4 === '3201') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Bogor';
                    } elseif ($prefix2 === '09' || $prefix4 === '3209') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Kuningan';
                    } elseif ($prefix2 === '10' || $prefix4 === '3210' || str_contains(strtoupper($mitra->alamat ?? ''), 'MAJALENGKA')) {
                        $updateData['kabupaten_kota'] = 'Kabupaten Majalengka';
                    } elseif ($prefix2 === '12' || $prefix4 === '3212' || str_contains(strtoupper($mitra->alamat ?? ''), 'INDRAMAYU')) {
                        $updateData['kabupaten_kota'] = 'Kabupaten Indramayu';
                    } elseif ($prefix2 === '15' || $prefix4 === '3215') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Karawang';
                    } elseif ($prefix2 === '16' || $prefix4 === '3216') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Bekasi';
                    } elseif ($prefix2 === '17' || $prefix4 === '3217') {
                        $updateData['kabupaten_kota'] = 'Kabupaten Bandung Barat';
                    } else {
                        $updateData['kabupaten_kota'] = 'Kabupaten Tasikmalaya';
                    }

                    // Match BPS Maps for external regions
                    if (isset($bpsMaps[$prefix2][$subKec])) {
                        $kecInfo = $bpsMaps[$prefix2][$subKec];
                        $updateData['kecamatan'] = $kecInfo[0];
                        $updateData['desa'] = $kecInfo[1][$subDesa] ?? ('DESA ' . $subDesa);
                    } else {
                        // Regex fallback from address text
                        $text = ($mitra->alamat_detail ?? '') . ' ' . ($mitra->alamat ?? '');
                        if (preg_match('/(?:kec|kecamatan)[\.\s:]+([A-Za-z\s]+?)(?:kab|kota|prov|rt|rw|,|$)/i', $text, $km)) {
                            $updateData['kecamatan'] = strtoupper(trim($km[1]));
                        }
                        if (preg_match('/(?:ds|desa|kel|kelurahan)[\.\s:]+([A-Za-z\s]+?)(?:kec|kab|kota|rt|rw|,|$)/i', $text, $dm)) {
                            $updateData['desa'] = strtoupper(trim($dm[1]));
                        }
                    }

                    // Dummy mitras fallback
                    if (str_contains($mitra->nama, 'Dummy 2025')) {
                        if (str_contains($mitra->alamat, 'Singaparna')) {
                            $updateData['kecamatan'] = 'SINGAPARNA';
                            $updateData['desa'] = 'SINGAPARNA';
                        } elseif (str_contains($mitra->alamat, 'Ciawi')) {
                            $updateData['kecamatan'] = 'CIAWI';
                            $updateData['desa'] = 'CIAWI';
                        } elseif (str_contains($mitra->alamat, 'Manonjaya')) {
                            $updateData['kecamatan'] = 'MANONJAYA';
                            $updateData['desa'] = 'MANONJAYA';
                        }
                    }
                }

                if (empty($mitra->alamat_detail) && !empty($mitra->alamat)) {
                    $updateData['alamat_detail'] = $mitra->alamat;
                }

                if (!empty($updateData)) {
                    $mitra->update($updateData);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}

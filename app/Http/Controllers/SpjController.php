<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Support\SpjParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SpjController extends Controller
{
    public function import()
    {
        $kegiatans = self::getLatestKegiatans();
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('bulan_angka')->get();

        return view('spj.import', compact('kegiatans', 'periodes'));
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file_spj' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('file_spj');
        $path = $file->store('temp_spj');

        try {
            $fullPath = Storage::path($path);
            $parsed = SpjParser::parse($fullPath);

            if (empty($parsed['items'])) {
                return back()->withErrors(['file_spj' => 'Tidak dapat menemukan tabel PPL di PDF. Pastikan format SPJ sesuai.'])->withInput();
            }

            $request->session()->put('spj_parsed', $parsed);
            $request->session()->put('spj_file_path', $path);

            $kegiatans = self::getLatestKegiatans();
            $periodes = Periode::orderBy('tahun', 'desc')->orderBy('bulan_angka')->get();

            return view('spj.import', compact('kegiatans', 'periodes'))
                ->with('parsed', $parsed)
                ->with('preview', true);
        } catch (\Exception $e) {
            return back()->withErrors(['file_spj' => 'Gagal membaca PDF: ' . $e->getMessage()])->withInput();
        }
    }

    public function importProcess(Request $request)
    {
        $request->validate([
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'periode_id' => 'required|exists:periodes,id',
        ]);

        $parsed = $request->session()->get('spj_parsed');
        if (!$parsed || empty($parsed['items'])) {
            return redirect()->route('spj.import')->withErrors(['error' => 'Data SPJ tidak ditemukan. Silakan upload ulang.']);
        }

        $kegiatan = Kegiatan::find($request->kegiatan_id);
        $periode = Periode::find($request->periode_id);
        $imported = 0;
        $createdMitra = 0;

        foreach ($parsed['items'] as $item) {
            $nama = trim($item['nama']);
            if (empty($nama)) continue;

            $mitra = Mitra::whereRaw('LOWER(nama) = LOWER(?)', [$nama])->first();
            if (!$mitra) {
                $mitra = Mitra::create([
                    'nama' => $nama,
                    'id_sobat' => 'SPJ-' . strtoupper(substr(md5($nama . time()), 0, 8)),
                    'posisi' => $item['jabatan'] ?: 'Petugas Pendataan Lapangan (PPL Survei)',
                    'pekerjaan' => 'Petugas Pendataan Lapangan',
                    'jk' => 'P',
                ]);
                $createdMitra++;
            }

            $bruto = $item['bruto'] ?? 0;
            $pajak = $item['pajak'] ?? 0;
            $volume = $item['volume'] ?? 1;
            $biayaSatuan = $item['biaya_satuan'] ?? ($volume > 0 ? $bruto / $volume : $bruto);
            $satuan = 'Dokumen';

            AlokasiHonor::updateOrCreate(
                [
                    'mitra_id' => $mitra->id,
                    'periode_id' => $periode->id,
                    'kegiatan_id' => $kegiatan->id,
                ],
                [
                    'nominal' => $bruto,
                    'pajak' => $pajak,
                    'volume' => $volume,
                    'satuan' => $satuan,
                    'tarif_satuan' => $biayaSatuan,
                ]
            );
            $imported++;
        }

        $filePath = $request->session()->get('spj_file_path');
        if ($filePath && file_exists(Storage::path($filePath))) {
            Storage::delete($filePath);
        }
        $request->session()->forget(['spj_parsed', 'spj_file_path']);

        return redirect()->route('monitoring.index')
            ->with('success', "Import SPJ berhasil! {$imported} alokasi terimpor, {$createdMitra} mitra baru dibuat.");
    }

    protected static function getLatestKegiatans()
    {
        return Kegiatan::whereIn('id', function ($query) {
            $query->select(DB::raw('MAX(id)'))
                ->from('kegiatans')
                ->groupBy('nama');
        })->orderBy('nama')->get();
    }
}

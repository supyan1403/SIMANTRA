<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\DocumentTemplate;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SpkController extends Controller
{
    protected array $bulanNama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->role === 'admin';
        $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);

        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $tahun = $request->tahun ?? Periode::whereHas('alokasiHonors')->orderBy('tahun', 'desc')->value('tahun') ?? date('Y');

        $monthOptions = $this->bulanNama;
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $bulanAwal = max(1, min(12, $bulanAwal));
        $bulanAkhir = max(1, min(12, $bulanAkhir));
        if ($bulanAkhir < $bulanAwal) { $bulanAkhir = $bulanAwal; }

        $bidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->bidang_id ?? null);
        if ($bidangId === '' || $bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
        $kategoriKegiatan = $request->kategori_kegiatan ?? 'umum'; // sensus, survei, umum
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $search = trim($request->search ?? '');

        $bidangOptions = $isAdmin ? Bidang::orderBy('nama')->get() : Bidang::where('id', $user->bidang_id)->get();
        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))->orderBy('nama')->get();
        $templates = DocumentTemplate::where('is_active', true)->get();

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        // Query Alokasi Honor per Mitra
        $query = AlokasiHonor::with(['mitra', 'kegiatan.bidang', 'periode'])
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('mitra', fn($m) => $m->where('nama', 'like', "%{$search}%")->orWhere('id_sobat', 'like', "%{$search}%"));
            });

        $alokasis = $query->get();

        // Grouping per Mitra
        $spkList = $alokasis->groupBy('mitra_id')->map(function ($items, $mitraId) {
            $mitra = $items->first()->mitra;
            $totalHonor = (float) $items->sum('nominal');
            $kegiatans = $items->pluck('kegiatan.nama')->unique()->values();

            return (object) [
                'mitra_id' => $mitraId,
                'mitra' => $mitra,
                'total_honor' => $totalHonor,
                'total_kegiatan' => $kegiatans->count(),
                'list_kegiatan' => $kegiatans->join(', '),
                'items' => $items,
            ];
        })->values();

        $selectedTemplate = $request->template_id ? DocumentTemplate::find($request->template_id) : null;

        return view('spk.index', compact(
            'tahunList', 'tahun', 'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId', 'search',
            'jenisDokumen', 'kategoriKegiatan', 'nomorAwal', 'templates', 'selectedTemplate',
            'spkList'
        ));
    }

    public function cetakUtama(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorAwal = (int) ($request->nomor_awal ?? 1);

        $templateId = $request->template_id;
        $jenisDokumen = 'spk';
        if ($templateId) {
            $docTmpl = DocumentTemplate::find($templateId);
            if ($docTmpl && $docTmpl->jenis_dokumen) {
                $jenisDokumen = $docTmpl->jenis_dokumen;
            }
        }

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $kegiatanId = $request->kegiatan_id ?: null;

        $items = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
            ->where('mitra_id', $mitraId)
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->get();

        $totalHonor = (float) $items->sum('nominal');
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;
        
        if ($jenisDokumen === 'bast') {
            $nomorDokumen = sprintf("B-%04d/BPS/3206/BAST/%02d/%s", $nomorAwal, $bulanAwal, $tahun);
            $batchList = collect([(object)[
                'mitra' => $mitra,
                'nomor_dokumen' => $nomorDokumen,
                'items' => $items,
                'total_honor' => $totalHonor,
            ]]);
            return view('spk.template_bast', compact('batchList', 'tahun', 'periodeLabel'));
        }

        $nomorDokumen = sprintf("B-%04d/BPS/3206/SPK/%02d/%s", $nomorAwal, $bulanAwal, $tahun);

        return view('spk.template_utama', compact('mitra', 'items', 'totalHonor', 'tahun', 'periodeLabel', 'nomorDokumen'));
    }

    public function cetakLampiran(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $kegiatanId = $request->kegiatan_id ?: null;

        $items = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
            ->where('mitra_id', $mitraId)
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->get();

        $totalHonor = (float) $items->sum('nominal');
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        return view('spk.template_lampiran', compact('mitra', 'items', 'totalHonor', 'tahun', 'periodeLabel'));
    }

    public function cetakMassal(Request $request)
    {
        $mitraIds = $request->mitra_ids ?? [];
        if (empty($mitraIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mitra untuk dicetak secara massal.');
        }

        $templateId = $request->template_id;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
        if ($templateId) {
            $docTmpl = DocumentTemplate::find($templateId);
            if ($docTmpl && $docTmpl->jenis_dokumen) {
                $jenisDokumen = $docTmpl->jenis_dokumen;
            }
        }

        $kategoriKegiatan = $request->kategori_kegiatan ?? 'umum';
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorStart = (int) ($request->nomor_awal ?? 1);
        $kegiatanId = $request->kegiatan_id ?: null;

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        $batchList = collect();
        $counter = $nomorStart;

        foreach ($mitraIds as $mId) {
            $mitra = Mitra::find($mId);
            if (!$mitra) continue;

            $items = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                ->where('mitra_id', $mId)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->get();

            if ($items->isEmpty()) continue;

            $prefix = strtoupper($jenisDokumen);
            $nomorDoc = sprintf("B-%04d/BPS/3206/%s/%02d/%s", $counter, $prefix, $bulanAwal, $tahun);
            $totalHonor = (float) $items->sum('nominal');

            $batchList->push((object) [
                'mitra' => $mitra,
                'items' => $items,
                'total_honor' => $totalHonor,
                'nomor_dokumen' => $nomorDoc,
            ]);

            $counter++;
        }

        $viewName = ($jenisDokumen === 'bast') ? 'spk.template_bast' : 'spk.cetak_massal';

        return view($viewName, compact('batchList', 'jenisDokumen', 'kategoriKegiatan', 'tahun', 'periodeLabel'));
    }

    public function downloadDocx(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $templateId = $request->template_id;

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $kegiatanId = $request->kegiatan_id ?: null;
        $items = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
            ->where('mitra_id', $mitraId)
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->get();

        $totalHonor = (float) $items->sum('nominal');
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;
        $nomorDokumen = sprintf("1001/PPK/SPK/%02d/%s", $bulanAwal, $tahun);

        $templatePath = base_path('File SPK (Sumber -2).docx');
        if ($templateId) {
            $docTmpl = DocumentTemplate::find($templateId);
            if ($docTmpl && $docTmpl->file_path && Storage::disk('public')->exists($docTmpl->file_path)) {
                $templatePath = Storage::disk('public')->path($docTmpl->file_path);
            }
        }

        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Berkas template DOCX rujukan tidak ditemukan.');
        }

        $tempFile = sys_get_temp_dir() . '/spk_' . time() . '_' . $mitra->id . '.docx';
        copy($templatePath, $tempFile);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) === true) {
            $xml = $zip->getFromName('word/document.xml');

            $formattedTotalHonor = number_format($totalHonor, 0, ',', '.');
            $terbilangText = $this->terbilang($totalHonor);

            $item1Nama = isset($items[0]) ? $items[0]->kegiatan->nama : '-';
            $item1Nominal = isset($items[0]) ? 'Rp ' . number_format($items[0]->nominal, 0, ',', '.') : 'Rp 0';
            $item1Mak = isset($items[0]) ? ($items[0]->kegiatan->kode_mata_anggaran ?? '054.01.GG.2903.BMA.009.005.A.521213') : '';

            $item2Nama = isset($items[1]) ? $items[1]->kegiatan->nama : '';
            $item2Nominal = isset($items[1]) ? 'Rp ' . number_format($items[1]->nominal, 0, ',', '.') : '';
            $item2Mak = isset($items[1]) ? ($items[1]->kegiatan->kode_mata_anggaran ?? '') : '';
            $item2Vol = isset($items[1]) ? '1' : '';
            $item2Sat = isset($items[1]) ? 'dokumen' : '';
            $item2Periode = isset($items[1]) ? $periodeLabel : '';

            $pekerjaanVal = $mitra->pekerjaan_clean;
            $alamatVal = $mitra->alamat_clean;

            $replacements = [
                // 1. Clean MERGEFIELD artifact text in Header Lampiran
                'MERGEFIELD Nama_Petugas LINA KARLINA' => strtoupper($mitra->nama),
                'MERGEFIELD Nama_Petugas' => '',
                'MERGEFIELD' => '',

                // 2. Mitra Details
                'LINA KARLINA' => strtoupper($mitra->nama),
                '${NAMA_MITRA}' => strtoupper($mitra->nama),
                '${NAMA}' => strtoupper($mitra->nama),

                'Lainnya/ Belum Bekerja' => $pekerjaanVal,
                '${PEKERJAAN}' => $pekerjaanVal,

                'Kp. Pameungpeuk RT/RW : 24/03 Desa Sukarasa Kec. Salawu' => $alamatVal,
                '${ALAMAT}' => $alamatVal,

                // 3. Document Numbering
                '1001/PPK/SPK/03/2024' => $nomorDokumen,
                '${NOMOR_SPK}' => $nomorDokumen,

                // 4. Annex Table Row 1 (Item 1)
                'pencacahan survei harga kemahalan konstruksi' => $item1Nama,
                'Rp. 60000,00' => $item1Nominal,
                'Rp. 900.000, 00' => $item1Nominal,
                '054.01.GG.2903.BMA.009.005.A.521213' => $item1Mak,

                // 5. Annex Table Row 2 (Item 2) - Cleared if only 1 item
                'Pencacahan survei harga konsumen perdesaan (hkd) non pns' => $item2Nama,
                'Rp. 65000,00' => $item2Nominal,
                'Rp. 260.000, 00' => $item2Nominal,

                // 6. Totals & Terbilang
                'Satu Juta Seratus Enam Puluh Ribu Rupiah' => $terbilangText,
                '${TERBILANG}' => $terbilangText,

                'Rp. 1.160.000, 00' => 'Rp ' . $formattedTotalHonor,
                'Rp. 1.160.000,00' => 'Rp ' . $formattedTotalHonor,
                'Rp. 1.160.000' => 'Rp ' . $formattedTotalHonor,
                '${TOTAL_HONOR}' => 'Rp ' . $formattedTotalHonor,

                // 7. Clean up default decimal values in empty rows
                'Rp. ,00' => '',
                'Rp., 00' => '',
                'Rp. 00' => '',
                'Rp.  00' => '',
            ];

            foreach ($replacements as $key => $val) {
                $xml = str_replace($key, htmlspecialchars($val, ENT_XML1, 'UTF-8'), $xml);
            }

            // DOM manipulation to ensure Row 5 (Item 2) is completely blank if mitra has only 1 activity
            if (!isset($items[1])) {
                $dom = new \DOMDocument();
                @$dom->loadXML($xml);
                $xpath = new \DOMXPath($dom);
                $xpath->registerNamespace('w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');

                $tables = $xpath->query('//w:tbl');
                if ($tables->length >= 2) {
                    $tbl2 = $tables->item(1);
                    $rows = $xpath->query('.//w:tr', $tbl2);
                    if ($rows->item(4) !== null) { // Row 5 (Index 4) is 2nd activity item
                        $cells = $xpath->query('.//w:tc', $rows->item(4));
                        foreach ($cells as $cIdx => $cell) {
                            if ($cIdx > 0) { // Keep row index '2', clear all other cells
                                $textNodes = $xpath->query('.//w:t', $cell);
                                foreach ($textNodes as $tn) {
                                    $tn->nodeValue = '';
                                }
                            }
                        }
                    }
                }
                $xml = $dom->saveXML();
            }

            $zip->addFromString('word/document.xml', $xml);
            $zip->close();
        }

        $downloadName = 'SPK_' . preg_replace('/[^a-zA-Z0-9]/', '_', $mitra->nama) . '.docx';
        return response()->download($tempFile, $downloadName)->deleteFileAfterSend(true);
    }

    private function terbilang($angka)
    {
        $angka = abs((float)$angka);
        $baca = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) return ' ' . $baca[(int)$angka];
        if ($angka < 20) return $this->terbilang($angka - 10) . ' Belas';
        if ($angka < 100) return $this->terbilang($angka / 10) . ' Puluh' . $this->terbilang($angka % 10);
        if ($angka < 200) return ' Seratus' . $this->terbilang($angka - 100);
        if ($angka < 1000) return $this->terbilang($angka / 100) . ' Ratus' . $this->terbilang($angka % 100);
        if ($angka < 2000) return ' Seribu' . $this->terbilang($angka - 1000);
        if ($angka < 1000000) return $this->terbilang($angka / 1000) . ' Ribu' . $this->terbilang($angka % 1000);
        if ($angka < 1000000000) return $this->terbilang($angka / 1000000) . ' Juta' . $this->terbilang($angka % 1000000);
        return trim((string)$angka) . ' Rupiah';
    }

    public function templateIndex()
    {
        $templates = DocumentTemplate::orderBy('created_at', 'desc')->get();
        return view('spk.templates.index', compact('templates'));
    }

    public function templateStore(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_dokumen' => 'required|in:spk,bast',
            'kategori_kegiatan' => 'required|in:sensus,survei,umum',
            'file_template' => 'nullable|file|mimes:docx,doc,pdf,txt|max:5120',
            'deskripsi' => 'nullable|string',
        ]);

        $filePath = null;
        if ($request->hasFile('file_template')) {
            $filePath = $request->file('file_template')->store('document-templates', 'public');
        }

        DocumentTemplate::create([
            'nama' => $request->nama,
            'jenis_dokumen' => $request->jenis_dokumen,
            'kategori_kegiatan' => $request->kategori_kegiatan,
            'file_path' => $filePath,
            'deskripsi' => $request->deskripsi,
            'is_active' => true,
        ]);

        return redirect()->route('spk.templates.index')->with('success', 'Template Dokumen berhasil ditambahkan.');
    }

    public function templateUpdate(Request $request, $id)
    {
        $template = DocumentTemplate::findOrFail($id);

        $request->validate([
            'nama' => 'required|string|max:255',
            'jenis_dokumen' => 'required|in:spk,bast',
            'kategori_kegiatan' => 'required|in:sensus,survei,umum',
            'file_template' => 'nullable|file|mimes:docx,doc,pdf,txt|max:5120',
            'deskripsi' => 'nullable|string',
        ]);

        if ($request->hasFile('file_template')) {
            if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
                Storage::disk('public')->delete($template->file_path);
            }
            $template->file_path = $request->file('file_template')->store('document-templates', 'public');
        }

        $template->nama = $request->nama;
        $template->jenis_dokumen = $request->jenis_dokumen;
        $template->kategori_kegiatan = $request->kategori_kegiatan;
        $template->deskripsi = $request->deskripsi;
        $template->save();

        return redirect()->route('spk.templates.index')->with('success', 'Template Dokumen berhasil diperbarui.');
    }

    public function templateDestroy($id)
    {
        $template = DocumentTemplate::findOrFail($id);
        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            Storage::disk('public')->delete($template->file_path);
        }
        $template->delete();

        return redirect()->route('spk.templates.index')->with('success', 'Template Dokumen berhasil dihapus.');
    }
}

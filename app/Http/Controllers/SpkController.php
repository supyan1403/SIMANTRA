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

        return view('spk.index', compact(
            'tahunList', 'tahun', 'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId', 'search',
            'jenisDokumen', 'kategoriKegiatan', 'nomorAwal', 'templates',
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

        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
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

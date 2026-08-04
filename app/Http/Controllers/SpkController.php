<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Services\SbmlHelper;
use Illuminate\Http\Request;

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
        $search = trim($request->search ?? '');

        $bidangOptions = $isAdmin ? Bidang::orderBy('nama')->get() : Bidang::where('id', $user->bidang_id)->get();
        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))->orderBy('nama')->get();

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
            'spkList'
        ));
    }

    public function cetakUtama(Request $request, $mitraId)
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

        return view('spk.template_utama', compact('mitra', 'items', 'totalHonor', 'tahun', 'periodeLabel'));
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
}

<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Periode;
use App\Models\AlokasiHonor;
use App\Models\Sbml;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;

class LandingController extends Controller
{
    protected array $bulanNama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $user = auth()->user();

        // Macro Filters
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $defaultTahun = Periode::whereHas('alokasiHonors')->orderBy('tahun', 'desc')->value('tahun') ?? $tahunList->first() ?? date('Y');
        
        $tahun = $request->tahun ?? $defaultTahun;

        $monthOptions = [];
        foreach ($this->bulanNama as $angka => $nm) {
            $monthOptions[$angka] = $nm;
        }

        $bulanAwal  = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $bulanAwal  = max(1, min(12, $bulanAwal));
        $bulanAkhir = max(1, min(12, $bulanAkhir));
        if ($bulanAkhir < $bulanAwal) { $bulanAkhir = $bulanAwal; }

        $bidangId = $request->bidang_id ?: null;
        if ($bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;

        // Periode Macro
        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        // Scoping Query
        $honorQuery = fn() => AlokasiHonor::query()
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)));

        $realisasiHonor = (float) $honorQuery()->sum('nominal');
        $totalTransaksi = $honorQuery()->count();

        $paguQuery = fn() => Kegiatan::where('tahun', $tahun)
            ->when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
            ->when($kegiatanId, fn($q) => $q->where('id', $kegiatanId));
        $paguMataAnggaran = (float) $paguQuery()->sum('total');
        $sisaAnggaran = $paguMataAnggaran - $realisasiHonor;

        $sbmlQuery = Sbml::query()->whereIn('periode_id', $periodeIds);
        if ($bidangId || $kegiatanId) {
            $scopeMitraIds = collect($honorQuery()->pluck('mitra_id')->unique());
            $sbmlQuery->whereIn('mitra_id', $scopeMitraIds);
        }
        $paguSBML = (float) $sbmlQuery->sum('nominal');

        $bidangOptions = Bidang::orderBy('nama')->get();
        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
            ->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);

        $honorPerBulan = $this->honorPerBulan($periodeIds, $bulanAwal, $bulanAkhir, $bidangId, $kegiatanId);
        $honorPerBidang = $this->honorPerBidang($periodeIds, $kegiatanId);

        $totalMitra = Mitra::count();
        $totalOperator = User::where('role', 'operator')->count();

        return view('landing', compact(
            'user',
            'tahunList', 'tahun',
            'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId',
            'paguMataAnggaran', 'realisasiHonor', 'sisaAnggaran', 'paguSBML',
            'totalTransaksi', 'totalMitra', 'totalOperator',
            'honorPerBulan', 'honorPerBidang'
        ));
    }

    protected function honorPerBulan($periodeIds, $awal, $akhir, $bidangId, $kegiatanId)
    {
        $base = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->with('periode')
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)))
            ->get();

        $map = $base->groupBy(fn($a) => $a->periode->bulan_angka)->map(fn($it) => (float) $it->sum('nominal'));

        $result = [];
        for ($m = $awal; $m <= $akhir; $m++) {
            $result[] = (object) ['bulan' => $this->bulanNama[$m] ?? '', 'bulan_angka' => $m, 'total' => $map->get($m, 0)];
        }
        return collect($result);
    }

    protected function honorPerBidang($periodeIds, $kegiatanId)
    {
        return Bidang::orderBy('nama')->get()->map(function ($b) use ($periodeIds, $kegiatanId) {
            $sum = AlokasiHonor::whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $b->id))
                ->sum('nominal');
            return (object) ['nama' => $b->nama, 'total' => (float) $sum];
        });
    }
}

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
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    protected array $bulanNama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        // Enforce strict public context: reset any prior session when visiting public landing
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        $user = null;

        // Macro Filters
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $defaultTahun = Periode::whereHas('alokasiHonors')->orderBy('tahun', 'desc')->value('tahun') ?? $tahunList->first() ?? date('Y');
        
        $tahun = $request->tahun ?? $defaultTahun;

        $monthOptions = [];
        foreach ($this->bulanNama as $angka => $nm) {
            $monthOptions[$angka] = $nm;
        }

        $bulanInput = $request->bulan ?? $request->bulan_pencairan;
        $bulanPencairan = [];

        if (is_string($bulanInput)) {
            if (str_contains($bulanInput, '-')) {
                [$start, $end] = explode('-', $bulanInput, 2);
                $start = max(1, min(12, (int) $start));
                $end = max(1, min(12, (int) $end));
                $bulanPencairan = range(min($start, $end), max($start, $end));
            } elseif (str_contains($bulanInput, ',')) {
                $bulanPencairan = array_map('intval', explode(',', $bulanInput));
            } elseif (is_numeric($bulanInput)) {
                $bulanPencairan = [(int) $bulanInput];
            }
        } elseif (is_array($bulanInput)) {
            $bulanPencairan = array_map('intval', $bulanInput);
        }

        if (empty($bulanPencairan)) {
            if ($request->filled('bulan_awal') && $request->filled('bulan_akhir')) {
                $bAwal = max(1, min(12, (int) $request->bulan_awal));
                $bAkhir = max(1, min(12, (int) $request->bulan_akhir));
                $bulanPencairan = range(min($bAwal, $bAkhir), max($bAwal, $bAkhir));
            } else {
                $bulanPencairan = range(1, 12);
            }
        }

        $bulanPencairan = array_values(array_unique(array_filter($bulanPencairan, fn($b) => $b >= 1 && $b <= 12)));
        if (empty($bulanPencairan)) {
            $bulanPencairan = range(1, 12);
        }

        $bidangId = $request->bidang_id ?: null;
        if ($bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;

        // Periode Macro matching bulanPencairan
        $periodeIds = Periode::where('tahun', $tahun)
            ->whereIn('bulan_angka', $bulanPencairan)
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

        $honorPerBulan = $this->honorPerBulanSelected($periodeIds, $bulanPencairan, $bidangId, $kegiatanId);
        $honorPerBidang = $this->honorPerBidang($periodeIds, $kegiatanId);

        $totalMitra = Mitra::count();
        $totalOperator = User::where('role', 'operator')->count();

        // 3 Diagram Batang Mitra
        $allocatedMitraIds = $honorQuery()->pluck('mitra_id')->unique()->filter()->values();
        $sudahDipekerjakanCount = $allocatedMitraIds->count();
        $belumDipekerjakanCount = max(0, $totalMitra - $sudahDipekerjakanCount);

        return view('landing', compact(
            'user',
            'tahunList', 'tahun',
            'monthOptions', 'bulanPencairan',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId',
            'paguMataAnggaran', 'realisasiHonor', 'sisaAnggaran', 'paguSBML',
            'totalTransaksi', 'totalMitra', 'totalOperator',
            'sudahDipekerjakanCount', 'belumDipekerjakanCount',
            'honorPerBulan', 'honorPerBidang'
        ));
    }

    protected function honorPerBulanSelected($periodeIds, array $selectedMonths, $bidangId, $kegiatanId)
    {
        $base = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->with('periode')
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)))
            ->get();

        $map = $base->groupBy(fn($a) => $a->periode->bulan_angka)->map(fn($it) => (float) $it->sum('nominal'));

        sort($selectedMonths);
        $result = [];
        foreach ($selectedMonths as $m) {
            $result[] = (object) [
                'bulan' => $this->bulanNama[$m] ?? "Bulan $m",
                'bulan_angka' => $m,
                'total' => $map->get($m, 0)
            ];
        }
        return collect($result);
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

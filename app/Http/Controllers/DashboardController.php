<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Periode;
use App\Models\AlokasiHonor;
use App\Models\Sbml;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Support\SbmlHelper;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    protected array $bulanNama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function index(Request $request)
    {
        $user = auth()->user()->load('bidang');
        $isAdmin = $user->role === 'admin';

        // ===== Filter =====
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        
        $kegiatanId = $request->kegiatan_id ?: null;
        $mitraId = $request->mitra_id ?: null;
        $searchMitra = trim($request->search_mitra ?? '');

        if ($searchMitra !== '' && !$mitraId) {
            $foundMitra = Mitra::where('nama', 'like', "%{$searchMitra}%")
                ->orWhere('id_sobat', 'like', "%{$searchMitra}%")
                ->first();
            if ($foundMitra) {
                $mitraId = $foundMitra->id;
            }
        }

        // Tentukan default tahun pintar (utamakan tahun yang memiliki transaksi)
        $defaultTahun = null;
        if ($mitraId) {
            $defaultTahun = Periode::whereHas('alokasiHonors', fn($q) => $q->where('mitra_id', $mitraId))
                ->orderBy('tahun', 'desc')
                ->value('tahun');
        }
        if (!$defaultTahun) {
            $defaultTahun = Periode::whereHas('alokasiHonors')->orderBy('tahun', 'desc')->value('tahun') ?? $tahunList->first() ?? date('Y');
        }

        $tahun = $request->tahun ?? $defaultTahun;

        $monthOptions = [];
        foreach ($this->bulanNama as $angka => $nm) {
            $monthOptions[$angka] = $nm;
        }

        $bulanAwal  = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        // ensure valid ordering
        $bulanAwal  = max(1, min(12, $bulanAwal));
        $bulanAkhir = max(1, min(12, $bulanAkhir));
        if ($bulanAkhir < $bulanAwal) { $bulanAkhir = $bulanAwal; }

        // Bidang: operator dipaksa ke bidangnya
        $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);
        $bidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->bidang_id ?? null);
        if ($bidangId === '' || $bidangId === 'all') $bidangId = null;

        // Periode dalam rentang
        $periodeInRange = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $periodeIds = $periodeInRange;

        // ===== Scoping Honor =====
        $honorQuery = fn() => AlokasiHonor::query()
            ->whereIn('periode_id', $periodeIds)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)));

        $realisasiHonor = (float) $honorTotal = 0;
        $honorTotal = (float) $honorQuery()->sum('nominal');
        $totalTransaksi = $honorQuery()->count();
        $realisasiHonor = $honorTotal;

        // ===== Pagu Mata Anggaran (tahun = terpilih) =====
        $paguQuery = fn() => Kegiatan::where('tahun', $tahun)
            ->when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
            ->when($kegiatanId, fn($q) => $q->where('id', $kegiatanId));
        $paguMataAnggaran = (float) $paguQuery()->sum('total');
        $sisaAnggaran = $paguMataAnggaran - $realisasiHonor;

        // ===== Kapasitas Honor (SBML) =====
        $sbmlQuery = Sbml::query()
            ->whereIn('periode_id', $periodeIds)
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId));
        if (!$mitraId && ($bidangId || $kegiatanId)) {
            $scopeMitraIds = collect($honorQuery()->pluck('mitra_id')->unique());
            $sbmlQuery->whereIn('mitra_id', $scopeMitraIds);
        }
        $paguSBML = (float) $sbmlQuery->sum('nominal');

        // ===== Opsi Filter =====
        if ($isAdmin) {
            $bidangOptions = Bidang::orderBy('nama')->get();
        } elseif ($user->bidang_id) {
            $bidangOptions = Bidang::where('id', $user->bidang_id)->get();
        } else {
            $bidangOptions = Bidang::orderBy('nama')->get();
        }

        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
            ->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);

        // ===== Grafik =====
        $honorPerBulan = $this->honorPerBulan($periodeIds, $bulanAwal, $bulanAkhir, $bidangId, $kegiatanId, $mitraId);
        $honorPerBidang = $this->honorPerBidang($periodeIds, $mitraId, $kegiatanId);

        // ===== Statistik global (opsional) =====
        $totalMitra = Mitra::count();
        $totalOperator = User::where('role', 'operator')->count();
        $mitraOptions = Mitra::orderBy('nama')->get(['id', 'nama', 'id_sobat']);

        // Transaksi terakhir (terfilter) 
        $latestTransaksis = $honorQuery()
            ->with(['mitra', 'periode', 'kegiatan.bidang'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // ===== Beban Kerja Mitra =====
        $mitraProfile = null;
        $workloadKegiatans = collect();
        $workloadMonths = collect();
        $estimasiHonor = 0;
        if ($mitraId) {
            $mitraProfile = Mitra::find($mitraId);
            if ($mitraProfile) {
                $workload = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                    ->where('mitra_id', $mitraId)
                    ->whereIn('periode_id', $periodeIds)
                    ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                    ->get();

                $estimasiHonor = (float) $workload->sum('nominal');
                $workloadKegiatans = $workload->groupBy('kegiatan_id')->map(function ($items, $kid) {
                    $first = $items->first();
                    return (object) [
                        'kegiatan' => $first->kegiatan,
                        'list' => $items->sortBy('periode.bulan_angka'),
                        'honor' => (float) $items->sum('nominal'),
                        'jumlah' => $items->count(),
                    ];
                })->sortByDesc('honor')->values();

                // matriks bulan
                $workloadMonths = collect([]);
                foreach (range($bulanAwal, $bulanAkhir) as $m) {
                    $pId = Periode::where('tahun', $tahun)->where('bulan_angka', $m)->value('id');
                    $sum = $pId ? (float) AlokasiHonor::where('mitra_id', $mitraId)->where('periode_id', $pId)
                        ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                        ->sum('nominal') : 0;
                    $sbml = $pId ? SbmlHelper::limitFor($mitraId, $pId) : 0;
                    $workloadMonths->push((object)[
                        'bulan' => $this->bulanNama[$m],
                        'bulan_angka' => $m,
                        'honor' => $sum,
                        'sbml' => $sbml,
                        'sisa' => $sbml - $sum,
                    ]);
                }
            }
        }

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'tahunList', 'tahun', 'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId', 'mitraId',
            'paguMataAnggaran', 'realisasiHonor', 'sisaAnggaran', 'paguSBML',
            'totalTransaksi', 'totalMitra', 'totalOperator',
            'mitraOptions', 'searchMitra',
            'honorPerBulan', 'honorPerBidang',
            'latestTransaksis',
            'mitraProfile', 'workloadKegiatans', 'workloadMonths', 'estimasiHonor'
        ));
    }

protected function honorPerBulan($periodeIds, $awal, $akhir, $bidangId, $kegiatanId, $mitraId)
{
        $base = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->with('periode')
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
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

    protected function honorPerBidang($periodeIds, $mitraId, $kegiatanId)
    {
        return Bidang::orderBy('nama')->get()->map(function ($b) use ($periodeIds, $mitraId, $kegiatanId) {
            $sum = AlokasiHonor::whereIn('periode_id', $periodeIds)
                ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $b->id))
                ->sum('nominal');
            return (object) ['nama' => $b->nama, 'total' => (float) $sum];
        });
    }

    public function mitraOptions(Request $request)
    {
        $search = trim($request->q ?? '');
        $kegiatanId = $request->kegiatan_id;

        $query = Mitra::query();
        if ($search !== '') {
            $query->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                   ->orWhere('id_sobat', 'like', "%{$search}%")
                   ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }
        if ($kegiatanId) {
            $ids = AlokasiHonor::where('kegiatan_id', $kegiatanId)->pluck('mitra_id');
            $query->whereIn('id', $ids);
        }

        $mitras = $query->orderBy('nama')->limit(30)->get(['id', 'nama', 'id_sobat']);
        $results = $mitras->map(fn($m) => [
            'id' => $m->id,
            'text' => $m->nama . ($m->id_sobat ? ' (' . $m->id_sobat . ')' : ''),
        ]);

        return response()->json(['results' => $results]);
    }
}
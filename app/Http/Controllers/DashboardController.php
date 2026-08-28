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

        // ===== FILTER 1: MACRO TOP FILTER (KARTU & GRAFIK) =====
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

        $bulanAwal = min($bulanPencairan);
        $bulanAkhir = max($bulanPencairan);

        $isOperatorScoped = ($user->role === 'operator' && $user->bidang_id);
        $bidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->bidang_id ?? null);
        if ($bidangId === '' || $bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;

        // Periode Macro matching bulanPencairan
        $periodeIds = Periode::where('tahun', $tahun)
            ->whereIn('bulan_angka', $bulanPencairan)
            ->pluck('id');

        // Scoping Macro
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

        if ($isAdmin) {
            $bidangOptions = Bidang::orderBy('nama')->get();
        } elseif ($user->bidang_id) {
            $bidangOptions = Bidang::where('id', $user->bidang_id)->get();
        } else {
            $bidangOptions = Bidang::orderBy('nama')->get();
        }

        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))
            ->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);

        $honorPerBulan = $this->honorPerBulanSelected($periodeIds, $bulanPencairan, $bidangId, $kegiatanId, null);
        $honorPerBidang = $this->honorPerBidang($periodeIds, null, $kegiatanId);

        $totalMitra = Mitra::count();
        $totalOperator = User::where('role', 'operator')->count();
        $mitraOptions = Mitra::orderBy('nama')->get(['id', 'nama', 'id_sobat']);

        // ===== FILTER 2: MITRA SPECIFIC FILTER (BAWAH GRAFIK) =====
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

        $mTahun = $request->m_tahun ?? $tahun;
        $mBulanAwal = (int) ($request->m_bulan_awal ?? $bulanAwal);
        $mBulanAkhir = (int) ($request->m_bulan_akhir ?? $bulanAkhir);
        $mBulanAwal = max(1, min(12, $mBulanAwal));
        $mBulanAkhir = max(1, min(12, $mBulanAkhir));
        if ($mBulanAkhir < $mBulanAwal) { $mBulanAkhir = $mBulanAwal; }

        $mBidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->m_bidang_id ?? $bidangId);
        if ($mBidangId === '' || $mBidangId === 'all') $mBidangId = null;

        $mKegiatanId = $request->m_kegiatan_id ?: null;

        $mKegiatanOptions = Kegiatan::when($mBidangId, fn($q) => $q->where('bidang_id', $mBidangId))
            ->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);

        $mPeriodeIds = Periode::where('tahun', $mTahun)
            ->where('bulan_angka', '>=', $mBulanAwal)
            ->where('bulan_angka', '<=', $mBulanAkhir)
            ->pluck('id');

        $mitraProfile = null;
        $workloadKegiatans = collect();
        $workloadMonths = collect();
        $estimasiHonor = 0;
        if ($mitraId) {
            $mitraProfile = Mitra::find($mitraId);
            if ($mitraProfile) {
                $workload = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                    ->where('mitra_id', $mitraId)
                    ->whereIn('periode_id', $mPeriodeIds)
                    ->when($mKegiatanId, fn($q) => $q->where('kegiatan_id', $mKegiatanId))
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

                $workloadMonths = collect([]);
                foreach (range($mBulanAwal, $mBulanAkhir) as $m) {
                    $pId = Periode::where('tahun', $mTahun)->where('bulan_angka', $m)->value('id');
                    $sum = $pId ? (float) AlokasiHonor::where('mitra_id', $mitraId)->where('periode_id', $pId)
                        ->when($mKegiatanId, fn($q) => $q->where('kegiatan_id', $mKegiatanId))
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

        // ===== FITUR MONITORING STATUS PEKERJAAN MITRA (100% DECOUPLED & MODAL SUPPORT) =====
        $sTahun = $request->s_tahun ?? $tahun;
        $sBulanAwal = (int) ($request->s_bulan_awal ?? 1);
        $sBulanAkhir = (int) ($request->s_bulan_akhir ?? 12);
        $sBulanAwal = max(1, min(12, $sBulanAwal));
        $sBulanAkhir = max(1, min(12, $sBulanAkhir));
        if ($sBulanAkhir < $sBulanAwal) { $sBulanAkhir = $sBulanAwal; }

        $sBidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->s_bidang_id ?? null);
        if ($sBidangId === '' || $sBidangId === 'all') $sBidangId = null;

        $sKegiatanId = $request->s_kegiatan_id ?: null;
        $sStatus = $request->s_status ?? 'all'; // 'all', 'sudah', 'belum'
        $sSearch = trim($request->s_search ?? '');

        $sKegiatanOptions = Kegiatan::when($sBidangId, fn($q) => $q->where('bidang_id', $sBidangId))
            ->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);

        $sPeriodeIds = Periode::where('tahun', $sTahun)
            ->where('bulan_angka', '>=', $sBulanAwal)
            ->where('bulan_angka', '<=', $sBulanAkhir)
            ->pluck('id');

        $allocatedQuery = AlokasiHonor::whereIn('periode_id', $sPeriodeIds)
            ->when($sKegiatanId, fn($q) => $q->where('kegiatan_id', $sKegiatanId))
            ->when($sBidangId && !$sKegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $sBidangId)));

        $allocatedMitraIds = $allocatedQuery->pluck('mitra_id')->unique()->filter()->values();

        $sudahDipekerjakanCount = $allocatedMitraIds->count();
        $belumDipekerjakanCount = max(0, $totalMitra - $sudahDipekerjakanCount);

        $mitraStatusQuery = Mitra::query();

        if ($sStatus === 'sudah') {
            $mitraStatusQuery->whereIn('id', $allocatedMitraIds);
        } elseif ($sStatus === 'belum') {
            $mitraStatusQuery->whereNotIn('id', $allocatedMitraIds);
        }

        if ($sSearch !== '') {
            $mitraStatusQuery->where(function ($qq) use ($sSearch) {
                $qq->where('nama', 'like', "%{$sSearch}%")
                   ->orWhere('id_sobat', 'like', "%{$sSearch}%")
                   ->orWhere('kecamatan', 'like', "%{$sSearch}%")
                   ->orWhere('desa', 'like', "%{$sSearch}%")
                   ->orWhere('kabupaten_kota', 'like', "%{$sSearch}%");
            });
        }

        $mitraStatusPaginated = $mitraStatusQuery->orderBy('nama')->paginate(15, ['*'], 's_page')->withQueryString();

        // Enrich each paginated mitra with modal detail workload data
        $mitraStatusList = $mitraStatusPaginated->getCollection()->map(function ($m) use ($sPeriodeIds, $sKegiatanId, $sBidangId, $sTahun, $sBulanAwal, $sBulanAkhir) {
            $allocs = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                ->where('mitra_id', $m->id)
                ->whereIn('periode_id', $sPeriodeIds)
                ->when($sKegiatanId, fn($q) => $q->where('kegiatan_id', $sKegiatanId))
                ->when($sBidangId && !$sKegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $sBidangId)))
                ->get();

            $totalHonor = (float) $allocs->sum('nominal');
            $jumlahAlokasi = $allocs->count();
            $isAllocated = $jumlahAlokasi > 0;

            $m->total_honor_periode = $totalHonor;
            $m->jumlah_alokasi_periode = $jumlahAlokasi;
            $m->status_pekerjaan = $isAllocated ? 'Sudah di-Pekerjakan' : 'Belum di-Pekerjakan';

            // Grouped activities for modal popup
            $m->modal_workload_kegiatans = $allocs->groupBy('kegiatan_id')->map(function ($items) {
                $first = $items->first();
                return (object) [
                    'kegiatan' => $first->kegiatan,
                    'list' => $items->sortBy('periode.bulan_angka'),
                    'honor' => (float) $items->sum('nominal'),
                    'jumlah' => $items->count(),
                ];
            })->sortByDesc('honor')->values();

            // Monthly matrix for modal popup
            $mWorkloadMonths = collect([]);
            foreach (range($sBulanAwal, $sBulanAkhir) as $b) {
                $pId = Periode::where('tahun', $sTahun)->where('bulan_angka', $b)->value('id');
                $sum = $pId ? (float) AlokasiHonor::where('mitra_id', $m->id)->where('periode_id', $pId)
                    ->when($sKegiatanId, fn($q) => $q->where('kegiatan_id', $sKegiatanId))
                    ->sum('nominal') : 0;
                $sbml = $pId ? SbmlHelper::limitFor($m->id, $pId) : 0;
                $mWorkloadMonths->push((object)[
                    'bulan' => $this->bulanNama[$b],
                    'bulan_angka' => $b,
                    'honor' => $sum,
                    'sbml' => $sbml,
                    'sisa' => $sbml - $sum,
                ]);
            }
            $m->modal_workload_months = $mWorkloadMonths;

            return $m;
        });

        $mitraStatusPaginated->setCollection($mitraStatusList);

        return view('dashboard', compact(
            'user',
            'isAdmin',
            'isOperatorScoped',
            'tahunList', 'tahun',
            'monthOptions', 'bulanPencairan', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId',
            'paguMataAnggaran', 'realisasiHonor', 'sisaAnggaran', 'paguSBML',
            'totalTransaksi', 'totalMitra', 'totalOperator',
            'mitraOptions', 'searchMitra',
            'honorPerBulan', 'honorPerBidang',
            'mTahun', 'mBulanAwal', 'mBulanAkhir', 'mBidangId', 'mKegiatanId', 'mKegiatanOptions', 'mitraId',
            'mitraProfile', 'workloadKegiatans', 'workloadMonths', 'estimasiHonor',
            'sTahun', 'sBulanAwal', 'sBulanAkhir', 'sBidangId', 'sKegiatanId', 'sKegiatanOptions', 'sStatus', 'sSearch',
            'sudahDipekerjakanCount', 'belumDipekerjakanCount', 'mitraStatusPaginated'
        ));
    }

    protected function honorPerBulanSelected($periodeIds, array $selectedMonths, $bidangId, $kegiatanId, $mitraId)
    {
        $base = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->with('periode')
            ->when($mitraId, fn($q) => $q->where('mitra_id', $mitraId))
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
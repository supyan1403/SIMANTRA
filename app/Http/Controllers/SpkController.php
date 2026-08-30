<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\DocumentTemplate;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\SpkCounter;
use App\Traits\HasBidangScope;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class SpkController extends Controller
{
    use HasBidangScope;

    protected array $bulanNama = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public static function formatRentangTanggalBulan($bulanAngka, $tahun): string
    {
        $bulanNama = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $b = (int) $bulanAngka;
        $t = (int) $tahun;
        if ($b < 1 || $b > 12) $b = 1;
        if ($t < 2000) $t = (int) date('Y');
        
        $lastDay = date('t', strtotime(sprintf('%04d-%02d-01', $t, $b)));
        $namaBulan = $bulanNama[$b] ?? 'Januari';
        return "1-{$lastDay} {$namaBulan} {$t}";
    }

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

        $mode = $request->mode ?? 'kegiatan'; // 'kegiatan' (default) or 'bulan'

        $bidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->bidang_id ?? null);
        if ($bidangId === '' || $bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
        $kategoriKegiatan = $request->kategori_kegiatan ?? 'sensus'; // sensus, survei, umum
        $formatSpk = $request->format_spk ?? '';
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $bulanSpk = (int) ($request->bulan_spk ?? $bulanAwal);
        $tahunSpk = $request->tahun_spk ?? $tahun;
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

        if ($mode === 'bulan') {
            // Grouping per Mitra + Bulan Periode (1 SPK per bulan untuk semua kegiatan di bulan tsb)
            $allSpkList = $alokasis->groupBy(fn($item) => $item->mitra_id . '-' . $item->periode_id)
                ->map(function ($items, $key) {
                    $mitra = $items->first()->mitra;
                    $periode = $items->first()->periode;
                    $kegiatanNames = $items->pluck('kegiatan.nama')->unique()->join(', ');

                    return (object) [
                        'mitra_id' => $items->first()->mitra_id,
                        'kegiatan_id' => null,
                        'periode_id' => $items->first()->periode_id,
                        'bulan_angka' => $periode->bulan_angka,
                        'tahun' => $periode->tahun,
                        'periode_label' => $periode->bulan . ' ' . $periode->tahun,
                        'mitra' => $mitra,
                        'kegiatan' => (object) [
                            'nama' => $kegiatanNames,
                            'bidang' => (object) ['nama' => $items->pluck('kegiatan.bidang.nama')->unique()->join(', ')],
                        ],
                        'total_honor' => (float) $items->sum('nominal'),
                        'items' => $items,
                    ];
                })->values();
        } else {
            // Grouping per Mitra + Kegiatan (1 SPK per kegiatan)
            $allSpkList = $alokasis->groupBy(fn($item) => $item->mitra_id . '-' . $item->kegiatan_id)
                ->map(function ($items, $key) {
                    $mitra = $items->first()->mitra;
                    $kegiatan = $items->first()->kegiatan;
                    $periode = $items->first()->periode;

                    return (object) [
                        'mitra_id' => $items->first()->mitra_id,
                        'kegiatan_id' => $items->first()->kegiatan_id,
                        'periode_id' => $items->first()->periode_id,
                        'bulan_angka' => $periode ? $periode->bulan_angka : 1,
                        'tahun' => $periode ? $periode->tahun : date('Y'),
                        'periode_label' => $periode ? ($periode->bulan . ' ' . $periode->tahun) : '',
                        'mitra' => $mitra,
                        'kegiatan' => $kegiatan,
                        'total_honor' => (float) $items->sum('nominal'),
                        'items' => $items,
                    ];
                })->values();
        }

        // Paginate 15 items per page
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allSpkList->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $spkList = new LengthAwarePaginator(
            $currentItems,
            $allSpkList->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $tanggalDokumen = $request->tanggal_dokumen ?? date('Y-m-d');

        $selectedTemplate = $request->template_id ? DocumentTemplate::find($request->template_id) : ($templates->first() ?? null);
        $currentTemplateId = $selectedTemplate ? $selectedTemplate->id : null;

        return view('spk.index', compact(
            'tahunList', 'tahun', 'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId', 'search',
            'jenisDokumen', 'formatSpk', 'nomorAwal', 'bulanSpk', 'tahunSpk',
            'templates', 'selectedTemplate', 'currentTemplateId', 'tanggalDokumen',
            'spkList', 'mode'
        ));
    }

    public function penomoranIndex(Request $request)
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

        $mode = $request->mode ?? 'kegiatan'; // 'kegiatan' (default) or 'bulan'

        $bidangId = ($isOperatorScoped) ? $user->bidang_id : ($request->bidang_id ?? null);
        if ($bidangId === '' || $bidangId === 'all') $bidangId = null;

        $kegiatanId = $request->kegiatan_id ?: null;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk';
        $kategoriKegiatan = $request->kategori_kegiatan ?? 'sensus';
        $formatSpk = $request->format_spk ?? '';
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $bulanSpk = (int) ($request->bulan_spk ?? $bulanAwal);
        $tahunSpk = $request->tahun_spk ?? $tahun;
        $search = trim($request->search ?? '');

        $bidangOptions = $isAdmin ? Bidang::orderBy('nama')->get() : Bidang::where('id', $user->bidang_id)->get();
        $kegiatanOptions = Kegiatan::when($bidangId, fn($q) => $q->where('bidang_id', $bidangId))->orderBy('nama')->get();

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $query = AlokasiHonor::with(['mitra', 'kegiatan.bidang', 'periode'])
            ->whereIn('periode_id', $periodeIds)
            ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
            ->when($bidangId && !$kegiatanId, fn($q) => $q->whereHas('kegiatan', fn($qq) => $qq->where('bidang_id', $bidangId)))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('mitra', fn($m) => $m->where('nama', 'like', "%{$search}%")->orWhere('id_sobat', 'like', "%{$search}%"));
            });

        $alokasis = $query->get();

        // Cari nomor urut tertinggi yang sudah ada di database untuk periode & tahun ini
        $allSpkNumbers = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->whereNotNull('nomor_spk')
            ->pluck('nomor_spk')
            ->unique();
        
        $allBastNumbers = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->whereNotNull('nomor_bast')
            ->pluck('nomor_bast')
            ->unique();

        $extractMaxSeq = function ($numbers) {
            $maxSeq = 0;
            $lastFullNum = null;
            foreach ($numbers as $num) {
                // Pola format B-0001/... atau angka di awal string nomor (misal 0001/...)
                if (preg_match('/^(?:B-)?(\d{1,6})/i', trim($num), $matches)) {
                    $seq = (int) $matches[1];
                    if ($seq > $maxSeq) {
                        $maxSeq = $seq;
                        $lastFullNum = $num;
                    }
                }
            }
            return ['max_seq' => $maxSeq, 'last_num' => $lastFullNum];
        };

        $spkSeqInfo = $extractMaxSeq($allSpkNumbers);
        $bastSeqInfo = $extractMaxSeq($allBastNumbers);

        $maxSpkSeq = $spkSeqInfo['max_seq'];
        $lastSpkDoc = $spkSeqInfo['last_num'];

        $maxBastSeq = $bastSeqInfo['max_seq'];
        $lastBastDoc = $bastSeqInfo['last_num'];

        // Jika nomorAwal tidak dikirim user secara spesifik, otomatis gunakan lanjutan jika ada
        if (!$request->has('nomor_awal')) {
            $activeMax = ($jenisDokumen === 'bast') ? $maxBastSeq : $maxSpkSeq;
            if ($activeMax > 0) {
                $nomorAwal = $activeMax + 1;
            }
        }

        if ($mode === 'bulan') {
            // Grouping per Mitra + Bulan Periode (1 SPK per bulan untuk semua kegiatan di bulan tsb)
            $allSpkList = $alokasis->groupBy(fn($item) => $item->mitra_id . '-' . $item->periode_id)
                ->map(function ($items, $key) {
                    $mitra = $items->first()->mitra;
                    $periode = $items->first()->periode;
                    $kegiatanNames = $items->pluck('kegiatan.nama')->filter()->unique()->join(', ');
                    $nomor_spk = $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;
                    $nomor_bast = $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast;

                    return (object) [
                        'mitra_id' => $items->first()->mitra_id,
                        'kegiatan_id' => null,
                        'periode_id' => $items->first()->periode_id,
                        'bulan_angka' => $periode ? $periode->bulan_angka : 1,
                        'tahun' => $periode ? $periode->tahun : date('Y'),
                        'periode_label' => $periode ? ($periode->bulan . ' ' . $periode->tahun) : '',
                        'mitra' => $mitra,
                        'kegiatan' => (object) [
                            'nama' => $kegiatanNames ?: 'Kegiatan Gabungan',
                            'bidang' => (object) ['nama' => $items->pluck('kegiatan.bidang.nama')->filter()->unique()->join(', ') ?: '-'],
                        ],
                        'total_honor' => (float) $items->sum('nominal'),
                        'nomor_spk' => $nomor_spk,
                        'nomor_bast' => $nomor_bast,
                        'items' => $items,
                    ];
                })->values();
        } else {
            // Grouping per Mitra + Kegiatan (1 SPK per kegiatan)
            $allSpkList = $alokasis->groupBy(fn($item) => $item->mitra_id . '-' . $item->kegiatan_id)
                ->map(function ($items, $key) {
                    $mitra = $items->first()->mitra;
                    $kegiatan = $items->first()->kegiatan;
                    $nomor_spk = $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;
                    $nomor_bast = $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast;

                    return (object) [
                        'mitra_id' => $items->first()->mitra_id,
                        'kegiatan_id' => $items->first()->kegiatan_id,
                        'mitra' => $mitra,
                        'kegiatan' => $kegiatan,
                        'total_honor' => (float) $items->sum('nominal'),
                        'nomor_spk' => $nomor_spk,
                        'nomor_bast' => $nomor_bast,
                        'items' => $items,
                    ];
                })->values();
        }

        // Paginate 15 items per page
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allSpkList->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $spkList = new LengthAwarePaginator(
            $currentItems,
            $allSpkList->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Build kegiatanMeta mapping: kegiatan_id → { format_spk, short_name }
        $kegiatanMeta = $kegiatanOptions->mapWithKeys(fn($k) => [
            $k->id => [
                'format_spk' => $k->format_spk ?: ($formatSpk ?: 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}'),
                'short_name' => $k->short_name ?? '',
            ]
        ])->toArray();

        return view('spk.penomoran', compact(
            'mode',
            'tahunList', 'tahun', 'monthOptions', 'bulanAwal', 'bulanAkhir',
            'bidangOptions', 'bidangId', 'kegiatanOptions', 'kegiatanId', 'search',
            'jenisDokumen', 'formatSpk', 'nomorAwal', 'bulanSpk', 'tahunSpk',
            'maxSpkSeq', 'lastSpkDoc', 'maxBastSeq', 'lastBastDoc',
            'spkList', 'allSpkList', 'kegiatanMeta'
        ));
    }

    public function getCounter(Request $request)
    {
        $kegiatanId = $request->query('kegiatan_id', null);
        $tahun = $request->query('tahun', date('Y'));
        $jenisDokumen = $request->query('jenis', 'spk');

        $periodeIds = Periode::where('tahun', $tahun)->pluck('id');
        $field = ($jenisDokumen === 'bast') ? 'nomor_bast' : 'nomor_spk';

        $query = AlokasiHonor::whereIn('periode_id', $periodeIds)
            ->whereNotNull($field)
            ->where($field, '!=', '');

        if ($kegiatanId) {
            $query->where('kegiatan_id', $kegiatanId);
        }

        $existingNomors = $query->pluck($field)->unique();
        $globalTotal = $existingNomors->count();

        if (!$kegiatanId) {
            return response()->json(['last_number' => 0, 'next_number' => 1, 'global_total' => $globalTotal]);
        }

        // Cari nomor urut maksimum dari nomor-nomor dokumen yang ada di database
        $maxSeq = 0;
        foreach ($existingNomors as $num) {
            if (preg_match('/^(?:B-)?(\d{1,6})/i', trim($num), $m)) {
                $seq = (int) $m[1];
                if ($seq > $maxSeq) {
                    $maxSeq = $seq;
                }
            }
        }

        // Sinkronkan counter di DB
        $counter = SpkCounter::where('kegiatan_id', (int) $kegiatanId)
            ->where('jenis_dokumen', $jenisDokumen)
            ->where('tahun', $tahun)
            ->first();

        if ($counter) {
            $counter->update(['last_number' => $maxSeq]);
        } else {
            SpkCounter::create([
                'kegiatan_id' => (int) $kegiatanId,
                'jenis_dokumen' => $jenisDokumen,
                'tahun' => $tahun,
                'last_number' => $maxSeq,
            ]);
        }

        return response()->json([
            'last_number' => $maxSeq,
            'next_number' => $maxSeq + 1,
            'global_total' => $globalTotal,
        ]);
    }

    public function updateKegiatanFormat(Request $request)
    {
        $kegiatanId = $request->kegiatan_id;
        $format = $request->format;

        if (!$kegiatanId || !$format) {
            return response()->json(['error' => 'Data tidak lengkap'], 400);
        }

        $kegiatan = \App\Models\Kegiatan::findOrFail($kegiatanId);
        $kegiatan->update(['format_spk' => $format]);

        return response()->json(['success' => true, 'message' => "Format pola {$kegiatan->short_name} berhasil diperbarui."]);
    }

    public function resetAllFormat(Request $request)
    {
        $defaultFormat = 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
        \App\Models\Kegiatan::where('format_spk', '!=', $defaultFormat)->update(['format_spk' => $defaultFormat]);
        $count = \App\Models\Kegiatan::count();

        return response()->json(['success' => true, 'message' => "Semua {$count} kegiatan berhasil di-reset ke pola default."]);
    }

    public function terapkanPenomoran(Request $request)
    {
        $mitraIds = $request->mitra_ids ?? [];
        if (empty($mitraIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mitra untuk diterapkan nomornya.');
        }

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $tahun = $request->tahun ?? date('Y');
            $bulanAwal = (int) ($request->bulan_awal ?? 1);
            $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
            $kegiatanId = $request->kegiatan_id ?: null;
            $periodeIds = Periode::where('tahun', $tahun)
                ->where('bulan_angka', '>=', $bulanAwal)
                ->where('bulan_angka', '<=', $bulanAkhir)
                ->pluck('id');

            $rawMitraIds = array_map(fn($v) => (int) explode('_', $v)[0], $mitraIds);
            $rawMitraIds = array_unique($rawMitraIds);

            $invalidCount = AlokasiHonor::whereIn('mitra_id', $rawMitraIds)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', '!=', $user->bidang_id))
                ->count();

            if ($invalidCount > 0) {
                return redirect()->back()->with('error', 'Beberapa mitra memiliki alokasi di luar bidang Anda. Aksi dibatalkan.');
            }
        }

        $jenisDokumen = $request->jenis_dokumen ?? 'spk';
        $formatSpk = $request->format_spk ?? 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
        $jenisIsi = $request->jenis_isi ?? 'kegiatan'; // 'dokumen' atau 'kegiatan'
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorStart = (int) ($request->nomor_awal ?? 1);
        $bulanSpk = (int) ($request->bulan_spk ?? $bulanAwal);
        $tahunSpk = $request->tahun_spk ?? $tahun;
        $kegiatanId = $request->kegiatan_id ?: null;
        $customNomors = $request->custom_nomors ?? [];

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $savedCount = 0;
        // Track max number used per kegiatan
        $kegiatanMaxNumbers = [];

        // Parse composite key: each value is "mitraId_kegiatanId"
        $pairs = [];
        foreach ($mitraIds as $val) {
            $parts = explode('_', $val);
            $mId = (int) $parts[0];
            $kId = (int) ($parts[1] ?? 0);
            $pairs[] = ['mitra_id' => $mId, 'kegiatan_id' => $kId];
        }

        // Group by mitra_id
        $byMitra = collect($pairs)->groupBy('mitra_id');

        foreach ($byMitra as $mId => $kegiatanPairs) {
            $kegiatanIds = $kegiatanPairs->pluck('kegiatan_id')->filter()->unique()->values();

            $allItems = AlokasiHonor::where('mitra_id', $mId)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanIds->isNotEmpty(), fn($q) => $q->whereIn('kegiatan_id', $kegiatanIds))
                ->get();

            if ($allItems->isEmpty()) continue;

            $grouped = $allItems->groupBy('kegiatan_id');

            foreach ($grouped as $kegId => $items) {
                // Ambil format dari kegiatan, fallback ke global format
                $kegiatanModel = \App\Models\Kegiatan::find($kegId);
                $fmt = $kegiatanModel?->format_spk ?: $formatSpk;
                $shortName = $kegiatanModel?->short_name ?? '';

                $customKey = $mId . '_' . $kegId;
                if (!empty($customNomors[$customKey])) {
                    $nomorDoc = trim($customNomors[$customKey]);
                } else {
                    // Hitung {jenis} berdasarkan radio toggle
                    $jenisPlaceholder = ($jenisIsi === 'dokumen') ? $jenisDokumen : $shortName;

                    // Counter per kegiatan_id per tahun
                    $nextNum = SpkCounter::getNextNumber((int) $kegId, $jenisDokumen, $tahunSpk);
                    $nomorDoc = $this->generateNomorDokumen($fmt, $nextNum, $bulanSpk, $tahunSpk, $jenisPlaceholder);

                    // Track max number per kegiatan
                    if (!isset($kegiatanMaxNumbers[$kegId]) || $nextNum > $kegiatanMaxNumbers[$kegId]) {
                        $kegiatanMaxNumbers[$kegId] = $nextNum;
                    }
                }

                foreach ($items as $item) {
                    if ($jenisDokumen === 'bast') {
                        $item->update(['nomor_bast' => $nomorDoc]);
                    } else {
                        $item->update(['nomor_spk' => $nomorDoc]);
                    }
                }
                $savedCount++;
            }
        }

        // Update counter per kegiatan per jenis dokumen per tahun
        if ($savedCount > 0) {
            foreach ($kegiatanMaxNumbers as $kegId => $maxNum) {
                SpkCounter::incrementTo((int) $kegId, $jenisDokumen, $tahunSpk, $maxNum);
            }
        }

        return redirect()->back()->with('success', "Berhasil menerapkan & menyimpan nomor resmi untuk {$savedCount} mitra ke database.");
    }

    public function generateNomorDokumen(string $format, int $nomorUrut, int $bulan, string $tahun, string $kegiatanShortName = ''): string
    {
        if (empty($format) || $format === 'default') {
            $format = 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
        }

        $replacements = [
            '{nomor}' => sprintf('%04d', $nomorUrut),
            '{nomor_raw}' => (string) $nomorUrut,
            '{bulan}' => sprintf('%02d', $bulan),
            '{bulan_romawi}' => $this->bulanRomawi($bulan),
            '{tahun}' => $tahun,
            '{jenis}' => strtoupper($kegiatanShortName ?: 'SPK'),
        ];

        $result = $format;
        foreach ($replacements as $key => $val) {
            $result = str_replace($key, $val, $result);
        }

        return $result;
    }

    private function bulanRomawi(int $bulan): string
    {
        $romawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];
        return $romawi[$bulan] ?? 'I';
    }

    public static function terbilang(float|int $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        $temp = '';

        if ($nilai < 12) {
            $temp = ' ' . $huruf[(int)$nilai];
        } else if ($nilai < 20) {
            $temp = self::terbilang($nilai - 10) . ' Belas';
        } else if ($nilai < 100) {
            $temp = self::terbilang((int)($nilai / 10)) . ' Puluh ' . self::terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = ' Seratus ' . self::terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = self::terbilang((int)($nilai / 100)) . ' Ratus ' . self::terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = ' Seribu ' . self::terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = self::terbilang((int)($nilai / 1000)) . ' Ribu ' . self::terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = self::terbilang((int)($nilai / 1000000)) . ' Juta ' . self::terbilang($nilai % 1000000);
        } else if ($nilai < 1000000000000) {
            $temp = self::terbilang((int)($nilai / 1000000000)) . ' Milyar ' . self::terbilang(fmod($nilai, 1000000000));
        }

        return trim(preg_replace('/\s+/', ' ', $temp));
    }

    public static function formatTanggalTerbilang(string $dateStr): array
    {
        try {
            $carbon = \Carbon\Carbon::parse($dateStr);
        } catch (\Exception $e) {
            $carbon = \Carbon\Carbon::now();
        }

        $namaHari = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu'
        ];

        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $hari = $namaHari[$carbon->format('l')] ?? 'Senin';
        $tglAngka = (int)$carbon->format('j');
        $blnAngka = (int)$carbon->format('n');
        $thnAngka = (int)$carbon->format('Y');

        $tglTeks = 'tanggal ' . strtolower(self::terbilang($tglAngka));
        $blnTeks = 'bulan ' . ($namaBulan[$blnAngka] ?? 'Januari');
        $thnTeks = 'tahun ' . strtolower(self::terbilang($thnAngka));

        return [
            'hari' => $hari,
            'tanggal_teks' => $tglTeks,
            'bulan_teks' => $blnTeks,
            'tahun_teks' => $thnTeks,
            'full_text' => "Pada hari ini {$hari}, {$tglTeks}, {$blnTeks}, {$thnTeks}, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini::"
        ];
    }

    public function cetakUtama(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $hasAlokasiInBidang = AlokasiHonor::where('mitra_id', $mitraId)
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id))
                ->exists();
            if (!$hasAlokasiInBidang) {
                return redirect()->back()->with('error', 'Mitra ini tidak memiliki alokasi di bidang Anda. Aksi dibatalkan.');
            }
        }

        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $bulanSpk = (int) ($request->bulan_spk ?? $bulanAwal);
        $tahunSpk = $request->tahun_spk ?? $tahun;
        $formatSpk = $request->format_spk ?? 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';

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

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada alokasi honor untuk {$mitra->nama} pada periode ini.");
        }

        $totalHonor = (float) $items->sum('nominal');
        $terbilangHonor = self::terbilang($totalHonor) . ' Rupiah';
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;
        
        // Cek validasi nomor resmi di database
        $nomorDokumen = ($jenisDokumen === 'bast') 
            ? $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast
            : $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;

        if (empty($nomorDokumen)) {
            return redirect()->route('spk.penomoran.index')->with('error', "Mitra {$mitra->nama} belum memiliki nomor resmi. Silakan tetapkan nomor di halaman Penomoran terlebih dahulu.");
        }

        $tanggalSpkInput = $request->tanggal_spk ?? $request->tanggal_dokumen ?? date('Y-m-d');
        $tanggalTerbilang = self::formatTanggalTerbilang($tanggalSpkInput);

        if ($jenisDokumen === 'bast') {
            $batchList = collect([(object)[
                'mitra' => $mitra,
                'nomor_dokumen' => $nomorDokumen,
                'items' => $items,
                'total_honor' => $totalHonor,
                'terbilang_honor' => $terbilangHonor,
            ]]);
            return view('spk.template_bast', compact('batchList', 'tahun', 'periodeLabel', 'tanggalTerbilang'));
        }

        return view('spk.template_utama', compact('mitra', 'items', 'totalHonor', 'terbilangHonor', 'tahun', 'periodeLabel', 'nomorDokumen', 'tanggalTerbilang'));
    }

    public function downloadPdf(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $hasAlokasiInBidang = AlokasiHonor::where('mitra_id', $mitraId)
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id))
                ->exists();
            if (!$hasAlokasiInBidang) {
                return redirect()->back()->with('error', 'Mitra ini tidak memiliki alokasi di bidang Anda. Aksi dibatalkan.');
            }
        }

        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
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

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada alokasi honor untuk {$mitra->nama} pada periode ini.");
        }

        $nomorDokumen = ($jenisDokumen === 'bast') 
            ? $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast
            : $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;

        if (empty($nomorDokumen)) {
            return redirect()->route('spk.penomoran.index')->with('error', "Mitra {$mitra->nama} belum memiliki nomor resmi.");
        }

        $totalHonor = (float) $items->sum('nominal');
        $terbilangHonor = self::terbilang($totalHonor) . ' Rupiah';
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        // =========================================================================
        // NATIVE MS WORD INJECTION (MAIL MERGE .DOCX / .PDF) -> 100% IDENTIK
        // =========================================================================
        $docTmpl = $templateId ? DocumentTemplate::find($templateId) : null;
        if ($docTmpl && $docTmpl->jenis_dokumen) {
            $jenisDokumen = $docTmpl->jenis_dokumen;
        }

        $isBast = ($jenisDokumen === 'bast');
        $defaultTemplate = $isBast ? 'BAST PETUGAS (Sumber -2).docx' : (file_exists(base_path('File SPK (Sumber - 3 hasil edit).docx')) ? 'File SPK (Sumber - 3 hasil edit).docx' : 'File SPK (Sumber -2).docx');
        $templatePath = base_path($defaultTemplate);

        if ($docTmpl && $docTmpl->file_path && Storage::disk('public')->exists($docTmpl->file_path)) {
            $templatePath = Storage::disk('public')->path($docTmpl->file_path);
        }

        if (file_exists($templatePath)) {
            $tempDir = storage_path('app/temp_spk');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            $uniqueId = uniqid($isBast ? 'bast_' : 'spk_');
            $tempDocx = $tempDir . '/' . $uniqueId . '.docx';
            $tempJson = $tempDir . '/' . $uniqueId . '.json';

            $tanggalSpkInput = $request->tanggal_spk ?? $request->tanggal_dokumen ?? date('Y-m-d');
            $tanggalInfo = self::formatTanggalTerbilang($tanggalSpkInput);

            $nomorSpkRef = $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk ?? '';
            $nomorBastRef = $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast ?? $nomorDokumen;

            if ($isBast) {
                $payload = [
                    'nomor_bast' => $nomorBastRef,
                    'nomor_spk' => $nomorSpkRef ?: str_replace('BAST', 'SPK', $nomorBastRef),
                    'nama_mitra' => strtoupper($mitra->nama),
                    'pekerjaan_mitra' => $mitra->pekerjaan_clean,
                    'alamat_mitra' => $mitra->alamat_clean,
                    'tahun' => $tahun,
                    'hari' => $tanggalInfo['hari'],
                    'tanggal_teks' => $tanggalInfo['tanggal_teks'],
                    'bulan_teks' => $tanggalInfo['bulan_teks'],
                    'tahun_teks' => $tanggalInfo['tahun_teks'],
                    'tanggal_angka' => date('j', strtotime($tanggalSpkInput)) . ' ' . $this->bulanNama[(int)date('n', strtotime($tanggalSpkInput))] . ' ' . date('Y', strtotime($tanggalSpkInput)),
                    'tanggal_spk_text' => date('j', strtotime($tanggalSpkInput)) . ' ' . $this->bulanNama[(int)date('n', strtotime($tanggalSpkInput))],
                ];
                $pyScript = base_path('scripts/export_bast_docx.py');
                $docPrefix = 'BAST_';
            } else {
                $itemsData = [];
                foreach ($items as $it) {
                    $rentangBulan = self::formatRentangTanggalBulan($it->periode->bulan_angka, $it->periode->tahun);
                    $volume = (float)($it->volume ?? 1);
                    $satuan = $it->satuan ?? 'dokumen';
                    $hargaSatuan = ($volume > 0) ? ($it->nominal / $volume) : $it->nominal;

                    $itemsData[] = [
                        'nama' => $it->kegiatan->nama,
                        'periode' => $rentangBulan,
                        'volume' => $volume,
                        'satuan' => $satuan,
                        'harga_satuan' => 'Rp. ' . number_format($hargaSatuan, 0, ',', '.') . ',00',
                        'nilai_perjanjian' => 'Rp. ' . number_format($it->nominal, 0, ',', '.') . ', 00',
                        'mak' => $it->kegiatan->kode_mata_anggaran ?: '-',
                    ];
                }
                $payload = [
                    'nomor_spk' => $nomorDokumen,
                    'nama_mitra' => strtoupper($mitra->nama),
                    'pekerjaan_mitra' => $mitra->pekerjaan_clean,
                    'alamat_mitra' => $mitra->alamat_clean,
                    'periode_label' => $periodeLabel,
                    'total_honor' => 'Rp. ' . number_format($totalHonor, 0, ',', '.'),
                    'terbilang_honor' => $terbilangHonor,
                    'tahun' => $tahun,
                    'hari' => $tanggalInfo['hari'],
                    'tanggal_teks' => $tanggalInfo['tanggal_teks'],
                    'bulan_teks' => $tanggalInfo['bulan_teks'],
                    'tahun_teks' => $tanggalInfo['tahun_teks'],
                    'full_pembuka' => $tanggalInfo['full_text'],
                    'items' => $itemsData,
                ];
                $pyScript = base_path('scripts/export_spk_docx.py');
                $docPrefix = 'SPK_';
            }

            file_put_contents($tempJson, json_encode($payload));

            $isPdfReq = ($request->format === 'pdf');
            $pdfFlag = $isPdfReq ? ' --pdf' : '';
            
            exec("python \"{$pyScript}\" \"{$templatePath}\" \"{$tempDocx}\" \"{$tempJson}\"{$pdfFlag}");

            if ($isPdfReq) {
                $tempPdf = $tempDir . '/' . $uniqueId . '.pdf';
                if (file_exists($tempPdf)) {
                    @unlink($tempJson);
                    @unlink($tempDocx);
                    $downloadName = $docPrefix . preg_replace('/[^a-zA-Z0-9]/', '_', $mitra->nama) . '.pdf';
                    return response()->download($tempPdf, $downloadName, [
                        'Content-Type' => 'application/pdf'
                    ])->deleteFileAfterSend(true);
                }
            }

            if ($request->format === 'docx') {
                if (!file_exists($tempDocx) && file_exists($templatePath)) {
                    $this->generateWordDocxFallback($templatePath, $tempDocx, $payload);
                }
                if (file_exists($tempDocx)) {
                    @unlink($tempJson);
                    $downloadName = $docPrefix . preg_replace('/[^a-zA-Z0-9]/', '_', $mitra->nama) . '.docx';
                    return response()->download($tempDocx, $downloadName)->deleteFileAfterSend(true);
                }
            }
        }

        // Fallback jika tidak lewat python
        $html = view('spk.pdf_utama', compact('mitra', 'items', 'totalHonor', 'terbilangHonor', 'tahun', 'periodeLabel', 'nomorDokumen', 'tanggalInfo'))->render();
        $filename = 'SPK_' . \Illuminate\Support\Str::slug($mitra->nama) . '_' . $tahun . '.pdf';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'serif');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 612.00, 936.00], 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function downloadLampiranPdf(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $hasAlokasiInBidang = AlokasiHonor::where('mitra_id', $mitraId)
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id))
                ->exists();
            if (!$hasAlokasiInBidang) {
                return redirect()->back()->with('error', 'Mitra ini tidak memiliki alokasi di bidang Anda. Aksi dibatalkan.');
            }
        }

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

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada alokasi honor untuk {$mitra->nama} pada periode ini.");
        }

        $totalHonor = (float) $items->sum('nominal');
        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        $html = view('spk.pdf_lampiran', compact('mitra', 'items', 'totalHonor', 'tahun', 'periodeLabel'))->render();
        $filename = 'Lampiran_SPK_' . \Illuminate\Support\Str::slug($mitra->nama) . '_' . $tahun . '.pdf';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'times');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function cetakLampiran(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $hasAlokasiInBidang = AlokasiHonor::where('mitra_id', $mitraId)
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id))
                ->exists();
            if (!$hasAlokasiInBidang) {
                return redirect()->back()->with('error', 'Mitra ini tidak memiliki alokasi di bidang Anda. Aksi dibatalkan.');
            }
        }

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

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', "Tidak ada alokasi honor untuk {$mitra->nama} pada periode ini.");
        }

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

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $tahun = $request->tahun ?? date('Y');
            $bulanAwal = (int) ($request->bulan_awal ?? 1);
            $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
            $kegiatanId = $request->kegiatan_id ?: null;
            $periodeIds = Periode::where('tahun', $tahun)
                ->where('bulan_angka', '>=', $bulanAwal)
                ->where('bulan_angka', '<=', $bulanAkhir)
                ->pluck('id');

            $invalidCount = AlokasiHonor::whereIn('mitra_id', $mitraIds)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', '!=', $user->bidang_id))
                ->count();

            if ($invalidCount > 0) {
                return redirect()->back()->with('error', 'Beberapa mitra memiliki alokasi di luar bidang Anda. Cetak massal dibatalkan.');
            }
        }

        $templateId = $request->template_id;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
        if ($templateId) {
            $docTmpl = DocumentTemplate::find($templateId);
            if ($docTmpl && $docTmpl->jenis_dokumen) {
                $jenisDokumen = $docTmpl->jenis_dokumen;
            }
        }

        $kategoriKegiatan = $request->kategori_kegiatan ?? 'sensus';
        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $kegiatanId = $request->kegiatan_id ?: null;

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        $batchList = collect();

        // Parse composite key: each value is "mitraId_kegiatanId"
        $pairs = [];
        foreach ($mitraIds as $val) {
            $parts = explode('_', $val);
            $mId = (int) $parts[0];
            $kId = (int) ($parts[1] ?? 0);
            $pairs[] = ['mitra_id' => $mId, 'kegiatan_id' => $kId];
        }

        // Group by mitra_id
        $byMitra = collect($pairs)->groupBy('mitra_id');

        foreach ($byMitra as $mId => $kegiatanPairs) {
            $mitra = Mitra::find($mId);
            if (!$mitra) continue;

            $kegiatanIds = $kegiatanPairs->pluck('kegiatan_id')->filter()->unique()->values();

            $allItems = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                ->where('mitra_id', $mId)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanIds->isNotEmpty(), fn($q) => $q->whereIn('kegiatan_id', $kegiatanIds))
                ->get();

            if ($allItems->isEmpty()) continue;

            $grouped = $allItems->groupBy('kegiatan_id');

            foreach ($grouped as $kegId => $items) {
                $nomorDoc = ($jenisDokumen === 'bast')
                    ? $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast
                    : $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;

                if (empty($nomorDoc)) {
                    return redirect()->route('spk.penomoran.index')->with('error', "Gagal mencetak massal: Mitra {$mitra->nama} belum memiliki nomor {$jenisDokumen} resmi untuk kegiatan ini.");
                }

                $totalHonor = (float) $items->sum('nominal');

                $batchList->push((object) [
                    'mitra' => $mitra,
                    'kegiatan' => $items->first()->kegiatan,
                    'items' => $items,
                    'total_honor' => $totalHonor,
                    'nomor_dokumen' => $nomorDoc,
                ]);
            }
        }

        $tanggalSpkInput = $request->tanggal_spk ?? $request->tanggal_dokumen ?? date('Y-m-d');
        $tanggalTerbilang = self::formatTanggalTerbilang($tanggalSpkInput);

        $viewName = ($jenisDokumen === 'bast') ? 'spk.template_bast' : 'spk.cetak_massal';

        return view($viewName, compact('batchList', 'jenisDokumen', 'kategoriKegiatan', 'tahun', 'periodeLabel', 'tanggalTerbilang'));
    }

    public function unduhMassalZip(Request $request)
    {
        $mitraIds = $request->mitra_ids ?? [];
        if (empty($mitraIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mitra untuk diunduh secara massal.');
        }

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $tahun = $request->tahun ?? date('Y');
            $bulanAwal = (int) ($request->bulan_awal ?? 1);
            $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
            $kegiatanId = $request->kegiatan_id ?: null;
            $periodeIds = Periode::where('tahun', $tahun)
                ->where('bulan_angka', '>=', $bulanAwal)
                ->where('bulan_angka', '<=', $bulanAkhir)
                ->pluck('id');

            $invalidCount = AlokasiHonor::whereIn('mitra_id', $mitraIds)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', '!=', $user->bidang_id))
                ->count();

            if ($invalidCount > 0) {
                return redirect()->back()->with('error', 'Beberapa mitra memiliki alokasi di luar bidang Anda. Unduh massal dibatalkan.');
            }
        }

        $templateId = $request->template_id;
        $jenisDokumen = $request->jenis_dokumen ?? 'spk'; // spk or bast
        if ($templateId) {
            $docTmpl = DocumentTemplate::find($templateId);
            if ($docTmpl && $docTmpl->jenis_dokumen) {
                $jenisDokumen = $docTmpl->jenis_dokumen;
            }
        }

        $docTmpl = $templateId ? DocumentTemplate::find($templateId) : null;
        $isBast = ($jenisDokumen === 'bast');
        $defaultTemplate = $isBast ? 'BAST PETUGAS (Sumber -2).docx' : (file_exists(base_path('File SPK (Sumber - 3 hasil edit).docx')) ? 'File SPK (Sumber - 3 hasil edit).docx' : 'File SPK (Sumber -2).docx');
        $templatePath = base_path($defaultTemplate);

        if ($docTmpl && $docTmpl->file_path && Storage::disk('public')->exists($docTmpl->file_path)) {
            $templatePath = Storage::disk('public')->path($docTmpl->file_path);
        }

        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $kegiatanId = $request->kegiatan_id ?: null;

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $periodeLabel = $this->bulanNama[$bulanAwal] . ($bulanAwal !== $bulanAkhir ? ' s.d ' . $this->bulanNama[$bulanAkhir] : '') . ' ' . $tahun;

        // Parse composite keys
        $pairs = [];
        foreach ($mitraIds as $val) {
            $parts = explode('_', $val);
            $mId = (int) $parts[0];
            $kId = (int) ($parts[1] ?? 0);
            $pairs[] = ['mitra_id' => $mId, 'kegiatan_id' => $kId];
        }

        $byMitra = collect($pairs)->groupBy('mitra_id');
        $downloadFormat = strtolower($request->format ?? 'docx'); // docx or pdf
        $isPdfReq = ($downloadFormat === 'pdf');
        $pdfFlag = $isPdfReq ? ' --pdf' : '';

        $tempBase = storage_path('app/temp_zip_' . uniqid());
        if (!file_exists($tempBase)) {
            mkdir($tempBase, 0777, true);
        }

        $zipPath = $tempBase . DIRECTORY_SEPARATOR . 'Arsip_Dokumen_' . strtoupper($jenisDokumen) . '_' . date('Ymd_His') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file arsip ZIP.');
        }

        $filesAdded = 0;
        $tanggalSpkInput = $request->tanggal_spk ?? $request->tanggal_dokumen ?? date('Y-m-d');
        $tanggalInfo = self::formatTanggalTerbilang($tanggalSpkInput);

        foreach ($byMitra as $mId => $kegiatanPairs) {
            $mitra = Mitra::find($mId);
            if (!$mitra) continue;

            $kegiatanIds = $kegiatanPairs->pluck('kegiatan_id')->filter()->unique()->values();

            $allItems = AlokasiHonor::with(['kegiatan.bidang', 'periode'])
                ->where('mitra_id', $mId)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanIds->isNotEmpty(), fn($q) => $q->whereIn('kegiatan_id', $kegiatanIds))
                ->get();

            if ($allItems->isEmpty()) continue;

            $grouped = $allItems->groupBy('kegiatan_id');

            foreach ($grouped as $kegId => $items) {
                $nomorDoc = ($jenisDokumen === 'bast')
                    ? $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast
                    : $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk;

                if (empty($nomorDoc)) {
                    continue;
                }

                $totalHonor = (float) $items->sum('nominal');
                $terbilangHonor = self::terbilang($totalHonor) . ' Rupiah';
                $nomorSpkRef = $items->firstWhere('nomor_spk', '!=', null)?->nomor_spk ?? '';
                $nomorBastRef = $items->firstWhere('nomor_bast', '!=', null)?->nomor_bast ?? $nomorDoc;

                $fileUid = uniqid($isBast ? 'bast_' : 'spk_');
                $tempDocx = $tempBase . DIRECTORY_SEPARATOR . $fileUid . '.docx';
                $tempJson = $tempBase . DIRECTORY_SEPARATOR . $fileUid . '.json';

                if ($isBast) {
                    $payload = [
                        'nomor_bast' => $nomorBastRef,
                        'nomor_spk' => $nomorSpkRef ?: str_replace('BAST', 'SPK', $nomorBastRef),
                        'nama_mitra' => strtoupper($mitra->nama),
                        'pekerjaan_mitra' => $mitra->pekerjaan_clean,
                        'alamat_mitra' => $mitra->alamat_clean,
                        'tahun' => $tahun,
                        'hari' => $tanggalInfo['hari'],
                        'tanggal_teks' => $tanggalInfo['tanggal_teks'],
                        'bulan_teks' => $tanggalInfo['bulan_teks'],
                        'tahun_teks' => $tanggalInfo['tahun_teks'],
                        'tanggal_angka' => date('j', strtotime($tanggalSpkInput)) . ' ' . $this->bulanNama[(int)date('n', strtotime($tanggalSpkInput))] . ' ' . date('Y', strtotime($tanggalSpkInput)),
                        'tanggal_spk_text' => date('j', strtotime($tanggalSpkInput)) . ' ' . $this->bulanNama[(int)date('n', strtotime($tanggalSpkInput))],
                    ];
                    $pyScript = base_path('scripts/export_bast_docx.py');
                    $docPrefix = 'BAST_';
                } else {
                    $itemsData = [];
                    foreach ($items as $it) {
                        $rentangBulan = self::formatRentangTanggalBulan($it->periode->bulan_angka, $it->periode->tahun);
                        $volume = (float)($it->volume ?? 1);
                        $satuan = $it->satuan ?? 'dokumen';
                        $hargaSatuan = ($volume > 0) ? ($it->nominal / $volume) : $it->nominal;

                        $itemsData[] = [
                            'nama' => $it->kegiatan->nama,
                            'periode' => $rentangBulan,
                            'volume' => $volume,
                            'satuan' => $satuan,
                            'harga_satuan' => 'Rp. ' . number_format($hargaSatuan, 0, ',', '.') . ',00',
                            'nilai_perjanjian' => 'Rp. ' . number_format($it->nominal, 0, ',', '.') . ', 00',
                            'mak' => $it->kegiatan->kode_mata_anggaran ?: '-',
                        ];
                    }
                    $payload = [
                        'nomor_spk' => $nomorDoc,
                        'nama_mitra' => strtoupper($mitra->nama),
                        'pekerjaan_mitra' => $mitra->pekerjaan_clean,
                        'alamat_mitra' => $mitra->alamat_clean,
                        'periode_label' => $periodeLabel,
                        'total_honor' => 'Rp. ' . number_format($totalHonor, 0, ',', '.'),
                        'terbilang_honor' => $terbilangHonor,
                        'tahun' => $tahun,
                        'hari' => $tanggalInfo['hari'],
                        'tanggal_teks' => $tanggalInfo['tanggal_teks'],
                        'bulan_teks' => $tanggalInfo['bulan_teks'],
                        'tahun_teks' => $tanggalInfo['tahun_teks'],
                        'full_pembuka' => $tanggalInfo['full_text'],
                        'items' => $itemsData,
                    ];
                    $pyScript = base_path('scripts/export_spk_docx.py');
                    $docPrefix = 'SPK_';
                }

                file_put_contents($tempJson, json_encode($payload));

                if (file_exists($templatePath)) {
                    exec("python \"{$pyScript}\" \"{$templatePath}\" \"{$tempDocx}\" \"{$tempJson}\"{$pdfFlag}");
                }

                $kegiatanSlug = \Illuminate\Support\Str::slug($items->first()->kegiatan->nama ?? 'Kegiatan');
                $mitraSlug = \Illuminate\Support\Str::slug($mitra->nama);

                if ($isPdfReq) {
                    $tempPdf = $tempBase . DIRECTORY_SEPARATOR . $fileUid . '.pdf';
                    if (file_exists($tempPdf)) {
                        $entryName = $docPrefix . $mitraSlug . '_' . $kegiatanSlug . '.pdf';
                        $zip->addFile($tempPdf, $entryName);
                        $filesAdded++;
                    }
                } else {
                    if (!file_exists($tempDocx) && file_exists($templatePath)) {
                        $this->generateWordDocxFallback($templatePath, $tempDocx, $payload);
                    }
                    if (file_exists($tempDocx)) {
                        $entryName = $docPrefix . $mitraSlug . '_' . $kegiatanSlug . '.docx';
                        $zip->addFile($tempDocx, $entryName);
                        $filesAdded++;
                    }
                }
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            @unlink($zipPath);
            @rmdir($tempBase);
            return redirect()->back()->with('error', 'Tidak ada dokumen resmi yang siap diunduh (pastikan mitra terpilih sudah memiliki nomor SPK/BAST resmi).');
        }

        $downloadFilename = strtoupper($jenisDokumen) . '_Massal_' . preg_replace('/[^a-zA-Z0-9]/', '_', $periodeLabel) . '_' . strtoupper($downloadFormat) . '.zip';

        return response()->download($zipPath, $downloadFilename)->deleteFileAfterSend(true);
    }

    public function resetNomor(Request $request)
    {
        $mitraIds = $request->mitra_ids ?? [];
        if (empty($mitraIds)) {
            return redirect()->back()->with('error', 'Pilih minimal 1 mitra yang ingin direset nomornya.');
        }

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $tahun = $request->tahun ?? date('Y');
            $bulanAwal = (int) ($request->bulan_awal ?? 1);
            $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
            $kegiatanId = $request->kegiatan_id ?: null;
            $periodeIds = Periode::where('tahun', $tahun)
                ->where('bulan_angka', '>=', $bulanAwal)
                ->where('bulan_angka', '<=', $bulanAkhir)
                ->pluck('id');

            $invalidCount = AlokasiHonor::whereIn('mitra_id', $mitraIds)
                ->whereIn('periode_id', $periodeIds)
                ->when($kegiatanId, fn($q) => $q->where('kegiatan_id', $kegiatanId))
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', '!=', $user->bidang_id))
                ->count();

            if ($invalidCount > 0) {
                return redirect()->back()->with('error', 'Beberapa mitra memiliki alokasi di luar bidang Anda. Reset dibatalkan.');
            }
        }

        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $jenis = $request->jenis_reset ?? 'semua'; // 'spk', 'bast', atau 'semua'

        $mitraIds = $request->mitra_ids ?? [];
        $kegiatanIds = $request->kegiatan_ids ?? [];

        $periodeIds = Periode::where('tahun', $tahun)
            ->where('bulan_angka', '>=', $bulanAwal)
            ->where('bulan_angka', '<=', $bulanAkhir)
            ->pluck('id');

        $resetCount = 0;
        foreach ($mitraIds as $idx => $mId) {
            $kId = $kegiatanIds[$idx] ?? null;

            $query = AlokasiHonor::where('mitra_id', $mId)
                ->whereIn('periode_id', $periodeIds);

            if ($kId) {
                $query->where('kegiatan_id', $kId);
            }

            if ($jenis === 'spk') {
                $resetCount += $query->update(['nomor_spk' => null, 'tanggal_spk' => null]);
            } elseif ($jenis === 'bast') {
                $resetCount += $query->update(['nomor_bast' => null]);
            } else {
                $resetCount += $query->update(['nomor_spk' => null, 'nomor_bast' => null, 'tanggal_spk' => null]);
            }
        }

        return redirect()->back()->with('success', "{$resetCount} item berhasil direset nomor SPK & BAST-nya menjadi kosong.");
    }

    public function downloadDocx(Request $request, $mitraId)
    {
        $mitra = Mitra::findOrFail($mitraId);

        $user = auth()->user();
        if ($user->role === 'operator' && $user->bidang_id) {
            $hasAlokasiInBidang = AlokasiHonor::where('mitra_id', $mitraId)
                ->whereHas('kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id))
                ->exists();
            if (!$hasAlokasiInBidang) {
                return redirect()->back()->with('error', 'Mitra ini tidak memiliki alokasi di bidang Anda. Aksi dibatalkan.');
            }
        }

        $tahun = $request->tahun ?? date('Y');
        $bulanAwal = (int) ($request->bulan_awal ?? 1);
        $bulanAkhir = (int) ($request->bulan_akhir ?? 12);
        $nomorAwal = (int) ($request->nomor_awal ?? 1);
        $bulanSpk = (int) ($request->bulan_spk ?? $bulanAwal);
        $tahunSpk = $request->tahun_spk ?? $tahun;
        $formatSpk = $request->format_spk ?? 'B-{nomor}/BPS/3206/{jenis}/{bulan}/{tahun}';
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
        $docTmpl = $templateId ? DocumentTemplate::find($templateId) : null;
        $jenisDokumen = ($docTmpl && $docTmpl->jenis_dokumen) ? $docTmpl->jenis_dokumen : ($request->jenis_dokumen ?? 'spk');
        
        // Ambil format & short_name dari kegiatan
        $firstKegiatan = $items->first()?->kegiatan;
        $fmt = $firstKegiatan?->format_spk ?: $formatSpk;
        $shortName = $firstKegiatan?->short_name ?? '';
        $jenisIsi = $request->jenis_isi ?? 'kegiatan';
        $jenisPlaceholder = ($jenisIsi === 'dokumen') ? $jenisDokumen : $shortName;
        $nomorDokumen = $this->generateNomorDokumen($fmt, $nomorAwal, $bulanSpk, $tahunSpk, $jenisPlaceholder);

        $docTmpl = $templateId ? DocumentTemplate::find($templateId) : null;
        $isBast = ($docTmpl && $docTmpl->jenis_dokumen === 'bast') || ($request->jenis_dokumen === 'bast');

        $templatePath = $isBast ? base_path('BAST PETUGAS (Sumber -2).docx') : (file_exists(base_path('File SPK (Sumber - 3 hasil edit).docx')) ? base_path('File SPK (Sumber - 3 hasil edit).docx') : base_path('File SPK (Sumber -2).docx'));
        if ($docTmpl && $docTmpl->file_path && Storage::disk('public')->exists($docTmpl->file_path)) {
            $templatePath = Storage::disk('public')->path($docTmpl->file_path);
        }

        if (!file_exists($templatePath)) {
            return redirect()->back()->with('error', 'Berkas template DOCX rujukan tidak ditemukan.');
        }

        $tempFile = sys_get_temp_dir() . '/' . ($isBast ? 'bast_' : 'spk_') . time() . '_' . $mitra->id . '.docx';
        copy($templatePath, $tempFile);

        $zip = new \ZipArchive();
        if ($zip->open($tempFile) === true) {
            $xml = $zip->getFromName('word/document.xml');

            $formattedTotalHonor = number_format($totalHonor, 0, ',', '.');
            $terbilangText = $this->terbilang($totalHonor);

            $item1Nama = isset($items[0]) ? $items[0]->kegiatan->nama : '-';
            $item1Nominal = isset($items[0]) ? 'Rp ' . number_format($items[0]->nominal, 0, ',', '.') : 'Rp 0';
            $item1Mak = isset($items[0]) ? ($items[0]->kegiatan->kode_mata_anggaran ?: '-') : '';

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
                'Nur Azizah Muyassaroh, S.ST' => strtoupper($mitra->nama),
                'LINA KARLINA' => strtoupper($mitra->nama),
                '${NAMA_MITRA}' => strtoupper($mitra->nama),
                '${NAMA}' => strtoupper($mitra->nama),

                'Lainnya/ Belum Bekerja' => $pekerjaanVal,
                '${PEKERJAAN}' => $pekerjaanVal,

                'Kp. Pameungpeuk RT/RW : 24/03 Desa Sukarasa Kec. Salawu' => $alamatVal,
                '${ALAMAT}' => $alamatVal,

                // 3. Document Numbering & Year
                'EGIATAN SURVEI/SENSUS TAHUN 2023' => 'EGIATAN SURVEI/SENSUS TAHUN ' . $tahun,
                '1001/PPK/SPK/03/2024' => $nomorDokumen,
                '${NOMOR_SPK}' => $nomorDokumen,
                '${NOMOR_BAST}' => $nomorDokumen,

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
            'kategori_kegiatan' => 'umum',
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
        $template->deskripsi = $request->deskripsi;
        $template->save();

        return redirect()->route('spk.templates.index')->with('success', 'Template Dokumen berhasil diperbarui.');
    }

    public function templateDownload($id)
    {
        $template = DocumentTemplate::findOrFail($id);

        if ($template->file_path && Storage::disk('public')->exists($template->file_path)) {
            $ext = pathinfo($template->file_path, PATHINFO_EXTENSION) ?: 'docx';
            $downloadName = \Illuminate\Support\Str::slug($template->nama) . '.' . $ext;
            return Storage::disk('public')->download($template->file_path, $downloadName);
        }

        // Fallback ke master template fisik BPS
        $isBast = ($template->jenis_dokumen === 'bast');
        $defaultTemplate = $isBast ? 'BAST PETUGAS (Sumber -2).docx' : (file_exists(base_path('File SPK (Sumber - 3 hasil edit).docx')) ? 'File SPK (Sumber - 3 hasil edit).docx' : 'File SPK (Sumber -2).docx');
        $templatePath = base_path($defaultTemplate);

        if (file_exists($templatePath)) {
            $downloadName = \Illuminate\Support\Str::slug($template->nama) . '.docx';
            return response()->download($templatePath, $downloadName);
        }

        return redirect()->back()->with('error', 'Berkas template fisik tidak ditemukan.');
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

    private function generateWordDocxFallback($templatePath, $outputPath, $payload)
    {
        copy($templatePath, $outputPath);
        $zip = new \ZipArchive();
        if ($zip->open($outputPath) === true) {
            $xml = $zip->getFromName('word/document.xml');
            if ($xml) {
                $replacements = [
                    'AHMAD HIDAYAT (DUMMY 2025)' => $payload['mitra_nama'] ?? '',
                    'Mitra Lapangan 2025' => $payload['mitra_pekerjaan'] ?? '',
                    'Kec. Manonjaya, Desa MANONJAYA, Kec. MANONJAYA, Kabupaten Tasikmalaya' => $payload['mitra_alamat'] ?? '',
                    'B-0004/BPS/3206/SKTR/01/2025' => $payload['nomor_dokumen'] ?? '',
                    'B-0004/BPS/3206/BAST/01/2025' => $payload['nomor_dokumen'] ?? '',
                    'periode Januari s.d Desember 2025' => 'periode ' . ($payload['periode_label'] ?? ''),
                    'Januari s.d Desember 2025' => $payload['periode_label'] ?? '',
                    'Rp. 12.600.000' => $payload['total_honor'] ?? '',
                    'Dua Belas Juta Enam Ratus Ribu Rupiah' => $payload['terbilang_honor'] ?? '',
                    'Tahun 2025' => 'Tahun ' . ($payload['tahun'] ?? date('Y')),
                    'Tahun 2024' => 'Tahun ' . ($payload['tahun'] ?? date('Y')),
                ];

                if (!empty($payload['tanggal_pembukaan'])) {
                    $replacements['Pada hari ini, bertempat di Tasikmalaya, yang bertanda tangan di bawah ini::'] = $payload['tanggal_pembukaan'];
                }

                foreach ($replacements as $search => $replace) {
                    $xml = str_replace($search, htmlspecialchars($replace, ENT_XML1, 'UTF-8'), $xml);
                }

                $xml = preg_replace('/<w:color\s+[^>]*w:val="(?:ff0000|FF0000)"[^>]*\/>/i', '', $xml);

                $zip->addFromString('word/document.xml', $xml);
            }
            $zip->close();
        }
    }
}

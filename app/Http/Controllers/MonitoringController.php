<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Periode;
use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Support\SbmlHelper;
use App\Traits\HasBidangScope;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    use HasBidangScope;
    public function index(Request $request)
    {
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('bulan_angka')->get();
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');
        $query = AlokasiHonor::with(['mitra', 'periode', 'kegiatan.bidang']);

        // Scope Bidang untuk Operator (Bab 3.2 & Bab 4)
        $user = auth()->user();
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $query->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $user->bidang_id));
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            if ($request->bidang_id) {
                $query->whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $request->bidang_id));
            }
            $bidangs = Bidang::all();
        }

        if ($request->periode_id) {
            $query->where('periode_id', $request->periode_id);
        }
        if ($request->kegiatan_id) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }
        if ($request->search) {
            $query->whereHas('mitra', fn ($q) => $q->where('nama', 'like', '%' . $request->search . '%'));
        }

        $alokasis = $query->orderBy('created_at', 'desc')->paginate(20)->onEachSide(1);

        // ===== Peringatan SBML: akumulasi honor per mitra per periode =====
        $sbmlWarnings = [];
        foreach ($alokasis as $a) {
            $key = $a->mitra_id . '-' . $a->periode_id;
            if (!isset($sbmlWarnings[$key])) {
                $evaluated = SbmlHelper::evaluate($a->mitra_id, $a->periode_id);
                if ($evaluated['exceeded']) {
                    $sbmlWarnings[$key] = $evaluated;
                }
            }
        }

        $kegiatansQuery = Kegiatan::query();
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $kegiatansQuery->where('bidang_id', $user->bidang_id);
        }
        $kegiatans = $kegiatansQuery->orderBy('nama')->get();

        return view('monitoring.index', compact('alokasis', 'periodes', 'bidangs', 'tahunList', 'sbmlWarnings', 'kegiatans'));
    }

    public function create()
    {
        $user = auth()->user();
        $mitras = Mitra::orderBy('nama')->get();
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('bulan_angka')->get();
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangs = Bidang::where('id', $user->bidang_id)->with(['kegiatans' => fn($q) => $q->orderBy('nama')])->get();
        } else {
            $bidangs = Bidang::with(['kegiatans' => fn($q) => $q->orderBy('nama')])->get();
        }

        return view('monitoring.form', compact('mitras', 'periodes', 'tahunList', 'bidangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'mitra_id' => 'required|exists:mitras,id',
            'periode_id' => 'required|exists:periodes,id',
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nominal' => 'required|numeric|min:0',
        ]);

        if (!$this->validateOperatorKegiatan($validated['kegiatan_id'])) {
            return $this->bidangAccessDenied();
        }

        $alokasi = AlokasiHonor::create($validated);

        // Check total honor for this Mitra in this Periode against SBML (Requirement 4.8)
        $totalHonorBulan = AlokasiHonor::where('mitra_id', $validated['mitra_id'])
            ->where('periode_id', $validated['periode_id'])
            ->sum('nominal');

        $mitra = Mitra::find($validated['mitra_id']);
        $periode = Periode::find($validated['periode_id']);
        $sbmlLimit = SbmlHelper::limitFor($mitra->id, $periode->id);

        if ($totalHonorBulan > $sbmlLimit) {
            $formattedTotal = 'Rp ' . number_format($totalHonorBulan, 0, ',', '.');
            $formattedLimit = 'Rp ' . number_format($sbmlLimit, 0, ',', '.');
            return redirect()->route('monitoring.index')->with('warning', "Alokasi honor berhasil disimpan. PERINGATAN SBML: Total honor {$mitra->nama} bulan {$periode->bulan} {$periode->tahun} ({$formattedTotal}) melebihi batas SBML ({$formattedLimit}).");
        }

        return redirect()->route('monitoring.index')->with('success', 'Alokasi honor berhasil ditambahkan.');
    }

    public function edit(AlokasiHonor $monitoring)
    {
        $user = auth()->user();
        $alokasi = $monitoring;
        $mitras = Mitra::orderBy('nama')->get();
        $periodes = Periode::orderBy('tahun', 'desc')->orderBy('bulan_angka')->get();
        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangs = Bidang::where('id', $user->bidang_id)->with(['kegiatans' => fn($q) => $q->orderBy('nama')])->get();
        } else {
            $bidangs = Bidang::with(['kegiatans' => fn($q) => $q->orderBy('nama')])->get();
        }

        $alokasi->load(['periode', 'kegiatan.bidang']);

        return view('monitoring.form', compact('alokasi', 'mitras', 'periodes', 'tahunList', 'bidangs'));
    }

    public function update(Request $request, AlokasiHonor $monitoring)
    {
        $alokasi = $monitoring;
        $validated = $request->validate([
            'mitra_id' => 'required|exists:mitras,id',
            'periode_id' => 'required|exists:periodes,id',
            'kegiatan_id' => 'required|exists:kegiatans,id',
            'nominal' => 'required|numeric|min:0',
        ]);

        if (!$this->validateOperatorKegiatan($validated['kegiatan_id'])) {
            return $this->bidangAccessDenied();
        }

        $alokasi->update($validated);

        // Check total honor for this Mitra in this Periode against SBML (Requirement 4.8)
        $totalHonorBulan = AlokasiHonor::where('mitra_id', $validated['mitra_id'])
            ->where('periode_id', $validated['periode_id'])
            ->sum('nominal');

        $mitra = Mitra::find($validated['mitra_id']);
        $periode = Periode::find($validated['periode_id']);
        $sbmlLimit = SbmlHelper::limitFor($mitra->id, $periode->id);

        if ($totalHonorBulan > $sbmlLimit) {
            $formattedTotal = 'Rp ' . number_format($totalHonorBulan, 0, ',', '.');
            $formattedLimit = 'Rp ' . number_format($sbmlLimit, 0, ',', '.');
            return redirect()->route('monitoring.index')->with('warning', "Alokasi honor berhasil diperbarui. PERINGATAN SBML: Total honor {$mitra->nama} bulan {$periode->bulan} {$periode->tahun} ({$formattedTotal}) melebihi batas SBML ({$formattedLimit}).");
        }

        return redirect()->route('monitoring.index')->with('success', 'Alokasi honor berhasil diupdate.');
    }

    public function destroy(AlokasiHonor $monitoring)
    {
        if (!$this->validateSingleAlokasi($monitoring->id)) {
            return $this->bidangAccessDenied();
        }

        $monitoring->delete();
        return redirect()->route('monitoring.index')->with('success', 'Data alokasi honor berhasil dihapus.');
    }

    public function checkLimit(Request $request)
    {
        $mitraId = $request->query('mitra_id');
        $periodeId = $request->query('periode_id');
        $nominal = floatval($request->query('nominal', 0));
        $currentId = $request->query('current_id');

        if (!$mitraId || !$periodeId) {
            return response()->json(['exceeded' => false]);
        }

        $mitra = Mitra::find($mitraId);
        $periode = Periode::find($periodeId);

        if (!$mitra || !$periode) {
            return response()->json(['exceeded' => false]);
        }

        $query = AlokasiHonor::where('mitra_id', $mitraId)->where('periode_id', $periodeId);
        if ($currentId) {
            $query->where('id', '!=', $currentId);
        }
        $existingTotal = floatval($query->sum('nominal'));
        $newTotal = $existingTotal + $nominal;

        $sbmlLimit = SbmlHelper::limitFor($mitraId, $periodeId);
        $exceeded = $newTotal > $sbmlLimit;

        $availableMitras = [];
        if ($exceeded) {
            $user = auth()->user();
            $allMitras = Mitra::where('id', '!=', $mitraId)->orderBy('nama');
            if ($user && $user->role === 'operator' && $user->bidang_id) {
                $allMitras->whereHas('alokasiHonors.kegiatan', fn($q) => $q->where('bidang_id', $user->bidang_id));
            }
            $allMitras = $allMitras->get();
            foreach ($allMitras as $m) {
                $mTotal = floatval(AlokasiHonor::where('mitra_id', $m->id)->where('periode_id', $periodeId)->sum('nominal'));
                $mLimit = SbmlHelper::limitFor($m->id, $periodeId);
                $remainingQuota = max(0, $mLimit - $mTotal);
                
                if ($mTotal < $mLimit) {
                    $availableMitras[] = [
                        'id' => $m->id,
                        'nama' => $m->nama,
                        'pekerjaan' => $m->pekerjaan ?? 'Mitra',
                        'current_total' => $mTotal,
                        'remaining_quota' => $remainingQuota,
                        'formatted_current' => 'Rp ' . number_format($mTotal, 0, ',', '.'),
                        'formatted_remaining' => 'Rp ' . number_format($remainingQuota, 0, ',', '.'),
                    ];
                }
            }
            // Sort by lowest current honor, then by name
            usort($availableMitras, function($a, $b) {
                if ($a['current_total'] == $b['current_total']) {
                    return strcmp($a['nama'], $b['nama']);
                }
                return $a['current_total'] <=> $b['current_total'];
            });
        }

        return response()->json([
            'exceeded' => $exceeded,
            'mitra_name' => $mitra->nama,
            'periode_name' => "{$periode->bulan} {$periode->tahun}",
            'existing_total' => $existingTotal,
            'nominal' => $nominal,
            'new_total' => $newTotal,
            'limit' => $sbmlLimit,
            'formatted_existing' => 'Rp ' . number_format($existingTotal, 0, ',', '.'),
            'formatted_nominal' => 'Rp ' . number_format($nominal, 0, ',', '.'),
            'formatted_new_total' => 'Rp ' . number_format($newTotal, 0, ',', '.'),
            'formatted_limit' => 'Rp ' . number_format($sbmlLimit, 0, ',', '.'),
            'available_mitras' => $availableMitras
        ]);
    }

    public function updateSpkManual(Request $request)
    {
        $validated = $request->validate([
            'kegiatan_id'   => 'required|exists:kegiatans,id',
            'periode_id'    => 'required|exists:periodes,id',
            'mode_penomoran'=> 'nullable|in:berurutan,seragam',
            'nomor_awal'    => 'nullable|integer|min:1',
            'format_spk'    => 'nullable|string|max:150',
            'format_bast'   => 'nullable|string|max:150',
            'nomor_spk'     => 'nullable|string|max:100',
            'nomor_bast'    => 'nullable|string|max:100',
            'tanggal_spk'   => 'nullable|date',
        ]);

        $user = auth()->user();
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $kegiatan = Kegiatan::find($validated['kegiatan_id']);
            if ($kegiatan && $kegiatan->bidang_id != $user->bidang_id) {
                return redirect()->back()->with('error', 'Anda tidak memiliki izin mengedit SPK pada kegiatan di luar bidang Anda.');
            }
        }

        $kegiatan = Kegiatan::find($validated['kegiatan_id']);
        $periode = Periode::find($validated['periode_id']);
        $mode = $request->input('mode_penomoran');
        if (!$mode) {
            $mode = (!empty($validated['nomor_spk']) && empty($validated['format_spk'])) ? 'seragam' : 'berurutan';
        }

        $alokasis = AlokasiHonor::where('kegiatan_id', $validated['kegiatan_id'])
            ->where('periode_id', $validated['periode_id'])
            ->join('mitras', 'alokasi_honors.mitra_id', '=', 'mitras.id')
            ->orderBy('mitras.nama', 'asc')
            ->select('alokasi_honors.*')
            ->get();

        $bulanAngka = $periode->bulan_angka ?? date('n');
        $tahun = $periode->tahun ?? date('Y');
        $tanggalSpk = $validated['tanggal_spk'] ?? null;
        $count = 0;

        if ($mode === 'berurutan') {
            $formatSpk = $request->input('format_spk') ?: 'B-{nomor}/BPS/3206/SPK/{bulan}/{tahun}';
            $formatBast = $request->input('format_bast') ?: 'B-{nomor}/BPS/3206/BAST/{bulan}/{tahun}';
            $counter = (int) ($request->input('nomor_awal') ?: 1);

            foreach ($alokasis as $alokasi) {
                $noSpk = str_replace(
                    ['{nomor}', '{nomor_raw}', '{bulan}', '{tahun}'],
                    [sprintf('%04d', $counter), (string) $counter, sprintf('%02d', $bulanAngka), $tahun],
                    $formatSpk
                );

                $noBast = str_replace(
                    ['{nomor}', '{nomor_raw}', '{bulan}', '{tahun}'],
                    [sprintf('%04d', $counter), (string) $counter, sprintf('%02d', $bulanAngka), $tahun],
                    $formatBast
                );

                $alokasi->update([
                    'nomor_spk'   => $noSpk,
                    'nomor_bast'  => $noBast,
                    'tanggal_spk' => $tanggalSpk,
                ]);

                $counter++;
                $count++;
            }
        } else {
            // Mode Seragam (Satu nomor sama untuk semua mitra)
            $noSpk = trim($request->input('nomor_spk') ?: '');
            $noBast = $request->input('nomor_bast') ? trim($request->input('nomor_bast')) : null;

            foreach ($alokasis as $alokasi) {
                $alokasi->update([
                    'nomor_spk'   => $noSpk ?: $alokasi->nomor_spk,
                    'nomor_bast'  => $noBast,
                    'tanggal_spk' => $tanggalSpk,
                ]);
                $count++;
            }
        }

        return redirect()->back()->with('success', "Nomor SPK berurutan berhasil dibuat untuk kegiatan '{$kegiatan->nama}' periode {$periode->bulan} {$periode->tahun}. Sebanyak {$count} mitra telah diberikan nomor urut SPK yang unik.");
    }
}

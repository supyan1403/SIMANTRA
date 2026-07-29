<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use App\Models\Periode;
use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user()->load('bidang');
        
        $totalMitra = Mitra::count();
        $totalPeriode = Periode::count();
        $totalHonor = AlokasiHonor::sum('nominal');
        $totalOperator = User::where('role', 'operator')->count();
        $totalKegiatan = Kegiatan::count();
        $totalTransaksi = AlokasiHonor::count();

        $bidangs = Bidang::all();
        $honorPerBidang = $bidangs->map(function ($b) {
            $sum = AlokasiHonor::whereHas('kegiatan', fn ($q) => $q->where('bidang_id', $b->id))->sum('nominal');
            return (object) [
                'nama' => $b->nama,
                'total' => (float) $sum,
            ];
        });

        $periodes = Periode::orderBy('bulan_angka')->get();
        $honorPerBulan = $periodes->map(function ($p) {
            $sum = AlokasiHonor::where('periode_id', $p->id)->sum('nominal');
            return (object) [
                'bulan' => $p->bulan,
                'total' => (float) $sum,
            ];
        });

        // Grafik Transaksi per Tahun (Bab 4.2)
        $transaksiPerTahun = Periode::select('tahun', DB::raw('count(*) as count_periode'))
            ->groupBy('tahun')
            ->orderBy('tahun', 'desc')
            ->get()
            ->map(function ($p) {
                $totalNominal = AlokasiHonor::whereHas('periode', fn($q) => $q->where('tahun', $p->tahun))->sum('nominal');
                $totalTrans = AlokasiHonor::whereHas('periode', fn($q) => $q->where('tahun', $p->tahun))->count();
                return (object) [
                    'tahun' => $p->tahun,
                    'total_transaksi' => $totalTrans,
                    'total_nominal' => $totalNominal,
                ];
            });

        // Detail Transaksi Berjalan untuk Admin
        $latestTransaksis = AlokasiHonor::with(['mitra', 'periode', 'kegiatan.bidang'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Data Khusus Operator (Bab 4.3)
        $operatorBidang = $user->bidang;
        $operatorBidangId = $user->bidang_id;
        
        $totalHonorOperator = 0;
        $totalTransaksiOperator = 0;
        $kegiatansOperator = collect();
        $transaksiOperator = collect();

        if ($operatorBidangId) {
            $totalHonorOperator = AlokasiHonor::whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operatorBidangId))->sum('nominal');
            $totalTransaksiOperator = AlokasiHonor::whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operatorBidangId))->count();
            
            $kegiatansOperator = Kegiatan::where('bidang_id', $operatorBidangId)
                ->withCount('alokasiHonors as total_alokasi')
                ->withSum('alokasiHonors as total_honor', 'nominal')
                ->orderBy('nama')
                ->get();

            $transaksiOperator = AlokasiHonor::whereHas('kegiatan', fn($q) => $q->where('bidang_id', $operatorBidangId))
                ->with(['mitra', 'periode', 'kegiatan'])
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();
        } else {
            // Operator without specific bidang sees overall or first bidang
            $totalHonorOperator = $totalHonor;
            $totalTransaksiOperator = $totalTransaksi;
            $kegiatansOperator = Kegiatan::withCount('alokasiHonors as total_alokasi')->withSum('alokasiHonors as total_honor', 'nominal')->take(10)->get();
            $transaksiOperator = $latestTransaksis;
        }

        return view('dashboard', compact(
            'user',
            'totalMitra', 
            'totalPeriode', 
            'totalHonor', 
            'totalOperator', 
            'totalKegiatan', 
            'totalTransaksi', 
            'honorPerBidang', 
            'honorPerBulan',
            'transaksiPerTahun',
            'latestTransaksis',
            'operatorBidang',
            'totalHonorOperator',
            'totalTransaksiOperator',
            'kegiatansOperator',
            'transaksiOperator'
        ));
    }
}

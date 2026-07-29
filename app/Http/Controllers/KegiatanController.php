<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\Bidang;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->query('search');
        $bidangId = $request->query('bidang_id');

        $query = Kegiatan::with(['bidang', 'alokasiHonors.mitra', 'alokasiHonors.periode'])
            ->withCount('alokasiHonors as total_alokasi');

        // Scope Operator Khusus Bidangnya (Bab 3.2)
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangId = $user->bidang_id;
            $query->where('bidang_id', $user->bidang_id);
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            if ($bidangId && $bidangId !== 'all') {
                $query->where('bidang_id', $bidangId);
            }
            $bidangs = Bidang::all();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('kode_mata_anggaran', 'like', "%{$search}%");
            });
        }

        $kegiatans = $query->latest()->paginate(15)->withQueryString();

        return view('kegiatan.index', compact('kegiatans', 'bidangs', 'search', 'bidangId'));
    }

    public function create()
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $user = auth()->user();
        if ($user && $user->role === 'operator' && $user->bidang_id) {
            $bidangs = Bidang::where('id', $user->bidang_id)->get();
        } else {
            $bidangs = Bidang::all();
        }
        return view('kegiatan.form', compact('bidangs'));
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        $validated['total'] = $validated['jumlah'] * $validated['harga'];

        Kegiatan::create($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Mata Anggaran/Kegiatan berhasil ditambahkan.');
    }

    public function edit(Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $bidangs = Bidang::all();
        return view('kegiatan.form', compact('kegiatan', 'bidangs'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'bidang_id' => 'required|exists:bidangs,id',
            'kode_mata_anggaran' => 'nullable|string|max:100',
            'tahun' => 'nullable|string|max:4',
            'jumlah' => 'nullable|numeric|min:0',
            'satuan' => 'nullable|string|max:50',
            'harga' => 'nullable|numeric|min:0',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date',
        ]);

        $validated['jumlah'] = $validated['jumlah'] ?? 0;
        $validated['harga'] = $validated['harga'] ?? 0;
        $validated['total'] = $validated['jumlah'] * $validated['harga'];

        $kegiatan->update($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Mata Anggaran/Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        if (auth()->user()?->role !== 'admin') {
            return redirect()->route('kegiatan.index')->with('error', 'Hanya Admin yang memiliki hak akses untuk mengelola data Mata Anggaran.');
        }

        $kegiatan->delete();
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }

    public function byBidang($bidangId)
    {
        $kegiatans = Kegiatan::where('bidang_id', $bidangId)->orderBy('nama')->get(['id', 'nama', 'kode_mata_anggaran']);
        return response()->json($kegiatans);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Periode;
use Illuminate\Http\Request;

class PeriodeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $tahun = $request->query('tahun');

        $query = Periode::query();

        if ($search) {
            $query->where('bulan', 'like', "%{$search}%")
                  ->orWhere('tahun', 'like', "%{$search}%");
        }

        if ($tahun && $tahun !== 'all') {
            $query->where('tahun', $tahun);
        }

        $periodes = $query->orderBy('tahun', 'desc')
                          ->orderBy('bulan_angka', 'asc')
                          ->paginate(15)
                          ->withQueryString();

        $tahunList = Periode::select('tahun')->distinct()->orderBy('tahun', 'desc')->pluck('tahun');

        return view('periode.index', compact('periodes', 'tahunList', 'search', 'tahun'));
    }

    public function create()
    {
        return view('periode.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'bulan' => 'required|string|max:50',
            'tahun' => 'required|integer|min:2020|max:2099',
            'bulan_angka' => 'required|integer|min:1|max:12',
        ]);

        Periode::create($validated);

        return redirect()->route('periode.index')->with('success', 'Periode berhasil ditambahkan.');
    }

    public function edit(Periode $periode)
    {
        return view('periode.form', compact('periode'));
    }

    public function update(Request $request, Periode $periode)
    {
        $validated = $request->validate([
            'bulan' => 'required|string|max:50',
            'tahun' => 'required|integer|min:2020|max:2099',
            'bulan_angka' => 'required|integer|min:1|max:12',
        ]);

        $periode->update($validated);

        return redirect()->route('periode.index')->with('success', 'Periode berhasil diperbarui.');
    }

    public function destroy(Periode $periode)
    {
        $periode->delete();
        return redirect()->route('periode.index')->with('success', 'Periode berhasil dihapus.');
    }
}

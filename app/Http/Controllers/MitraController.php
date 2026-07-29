<?php

namespace App\Http\Controllers;

use App\Models\Mitra;
use Illuminate\Http\Request;

class MitraController extends Controller
{
    public function index(Request $request)
    {
        $query = Mitra::query();

        if ($request->filled('jk')) {
            $query->where('jk', $request->jk);
        }

        if ($request->filled('pekerjaan')) {
            $query->where('pekerjaan', $request->pekerjaan);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('alamat', 'like', "%{$search}%")
                  ->orWhere('kode_alamat', 'like', "%{$search}%");
            });
        }

        $pekerjaanList = Mitra::whereNotNull('pekerjaan')
            ->where('pekerjaan', '!=', '')
            ->distinct()
            ->pluck('pekerjaan')
            ->sort();

        $mitras = $query->orderBy('nama')
            ->paginate(20)
            ->onEachSide(1)
            ->withQueryString();

        return view('mitra.index', compact('mitras', 'pekerjaanList'));
    }

    public function create()
    {
        return view('mitra.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);
        Mitra::create($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil ditambahkan');
    }

    public function edit(Mitra $mitra)
    {
        return view('mitra.form', compact('mitra'));
    }

    public function update(Request $request, Mitra $mitra)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'pekerjaan' => 'nullable|string|max:255',
            'kode_alamat' => 'nullable|string|max:50',
            'jk' => 'nullable|in:L,P',
        ]);
        $mitra->update($validated);
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil diupdate');
    }

    public function destroy(Mitra $mitra)
    {
        $mitra->delete();
        return redirect()->route('mitra.index')->with('success', 'Mitra berhasil dihapus');
    }
}

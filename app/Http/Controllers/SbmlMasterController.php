<?php

namespace App\Http\Controllers;

use App\Models\SbmlMaster;
use Illuminate\Http\Request;

class SbmlMasterController extends Controller
{
    public function index()
    {
        $masters = SbmlMaster::orderBy('tahun', 'desc')->get();
        return view('sbml_master.index', compact('masters'));
    }

    public function create()
    {
        return view('sbml_master.form', ['master' => null]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|between:1980,2100|unique:sbml_masters,tahun',
            'nominal' => 'required|numeric|min:0',
        ]);
        SbmlMaster::create($validated);
        return redirect()->route('master-sbml.index')->with('success', 'Batas SBML tahun ' . $validated['tahun'] . ' berhasil ditambahkan.');
    }

    public function edit(SbmlMaster $sbmlMaster)
    {
        return view('sbml_master.form', ['master' => $sbmlMaster]);
    }

    public function update(Request $request, SbmlMaster $sbmlMaster)
    {
        $validated = $request->validate([
            'tahun' => 'required|integer|between:1980,2100|unique:sbml_masters,tahun,' . $sbmlMaster->id,
            'nominal' => 'required|numeric|min:0',
        ]);
        $sbmlMaster->update($validated);
        return redirect()->route('master-sbml.index')->with('success', 'Batas SBML tahun ' . $validated['tahun'] . ' berhasil diperbarui.');
    }

    public function destroy(SbmlMaster $sbmlMaster)
    {
        $tahun = $sbmlMaster->tahun;
        $sbmlMaster->delete();
        return redirect()->route('master-sbml.index')->with('success', 'Batas SBML tahun ' . $tahun . ' berhasil dihapus.');
    }
}
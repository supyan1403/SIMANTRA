<?php

namespace App\Http\Controllers;

use App\Models\PosisiMitra;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosisiMitraController extends Controller
{
    /**
     * Ambil daftar posisi mitra dalam format JSON untuk modal & dropdown
     */
    public function listJson()
    {
        $positions = PosisiMitra::orderBy('nama', 'asc')->get()->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'keterangan' => $p->keterangan ?? '-',
                'mitra_count' => $p->mitra_count,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $positions,
        ]);
    }

    /**
     * Simpan posisi mitra baru (mendukung request form normal & AJAX)
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:posisi_mitras,nama',
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama posisi mitra wajib diisi.',
            'nama.unique' => 'Nama posisi mitra ini sudah ada di master data.',
        ]);

        $posisi = PosisiMitra::create([
            'nama' => trim($request->nama),
            'keterangan' => $request->keterangan ? trim($request->keterangan) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Posisi mitra baru berhasil ditambahkan.',
                'data' => [
                    'id' => $posisi->id,
                    'nama' => $posisi->nama,
                    'keterangan' => $posisi->keterangan ?? '-',
                ],
            ]);
        }

        return redirect()->back()->with('success', 'Posisi mitra berhasil ditambahkan.');
    }

    /**
     * Perbarui nama atau keterangan posisi mitra
     */
    public function update(Request $request, PosisiMitra $posisiMitra)
    {
        $request->validate([
            'nama' => 'required|string|max:100|unique:posisi_mitras,nama,' . $posisiMitra->id,
            'keterangan' => 'nullable|string|max:255',
        ], [
            'nama.required' => 'Nama posisi mitra wajib diisi.',
            'nama.unique' => 'Nama posisi mitra ini sudah terdaftar.',
        ]);

        $oldName = $posisiMitra->nama;
        $newName = trim($request->nama);

        DB::beginTransaction();
        try {
            $posisiMitra->update([
                'nama' => $newName,
                'keterangan' => $request->keterangan ? trim($request->keterangan) : null,
            ]);

            // Sinkronisasi data mitra jika nama posisi diubah
            if ($oldName !== $newName) {
                Mitra::where('posisi', $oldName)->update(['posisi' => $newName]);
            }

            DB::commit();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Posisi mitra berhasil diperbarui.',
                    'data' => $posisiMitra,
                ]);
            }

            return redirect()->back()->with('success', 'Posisi mitra berhasil diperbarui.');
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal memperbarui posisi: ' . $e->getMessage(),
                ], 500);
            }
            return redirect()->back()->with('error', 'Gagal memperbarui posisi: ' . $e->getMessage());
        }
    }

    /**
     * Hapus master posisi mitra
     */
    public function destroy(Request $request, PosisiMitra $posisiMitra)
    {
        $nama = $posisiMitra->nama;
        $posisiMitra->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => "Posisi '{$nama}' berhasil dihapus.",
            ]);
        }

        return redirect()->back()->with('success', "Posisi '{$nama}' berhasil dihapus.");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PengaturanController extends Controller
{
    public function index()
    {
        $users = User::with('bidang')->orderBy('name')->paginate(20)->onEachSide(1);
        return view('pengaturan.index', compact('users'));
    }

    public function create()
    {
        $bidangs = Bidang::all();
        return view('pengaturan.form', compact('bidangs'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,operator',
            'bidang_id' => 'nullable|exists:bidangs,id',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        User::create($validated);
        return redirect()->route('pengaturan.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(User $user)
    {
        $bidangs = Bidang::all();
        return view('pengaturan.form', compact('user', 'bidangs'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,operator',
            'bidang_id' => 'nullable|exists:bidangs,id',
        ]);
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }
        $user->update($validated);
        return redirect()->route('pengaturan.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    public function resetPassword(User $user)
    {
        $defaultPassword = 'password123';
        $user->update([
            'password' => Hash::make($defaultPassword)
        ]);
        return redirect()->route('pengaturan.index')->with('success', "Kata sandi pengguna {$user->name} berhasil di-reset ke: {$defaultPassword}");
    }

    public function destroy(User $user)
    {
        if ($user->id == auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri');
        }
        $user->delete();
        return redirect()->route('pengaturan.index')->with('success', 'Pengguna berhasil dihapus');
    }
}

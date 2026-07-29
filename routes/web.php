<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use Illuminate\Support\Facades\Route;

// Redirect Root / directly to Login page first
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('mitra', MitraController::class);
    Route::get('/kegiatan/by-bidang/{bidangId}', [KegiatanController::class, 'byBidang'])->name('kegiatan.by-bidang');
    Route::resource('kegiatan', KegiatanController::class);
    Route::resource('periode', PeriodeController::class);
    Route::get('/monitoring/check-limit', [MonitoringController::class, 'checkLimit'])->name('monitoring.check-limit');
    Route::resource('monitoring', MonitoringController::class);

    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/process', [ImportController::class, 'process'])->name('import.process');

    // Protected Admin-Only Routes
    Route::middleware(['admin'])->group(function () {
        Route::post('pengaturan/{user}/reset-password', [PengaturanController::class, 'resetPassword'])->name('pengaturan.reset-password');
        Route::resource('pengaturan', PengaturanController::class)->parameters(['pengaturan' => 'user']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

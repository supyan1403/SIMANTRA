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
use App\Http\Controllers\SbmlMasterController;
use Illuminate\Support\Facades\Route;

// Redirect Root / directly to Login page first
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/mitra-options', [DashboardController::class, 'mitraOptions'])->name('dashboard.mitra-options');

    Route::get('/mitra/desas/{kecamatanNama}', [MitraController::class, 'getDesasByKecamatan'])->name('mitra.desas');
    Route::resource('mitra', MitraController::class);
    Route::get('/import-mitra', [MitraController::class, 'importIndex'])->name('mitra.import.index');
    Route::get('/import-mitra/template', [MitraController::class, 'importTemplate'])->name('mitra.import.template');
    Route::post('/import-mitra/preview', [MitraController::class, 'importPreview'])->name('mitra.import.preview');
    Route::post('/import-mitra/process', [MitraController::class, 'importProcess'])->name('mitra.import.process');
    Route::get('/kegiatan/by-bidang/{bidangId}', [KegiatanController::class, 'byBidang'])->name('kegiatan.by-bidang');
    Route::resource('kegiatan', KegiatanController::class);
    Route::get('/import-kegiatan', [KegiatanController::class, 'importIndex'])->name('kegiatan.import.index');
    Route::get('/import-kegiatan/template', [KegiatanController::class, 'importTemplate'])->name('kegiatan.import.template');
    Route::post('/import-kegiatan/preview', [KegiatanController::class, 'importPreview'])->name('kegiatan.import.preview');
    Route::post('/import-kegiatan/process', [KegiatanController::class, 'importProcess'])->name('kegiatan.import.process');
    Route::resource('periode', PeriodeController::class);
    Route::get('/monitoring/check-limit', [MonitoringController::class, 'checkLimit'])->name('monitoring.check-limit');
    Route::resource('monitoring', MonitoringController::class);

    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export');

    // Modul Cetak SPK & BAST
    Route::get('/spk', [\App\Http\Controllers\SpkController::class, 'index'])->name('spk.index');
    Route::post('/spk/cetak-massal', [\App\Http\Controllers\SpkController::class, 'cetakMassal'])->name('spk.cetak-massal');
    Route::get('/spk/{mitra}/cetak-utama', [\App\Http\Controllers\SpkController::class, 'cetakUtama'])->name('spk.cetak-utama');
    Route::get('/spk/{mitra}/cetak-lampiran', [\App\Http\Controllers\SpkController::class, 'cetakLampiran'])->name('spk.cetak-lampiran');
    Route::get('/spk/{mitra}/download-docx', [\App\Http\Controllers\SpkController::class, 'downloadDocx'])->name('spk.download-docx');
    Route::get('/spk-templates', [\App\Http\Controllers\SpkController::class, 'templateIndex'])->name('spk.templates.index');
    Route::post('/spk-templates', [\App\Http\Controllers\SpkController::class, 'templateStore'])->name('spk.templates.store');
    Route::put('/spk-templates/{id}', [\App\Http\Controllers\SpkController::class, 'templateUpdate'])->name('spk.templates.update');
    Route::delete('/spk-templates/{id}', [\App\Http\Controllers\SpkController::class, 'templateDestroy'])->name('spk.templates.destroy');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/process', [ImportController::class, 'process'])->name('import.process');

    // Protected Admin-Only Routes
    Route::middleware(['admin'])->group(function () {
        Route::post('pengaturan/{user}/reset-password', [PengaturanController::class, 'resetPassword'])->name('pengaturan.reset-password');
        Route::resource('pengaturan', PengaturanController::class)->parameters(['pengaturan' => 'user']);
        Route::resource('master-sbml', SbmlMasterController::class)->parameters(['master-sbml' => 'sbmlMaster'])->except(['show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

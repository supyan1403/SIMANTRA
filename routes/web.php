<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MitraController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PeriodeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\SbmlMasterController;
use Illuminate\Support\Facades\Route;

// Public Executive Landing Page
Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware(['auth', 'prevent-back-history'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/mitra-options', [DashboardController::class, 'mitraOptions'])->name('dashboard.mitra-options');

    Route::get('/mitra/desas/{kecamatanNama}', [MitraController::class, 'getDesasByKecamatan'])->name('mitra.desas');
    Route::get('/mitra/export', [MitraController::class, 'export'])->name('mitra.export');
    Route::post('/mitra/bulk-delete', [MitraController::class, 'bulkDestroy'])->name('mitra.bulk-delete');
    Route::resource('mitra', MitraController::class);

    // Master Posisi Mitra
    Route::get('/posisi-mitra/list-json', [\App\Http\Controllers\PosisiMitraController::class, 'listJson'])->name('posisi-mitra.list-json');
    Route::resource('posisi-mitra', \App\Http\Controllers\PosisiMitraController::class)->except(['create', 'show', 'edit']);
    Route::get('/import-mitra', [MitraController::class, 'importIndex'])->name('mitra.import.index');
    Route::get('/import-mitra/template', [MitraController::class, 'importTemplate'])->name('mitra.import.template');
    Route::post('/import-mitra/preview', [MitraController::class, 'importPreview'])->name('mitra.import.preview');
    Route::post('/import-mitra/process', [MitraController::class, 'importProcess'])->name('mitra.import.process');
    Route::get('/kegiatan/by-bidang/{bidangId}', [KegiatanController::class, 'byBidang'])->name('kegiatan.by-bidang');
    Route::post('/bidang/ajax-store', [KegiatanController::class, 'storeBidangAjax'])->name('bidang.ajax-store');
    Route::get('/kegiatan/export', [KegiatanController::class, 'export'])->name('kegiatan.export');
    Route::resource('kegiatan', KegiatanController::class);
    Route::get('/import-kegiatan', [KegiatanController::class, 'importIndex'])->name('kegiatan.import.index');
    Route::get('/import-kegiatan/template', [KegiatanController::class, 'importTemplate'])->name('kegiatan.import.template');
    Route::post('/import-kegiatan/preview', [KegiatanController::class, 'importPreview'])->name('kegiatan.import.preview');
    Route::post('/import-kegiatan/process', [KegiatanController::class, 'importProcess'])->name('kegiatan.import.process');
    Route::resource('periode', PeriodeController::class);
    Route::get('/monitoring/check-limit', [MonitoringController::class, 'checkLimit'])->name('monitoring.check-limit');
    Route::get('/monitoring/export', [MonitoringController::class, 'export'])->name('monitoring.export');
    Route::resource('monitoring', MonitoringController::class);

    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export');
    Route::get('/rekap/export-mantra-matrix', [RekapController::class, 'exportMantraMatrix'])->name('rekap.export-mantra-matrix');

    // Modul Cetak & Unduh Dokumen SPK & BAST (Khusus Output / Print / Download)
    Route::get('/spk', [\App\Http\Controllers\SpkController::class, 'index'])->name('spk.index');
    Route::post('/spk/cetak-massal', [\App\Http\Controllers\SpkController::class, 'cetakMassal'])->name('spk.cetak-massal');
    Route::post('/spk/unduh-massal-zip', [\App\Http\Controllers\SpkController::class, 'unduhMassalZip'])->name('spk.unduh-massal-zip');
    Route::post('/spk/reset-nomor', [\App\Http\Controllers\SpkController::class, 'resetNomor'])->name('spk.reset-nomor');
    Route::get('/spk/{mitra}/cetak-utama', [\App\Http\Controllers\SpkController::class, 'cetakUtama'])->name('spk.cetak-utama');
    Route::get('/spk/{mitra}/download-pdf', [\App\Http\Controllers\SpkController::class, 'downloadPdf'])->name('spk.download-pdf');
    Route::get('/spk/{mitra}/cetak-lampiran', [\App\Http\Controllers\SpkController::class, 'cetakLampiran'])->name('spk.cetak-lampiran');
    Route::get('/spk/{mitra}/download-lampiran-pdf', [\App\Http\Controllers\SpkController::class, 'downloadLampiranPdf'])->name('spk.download-lampiran-pdf');
    Route::get('/spk-templates', [\App\Http\Controllers\SpkController::class, 'templateIndex'])->name('spk.templates.index');
    Route::post('/spk-templates', [\App\Http\Controllers\SpkController::class, 'templateStore'])->name('spk.templates.store');
    Route::get('/spk-templates/{id}/download', [\App\Http\Controllers\SpkController::class, 'templateDownload'])->name('spk.templates.download');
    Route::put('/spk-templates/{id}', [\App\Http\Controllers\SpkController::class, 'templateUpdate'])->name('spk.templates.update');
    Route::delete('/spk-templates/{id}', [\App\Http\Controllers\SpkController::class, 'templateDestroy'])->name('spk.templates.destroy');

    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::get('/import/template-universal', [ImportController::class, 'downloadUniversalTemplate'])->name('import.template-universal');
    Route::get('/import/template-mantra', [ImportController::class, 'downloadMantraTemplate'])->name('import.template-mantra');
    Route::get('/import/template-mitra', [ImportController::class, 'downloadMitraTemplate'])->name('import.template-mitra');
    Route::get('/import/template-kegiatan', [ImportController::class, 'downloadKegiatanTemplate'])->name('import.template-kegiatan');
    Route::post('/import/preview', [ImportController::class, 'preview'])->name('import.preview');
    Route::post('/import/process', [ImportController::class, 'process'])->name('import.process');
    Route::post('/import/mitra-kepka/process', [ImportController::class, 'processMitraKepkaSE'])->name('import.mitra-kepka.process');

    // Protected Admin-Only Routes
    Route::middleware(['admin'])->group(function () {
        Route::post('pengaturan/{user}/reset-password', [PengaturanController::class, 'resetPassword'])->name('pengaturan.reset-password');
        Route::resource('pengaturan', PengaturanController::class)->parameters(['pengaturan' => 'user']);
        Route::resource('master-sbml', SbmlMasterController::class)->parameters(['master-sbml' => 'sbmlMaster'])->except(['show']);

        // Modul Manajemen Database (Khusus Administrator)
        Route::get('/manajemen-database', [\App\Http\Controllers\DatabaseController::class, 'index'])->name('database.index');
        Route::get('/manajemen-database/backup', [\App\Http\Controllers\DatabaseController::class, 'backup'])->name('database.backup');
        Route::post('/manajemen-database/restore', [\App\Http\Controllers\DatabaseController::class, 'restore'])->name('database.restore');
        Route::post('/manajemen-database/optimize', [\App\Http\Controllers\DatabaseController::class, 'optimize'])->name('database.optimize');
        Route::post('/manajemen-database/wipe', [\App\Http\Controllers\DatabaseController::class, 'wipe'])->name('database.wipe');
        Route::get('/manajemen-database/backup-download/{filename}', [\App\Http\Controllers\DatabaseController::class, 'downloadBackup'])->name('database.download-backup');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

<?php

namespace App\Http\Controllers;

use App\Models\AlokasiHonor;
use App\Models\Bidang;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\Periode;
use App\Models\PosisiMitra;
use App\Models\SbmlMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DatabaseController extends Controller
{
    /**
     * Tampilkan halaman status dan manajemen database.
     */
    public function index()
    {
        $connection = config('database.default', 'sqlite');
        $dbPath = config("database.connections.{$connection}.database", database_path('database.sqlite'));

        $dbSize = 0;
        $dbLastModified = '-';
        if (File::exists($dbPath)) {
            $dbSize = File::size($dbPath);
            $dbLastModified = date('d M Y H:i:s', File::lastModified($dbPath));
        }

        // Hitung statistik baris data di setiap tabel aktif
        $tables = [
            [
                'name' => 'users',
                'label' => 'Pengguna & Petugas Sistem',
                'category' => 'Master',
                'count' => User::count(),
                'icon' => 'bi-person-badge',
            ],
            [
                'name' => 'mitras',
                'label' => 'Database Mitra Statistik',
                'category' => 'Master',
                'count' => Mitra::count(),
                'icon' => 'bi-people',
            ],
            [
                'name' => 'posisi_mitras',
                'label' => 'Master Posisi Mitra',
                'category' => 'Master',
                'count' => Schema::hasTable('posisi_mitras') ? PosisiMitra::count() : 0,
                'icon' => 'bi-person-vcard',
            ],
            [
                'name' => 'bidangs',
                'label' => 'Bidang / Bagian Kerja',
                'category' => 'Master',
                'count' => Bidang::count(),
                'icon' => 'bi-building',
            ],
            [
                'name' => 'kegiatans',
                'label' => 'Kegiatan & Anggaran Statistik',
                'category' => 'Master',
                'count' => Kegiatan::count(),
                'icon' => 'bi-briefcase',
            ],
            [
                'name' => 'periodes',
                'label' => 'Periode Anggaran (Bulan/Tahun)',
                'category' => 'Master',
                'count' => Periode::count(),
                'icon' => 'bi-calendar3',
            ],
            [
                'name' => 'sbml_masters',
                'label' => 'Master Batas Tarif SBML',
                'category' => 'Master',
                'count' => Schema::hasTable('sbml_masters') ? SbmlMaster::count() : 0,
                'icon' => 'bi-piggy-bank',
            ],
            [
                'name' => 'document_templates',
                'label' => 'Template Dokumen SPK/BAST',
                'category' => 'Master',
                'count' => Schema::hasTable('document_templates') ? \App\Models\DocumentTemplate::count() : 0,
                'icon' => 'bi-file-earmark-richtext',
            ],
            [
                'name' => 'alokasi_honors',
                'label' => 'Alokasi Honor & Penomoran Dokumen',
                'category' => 'Transaksi',
                'count' => AlokasiHonor::count(),
                'icon' => 'bi-cash-stack',
            ],
        ];

        $totalRecords = collect($tables)->sum('count');

        // Daftar file auto-backup yang tersimpan di storage
        $backupDir = storage_path('app/backups');
        $backups = [];
        if (File::isDirectory($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $f) {
                if ($f->getExtension() === 'sqlite') {
                    $backups[] = [
                        'filename' => $f->getFilename(),
                        'size' => round($f->getSize() / 1024, 2) . ' KB',
                        'created_at' => date('d M Y H:i:s', $f->getMTime()),
                        'path' => $f->getPathname(),
                    ];
                }
            }
            // Sort terbaru di atas
            usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        }

        return view('database.index', compact(
            'connection',
            'dbPath',
            'dbSize',
            'dbLastModified',
            'tables',
            'totalRecords',
            'backups'
        ));
    }

    /**
     * Unduh salinan cadangan file database (.sqlite).
     */
    public function backup()
    {
        $connection = config('database.default', 'sqlite');
        $dbPath = config("database.connections.{$connection}.database", database_path('database.sqlite'));

        if (!File::exists($dbPath)) {
            return back()->with('error', 'File database tidak ditemukan.');
        }

        $filename = 'SIMANTRA_Backup_' . date('Y-m-d_His') . '.sqlite';
        return response()->download($dbPath, $filename, [
            'Content-Type' => 'application/x-sqlite3',
        ]);
    }

    /**
     * Restore database dari file unggahan.
     */
    public function restore(Request $request)
    {
        $request->validate([
            'backup_file' => 'required|file|max:51200', // Max 50MB
        ]);

        $uploadedFile = $request->file('backup_file');
        $ext = strtolower($uploadedFile->getClientOriginalExtension());

        if (!in_array($ext, ['sqlite', 'db', 'sqlite3'])) {
            return back()->with('error', 'Format file tidak valid. Harap unggah file cadangan berekstensi .sqlite.');
        }

        $connection = config('database.default', 'sqlite');
        $dbPath = config("database.connections.{$connection}.database", database_path('database.sqlite'));

        try {
            // 1. Buat cadangan darurat DB saat ini sebelum ditimpa
            $this->createEmergencyBackup('before_restore');

            // 2. Timpa dengan file baru
            File::copy($uploadedFile->getRealPath(), $dbPath);

            return redirect()->route('database.index')->with('success', 'Database berhasil dipulihkan (Restore) dari file backup!');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Jalankan optimasi database SQLite (VACUUM & ANALYZE).
     */
    public function optimize()
    {
        try {
            $connection = config('database.default', 'sqlite');
            if ($connection === 'sqlite') {
                DB::statement('VACUUM;');
                DB::statement('ANALYZE;');
            }

            return redirect()->route('database.index')->with('success', 'Optimasi database (VACUUM & ANALYZE) berhasil dijalankan! Ruang penyimpanan telah dirapikan.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengoptimasi database: ' . $e->getMessage());
        }
    }

    /**
     * Hapus / Reset Database dengan verifikasi keamanan ganda.
     */
    public function wipe(Request $request)
    {
        $request->validate([
            'wipe_type' => 'required|in:transaksi,mitra,kegiatan,factory_reset',
            'password' => 'required|string',
            'confirm_phrase' => 'required|string',
        ]);

        // 1. Verifikasi Password Admin yang sedang login
        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Konfirmasi password Administrator salah! Tindakan dibatalkan.');
        }

        // 2. Verifikasi Frasa Persetujuan
        if (strtoupper(trim($request->confirm_phrase)) !== 'HAPUS-DATABASE-SIMANTRA') {
            return back()->with('error', 'Frasa konfirmasi tidak cocok. Ketik tepat: HAPUS-DATABASE-SIMANTRA.');
        }

        try {
            // 3. Auto-Backup Darurat Otomatis ke storage/app/backups/
            $backupName = $this->createEmergencyBackup('before_wipe_' . $request->wipe_type);

            $wipeType = $request->wipe_type;
            $deletedInfo = '';

            DB::beginTransaction();

            if ($wipeType === 'transaksi') {
                // Hapus hanya data transaksi honor & SPK/BAST
                $count = AlokasiHonor::count();
                AlokasiHonor::query()->delete();
                $deletedInfo = "Seluruh data transaksi alokasi honor ({$count} data) berhasil dihapus.";
            } elseif ($wipeType === 'mitra') {
                // Hapus data transaksi dan data mitra
                AlokasiHonor::query()->delete();
                $mCount = Mitra::count();
                Mitra::query()->delete();
                $deletedInfo = "Seluruh data mitra ({$mCount} data) dan riwayat alokasi terkait berhasil dihapus.";
            } elseif ($wipeType === 'kegiatan') {
                // Hapus data transaksi dan kegiatan
                AlokasiHonor::query()->delete();
                $kCount = Kegiatan::count();
                Kegiatan::query()->delete();
                $deletedInfo = "Seluruh data kegiatan ({$kCount} data) dan riwayat alokasi terkait berhasil dihapus.";
            } elseif ($wipeType === 'factory_reset') {
                // Reset Total: Bersihkan transaksi, mitra, kegiatan operasional
                AlokasiHonor::query()->delete();
                Mitra::query()->delete();
                Kegiatan::query()->delete();

                // SELURUH AKUN ADMIN & OPERATOR 100% DIPERTAHANKAN (TIDAK DIHAPUS)
                $deletedInfo = "Factory Reset berhasil dijalankan. Seluruh data operasional (Mitra, Kegiatan, Alokasi Honor) telah dikosongkan. Seluruh akun Administrator dan Operator tetap tersimpan aman.";
            }

            DB::commit();

            // Jalankan VACUUM setelah penghapusan
            if (config('database.default') === 'sqlite') {
                DB::statement('VACUUM;');
            }

            return redirect()->route('database.index')->with('success', "{$deletedInfo} Cadangan darurat otomatis telah disimpan ({$backupName}).");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menghapus database: ' . $e->getMessage());
        }
    }

    /**
     * Unduh file auto-backup darurat dari storage.
     */
    public function downloadBackup($filename)
    {
        $path = storage_path('app/backups/' . basename($filename));
        if (!File::exists($path)) {
            return back()->with('error', 'File cadangan tidak ditemukan.');
        }

        return response()->download($path);
    }

    /**
     * Helper: Buat salinan cadangan otomatis ke folder storage/app/backups
     */
    protected function createEmergencyBackup(string $prefix = 'auto_backup'): string
    {
        $backupDir = storage_path('app/backups');
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $connection = config('database.default', 'sqlite');
        $dbPath = config("database.connections.{$connection}.database", database_path('database.sqlite'));

        $filename = "{$prefix}_" . date('Ymd_His') . '.sqlite';
        $destPath = "{$backupDir}/{$filename}";

        if (File::exists($dbPath)) {
            File::copy($dbPath, $destPath);
        }

        return $filename;
    }
}

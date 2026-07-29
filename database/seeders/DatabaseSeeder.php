<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Bidang;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin User
        User::firstOrCreate(
            ['email' => 'admin@bps.go.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Operator User
        User::firstOrCreate(
            ['email' => 'operator@bps.go.id'],
            [
                'name' => 'Operator SIMANTRA',
                'password' => Hash::make('password'),
                'role' => 'operator',
            ]
        );

        // Bidang
        $bidangs = ['Distribusi', 'Neraca', 'Produksi', 'Sosial', 'Cadangan'];
        foreach ($bidangs as $nama) {
            Bidang::firstOrCreate(['nama' => $nama]);
        }
    }
}

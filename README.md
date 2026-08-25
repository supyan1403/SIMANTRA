<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="300" alt="SIMANTRA Banner">
</p>

<h1 align="center">✨ SIMANTRA ✨</h1>
<p align="center">
  <strong>Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra</strong><br>
  <em>Solusi Terpadu Manajemen Mitra Statistik, Validasi Limit SBML, dan Otomasi Dokumen SPK/BAST BPS</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-%3E%3D8.2-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP >= 8.2">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/Vite-6.x-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License MIT">
</p>

---

## 📌 Daftar Isi
1. [Tentang SIMANTRA](#-tentang-simantra)
2. [Fitur Unggulan](#-fitur-unggulan)
3. [Diagram Sistem (Mermaid)](#-diagram-sistem)
   - [1. Use Case Diagram](#1-use-case-diagram)
   - [2. Activity Diagram (Alur Alokasi Honor & Validasi SBML)](#2-activity-diagram-alur-alokasi-honor--validasi-sbml)
   - [3. Entity-Relationship Diagram (ERD)](#3-entity-relationship-diagram-erd)
   - [4. Diagram Arsitektur Aplikasi](#4-diagram-arsitektur-aplikasi)
4. [Persyaratan Sistem (Prerequisites)](#-persyaratan-sistem)
5. [Panduan Instalasi & Setup](#-panduan-instalasi--setup)
6. [Akun Pengguna Default (Default Credentials)](#-akun-pengguna-default)
7. [Panduan Menjalankan Pengujian (Testing)](#-pengujian-otomatis)
8. [Lisensi](#-lisensi)

---

## 📖 Tentang SIMANTRA

**SIMANTRA** (*Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra*) adalah platform berbasis web modern yang dirancang untuk mendukung operasional Badan Pusat Statistik (BPS) dalam:
- Mengelola data master mitra statistik dan sebaran wilayah kerja (Kecamatan/Desa).
- Mengontrol alokasi beban kerja dan penugasan kegiatan per bidang statistik.
- **Mencegah pelanggaran pagu anggaran / Standar Biaya Masukan Lainnya (SBML)** secara *real-time* sebelum data dikomit.
- Mengotomatiskan pembuatan dan pencetakan dokumen legalitas: **Surat Perjanjian Kerja (SPK)** dan **Berita Acara Serah Terima (BAST)** baik satuan maupun massal.
- Menyediakan rekapitulasi data dan ekspor data sesuai standar format pelaporan MANTRA.

---

## 🚀 Fitur Unggulan

| Fitur | Deskripsi |
| :--- | :--- |
| 📊 **Executive Dashboard** | Menampilkan metrik real-time statistik mitra, grafik distribusi alokasi per bidang kegiatan, dan deteksi peringatan honor mendekati/melebihi limit. |
| 👥 **Manajemen Master Mitra** | Pengelolaan data mitra terintegrasi (ID Sobat, Nama, NIK/No HP, Jenis Kelamin, Wilayah Kecamatan & Desa) dengan dukungan Import Excel batch. |
| 📋 **Manajemen Kegiatan & Jadwal** | Pengelompokan kegiatan per bidang (Distribusi, Neraca, Produksi, Sosial, Cadangan) disertai kode mata anggaran dan matriks jadwal bulanan. |
| ⚠️ **Validasi Real-time Limit SBML** | Sistem otomatis memvalidasi total akumulasi honor per mitra dalam satu bulan anggaran dan memunculkan peringatan/pencegahan jika melebihi pagu SBML. |
| 📄 **Generator & Cetak SPK/BAST** | Cetak langsung (Print view PDF/Browser) atau unduh DOCX untuk SPK Utama, Lampiran Rincian, dan BAST dengan penomoran surat otomatis & template dinamis. |
| 📥 **Import & Export Excel** | Dukungan impor data mitra & kegiatan serta ekspor rekapitulasi alokasi bulanan format Excel (.xlsx) menggunakan PhpSpreadsheet. |
| 🛡️ **Role-Based Access Control** | Pembagian hak akses terproteksi antara **Administrator** (Kelola User, Template Dokumen, Master SBML) dan **Operator** (Kelola Operasional Alokasi). |

---

## 📐 Diagram Sistem

### 1. Use Case Diagram
Diagram ini memetakan interaksi fungsional antara aktor (**Pengunjung Publik**, **Operator SIMANTRA**, dan **Administrator**) dengan sistem.

```mermaid
flowchart LR
    %% Actors
    Public["👤 Pengunjung Publik"]
    Operator["🧑‍💼 Operator SIMANTRA"]
    Admin["👨‍💻 Administrator"]

    subgraph SIMANTRA_System [" Aplikasi SIMANTRA "]
        UC1["Melihat Landing Page & Statistik Publik"]
        UC2["Autentikasi (Login / Logout / Profil)"]
        UC3["Monitoring Dashboard & Rekapitulasi"]
        UC4["Kelola Data Mitra & Import Excel"]
        UC5["Kelola Kegiatan, Bidang & Jadwal"]
        UC6["Alokasi Honor Mitra & Validasi Limit SBML"]
        UC7["Export Rekap Honor (Format Excel/MANTRA)"]
        UC8["Generate & Cetak Dokumen SPK / BAST"]
        UC9["Kelola Template Dokumen SPK/BAST"]
        UC10["Kelola Master SBML & Pagu Honor"]
        UC11["Manajemen Akun Pengguna & Reset Password"]
    end

    %% Public Relations
    Public --> UC1

    %% Operator Relations
    Operator --> UC2
    Operator --> UC3
    Operator --> UC4
    Operator --> UC5
    Operator --> UC6
    Operator --> UC7
    Operator --> UC8

    %% Admin Relations (Inherits Operator + System Management)
    Admin --> UC2
    Admin --> UC3
    Admin --> UC4
    Admin --> UC5
    Admin --> UC6
    Admin --> UC7
    Admin --> UC8
    Admin --> UC9
    Admin --> UC10
    Admin --> UC11
```

---

### 2. Activity Diagram (Alur Alokasi Honor & Validasi SBML)
Diagram aktivitas berikut menggambarkan alur kerja mulai dari penugasan mitra pada kegiatan, kalkulasi otomatis batas limit SBML, hingga penerbitan dokumen SPK/BAST.

```mermaid
flowchart TD
    Start([🟢 Mulai]) --> Login[Operator Login ke SIMANTRA]
    Login --> OpenMonitoring[Buka Menu Monitoring / Alokasi Honor]
    OpenMonitoring --> SelectFilter[Pilih Periode Tahun/Bulan & Mitra]
    SelectFilter --> InputAlokasi[Input / Pilih Kegiatan & Nominal Honor]
    
    InputAlokasi --> CheckLimit{Sistem Cek Akumulasi Honor vs Limit SBML}
    
    CheckLimit -- "Total Honor > Pagu SBML (Over Limit)" --> ShowWarning[Sistem Tampilkan Peringatan / Limit Alert ⚠️]
    ShowWarning --> DecisionOver{Tetap Lanjutkan Alokasi?}
    DecisionOver -- "Tidak / Revisi" --> InputAlokasi
    DecisionOver -- "Ya / Konfirmasi" --> SaveData[Simpan Alokasi Honor ke Database]

    CheckLimit -- "Total Honor <= Pagu SBML (Aman)" --> SaveData
    
    SaveData --> UpdateRekap[Sistem Perbarui Rekapitulasi & Log Monitoring]
    UpdateRekap --> GenerateDocs[Generate Dokumen SPK & BAST Mitra]
    
    GenerateDocs --> ActionDocs{Pilih Aksi Dokumen}
    ActionDocs -- "Cetak Satuan / Massal" --> PrintDoc[Cetak SPK Utama / Lampiran / BAST]
    ActionDocs -- "Download File" --> DownloadWord[Unduh Dokumen Format DOCX / Word]
    ActionDocs -- "Export Laporan" --> ExportExcel[Export Rekapitulasi ke Excel]

    PrintDoc --> End([🔴 Selesai])
    DownloadWord --> End
    ExportExcel --> End
```

---

### 3. Entity-Relationship Diagram (ERD)
Diagram relasi entitas basis data SIMANTRA memperlihatkan keterhubungan antartabel utama.

```mermaid
erDiagram
    USERS ||--o{ ALOKASI_HONORS : "dikelola_oleh"
    BIDANGS ||--o{ KEGIATANS : "memayungi"
    KEGIATANS ||--o{ KEGIATAN_JADWALS : "memiliki_jadwal"
    KEGIATANS ||--o{ ALOKASI_HONORS : "dialokasikan_pada"
    PERIODES ||--o{ ALOKASI_HONORS : "periode_anggaran"
    PERIODES ||--o{ SBMLS : "periode_sbml"
    MITRAS ||--o{ ALOKASI_HONORS : "menerima"
    MITRAS ||--o{ SBMLS : "memiliki_batasan"
    KECAMATANS ||--o{ DESAS : "membawahi"
    KECAMATANS ||--o{ MITRAS : "domisili_kecamatan"
    DESAS ||--o{ MITRAS : "domisili_desa"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role "admin, operator"
        string foto_profil
        timestamp created_at
    }

    BIDANGS {
        bigint id PK
        string nama "Distribusi, Neraca, Produksi, Sosial, Cadangan"
        timestamp created_at
    }

    KEGIATANS {
        bigint id PK
        bigint bidang_id FK
        string nama
        string kode_mata_anggaran
        string tahun
        timestamp created_at
    }

    KEGIATAN_JADWALS {
        bigint id PK
        bigint kegiatan_id FK
        tinyint bulan "1 - 12"
        boolean status_aktif
    }

    PERIODES {
        bigint id PK
        string tahun
        string bulan
        tinyint bulan_angka
        timestamp created_at
    }

    MITRAS {
        bigint id PK
        string id_sobat UK
        string nama
        enum jk "L, P"
        string no_hp
        text alamat
        string pekerjaan
        string kode_alamat
        string kecamatan
        string desa
        timestamp created_at
    }

    ALOKASI_HONORS {
        bigint id PK
        bigint mitra_id FK
        bigint periode_id FK
        bigint kegiatan_id FK
        decimal nominal
        string nomor_dokumen
        timestamp created_at
    }

    SBMLS {
        bigint id PK
        bigint mitra_id FK
        bigint periode_id FK
        string jenis
        decimal nominal
        timestamp created_at
    }

    SBML_MASTERS {
        bigint id PK
        string jenis UK
        decimal nominal_default
        string keterangan
    }

    DOCUMENT_TEMPLATES {
        bigint id PK
        string template_name
        text content
        text header
        text footer
        boolean is_active
    }

    KECAMATANS {
        bigint id PK
        string kode_kecamatan UK
        string nama_kecamatan
    }

    DESAS {
        bigint id PK
        bigint kecamatan_id FK
        string kode_desa
        string nama_desa
    }
```

---

### 4. Diagram Arsitektur Aplikasi
Struktur arsitektur berlapis (*layered architecture*) aplikasi SIMANTRA:

```mermaid
graph TD
    subgraph ClientLayer [" 🖥️ Client Layer (Frontend) "]
        Browser["Web Browser (User / Admin)"]
        BladeUI["Laravel Blade Views & Components"]
        TailwindVite["Tailwind CSS 3 + Vite Asset Pipeline"]
        Browser <--> BladeUI
        BladeUI <--> TailwindVite
    end

    subgraph AppLayer [" ⚙️ Application Layer (Laravel 12 Backend) "]
        Router["Routing & Middleware (Auth, PreventBackHistory, AdminMiddleware)"]
        
        subgraph Controllers [" Controllers "]
            DashCtrl["Dashboard & Landing Controller"]
            MitraCtrl["Mitra & Wilayah Controller"]
            KegCtrl["Kegiatan & Jadwal Controller"]
            MonCtrl["Monitoring & Alokasi Controller"]
            SpkCtrl["SPK & BAST Document Generator"]
            ImpExpCtrl["Import & Export Controller"]
            AdmCtrl["User Management & SBML Controller"]
        end

        subgraph Services [" Logic & Utility "]
            SbmlValidator["SBML Limit Real-time Validator"]
            SpkNumbering["Dynamic SPK/BAST Numbering Engine"]
            ExcelEngine["PhpSpreadsheet Importer / Exporter"]
        end

        Router --> Controllers
        Controllers --> Services
    end

    subgraph DataLayer [" 🗄️ Database & Storage Layer "]
        Eloquent["Eloquent ORM Models"]
        MySQL[("Database (MySQL 8 / SQLite)")]
        Storage[("Local / Public Storage (Templates & Exports)")]
        
        Services --> Eloquent
        Controllers --> Eloquent
        Eloquent <--> MySQL
        SpkCtrl <--> Storage
        ImpExpCtrl <--> Storage
    end

    BladeUI <--> Router
```

---

## 💻 Persyaratan Sistem

Sebelum melakukan instalasi, pastikan lingkungan server/komputer Anda memenuhi spesifikasi berikut:

- **PHP**: Versi `>= 8.2`
- **Ekstensi PHP Wajib**:
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql` *(atau `pdo_sqlite`)*, `session`, `tokenizer`, `xml`, `zip`, `gd`
- **Composer**: Versi `>= 2.2`
- **Node.js & NPM**: Node.js `>= 18.x` & NPM `>= 9.x`
- **Database**: MySQL `>= 8.0` / MariaDB `>= 10.4` / SQLite `>= 3.35`
- **Git**: Versi terbaru

---

## 🛠️ Panduan Instalasi & Setup

Ikuti langkah-langkah berikut untuk menginstal SIMANTRA secara lokal pada komputer atau server pengembangan:

### 1. Kloning Repositori
```bash
git clone https://github.com/username/SIMANTRA.git
cd SIMANTRA
```

### 2. Instal Dependensi Backend (PHP)
```bash
composer install
```

### 3. Instal Dependensi Frontend (Node.js)
```bash
npm install
```

### 4. Konfigurasi File Environment (`.env`)
Salin file template environment dan buat application key:
```bash
# Untuk Linux / macOS / Git Bash:
cp .env.example .env

# Untuk Windows (Command Prompt / PowerShell):
copy .env.example .env
```

Buka file `.env` dengan teks editor, lalu sesuaikan koneksi database Anda:
```env
APP_NAME="SIMANTRA"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Database (Contoh MySQL)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simantra_db
DB_USERNAME=root
DB_PASSWORD=
```
*(Catatan: Buat database baru bernama `simantra_db` pada phpMyAdmin / MySQL client Anda jika menggunakan MySQL).*

Kemudian generate application key:
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi & Seeder Data Awal
Perintah ini akan membuat seluruh tabel basis data, mengisi data master bidang, kecamatan, desa, aturan SBML, dan data bawaan MANTRA:
```bash
php artisan migrate --seed
```

### 6. Buat Symbolic Link Storage
Untuk memastikan berkas profil dan dokumen template dapat diakses:
```bash
php artisan storage:link
```

### 7. Kompilasi Aset Frontend
```bash
# Untuk mode pengembangan (Hot Reload):
npm run dev

# ATAU untuk membuat bundle produksi:
npm run build
```

### 8. Jalankan Server Aplikasi
Buka terminal baru dan jalankan server lokal Laravel:
```bash
php artisan serve
```
Aplikasi sekarang dapat diakses melalui browser di alamat: **`http://localhost:8000`** 🚀

> 💡 **Tip:** Anda juga dapat menjalankan server dan asset watcher secara bersamaan menggunakan perintah:
> ```bash
> composer run dev
> ```

---

## 🔑 Akun Pengguna Default

Database Seeder telah menyediakan 2 (dua) akun pengguna siap pakai:

| Role | Email | Password | Hak Akses & Kemampuan |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@bps.go.id` | `password` | Akses penuh seluruh sistem: Kelola Pengguna, Reset Password, Kelola Master SBML, Template Dokumen SPK/BAST, Manajemen Mitra & Kegiatan. |
| **Operator** | `operator@bps.go.id` | `password` | Akses operasional: Kelola Mitra, Kelola Kegiatan & Jadwal, Alokasi Honor, Validasi SBML, Monitoring, Cetak SPK/BAST & Export Excel. |

---

## 🧪 Pengujian Otomatis

SIMANTRA dilengkapi dengan rangkaian pengujian fitur (*Feature & Unit Tests*) menggunakan PHPUnit:

```bash
# Menjalankan seluruh pengujian
php artisan test

# Menjalankan pengujian fitur penomoran SPK
php artisan test --filter=SpkNumberingTest

# Menjalankan pengujian otentikasi & keamanan riwayat halaman
php artisan test --filter=SecurityBackHistoryTest
```

---

## 📁 Struktur Direktori Utama

```
SIMANTRA/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Controller (Mitra, Kegiatan, Monitoring, Spk, dsb.)
│   │   └── Middleware/        # Middleware (AdminMiddleware, PreventBackHistory)
│   └── Models/                # Eloquent Models (Mitra, AlokasiHonor, Sbml, dsb.)
├── database/
│   ├── migrations/            # Skema migrasi database
│   └── seeders/               # Seeder data master, kecamatan/desa & mantra_db
├── public/                    # Entry point aplikasi (index.php) & aset terkompilasi
├── resources/
│   ├── css/                   # Stylesheet Tailwind CSS
│   ├── js/                    # Skrip JavaScript & Vite
│   └── views/                 # Blade Templates (Dashboard, SPK, Mitra, Rekap)
├── routes/
│   ├── web.php                # Rute utama aplikasi web
│   └── auth.php               # Rute autentikasi Laravel Breeze
└── tests/                     # Unit & Feature Test cases
```

---

## 📄 Lisensi

Proyek aplikasi SIMANTRA dilisensikan di bawah [MIT License](LICENSE).
Dikembangkan untuk mendukung efisiensi tata kelola administrasi dan monitoring mitra statistik BPS.

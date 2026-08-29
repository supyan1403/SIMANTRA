<p align="center">
  <img src="public/images/logo_kendi_trans.png" width="160" alt="SIMANTRA Logo Emblem">
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
  <img src="https://img.shields.io/badge/SQLite-3.35-003B57?style=for-the-badge&logo=sqlite&logoColor=white" alt="SQLite">
  <img src="https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge" alt="License MIT">
</p>

## 📌 Navigasi & Daftar Isi

| 📂 Kategori | 🔗 Pintasan Bagian & Diagram | 📝 Deskripsi |
| :--- | :--- | :--- |
| **🌟 Ringkasan Proyek** | • [Tentang SIMANTRA](#-tentang-simantra)<br>• [Fitur Unggulan](#-fitur-unggulan)<br>• [Struktur Direktori](#-struktur-direktori-utama) | Ikhtisar sistem BPS, keunggulan fitur, dan hierarki folder. |
| **📐 Diagram Rancang Bangun** | **Alur & Wewenang:**<br>• [1. Use Case Diagram](#1-use-case-diagram)<br>• [2. Activity Diagram (Alokasi & SBML)](#2-activity-diagram-alokasi-honor--validasi-sbml)<br>• [3. Sequence Validasi Limit SBML](#3-sequence-diagram-validasi-real-time-limit-sbml)<br>• [4. Sequence Cetak SPK/BAST](#4-sequence-diagram-generate--cetak-dokumen-spkbast)<br><br>**Data & Arsitektur:**<br>• [5. Entity-Relationship Diagram (ERD)](#5-entity-relationship-diagram-erd)<br>• [6. Class Diagram (Model Eloquent)](#6-class-diagram-domain-model-eloquent)<br>• [7. State Machine Status Alokasi](#7-state-machine-diagram-siklus-status-alokasi--dokumen)<br>• [8. Arsitektur & Deployment Server](#8-diagram-arsitektur--deployment-sistem) | Kumpulan 8 diagram visualisasi alur bisnis, database relasional, dan arsitektur sistem. |
| **🛠️ Instalasi & Konfigurasi** | • [Persyaratan Sistem](#-persyaratan-sistem)<br>• [Panduan Instalasi Langkah-demi-Langkah](#-panduan-instalasi--setup)<br>• [Kredensial Akun Default](#-akun-pengguna-default) | Petunjuk instalasi lokal, migrasi database, dan data akun login. |
| **🧪 Testing & Legalitas** | • [Pengujian Otomatis (PHPUnit)](#-pengujian-otomatis)<br>• [Lisensi Proyek](#-lisensi) | Perintah eksekusi test cases dan lisensi open-source MIT. |

---

## 📖 Tentang SIMANTRA

**SIMANTRA** (*Sistem Informasi Monitoring Alokasi Pekerjaan dan Honor Mitra*) adalah platform web terpadu yang dirancang khusus untuk mempermudah tata kelola administrasi mitra pada Badan Pusat Statistik (BPS).

Sistem ini hadir untuk mengatasi tantangan operasional seperti:
- Pengawasan alokasi pekerjaan mitra lintas bidang statistik agar merata.
- **Pencegahan pelanggaran pagu anggaran / Standar Biaya Masukan Lainnya (SBML)** secara otomatis sebelum Surat Perjanjian Kerja (SPK) diterbitkan.
- Otomasi pembuatan dan pencetakan dokumen legalitas: **SPK Utama**, **Lampiran Rincian Tugas**, dan **Berita Acara Serah Terima (BAST)** dalam format cetak maupun file Word (.docx).
- Rekapitulasi honor bulanan mitra yang siap diekspor ke format pelaporan standar Excel.

---

## 🚀 Fitur Unggulan

| Modul / Fitur | Rincian Fungsionalitas |
| :--- | :--- |
| 📊 **Executive Dashboard** | Grafik distribusi honor per bidang, statistik beban kerja mitra, animasi counter angka, dan indikator status batas SBML multi-tugas. |
| 👥 **Master Mitra & Wilayah** | Data lengkap mitra statistik (ID Sobat, Nama, NIK/No HP, Jenis Kelamin, Wilayah Kecamatan & Desa) dengan pagination tabel (15 data/hal) dan batch Import Excel. |
| 📋 **Master Kegiatan & Jadwal** | Pengelompokan kegiatan per bidang statistik (Distribusi, Neraca, Produksi, Sosial, Cadangan), kode mata anggaran, matriks jadwal, dan deteksi otomatis kategori tugas (Badge Pencacahan / Pengolahan). |
| 🛡️ **Validasi SBML Multi-Tugas** | Pengecekan real-time batas honor bulanan per kategori tugas (Pencacahan Lapangan, Pengolahan Data, dan Total Gabungan) dengan master pagu per tahun anggaran di `/master-sbml`. |
| 🔢 **Penomoran Fleksibel & CRUD Pola** | Modul penomoran SPK/BAST dengan pengelolaan pola nomor dinamis (Tambah, Edit, Hapus, Reset ke Standar BPS), live preview nomor pertama, dan deteksi nomor terakhir di database. |
| 📄 **Generator SPK & BAST (Word & PDF)** | Pembuatan dokumen SPK Utama, Lampiran Rincian Tugas, dan BAST dalam format cetak browser, berkas Microsoft Word (.docx), dan berkas PDF otentik siap pakai satuan maupun massal. |
| 📥 **Import & Export Excel** | Dukungan impor master data mitra/kegiatan dan ekspor rekap honor format Excel (.xlsx) menggunakan PhpSpreadsheet. |
| 🔐 **Manajemen Hak Akses (RBAC)** | Pemisahan peran antara **Administrator** (Full System & User Control) dan **Operator** (Operasional Alokasi & Pencetakan). |

---

## 📐 Diagram Sistem & Penjelasan

Berikut adalah kumpulan diagram rancang bangun sistem SIMANTRA yang disajikan menggunakan standar **Mermaid** beserta penjelasan detail di setiap diagramnya.

---

### 1. Use Case Diagram
Diagram ini memetakan pembagian wewenang dan hak akses fungsionalitas sistem secara terstruktur per modul.

```mermaid
flowchart LR
    %% Definisi Aktor
    Public["👤 Pengunjung Publik"]
    Operator["🧑‍💼 Operator SIMANTRA"]
    Admin["👨‍💻 Administrator"]

    %% Hubungan Inheritance Hak Akses (Admin memiliki semua hak akses Operator)
    Admin -.->|Mewarisi Wewenang| Operator

    subgraph ModulPublik [" 🌐 1. Modul Publik & Akses "]
        direction TB
        UC_Landing["Melihat Landing Page & Statistik"]
        UC_Auth["Autentikasi (Login / Kelola Profil)"]
    end

    subgraph ModulOperasional [" 📋 2. Modul Operasional Alokasi "]
        direction TB
        UC_Mitra["Kelola Data Mitra & Import Excel"]
        UC_Kegiatan["Kelola Kegiatan & Jadwal Bidang"]
        UC_Monitoring["Alokasi Honor & Cek Limit SBML"]
        UC_SPK["Generate & Cetak Dokumen SPK / BAST"]
        UC_Rekap["Export Laporan Rekapitulasi (Excel)"]
    end

    subgraph ModulAdmin [" ⚙️ 3. Modul Khusus Administrator "]
        direction TB
        UC_UserMgmt["Kelola Akun Operator & Reset Password"]
        UC_SBMLMaster["Kelola Master Pagu Standar SBML"]
        UC_Template["Kelola & Edit Template SPK/BAST"]
    end

    %% Hubungan Garis Rapi per Modul
    Public --> UC_Landing

    Operator --> UC_Auth
    Operator --> UC_Mitra
    Operator --> UC_Kegiatan
    Operator --> UC_Monitoring
    Operator --> UC_SPK
    Operator --> UC_Rekap

    Admin --> UC_UserMgmt
    Admin --> UC_SBMLMaster
    Admin --> UC_Template
```

#### 📝 Penjelasan Use Case:
1. **Pengunjung Publik**: Hanya dapat mengakses landing page publik untuk melihat ringkasan informasi dan transparansi statistik umum.
2. **Operator SIMANTRA**: Bertugas mengelola data operasional harian, mencakup input mitra, penugasan kegiatan, pemantauan batas limit SBML, pengunduhan rekap Excel, serta pencetakan dokumen SPK dan BAST.
3. **Administrator**: Memiliki seluruh wewenang Operator ditambah kontrol sistem: manajemen akun operator, pengaturan hak akses, konfigurasi template dokumen dinamis, dan penyesuaian master pagu SBML.

---

### 2. Activity Diagram (Alokasi Honor & Validasi SBML)
Diagram ini menjelaskan alur aktivitas saat operator menginput honor mitra hingga sistem melakukan pengecekan batas anggaran SBML.

```mermaid
flowchart TD
    Start([🟢 Mulai]) --> Login[Operator Login ke SIMANTRA]
    Login --> OpenMonitoring[Buka Menu Monitoring / Alokasi Honor]
    OpenMonitoring --> SelectFilter[Pilih Periode Tahun/Bulan & Mitra]
    SelectFilter --> InputAlokasi[Input / Pilih Kegiatan & Nominal Honor]
    
    InputAlokasi --> CheckLimit{Sistem Cek Akumulasi Honor vs Limit SBML}
    
    CheckLimit -- "Total Honor > Pagu SBML (Over Limit)" --> ShowWarning[Sistem Tampilkan Peringatan Limit Alert ⚠️]
    ShowWarning --> DecisionOver{Tetap Lanjutkan Simpan?}
    DecisionOver -- "Revisi Nominal" --> InputAlokasi
    DecisionOver -- "Konfirmasi Simpan" --> SaveData[Simpan Alokasi Honor ke Database]

    CheckLimit -- "Total Honor <= Pagu SBML (Aman)" --> SaveData
    
    SaveData --> UpdateRekap[Sistem Perbarui Rekapitulasi & Log Monitoring]
    UpdateRekap --> GenerateDocs[Generate Dokumen SPK & BAST Mitra]
    
    GenerateDocs --> ActionDocs{Pilih Aksi Dokumen}
    ActionDocs -- "Cetak Satuan / Massal" --> PrintDoc[Cetak SPK Utama / Lampiran / BAST]
    ActionDocs -- "Download DOCX" --> DownloadWord[Unduh Dokumen Word .docx]
    ActionDocs -- "Export Laporan" --> ExportExcel[Export Rekapitulasi ke Excel]

    PrintDoc --> End([🔴 Selesai])
    DownloadWord --> End
    ExportExcel --> End
```

#### 📝 Penjelasan Activity:
1. Operator memilih mitra dan periode anggaran yang akan dialokasikan penugasannya.
2. Sistem secara otomatis menjumlahkan seluruh honor mitra di bulan tersebut dan membandingkannya dengan limit SBML yang berlaku.
3. Jika total honor melebihi batas ketentuan SBML, sistem menampilkan status peringatan (*Warning Alert*). Operator dapat merevisi atau mengonfirmasi penugasan.
4. Setelah tersimpan, data otomatis masuk ke antrean cetak SPK/BAST dan rekapitulasi honor bulanan.

---

### 3. Sequence Diagram (Validasi Real-time Limit SBML)
Diagram urutan interaksi komponen aplikasi saat validasi honor dilakukan dari sisi antarmuka pengguna hingga ke basis data.

```mermaid
sequenceDiagram
    autonumber
    actor User as Operator / PJ Kegiatan
    participant View as Web UI (Blade & JS)
    participant Ctrl as MonitoringController
    participant Model as AlokasiHonor & Sbml Model
    participant DB as MySQL Database

    User->>View: Memilih Mitra & Memasukkan Nominal Honor
    View->>Ctrl: GET /monitoring/check-limit?mitra_id=X&periode_id=Y&nominal=Z
    activate Ctrl
    Ctrl->>Model: Query total honor berjalan & pagu SBML mitra
    activate Model
    Model->>DB: SELECT SUM(nominal) FROM alokasi_honors WHERE ...
    DB-->>Model: Return total alokasi saat ini
    Model-->>Ctrl: Data akumulasi honor & limit SBML
    deactivate Model

    alt Akumulasi Honor > Limit SBML
        Ctrl-->>View: JSON Response (status: 'warning', is_over_limit: true, sisa_pagu: -N)
        View-->>User: Tampilkan Badge Merah / Peringatan Over Limit ⚠️
    else Akumulasi Honor <= Limit SBML
        Ctrl-->>View: JSON Response (status: 'safe', is_over_limit: false, sisa_pagu: +N)
        View-->>User: Tampilkan Badge Hijau / Alokasi Aman ✅
    end
    deactivate Ctrl

    User->>View: Klik Tombol "Simpan Alokasi"
    View->>Ctrl: POST /monitoring (Data Alokasi)
    activate Ctrl
    Ctrl->>Model: AlokasiHonor::create([...])
    Model->>DB: INSERT INTO alokasi_honors ...
    DB-->>Model: Success
    Ctrl-->>View: Redirect dengan Notifikasi Sukses
    deactivate Ctrl
    View-->>User: Menampilkan pesan alokasi berhasil disimpan
```

#### 📝 Penjelasan Sequence Validasi:
1. **Asynchronous Check**: Ketika user memilih mitra atau mengisi nominal honor di form, antarmuka memicu request pengecekan ke `MonitoringController`.
2. **Kalkulasi Cepat**: Controller menghitung total alokasi bulan berjalan ditambah alokasi baru, lalu membandingkannya dengan limit SBML mitra.
3. **Feedback Visual Instan**: UI memberikan respons warna status (hijau = aman, merah = melebihi limit) sebelum tombol simpan diklik.

---

### 4. Sequence Diagram (Generate & Cetak Dokumen SPK/BAST)
Diagram urutan proses penerbitan surat perjanjian kerja dan berita acara serah terima.

```mermaid
sequenceDiagram
    autonumber
    actor User as Operator / Admin
    participant View as Halaman SPK (Blade)
    participant Ctrl as SpkController
    participant NumEngine as SpkNumbering Engine
    participant Tmpl as DocumentTemplate
    participant DB as MySQL Database

    User->>View: Buka Menu SPK & Pilih Mitra (Cetak Satuan / Massal)
    View->>Ctrl: GET /spk/{mitra_id}/cetak-utama
    activate Ctrl
    Ctrl->>DB: Ambil data Mitra, Kegiatan, & AlokasiHonor
    DB-->>Ctrl: Return data alokasi lengkap
    
    Ctrl->>NumEngine: Generate Nomor SPK / BAST Otomatis
    activate NumEngine
    NumEngine-->>Ctrl: Format Nomor: [No]/BPS/3206/KGT/[BulanRomawi]/[Tahun]
    deactivate NumEngine

    Ctrl->>Tmpl: Ambil template dokumen aktif
    Tmpl-->>Ctrl: Return struktur template & format surat

    alt User Memilih Cetak Layar / PDF
        Ctrl-->>View: Render Blade View (Print Layout Siap Cetak)
        View-->>User: Tampilkan dialog cetak browser / Print Preview
    else User Memilih Download Word
        Ctrl->>Ctrl: Parse variabel ke PhpWord / Template Processor
        Ctrl-->>User: Download File (.docx)
    end
    deactivate Ctrl
```

#### 📝 Penjelasan Sequence Dokumen:
1. Sistem mengambil data mitra beserta seluruh rincian kegiatan yang dibebankan pada bulan terpilih.
2. Mesin penomoran (`SpkNumbering Engine`) menyusun format nomor surat dinamis sesuai kaidah tata naskah dinas BPS.
3. Sistem menyuntikkan data tersebut ke template dokumen untuk langsung dicetak via browser atau diunduh dalam format Word (.docx).

---

### 5. Entity-Relationship Diagram (ERD)
Diagram relasi database yang menunjukkan struktur tabel, atribut, dan hubungan antarentitas pada basis data SIMANTRA.

```mermaid
erDiagram
    USERS ||--o{ ALOKASI_HONORS : "menginput"
    BIDANGS ||--o{ KEGIATANS : "menaungi"
    KEGIATANS ||--o{ KEGIATAN_JADWALS : "memiliki_jadwal"
    KEGIATANS ||--o{ ALOKASI_HONORS : "diterapkan_pada"
    PERIODES ||--o{ ALOKASI_HONORS : "periode_anggaran"
    PERIODES ||--o{ SBMLS : "periode_sbml"
    MITRAS ||--o{ ALOKASI_HONORS : "menerima_alokasi"
    MITRAS ||--o{ SBMLS : "memiliki_pagu"
    KECAMATANS ||--o{ DESAS : "membawahi"
    KECAMATANS ||--o{ MITRAS : "domisili_kecamatan"
    DESAS ||--o{ MITRAS : "domisili_desa"

    USERS {
        bigint id PK
        string name "Nama Pengguna"
        string email UK "Email Login"
        string password "Hash Password"
        enum role "admin, operator"
        string foto_profil "Path Foto"
        timestamp created_at
    }

    BIDANGS {
        bigint id PK
        string nama "Distribusi, Neraca, Produksi, Sosial, Cadangan"
        timestamp created_at
    }

    KEGIATANS {
        bigint id PK
        bigint bidang_id FK "Relasi ke Bidang"
        string nama "Nama Kegiatan Statistik"
        string kode_mata_anggaran "Kode MAK / Anggaran"
        string tahun "Tahun Anggaran"
        timestamp created_at
    }

    KEGIATAN_JADWALS {
        bigint id PK
        bigint kegiatan_id FK "Relasi ke Kegiatan"
        tinyint bulan "Bulan Pelaksanaan (1-12)"
        boolean status_aktif "Status Pelaksanaan"
    }

    PERIODES {
        bigint id PK
        string tahun "Tahun (misal: 2024)"
        string bulan "Nama Bulan (misal: Januari)"
        tinyint bulan_angka "Angka Bulan (1-12)"
        timestamp created_at
    }

    MITRAS {
        bigint id PK
        string id_sobat UK "ID Sobat BPS"
        string nama "Nama Lengkap Mitra"
        enum jk "L, P (Jenis Kelamin)"
        string no_hp "Nomor Kontak / WhatsApp"
        text alamat "Alamat Lengkap"
        string pekerjaan "Pekerjaan Utama"
        string kode_alamat "Kode Wilayah BPS"
        string kecamatan "Nama Kecamatan"
        string desa "Nama Desa/Kelurahan"
        timestamp created_at
    }

    ALOKASI_HONORS {
        bigint id PK
        bigint mitra_id FK "Relasi ke Mitra"
        bigint periode_id FK "Relasi ke Periode"
        bigint kegiatan_id FK "Relasi ke Kegiatan"
        decimal nominal "Besaran Honor (Rp)"
        string nomor_dokumen "Nomor SPK/BAST"
        timestamp created_at
    }

    SBMLS {
        bigint id PK
        bigint mitra_id FK "Relasi ke Mitra"
        bigint periode_id FK "Relasi ke Periode"
        string jenis "Kategori SBML"
        decimal nominal "Pagu Maksimum (Rp)"
        timestamp created_at
    }

    SBML_MASTERS {
        bigint id PK
        string jenis UK "Jenis Standar SBML"
        decimal nominal_default "Besaran Default (Rp)"
        string keterangan "Deskripsi Ketentuan"
    }

    DOCUMENT_TEMPLATES {
        bigint id PK
        string template_name "Jenis Template (SPK / BAST)"
        text content "Konten / Klausul Surat"
        text header "Header Surat"
        text footer "Footer / Tanda Tangan"
        boolean is_active "Status Template Aktif"
    }

    KECAMATANS {
        bigint id PK
        string kode_kecamatan UK "Kode BPS Kecamatan"
        string nama_kecamatan "Nama Wilayah Kecamatan"
    }

    DESAS {
        bigint id PK
        bigint kecamatan_id FK "Relasi ke Kecamatan"
        string kode_desa "Kode BPS Desa"
        string nama_desa "Nama Wilayah Desa"
    }
```

#### 📝 Penjelasan ERD:
1. **Entitas Pusat (`ALOKASI_HONORS`)**: Berfungsi sebagai tabel transaksi yang menghubungkan `MITRAS`, `KEGIATANS`, dan `PERIODES`.
2. **Kontrol SBML (`SBMLS` & `SBML_MASTERS`)**: Menyimpan batas maksimum penerimaan honor per mitra untuk setiap periode anggaran.
3. **Master Wilayah (`KECAMATANS` & `DESAS`)**: Mendukung normalisasi data domisili mitra statistik.
4. **Template Dinamis (`DOCUMENT_TEMPLATES`)**: Menyimpan format redaksi klausul SPK dan BAST agar dapat dikonfigurasi langsung oleh Administrator.

---

### 6. Class Diagram (Domain Model Eloquent)
Diagram struktur kelas yang menggambarkan model data Eloquent di Laravel beserta relasi method OOP antar entitas.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string role
        +isAdmin() bool
        +isOperator() bool
    }

    class Mitra {
        +int id
        +string id_sobat
        +string nama
        +string jk
        +string no_hp
        +alokasiHonors() HasMany
        +sbmls() HasMany
        +getTotalHonorPeriode(periodeId) decimal
        +getSisaPaguSbml(periodeId) decimal
    }

    class Bidang {
        +int id
        +string nama
        +kegiatans() HasMany
    }

    class Kegiatan {
        +int id
        +int bidang_id
        +string nama
        +string kode_mata_anggaran
        +bidang() BelongsTo
        +jadwals() HasMany
        +alokasiHonors() HasMany
    }

    class Periode {
        +int id
        +string tahun
        +string bulan
        +alokasiHonors() HasMany
        +sbmls() HasMany
    }

    class AlokasiHonor {
        +int id
        +int mitra_id
        +int periode_id
        +int kegiatan_id
        +decimal nominal
        +string nomor_dokumen
        +mitra() BelongsTo
        +kegiatan() BelongsTo
        +periode() BelongsTo
    }

    class Sbml {
        +int id
        +int mitra_id
        +int periode_id
        +string jenis
        +decimal nominal
        +mitra() BelongsTo
        +periode() BelongsTo
    }

    class DocumentTemplate {
        +int id
        +string template_name
        +string content
        +bool is_active
        +getCompiledContent(data) string
    }

    Bidang "1" --> "0..*" Kegiatan : hasMany
    Kegiatan "1" --> "0..*" AlokasiHonor : hasMany
    Periode "1" --> "0..*" AlokasiHonor : hasMany
    Periode "1" --> "0..*" Sbml : hasMany
    Mitra "1" --> "0..*" AlokasiHonor : hasMany
    Mitra "1" --> "0..*" Sbml : hasMany
    AlokasiHonor --> Mitra : belongsTo
    AlokasiHonor --> Kegiatan : belongsTo
    AlokasiHonor --> Periode : belongsTo
```

#### 📝 Penjelasan Class Diagram:
1. `Mitra` memiliki method pembantu untuk menghitung akumulasi total honor dan sisa pagu SBML dalam periode tertentu.
2. `AlokasiHonor` bertindak sebagai model relasi many-to-many berbobot (*pivot with attributes*) antara `Mitra`, `Kegiatan`, dan `Periode`.
3. `DocumentTemplate` menyediakan abstraksi untuk memproses parsing placeholder variabel ke dalam format dokumen final.

---

### 7. State Machine Diagram (Siklus Status Alokasi & Dokumen)
Diagram status yang menunjukkan siklus hidup satu alokasi honor sejak awal input sampai penerbitan dokumen legalitas selesai.

```mermaid
stateDiagram-v2
    [*] --> DraftInput: Operator Memilih Mitra & Kegiatan
    
    state ValidasiLimit {
        DraftInput --> CekPagu: Evaluasi Akumulasi Honor
        CekPagu --> StatusAman: Total <= Limit SBML
        CekPagu --> StatusWarning: Total > Limit SBML
        StatusWarning --> DraftInput: Revisi Nominal
        StatusWarning --> Terkonfirmasi: Konfirmasi Override
        StatusAman --> Terkonfirmasi: Simpan Alokasi
    }

    Terkonfirmasi --> AlokasiTersimpan: Data Masuk Basis Data
    AlokasiTersimpan --> SPKDiterbitkan: Generate Nomor & Cetak SPK
    SPKDiterbitkan --> BASTSelesai: Kegiatan Selesai & BAST Dicetak
    BASTSelesai --> Terbayar: Proses Pencairan Honor Selesai
    Terbayar --> [*]
```

#### 📝 Penjelasan State Machine:
1. Setiap entri penugasan berawal dari status **DraftInput**.
2. Sistem mengevaluasi kondisi batas honor: jika aman maka langsung masuk **Terkonfirmasi**, jika melebihi pagu maka beralih ke **StatusWarning**.
3. Setelah data tersimpan, dokumen **SPK** dapat diterbitkan. Saat pelaksanaan kegiatan usai, dokumen **BAST** disahkan untuk pencairan honor (*Terbayar*).

---

### 8. Diagram Arsitektur & Deployment Sistem
Diagram yang menggambarkan susunan infrastruktur server dan interaksi lapisan software pada lingkungan operasional SIMANTRA.

```mermaid
graph TD
    subgraph ClientEnv [" 🌐 Client Tier "]
        UserClient["Pengguna / Operator / Admin Browser (Chrome, Edge, Firefox)"]
    end

    subgraph WebServerTier [" 🚀 Web & Application Server Tier "]
        WebServer["Web Server (Nginx / Apache)"]
        PHPEngine["PHP 8.2+ Runtime (PHP-FPM)"]
        LaravelApp["Laravel 12 Application Engine"]
        
        subgraph LaravelComponents [" Komponen Internal Laravel "]
            RoutingMW["Routing & Security Middleware"]
            ControllersLayer["Controllers (Mitra, SPK, Monitoring, Rekap)"]
            ServiceLayer["Service Layer (SBML Validator, SPK Engine)"]
            EloquentORM["Eloquent ORM Layer"]
        end

        WebServer --> PHPEngine
        PHPEngine --> LaravelApp
        LaravelApp --> RoutingMW
        RoutingMW --> ControllersLayer
        ControllersLayer --> ServiceLayer
        ServiceLayer --> EloquentORM
    end

    subgraph StorageTier [" 🗄️ Database & Storage Tier "]
        MySQLDB[("Database Server (SQLite / MySQL / MariaDB)")]
        FileStorage[("Local / Public Storage (Templates, Exports, Avatars)")]
    end

    UserClient <-->|HTTPS / HTTP Requests| WebServer
    EloquentORM <-->|SQL Queries| MySQLDB
    ControllersLayer <-->|Read / Write File| FileStorage
```

#### 📝 Penjelasan Arsitektur & Deployment:
1. **Client Tier**: Menjalankan aplikasi berbasis web responsif dengan Tailwind CSS dan Vite.
2. **Application Server Tier**: Memproses request melalui Nginx/Apache dan diteruskan ke PHP-FPM Laravel 12 yang mengeksekusi middleware keamanan, controller, dan business logic service.
3. **Database & Storage Tier**: Mengelola penyimpanan data relasional terstruktur pada MySQL dan berkas dokumen/template pada filesystem storage.

---

## 💻 Persyaratan Sistem

Sebelum melakukan instalasi, pastikan lingkungan komputer atau server Anda memenuhi persyaratan berikut:

- **PHP**: Versi `>= 8.2`
- **Ekstensi PHP Wajib**:
  - `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql` *(atau `pdo_sqlite`)*, `session`, `tokenizer`, `xml`, `zip`, `gd`
- **Composer**: Versi `>= 2.2`
- **Node.js & NPM**: Node.js `>= 18.x` & NPM `>= 9.x`
- **Database**: MySQL `>= 8.0` / MariaDB `>= 10.4` / SQLite `>= 3.35`
- **Git**: Versi terbaru

---

## 🛠️ Panduan Instalasi & Setup

Ikuti langkah-langkah di bawah ini untuk menginstal dan menjalankan SIMANTRA secara lokal:

### 1. Kloning Repositori
```bash
git clone https://github.com/supyan1403/SIMANTRA.git
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
Salin file `.env.example` menjadi `.env`:
```bash
# Untuk Linux / macOS / Git Bash:
cp .env.example .env

# Untuk Windows (Command Prompt / PowerShell):
copy .env.example .env
```

Buka file `.env` dan pastikan koneksi database menggunakan SQLite (pengaturan default project):
```env
APP_NAME="SIMANTRA"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Konfigurasi Database SQLite (default SIMANTRA)
DB_CONNECTION=sqlite
# Tidak perlu DB_HOST / DB_PORT / DB_USERNAME / DB_PASSWORD
```
*(Catatan: File basis data `database/database.sqlite` akan dibuat otomatis saat migrasi dijalankan. Tidak perlu menginstal atau mengonfigurasi server database seperti MySQL/MariaDB atau phpMyAdmin.)*

Kemudian generate application key:
```bash
php artisan key:generate
```

### 5. Jalankan Migrasi & Seeder Data Awal (Instalasi Baru)
Perintah ini akan membuat seluruh tabel basis data, mengisi data master bidang, kecamatan, desa, aturan SBML, dan data bawaan MANTRA:
```bash
php artisan migrate --seed
```
> 💡 **Data MANTRA otomatis**: Basis data proyek ini sudah menyertakan `database/database.sqlite` lengkap (2.566 Mitra, 47 Kegiatan, 737 Alokasi Honor, dan Aturan SBML 2024/2025). Anda **tidak perlu** melakukan import manual.

---

## 🔄 Panduan Sinkronisasi (Untuk Rekan Tim yang Sudah Clone)

Bagi rekan satu tim yang sebelumnya sudah meng-clone repositori ini, untuk mendapatkan **100% seluruh pembaruan kode, perbaikan UI Sidebar, serta data database terbaru**, Anda **cukup menjalankan**:

```bash
# 1. Tarik seluruh perubahan kode dan database terbaru
git pull origin main

# 2. Jalankan server aplikasi
php artisan serve
```

> [!TIP]
> Begitu perintah di atas dijalankan, seluruh tampilan baru, perbaikan sidebar zero-shift, master SBML multi-kategori, dan 2.566 data mitra riil langsung aktif dan sama persis 100% di komputer Anda tanpa perlu konfigurasi tambahan.

---

## 🔑 Akun Pengguna Default

Database telah menyediakan 2 (dua) akun pengguna siap pakai:

| Role | Email | Password | Hak Akses & Kemampuan |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@bps.go.id` | `password` | Akses penuh seluruh sistem: Kelola Pengguna, Reset Password, Kelola Master SBML (Pencacahan, Pengolahan, Gabungan), Template Dokumen SPK/BAST, Manajemen Mitra & Kegiatan. |
| **Operator** | `operator@bps.go.id` | `password` | Akses operasional: Kelola Mitra, Kelola Kegiatan & Jadwal, Alokasi Honor, Validasi SBML Multi-Tugas, Monitoring, Cetak SPK/BAST & Export Excel. |

---

## 🧪 Pengujian Otomatis

SIMANTRA dilengkapi dengan rangkaian pengujian fitur (*Feature & Unit Tests*) menggunakan PHPUnit:

```bash
# Menjalankan seluruh pengujian
php artisan test

# Menjalankan pengujian fitur penomoran SPK
php artisan test --filter=SpkNumberingTest

# Menjalankan pengujian keamanan riwayat halaman & otentikasi
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

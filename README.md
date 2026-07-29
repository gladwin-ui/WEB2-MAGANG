# ManufakTrack (BugTrack MFG)

ManufakTrack adalah sebuah aplikasi manajemen pelaporan dan pemantauan analitik cacat (bug) manufaktur berbasis web. Sistem ini dirancang untuk memproses data dari lini produksi secara nyata, mengolah laporan cacat produk dalam jumlah besar melalui sistem antrean, dan menghadirkan dasbor analitik komprehensif bagi level eksekutif menggunakan *Machine Learning* lokal.

## 🚀 Tech Stack

- **Framework Backend:** Laravel 13 (PHP 8.3+)
- **Frontend / Styling:** Tailwind CSS, Blade Templates, Alpine.js
- **Database:** MySQL / MariaDB
- **Analytics Engine:** Python FastAPI (Machine Learning & NLP Terpadu)
- **Libraries Lainnya:** ApexCharts (Visualisasi Data), PhpSpreadsheet (Export Laporan), SweetAlert2

## 🏗 System Architecture

ManufakTrack dibangun menggunakan arsitektur mikroservis terpisah (Laravel Web & Python Analytics) yang terhubung melalui protokol HTTP secara sinkron untuk proses analisis *Real-time*.

```text
+-------------------------------------------------------------+
|                        CLIENT LAYER                         |
|                                                             |
|   +-----------------------+       +---------------------+   |
|   | Admin Web Dashboard   |       | Exported Excel      |   |
|   | (Blade + Tailwind)    |       | Reports             |   |
|   +-----------------------+       +---------------------+   |
+-------------------------------------------------------------+
             |
             | (HTTP/UI)
             v
+-------------------------------------------------------------+
|                   APPLICATION LAYER (Laravel)               |
|                                                             |
|      CONTROLLERS                       AUTOMATION           |
|   +-------------------+           +-------------------+     |
|   | DashboardCtrl     |           | QueueWorker       |     |
|   | ImportCtrl        |           | (Process Import)  |     |
|   | LaporanKhususCtrl |           |                   |     |
|   +-------------------+           +-------------------+     |
|                                                             |
|      SERVICES                                               |
|   +---------------------------------------------------+     |
|   | BugAnalyticsService & SqlImportParser             |     |
|   +---------------------------------------------------+     |
+-------------------------------------------------------------+
             |                                 |
             v                                 v
+---------------------------+    +----------------------------+
|         DATA LAYER        |    |      ANALYTICS ENGINE      |
|   +-------------------+   |    |   +--------------------+   |
|   | MySQL Database    |   |    |   | FastAPI Python     |   |
|   | (Bugs, ImportJobs)|   |<---|   | (ML & NLP Models)  |   |
|   +-------------------+   |    |   +--------------------+   |
+---------------------------+    +----------------------------+
```

## ✨ Key Features

| Fitur Utama | Deskripsi |
| --- | --- |
| **Dasbor Analitik Eksekutif** | Pantau KPI utama (Total Defect, Rework Rate), grafik persebaran severity, dan tren harian dalam satu layar. |
| **Laporan Khusus & Clustering** | Pengelompokan otomatis laporan bug menggunakan algoritma *NLP Similarity* untuk mendeteksi *Top 5 Masalah* dan *Root Cause* tersering. |
| **Sistem Import Asinkron (Queue)** | Impor file SQL ribuan baris dengan lancar di latar belakang tanpa risiko jeda *timeout* pada browser. |
| **Export Excel Formal Berlogo** | Unduh hasil analisis dasbor dan data tabel mentah ke dalam format Excel (XLSX) terstandarisasi dengan branding perusahaan. |
| **Deteksi Sentimen & Severity** | *Analytics Engine* memberikan diagnosis sekunder untuk skor urgensi, tingkat keparahan yang direkomendasikan, dan sentimen laporan. |

## 🔀 Mode Aplikasi

ManufakTrack dapat dijalankan dalam 2 mode operasi melalui pengaturan variabel `APP_MODE` di file `.env`:

### 1. Mode Import (`APP_MODE=import` - Default)
- **Fungsi**: Mode standar untuk portofolio/cadangan. Mengimpor file `.sql` berukuran besar secara asinkron (menggunakan antrean antarmuka Laravel Queue) ke dalam tabel lokal `bugs`.
- **Fitur**: Seluruh menu impor di sidebar dan dasbor aktif sepenuhnya.

### 2. Mode Read-Only (`APP_MODE=readonly` - Utama Kantor)
- **Fungsi**: Membaca data langsung (*real-time*) dari tabel `bug` milik database produksi kantor (tanpa proses impor SQL dan tanpa VIEW database).
- **Logika Adaptif**: Logika kueri pada `DashboardController` dan `LaporanKhususController` secara dinamis memetakan nama kolom fisik (`idbug`, `bug_title`, `bugdesc`, dll.) dan mem-bypass JOIN ke tabel `projects` yang kosong.
- **AI Cache**: Hasil analisis AI (sentimen & tingkat keparahan direkomendasikan) dari FastAPI disimpan secara eksternal ke dalam tabel lokal **`bug_ai_cache`** (tabel asli kantor tidak pernah ditulisi).
- **UI Bersih**: Seluruh menu impor data `.sql` dan riwayat impor di sidebar otomatis disembunyikan.

#### Langkah Setup Mode Read-Only:
1. Atur mode di file `.env`:
   ```env
   APP_MODE=readonly
   ```
2. Atur kredensial koneksi database kantor (yang memuat tabel `bug`) di `.env`:
   ```env
   DB_DATABASE=nama_database_kantor
   ```
3. Bersihkan cache konfigurasi Laravel:
   ```bash
   php artisan config:clear
   ```
4. Jalankan migrasi database untuk membuat tabel sistem Laravel dan tabel cache AI (tabel `bug` kantor tidak akan tersentuh):
   ```bash
   php artisan migrate
   ```
5. Buat akun login admin via `php artisan tinker`:
   ```php
   App\Models\User::create(['name' => 'Admin', 'email' => 'admin@bugtrack.test', 'password' => Hash::make('password123'), 'role' => 'admin']);
   ```
6. Pastikan Python Analytics Service berjalan di port 8001 dan server Laravel aktif:
   ```bash
   # Terminal 1
   php artisan serve
   # Terminal 2 (FastAPI)
   cd analytics-service && uvicorn main:app --reload --port 8001
   ```
7. Buka web di browser dan login dengan akun admin di atas. Data live dari database kantor langsung tampil di dashboard.

## 📁 Project Structure

Struktur direktori utama terbagi atas aplikasi web Laravel dan *service* analitik Python:

- `app/Http/Controllers/` : Mengontrol logika *dashboard* analitik, impor data, dan *master data*.
- `app/Jobs/` : Mengelola *queue* latar belakang (seperti `ProcessImportChunkJob` dan `ReanalyzeBugsJob`).
- `app/Services/` : Integrasi eksternal (`BugAnalyticsService`) dan pemrosesan file SQL.
- `database/migrations/` & `seeders/` : Skema *database* berorientasi *forward-only* beserta data awalan *dummy*.
- `analytics-service/` : Proyek independen FastAPI untuk model Sentimen, Severity, dan Clustering NLP.

*(Catatan lengkap terkait keputusan desain, changelog, dan teknis detail proyek ini telah dipisahkan ke dalam file `memory.md`.)*

## ⚙️ Installation and Run Guide

Ikuti langkah-langkah di bawah ini untuk menjalankan ManufakTrack beserta *Analytics Service* secara utuh:

### 1. Persyaratan Sistem
- PHP >= 8.3
- Python >= 3.10
- Node.js & npm
- Composer
- MySQL / MariaDB

### 2. Instalasi Proyek
```bash
# Install dependensi PHP (Laravel)
composer install

# Install dependensi Frontend (Tailwind/Vite)
npm install
npm run build
```

### 3. Konfigurasi Database
Duplikat file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Generate *Application Key*:
```bash
php artisan key:generate
```
Konfigurasi kredensial koneksi *database* di `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=root
DB_PASSWORD=

# Pastikan antrean background menggunakan database
QUEUE_CONNECTION=database
```

### 4. Setup Database & Migrasi
Buat *database* MySQL terlebih dahulu (sesuai nama di `DB_DATABASE`), kemudian jalankan migrasi beserta *seeder*:
```bash
php artisan migrate --seed
```

### 5. Setup Python Analytics Service
Buka **terminal baru**, masuk ke folder `analytics-service`, dan install *requirements*:
```bash
cd analytics-service
pip install -r requirements.txt
```

### 6. Menjalankan Aplikasi Secara Utuh
Anda harus menjalankan **tiga terminal** secara bersamaan agar seluruh fungsi aplikasi (UI, Antrean, dan AI) berjalan lancar:

**Terminal 1 (Web Server Laravel):**
```bash
php artisan serve
```
*(Akses halaman web melalui http://127.0.0.1:8000)*

**Terminal 2 (Queue Worker Laravel):**
```bash
php artisan queue:work
```
*(Wajib berjalan untuk memproses data impor SQL di latar belakang)*

**Terminal 3 (Analytics Service FastAPI):**
```bash
cd analytics-service
uvicorn main:app --reload --port 8001
```
*(Wajib berjalan di **port 8001** untuk mencegah bentrok dengan Laravel)*
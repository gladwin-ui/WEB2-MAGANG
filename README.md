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

ManufakTrack mendukung 2 mode operasi yang dapat diatur via variabel `APP_MODE` pada file `.env`:

- **`APP_MODE=readonly`**: Membaca langsung tabel `bug` milik database produksi kantor (menggunakan skema nama kolom asli `idbug`, `bug_title`, `bugdesc`, dll.) secara *read-only*. Hasil analisis AI dari FastAPI akan disimpan secara eksternal ke dalam tabel cache lokal **`bug_ai_cache`** agar tidak mengotori/menulis database kantor.
- **`APP_MODE=import`** *(Default)*: Mode standar menggunakan tabel `bugs` lokal yang diisi via fitur impor file `.sql` di latar belakang.

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
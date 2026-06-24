# 🔧 BugTrack MFG — Manufacturing Bug Tracking System

> **CATATAN UNTUK AI CODING AGENT (Antigravity):** Dokumen ini adalah **satu-satunya sumber acuan** (single source of truth) untuk proyek ini. Baca dokumen ini SECARA PENUH sebelum mengerjakan task apapun. Setiap kali requirement baru ditambahkan oleh pengguna, dokumen ini akan DIPERBARUI (bagian "Requirement & Status Implementasi" dan "Changelog") — anggap versi TERBARU dari file ini sebagai instruksi yang berlaku, dan bagian "Changelog" sebagai riwayat keputusan yang SUDAH diputuskan (jangan diulang tanya/diskusikan ulang kecuali ada perubahan baru yang eksplisit diminta).

---

## 📌 Project Overview

Proyek ini adalah **aplikasi internal produksi nyata** berbasis Laravel untuk mengelola pelaporan dan penanganan bug/cacat produksi pada lingkungan manufaktur. Sistem ini mengadaptasi data nyata dari tabel `bug` pada database produksi (`mfg_record`) di tempat magang pengguna, dan dirancang untuk tiga peran pengguna: **Reporter** (pelapor bug), **Mekanik** (penangan/perbaikan), dan **Admin** (pemantau & analitik).

Proyek ini terinspirasi dari arsitektur proyek sebelumnya (**SmartReport** — simulasi Big Data customer-facing dengan pipeline asinkron penuh), namun BugTrack MFG memiliki kebutuhan **real-time**: laporan bug harus segera terlihat oleh mekanik tanpa delay antrean job, sambil **tetap mempertahankan proses analisis AI/NLP** (sentiment, spam detection, rekomendasi severity, kategorisasi penyebab kerusakan).

---

## 🛠️ Tech Stack & Strategi Arsitektur

- **Web Framework:** Laravel (Ingestion Gateway, Dashboard Presenter, & Role-based Access).
- **Database:** MySQL/MariaDB (kompatibel dengan sumber data asli `mfg_record`).
- **Analytics Engine:** Mikroservis Python (FastAPI) dengan pendekatan rule-based/keyword (sentiment, spam, severity recommendation, damage categorization) — dipanggil **SECARA SINKRON** dari Laravel, **BUKAN** via Queue/Job asinkron.
- **TIDAK ADA Redis/Queue Worker.** Ini perbedaan arsitektur paling mendasar dari SmartReport. Semua pemanggilan ke Analytics Engine terjadi dalam siklus request-response yang sama, karena mekanik membutuhkan visibilitas bug secara langsung/real-time.

---

## 🧩 Konsep Domain Utama

### Tiga Role Pengguna
1. **Reporter** — siapa saja yang menemukan & melaporkan bug (operator produksi, QA, dsb). Submit laporan baru, isi deskripsi, lampiran, lihat riwayat laporannya sendiri, dan menerima **feedback** dari mekanik.
2. **Mekanik** — menangani bug berstatus `OPEN` (queue kerja), mengisi `root_cause` dan `repair_action`, menandai `is_rework` jika ini perbaikan ulang, mengubah status menjadi `CLOSED`, dan dapat mengirim **feedback** langsung ke akun reporter terkait satu bug spesifik.
3. **Admin** — memantau dashboard analitik penuh (tanpa menangani bug secara langsung), mengelola data master (Project, Serial Number, Device).

### Dua Tahap Analisis AI (Keduanya SINKRON, Tanpa Job)

**Tahap 1 — Saat Bug Disubmit (oleh Reporter):**
Dari kolom `description`, Analytics Engine menghasilkan:
- `sentiment_label` (positive/neutral/negative/**spam**) & `sentiment_score`
- `is_spam` & `spam_reason` (deteksi laporan asal-asalan/tidak substantif dari staf internal)
- `severity_recommended` & `severity_recommendation_reason` — **REKOMENDASI AI yang TERPISAH** dari `severity` asli yang diisi manual oleh reporter. **Tidak pernah saling override** — keduanya disimpan dan ditampilkan berdampingan.

**Tahap 2 — Saat Bug Ditutup (oleh Mekanik):**
Dari kolom `root_cause` + `repair_action`, Analytics Engine menghasilkan:
- `damage_category` — kategori penyebab kerusakan (contoh: "Overheat/Panas Berlebih", "Korosi/Kelembapan", "Hubungan Pendek/Short Circuit", "Kesalahan Pemasangan", "Kualitas Komponen", "Lain-lain"). Basis untuk dashboard **"Penyebab Kerusakan Paling Sering Terjadi"**.

### Fitur Feedback Mekanik → Reporter
Mekanik dapat mengirim pesan bebas terkait satu bug spesifik, masuk ke "kotak pesan"/inbox milik akun reporter terkait (tabel `bug_feedback`, status `is_read`). Ini BUKAN bagian dari `repair_action` (dokumentasi teknis) — ini komunikasi personal untuk dibaca reporter.

### Arti `reporter_type` ('produk' vs 'sub')
Menunjukkan **level/cakupan objek fisik** yang dilaporkan bermasalah, BUKAN siapa yang melapor:
- `produk` — bug pada **unit produk utama/jadi** (kode SN berformat `SN_UNIT_...`)
- `sub` — bug pada **sub-komponen/part di dalam unit** (kode SN berformat `SUB_PN_...`)

---

## 📐 Skema Database (Ringkasan)

```
users              : id, name, email, password, role (reporter|mekanik|admin)
projects           : id, name                              -- tabel master MINIMAL/placeholder
devices            : id, name                               -- tabel master MINIMAL/placeholder
serial_numbers     : id, project_id (FK), sn_code, type (unit|sub)
bugs               : id, project_id (FK), title, severity (Critical|Major|Minor),
                      serial_number_id (FK), sn_code_snapshot, reporter_type (produk|sub),
                      device_id (FK), description, product_version, environment,
                      reproduce_steps, root_cause, repair_action, is_rework,
                      attachment_path, expected_result,
                      reported_by (FK users), status (OPEN|CLOSED),
                      fixed_by (FK users, nullable), closed_at,
                      -- Hasil AI Tahap 1 (saat submit):
                      sentiment_label, sentiment_score, is_spam, spam_reason,
                      severity_recommended, severity_recommendation_reason,
                      -- Hasil AI Tahap 2 (saat ditutup):
                      damage_category
bug_feedback       : id, bug_id (FK), from_user_id (FK users), to_user_id (FK users),
                      message, is_read
```

**Catatan:** `projects`, `devices`, dan `serial_numbers` adalah tabel master **MINIMAL/placeholder** — struktur tabel asli di database `mfg_record` tidak diketahui sepenuhnya. Data lama diimpor dengan nama generik ("Project #27", dst) dan **PERLU diedit manual** setelah data master asli didapat dari tempat magang.

`reported_by` dan `fixed_by` adalah **foreign key relasional** ke tabel `users` (BUKAN string bebas seperti pada data asli `bugcreatedby`/`bugfixby`).

---

## 🔁 Alur Kerja Utama (Core Workflow)

```
Reporter submit bug (deskripsi, severity manual, lampiran, dsb)
        │
        ▼
Laravel simpan bug (status: OPEN)
        │
        ▼
Laravel PANGGIL Python Analytics Service /analyze-bug-report SECARA SINKRON
   (sentiment, spam check, severity_recommended)
        │
        ▼
Laravel UPDATE bug dengan hasil analisis (dalam request yang SAMA)
        │
        ▼
Response ke Reporter SELESAI -- bug SUDAH lengkap dengan hasil AI,
SUDAH terlihat oleh Mekanik di queue OPEN tanpa delay job

   --- Mekanik menangani ---

Mekanik pilih bug dari queue OPEN
        │
        ▼
Mekanik isi root_cause + repair_action, tandai is_rework jika perlu,
(opsional) kirim feedback ke reporter, ubah status -> CLOSED
        │
        ▼
Laravel PANGGIL Python Analytics Service /analyze-damage-cause SECARA SINKRON
   (damage_category dari root_cause + repair_action)
        │
        ▼
Laravel UPDATE bug dengan damage_category, closed_at
        │
        ▼
Response ke Mekanik SELESAI -- bug CLOSED, kategori penyebab tercatat
untuk dashboard analitik Admin
```

---

## 📁 Struktur Repositori & Konvensi Kode

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── LoginController.php
│   │   ├── BugController.php          <- Submit (reporter), Queue & Close (mekanik)
│   │   ├── DashboardController.php    <- Analitik (admin)
│   │   ├── ProjectController.php      <- Kelola master project (admin)
│   │   ├── SerialNumberController.php <- Kelola master SN (admin)
│   │   └── BugFeedbackController.php  <- Kotak pesan mekanik -> reporter
│   └── Middleware/
│       └── RoleMiddleware.php          <- Role-based access (reporter|mekanik|admin)
├── Models/
│   ├── User.php (dengan kolom role)
│   ├── Project.php
│   ├── Device.php
│   ├── SerialNumber.php
│   ├── Bug.php (relasi 'reporter' & 'fixer' ke User, 'project', 'serialNumber', 'device')
│   └── BugFeedback.php (relasi 'sender' & 'receiver' ke User, 'bug')
├── Services/
│   └── BugAnalyticsService.php   <- HTTP Client SINKRON ke Python service (TIDAK ADA Job)
database/
├── migrations/                    <- Skema tabel (Strict Forward-Only)
└── seeders/                       <- Import 24 baris data asli dari mfg_record

analytics-service/
├── main.py
├── services/
│   ├── sentiment.py               <- Adaptasi konteks teknis manufaktur
│   ├── spam_detection.py          <- Adaptasi konteks staf internal (BUKAN customer)
│   ├── severity_recommendation.py <- Usulkan severity dari description
│   └── damage_categorization.py   <- Kategorikan penyebab dari root_cause+repair_action
└── requirements.txt
```

---

## ⚠️ Keputusan Desain Penting (Jangan Diubah Tanpa Diskusi Ulang)

1. **TIDAK ADA Queue/Job/Redis.** Semua pemanggilan ke Python Analytics Service bersifat SINKRON dalam siklus request yang sama. Keputusan SENGAJA karena mekanik membutuhkan visibilitas bug secara real-time.
2. **Severity manual TIDAK PERNAH di-override AI.** `severity` (reporter) dan `severity_recommended` (AI) adalah DUA KOLOM TERPISAH, keduanya disimpan dan ditampilkan berdampingan. Keputusan akhir tetap di tangan manusia.
3. **Konsep "Spam" dipertahankan** meski pelapor staf internal — staf internal juga bisa membuat laporan tidak substantif/asal-asalan.
4. **Feedback mekanik→reporter terpisah dari `repair_action`.** `repair_action` adalah dokumentasi teknis; `bug_feedback` adalah komunikasi personal.
5. **Tabel master (`projects`, `devices`, `serial_numbers`) bersifat sementara/placeholder** sampai struktur asli dari `mfg_record` didapatkan.
6. **`reported_by`/`fixed_by` adalah foreign key relasional**, bukan string bebas — akun dibuat untuk setiap nama unik yang ditemukan di data historis (Admin, Fioni Agriyani, Maneng, manufacture, program, dan nilai anomali "1").

---

## ✅ Requirement & Status Implementasi

> Bagian ini mencerminkan **kondisi terkini** proyek. Status: `✅ Selesai` / `🔄 Sedang dikerjakan` / `📋 Direncanakan, belum dikerjakan`.

### Fondasi
- ✅ Skema database (6 tabel: users extend, projects, devices, serial_numbers, bugs, bug_feedback)
- ✅ Seeder import 24 baris data historis dari `mfg_record`
- ✅ Auth & 3 role (reporter, mekanik, admin) + RoleMiddleware
- ✅ Python Analytics Service (FastAPI) — sentiment, spam detection, severity recommendation, damage categorization
- ✅ Pemanggilan SINKRON ke Analytics Service (tanpa Queue/Job)

### Fitur Inti
- ✅ Submit bug oleh Reporter (`BugController::store`) dengan analisis AI Tahap 1 sinkron
- ✅ Queue bug OPEN untuk Mekanik
- ✅ Form tutup bug oleh Mekanik (`root_cause`, `repair_action`, `is_rework`) dengan analisis AI Tahap 2 sinkron (`damage_category`)
- ✅ Fitur Feedback Mekanik → Reporter (`BugFeedbackController`, dengan status `is_read`)
- ✅ Dashboard Analitik Admin dasar + Export CSV (`DashboardController::exportCsv`)
- ✅ Kelola master data Project & Serial Number (admin)

### Dashboard Analitik (Mirroring SmartReport)
- 🔄 Summary cards: Total Bug, Open, Closed, Critical (belum closed), Rework Rate (%)
- 📋 Distribusi Sentimen (Positive/Neutral/Negative/Spam) — chart donut
- 📋 Project Paling Banyak Bug (Top 5)
- 📋 Tren Volume Laporan (harian/mingguan)
- 🔄 Tabel Audit Bug dengan filter (status/severity/project/tanggal) + pagination
- 📋 Analytics Penyebab Kerusakan (distribusi `damage_category`)

### Belum Dikerjakan
- 📋 UI/UX styling final (saat ini fungsional dasar, belum ada tema visual yang ditetapkan)
- 📋 Notifikasi in-app untuk Reporter saat menerima feedback baru

---

## 📝 Changelog

### v0.1 — Scaffold Awal
- Setup skema database 6 tabel, migration forward-only.
- Seeder import 24 baris data historis dari tabel `bug` milik database `mfg_record` (tempat magang).
- Akun dibuat untuk setiap nama unik di data historis: Admin, Fioni Agriyani, Maneng, manufacture, program, dan nilai anomali "1" (role default: mekanik).
- Auth 3 role (reporter, mekanik, admin) dengan RoleMiddleware.
- Python Analytics Service (FastAPI) dengan 4 modul: sentiment, spam_detection, severity_recommendation, damage_categorization — SEMUA dipanggil sinkron, tanpa Queue/Job.
- `BugAnalyticsService.php` sebagai HTTP Client sinkron ke Analytics Service, dengan fallback aman jika service down (tidak menggagalkan submit bug).

### v0.2 — Fitur Inti per Role
- `BugController`: submit bug (reporter), tampilkan queue OPEN, form tutup bug (mekanik) dengan analisis AI Tahap 2.
- `BugFeedbackController`: kirim & baca feedback mekanik → reporter.
- `DashboardController`: dashboard analitik dasar + export CSV.
- `ProjectController` & `SerialNumberController`: kelola master data (admin).

---

## 🚀 Cara Menjalankan (Development)

```bash
# Laravel
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# Python Analytics Service (terminal terpisah)
cd analytics-service
pip install -r requirements.txt
uvicorn main:app --reload --port 8000
```

Akses aplikasi di `http://127.0.0.1:8000` (Laravel), Analytics Service berjalan di port yang dikonfigurasi terpisah (cek `analytics-service/README.md` untuk detail port).
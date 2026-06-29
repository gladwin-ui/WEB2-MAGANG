# 🔧 BugTrack MFG — Manufacturing Bug Tracking System

> **CATATAN UNTUK AI CODING AGENT (Antigravity/Qoder):** Dokumen ini adalah **satu-satunya sumber acuan** (single source of truth) untuk proyek ini. Baca dokumen ini SECARA PENUH sebelum mengerjakan task apapun. Setiap kali requirement baru ditambahkan oleh pengguna, dokumen ini akan DIPERBARUI (bagian "Requirement & Status Implementasi" dan "Changelog") — anggap versi TERBARU dari file ini sebagai instruksi yang berlaku, dan bagian "Changelog" sebagai riwayat keputusan yang SUDAH diputuskan (jangan diulang tanya/diskusikan ulang kecuali ada perubahan baru yang eksplisit diminta).

---

## 📌 Project Overview

Proyek ini adalah **aplikasi internal produksi nyata** berbasis Laravel untuk mengelola pelaporan dan penanganan bug/cacat produksi pada lingkungan manufaktur. Sistem ini mengadaptasi data nyata dari tabel `bug` pada database produksi (`mfg_record`) di tempat magang pengguna, dan dirancang untuk tiga peran pengguna: **Reporter** (pelapor bug), **Mekanik** (penangan/perbaikan), dan **Admin** (pemantau & analitik).

Proyek ini terinspirasi dari arsitektur proyek sebelumnya (**SmartReport** — simulasi Big Data customer-facing dengan pipeline asinkron penuh), namun BugTrack MFG memiliki kebutuhan **real-time**: laporan bug harus segera terlihat oleh mekanik tanpa delay antrean job, sambil **tetap mempertahankan proses analisis AI/NLP** (sentiment, spam detection, rekomendasi severity, kategorisasi penyebab kerusakan).

---

## 🛠️ Tech Stack & Strategi Arsitektur

- **Web Framework:** Laravel (Ingestion Gateway, Dashboard Presenter, & Role-based Access).
- **Database:** MySQL/MariaDB (kompatibel dengan sumber data asli `mfg_record`).
- **Analytics Engine:** Mikroservis Python (FastAPI) dengan pendekatan rule-based/keyword (sentiment, spam, severity recommendation, damage categorization) — dipanggil dari Laravel saat import berjalan.
- **Queue:** Laravel Queue driver `database` dipakai untuk import `.sql` volume besar. Tidak perlu Redis, tetapi developer wajib menjalankan `php artisan queue:work`.

---

## 🧩 Konsep Domain Utama

### Role Pengguna

> **Status saat ini:** Runtime web adalah **admin-only**. Fitur reporter/mekanik, assignment, dan chat telah dihapus dari aplikasi web. Registrasi hanya membuat akun admin.


Pada implementasi saat ini, hanya **Admin** yang tersedia di aplikasi web: login, dashboard analytics, master data, import `.sql`, dan detail bug historis.

### Dua Tahap Analisis AI (Keduanya SINKRON, Tanpa Job)

**Tahap 1 — Saat Bug Disubmit (oleh Reporter):**
Dari kolom `description`, Analytics Engine menghasilkan:
- `sentiment_label` (positive/neutral/negative/**spam**) & `sentiment_score`
- `is_spam` & `spam_reason` (deteksi laporan asal-asalan/tidak substantif dari staf internal)
- `severity_recommended` & `severity_recommendation_reason` — **REKOMENDASI AI yang TERPISAH** dari `severity` asli yang diisi manual oleh reporter. **Tidak pernah saling override** — keduanya disimpan dan ditampilkan berdampingan.

**Tahap 2 — Saat Bug Ditutup (oleh Mekanik):**
Dari kolom `root_cause` + `repair_action`, Analytics Engine menghasilkan:
- `damage_category` — kategori penyebab kerusakan (contoh: "Overheat/Panas Berlebih", "Korosi/Kelembapan", "Hubungan Pendek/Short Circuit", "Kesalahan Pemasangan", "Kualitas Komponen", "Lain-lain"). Basis untuk dashboard **"Penyebab Kerusakan Paling Sering Terjadi"**.

### Assignment & Chat Mekanik ↔ Reporter (Dihapus)

> **Status saat ini:** Fitur assignment, chat dua arah, dan feedback mekanik→reporter **telah dihapus total** dari runtime web. Tabel `bug_feedback` dan `bug_chats` sudah di-drop. Kolom `assigned_to`/`assigned_at` juga telah dibersihkan dari tabel `bugs`. Semua bug dianggap data historis/analitik yang dikelola admin.

### Arti `reporter_type` ('produk' vs 'sub')
Menunjukkan **level/cakupan objek fisik** yang dilaporkan bermasalah, BUKAN siapa yang melapor:
- `produk` — bug pada **unit produk utama/jadi** (kode SN berformat `SN_UNIT_...`)
- `sub` — bug pada **sub-komponen/part di dalam unit** (kode SN berformat `SUB_PN_...`)

---

## 📐 Skema Database (Ringkasan)

```
users              : id, name, email, password, role (admin)  -- runtime saat ini hanya admin
projects           : id, name                              -- tabel master MINIMAL/placeholder
devices            : id, name                               -- tabel master MINIMAL/placeholder
serial_numbers     : id, project_id (FK), sn_code, type (unit|sub)
bugs               : id, project_id (FK), title, severity (Critical|Major|Minor),
                      serial_number_id (FK), sn_code_snapshot, reporter_type (produk|sub),
                      device_id (FK), description, product_version, environment,
                      reproduce_steps, root_cause, repair_action, is_rework,
                      attachment_path, expected_result,
                      reported_by (string bebas), status (OPEN|CLOSED),
                      fixed_by (string bebas, nullable), closed_at,
                      import_job_id (FK, nullable), deleted_at (nullable),
                      -- Hasil AI Tahap 1 (saat submit):
                      sentiment_label, sentiment_score, is_spam, spam_reason,
                      severity_recommended (varchar -- lihat Catatan Teknis),
                      severity_recommendation_reason,
                      -- Hasil AI Tahap 2 (saat ditutup):
                      damage_category
import_jobs        : id, filename, total_rows, processed_rows,
                      inserted_count, skipped_count, failed_count,
                      status (pending|processing|completed|failed),
                      error_message, started_at, finished_at, deleted_at, timestamps
                      (Catatan: kolom `updated_count` dan `deleted_count` tetap ada di tabel untuk kompatibilitas,
                      tetapi UI progress hanya menampilkan INSERT, SKIP, dan FAIL)
```

**Catatan:** `projects`, `devices`, dan `serial_numbers` adalah tabel master **MINIMAL/placeholder** — struktur tabel asli di database `mfg_record` tidak diketahui sepenuhnya. Data lama diimpor dengan nama generik ("Project #27", dst) dan **PERLU diedit manual** setelah data master asli didapat dari tempat magang.

`reported_by` dan `fixed_by` adalah **string bebas** dari sumber `mfg_record.bug`, bukan FK ke `users`. Field ini hanya informasional dan tidak dipakai untuk permission.

---

## 🔁 Alur Kerja Utama (Core Workflow)

> **Catatan:** Alur di bawah ini mencerminkan **implementasi saat ini (admin-only + import `.sql`)**. Secara historis sistem juga dirancang untuk alur reporter-submit → mekanik-close, tetapi fitur tersebut telah dihapus dari runtime web.

```
Admin upload file .sql (dump tabel bug dari mfg_record)
        │
        ▼
Laravel parse file → buat ImportJob → dispatch chunk jobs ke queue
        │
        ▼
Queue worker (ProcessImportChunkJob) insert setiap baris ke tabel bugs (insert-only; baris dengan ID yang sudah ada dilewati, tidak diupdate)
        │
        ▼
Untuk baris dengan description:
   Laravel PANGGIL Python Analytics Service /analyze-bug-report SECARA SINKRON
   (sentiment, spam check, severity_recommended)
        │
        ▼
Untuk baris dengan root_cause / repair_action:
   Laravel PANGGIL Python Analytics Service /analyze-damage-cause SECARA SINKRON
   (damage_category)
        │
        ▼
ImportJob UPDATE counters & status completed
        │
        ▼
Admin lihat dashboard analitik lengkap (summary, chart, audit table, export CSV)
```

---

## 📁 Struktur Repositori & Konvensi Kode

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── LoginController.php          <- Login, register, logout
│   │   ├── UserSettingsController.php   <- Profile photo serving
│   │   ├── BugController.php            <- Detail bug historis (admin)
│   │   ├── DashboardController.php      <- Analitik & export CSV (admin)
│   │   ├── ProjectController.php        <- Kelola master project (admin)
│   │   ├── SerialNumberController.php   <- Kelola master SN (admin)
│   │   ├── DeviceController.php         <- Kelola master device (admin)
│   │   └── ImportController.php         <- Upload, progress, history, reset, trash, reanalyze
│   └── Middleware/                      <- (kosong setelah role middleware dihapus)
├── Jobs/
│   ├── ProcessImportChunkJob.php        <- Proses chunk row SQL + trigger AI
│   └── ReanalyzeBugsJob.php             <- Re-analisis batch bug
├── Models/
│   ├── User.php (dengan kolom role)
│   ├── Project.php
│   ├── Device.php
│   ├── SerialNumber.php
│   ├── ImportJob.php                    <- Tracking progress import & trash
│   └── Bug.php (relasi 'project', 'serialNumber', 'device', 'importJob')
├── Services/
│   ├── BugAnalyticsService.php          <- HTTP Client SINKRON ke Python service
│   └── SqlImportParser.php              <- Parser .sql mfg_record.bug
database/
├── migrations/                          <- Skema tabel (Strict Forward-Only)
└── seeders/                             <- Admin + master data + 24 baris bug historis

analytics-service/
├── main.py
├── services/
│   ├── sentiment.py                     <- Adaptasi konteks teknis manufaktur
│   ├── spam_detection_improved.py       <- Sistem deteksi spam 3-Tier (Lokal & AI)
│   ├── severity_recommendation.py       <- Usulkan severity dari description
│   └── damage_categorization.py         <- Kategorikan penyebab dari root_cause+repair_action
└── requirements.txt
```

---

## ⚠️ Keputusan Desain Penting (Jangan Diubah Tanpa Diskusi Ulang)

1. **Import `.sql` memakai Queue driver `database`.** Pemrosesan ribuan baris dan pemanggilan Analytics Service dilakukan oleh worker queue agar request upload tidak timeout.
2. **Severity manual TIDAK PERNAH di-override AI.** `severity` (reporter) dan `severity_recommended` (AI) adalah DUA KOLOM TERPISAH, keduanya disimpan dan ditampilkan berdampingan. Keputusan akhir tetap di tangan manusia.
3. **Konsep "Spam" dipertahankan** meski pelapor staf internal — staf internal juga bisa membuat laporan tidak substantif/asal-asalan.
4. **Runtime web adalah admin-only.** Fitur reporter/mechanic workflow, assignment, chat, dan feedback telah dihapus dari aplikasi web. Registrasi hanya membuat akun admin.
5. **Tabel master (`projects`, `devices`, `serial_numbers`) bersifat sementara/placeholder** sampai struktur asli dari `mfg_record` didapatkan.
6. **`reported_by`/`fixed_by` adalah string bebas**, bukan relasi user. Jangan membuat akun `users` otomatis untuk nama dari dump SQL.

---

## ✅ Requirement & Status Implementasi

> Bagian ini mencerminkan **kondisi terkini** proyek (terakhir disinkronkan dengan kode saat ini — v0.9). Status: `✅ Selesai` / `🔄 Sedang dikerjakan` / `📋 Direncanakan, belum dikerjakan` / `🐛 Ada bug, perlu fix`. **Ini SATU-SATUNYA section status di README ini** — semua fitur (termasuk dashboard analitik) dan bug/catatan teknis terkait digabung di sini per kategori, TIDAK ADA section status terpisah lainnya.

### Fondasi
- ✅ Skema database (users extend, projects, devices, serial_numbers, bugs, import_jobs; soft deletes pada `bugs` & `import_jobs`)
- ✅ Seeder import 24 baris data historis dari `mfg_record`
- ✅ Auth & runtime admin-only untuk aplikasi web; flow reporter/mekanik, chat, assignment, dan RoleMiddleware sudah dihapus
- ✅ Register tetap dipertahankan, tetapi hanya membuat akun admin
- ✅ Python Analytics Service (FastAPI) — sentiment, spam detection (Offline Hybrid Context-Aware & ML), severity recommendation, damage categorization
- ✅ Queue database siap dipakai untuk import `.sql`; `QUEUE_CONNECTION=database`
- ✅ Skema `reported_by` dan `fixed_by` sudah menjadi string bebas, bukan FK `users`

### Fitur Inti Admin
- ✅ Dashboard analitik admin tetap dipertahankan
- ✅ Kelola master data Project, Serial Number, & Device
- ✅ Detail bug historis tetap bisa dibaca dari dashboard/halaman detail
- ✅ Fondasi tracking import: tabel `import_jobs` + model `ImportJob`
- ✅ Parser `.sql` untuk dump `mfg_record.bug`
- ✅ Job chunk untuk insert-only + trigger AI
- ✅ Controller upload + halaman progress polling `import_jobs/{id}`

### Dashboard Analitik Admin
- ✅ Export CSV (`DashboardController::exportCsv`)
- ✅ Summary cards (Total Bug, Open, Closed, Critical, Rework Rate, Spam Blocked)
- ✅ Tabel Audit Bug dengan filter (status/severity/project/tanggal) + pagination
- ❌ **Dihapus:** Distribusi Sentimen (Positive/Neutral/Negative/Spam) — donut chart tidak lagi ditampilkan.
- ❌ **Dihapus:** Protokol Rekomendasi / detail AI Stage 1 expandable dari kolom "AI Automation Diagnosis".
- ✅ **SPAM Marker** pada setiap baris tabel Audit Bug (Live Manufacturing Logs Feed) jika laporan terdeteksi spam, lengkap dengan alasan (`spam_reason`).
- ✅ **Log Aktivitas per Laporan** — setiap baris audit bug memiliki expandable "Log Aktivitas Laporan" yang menampilkan timeline per laporan: laporan masuk (reported_by, created_at) dan pengerjaan selesai (fixed_by, closed_at, root cause, repair action).
- ✅ Project Paling Banyak Bug (Top 5)
- ✅ Tren Volume Laporan (line chart, **7 hari terakhir**)
- ✅ Analytics Penyebab Kerusakan (distribusi `damage_category`)
- ✅ Skor Urgency dihitung berdasarkan data per laporan: `round((severity_weight + (1 - sentiment_score)) / 2, 2)`. Nilai `sentiment_score` yang NULL dianggap netral (**0.5**) agar laporan yang belum dianalisis tidak mendapat skor urgency tinggi secara artifisial.

### 🎨 Arah Visual/Styling
- ✅ **Light Mode Profesional/Korporat** — token dasar sudah diimplementasikan di `resources/css/app.css`: putih bersih (`#FFFFFF`), biru korporat `#2563EB` sebagai aksen utama, serta 4 pasang warna badge standar untuk severity/status.
- 🔄 **Penyempurnaan visual:** membersihkan sisa utility class/gradient non-standar (contoh: `premium-card-accent`, `btn-premium-gradient` yang memakai ungu `#7C3AED`) dan menyederhanakan tampilan dashboard yang masih terlalu padat dengan label uppercase/mono.

**Karakter visual yang dituju:** bersih, formal, dipercaya — cocok untuk laporan ke atasan/manajemen di lingkungan manufaktur. TIDAK ADA efek glow/shadow warna mencolok, TIDAK ADA background gelap sebagai default, TIDAK ADA istilah "neon"/"cyberpunk" dalam penamaan apapun (class CSS, variable, komentar kode).

**Design Token (CSS Variables / Tailwind custom colors):**

```
--bg-main: #FFFFFF              (putih bersih, default seluruh halaman)
--bg-sidebar: #FFFFFF
--bg-card: #FFFFFF
--bg-card-hover: #F8FAFC        (abu sangat muda untuk hover state)
--border-default: #E2E8F0       (border tipis standar)
--text-primary: #1E293B         (hampir hitam, untuk teks utama)
--text-secondary: #64748B       (abu sedang, untuk label/caption)
--text-tertiary: #94A3B8        (abu pudar, untuk teks paling minor)

--accent-primary: #2563EB       (biru korporat -- SATU-SATUNYA warna aksen
                                   utama: logo, tombol primary, link aktif,
                                   sidebar item aktif, border focus input)
--accent-primary-hover: #1D4ED8
--accent-primary-soft: #EFF6FF  (biru sangat muda, untuk background badge
                                   info/Batch-ID-style/Kategori, bukan
                                   warna solid)

-- Badge status (4 warna standar, dipakai KHUSUS untuk severity/status,
   BUKAN untuk elemen UI lain):
--badge-success-bg: #DCFCE7     (hijau muda -- CLOSED, severity Minor)
--badge-success-text: #16A34A
--badge-warning-bg: #FEF9C3     (kuning muda -- Major, perlu perhatian)
--badge-warning-text: #CA8A04
--badge-danger-bg: #FEE2E2      (merah muda -- Critical, OPEN urgent)
--badge-danger-text: #DC2626
--badge-neutral-bg: #F1F5F9     (abu muda -- spam, unanalyzed, netral)
--badge-neutral-text: #475569

--radius-card: 12px
--radius-pill: 999px            (untuk SEMUA badge status)
--shadow-card: 0 1px 2px rgba(0,0,0,0.05), 0 1px 3px rgba(0,0,0,0.04)
                                 (shadow SANGAT halus, BUKAN shadow warna)
```

**Aturan pemetaan komponen:**
- Card/panel: SELALU putih (`--bg-card`) dengan border tipis `--border-default`, TIDAK ADA gradient atau warna fill pada body card.
- Tipografi angka/metrik utama (summary cards): besar dan bold (font-weight 700+), warna `--text-primary` (BUKAN warna aksen kecuali untuk menyorot angka kritis — itupun pakai `--badge-danger-text`, bukan biru).
- Severity/Status badge: pill-shape (`--radius-pill`), pakai pasangan bg+text dari 4 token badge di atas — Critical=danger, Major=warning, Minor=success, OPEN=warning (perlu tindakan), CLOSED=success (selesai), Spam/Unanalyzed=neutral.
- Chart (donut sentimen, line tren, bar top-project): gunakan `--accent-primary` sebagai warna dominan untuk data utama, dan 4 token badge di atas untuk kategori bersangkutan (misal sentimen Positive=success, Negative=danger).
- Sidebar: putih, border-right tipis, item aktif memakai border-left `--accent-primary` + teks `--accent-primary` (TIDAK pakai background pill berwarna solid).
- Tombol primary (submit, simpan): solid `--accent-primary`, teks putih, `border-radius` sedang (8px, BUKAN pill).
- HAPUS seluruh elemen `animate-pulse`/efek berkedip yang sifatnya dekoratif murni — PERTAHANKAN HANYA untuk badge counter notifikasi unread (fungsional, bukan dekoratif).

### Housekeeping Repository
- ✅ `Gemini.md` di root sudah dihapus.
- ✅ `index.html` di root sudah dihapus.

### Belum Diputuskan / Direncanakan
- 📋 Penyempurnaan UI/UX dashboard: menyederhanakan tabel audit, mengurangi penggunaan uppercase/mono berlebihan, dan menyelaraskan warna chart sepenuhnya ke design token resmi.

---

## 📌 Catatan Teknis & Keterbatasan yang Diketahui

*(Bukan bug yang menghalangi fungsi, tapi perlu diketahui untuk pengembangan lanjutan)*

1. **`severity_recommended` adalah `varchar(50)`, bukan `enum`.** Sengaja dilonggarkan karena Python Analytics Service kadang mengembalikan nilai yang tidak persis cocok dengan 3 pilihan enum, dan `enum` ketat MySQL menolak insert untuk nilai di luar daftar. Trade-off: kode yang MEMBACA kolom ini (badge, filter) harus defensif terhadap nilai yang mungkin tidak persis "Critical"/"Major"/"Minor".

---

## 📝 Changelog

### v0.14 — Deteksi Spam 3-Tier Peka Konteks & ML Lokal (Offline / AI Hybrid)
- **[SPAM DETECTION]** Mengimplementasikan arsitektur sistem deteksi spam 3-Tier lengkap dengan pemisahan berkas ke [spam_detection_improved.py](file:///d:/MAGANG/WEB2-MAGANG/analytics-service/services/spam_detection_improved.py).
  - **Tier 1:** Deteksi kata kunci promosi, out-of-context filter (saham, skincare, dll.), dan judi slot online.
  - **Tier 2:** Validasi panjang teks, regex karakter berulang (dengan pengecualian format hex hardware), teks tanpa spasi, dan gibberish.
  - **Tier 3:** Menggunakan Gemini API jika API Key tersedia, atau otomatis *fallback* menggunakan model Machine Learning Ensemble lokal (`VotingSpamDetector`) secara 100% offline.
- **[SPAM DETECTION]** Menghapus kata kunci pengujian generik (`test`, `testing`, `uji`, `pengujian`, `coba`, `cobain`, `run`, `running`, `jalan`) dari *context whitelist* agar tidak disalahgunakan untuk meloloskan laporan palsu/sampah.
- **[SPAM DETECTION]** Menambahkan kolom `spam_confidence` ke dalam schema response `BugReportResponse`.
- **[CLEANUP]** Menghapus berkas lama `spam_detection.py` karena fungsinya sudah digantikan sepenuhnya oleh `spam_detection_improved.py`.

### v0.13 — Deteksi Spam Hibrida Peka Konteks & ML Lokal (Offline)
- **[SPAM DETECTION]** Migrasi penuh dari API Gemini ke deteksi spam offline menggunakan **Pendekatan Hibrida Peka Konteks & Machine Learning Lokal** (Ensemble Voting dari 5 model ML lokal via `spam-detector-ai`).
- **[SPAM DETECTION]** Menerapkan hotfix dinamis pada kelas `sklearn.svm.SVC` untuk mengatasi ketidakcocokan versi model *scikit-learn* (menambal properti internal `_effective_probability` di memori).
- **[SPAM DETECTION]** Menambahkan list **`OUT_OF_CONTEXT_KEYWORDS`** untuk mendepak teks promosi non-manufaktur (saham, resep kue, wisata, loker, dll.) yang kebetulan mengandung kata kunci teknis minor (seperti `aplikasi`, `mobile`).
- **[SPAM DETECTION]** Memperbaiki aturan batas kata (`\b`) pada deteksi judi (`slot` hanya dideteksi jika berupa pola judi online/gacor, bukan slot fisik perangkat keras seperti *slot SD card*).
- **[SPAM DETECTION]** Mengecualikan format hexadesimal hardware (seperti `0xFFFFFF`) dari filter deteksi karakter berulang.
- **[CLEANUP]** Menghapus file `verify_api.py` karena verifikasi API key Gemini sudah tidak lagi diperlukan.

### v0.12 — Sederhanakan Proses Import: Hanya INSERT, SKIP, FAIL
- **[IMPORT]** Logika import diubah dari **upsert** menjadi **insert-only**.
  - `INSERT`: baris dengan `idbug` baru berhasil ditambahkan. Jika `idbug` tersebut sebelumnya sudah pernah diimport lalu **dihapus (soft-delete)**, data lama dihapus permanen terlebih dahulu sebelum baris baru dimasukkan — sehingga tetap dihitung sebagai INSERT.
  - `SKIP`: baris dengan `idbug` yang **masih aktif** di database dilewati (tidak diupdate).
  - `FAIL`: baris dengan data tidak valid (id/title kosong, status tidak dikenali, dll.) gagal diproses.
- **[IMPORT]** Counter "Diperbarui" dan "Dihapus" dihapus dari halaman progress import dan riwayat import.
- **[IMPORT]** Banner completion diperbarui: hanya menyebutkan jumlah INSERT, SKIP, dan FAIL.

### v0.11 — Revisi Dashboard: Hapus Protokol Rekomendasi, Log Aktivitas per Laporan, Spam Marker di Audit, 7 Hari, Urgency Fix
- **[DASHBOARD]** Blok expandable "// PROTOCOL RECOMMENDATION" dihapus dari kolom "AI Automation Diagnosis" tabel audit.
- **[DASHBOARD]** SPAM marker dipindahkan dari tabel "Top 5 Project" ke setiap baris tabel Audit Bug (Live Manufacturing Logs Feed), dengan badge merah dan alasan spam.
- **[DASHBOARD]** Log aktivitas ditambahkan secara per-laporan — setiap baris audit bug memiliki expandable "Log Aktivitas Laporan" yang menampilkan: laporan masuk (reported_by, created_at) dan pengerjaan selesai (fixed_by, closed_at, root cause, repair action).
- **[DASHBOARD]** Tren volume laporan diubah dari 15 hari menjadi **7 hari terakhir**.
- **[DASHBOARD]** Perhitungan skor urgency dikoreksi: nilai `sentiment_score` yang NULL sekarang dianggap **netral (0.5)**, bukan 0.0, sehingga laporan belum dianalisis tidak lagi mendapat skor urgency tinggi secara artifisial.
- **[IMPORT]** Status `RESOLVED` dan `IN PROGRESS` dari file SQL kini dipetakan ke `CLOSED`/`OPEN`, mengatasi error `Data truncated for column 'status'` saat import.

### v0.10 — Revisi Dashboard: Hapus Distribusi Sentimen, Tambah Marker Spam per Project
- **[DASHBOARD]** Chart "Distribusi Sentimen" dihapus dari dashboard.
- **[DASHBOARD]** Deteksi spam tetap dipertahankan: summary card "AI Spam Blocked", marker spam pada baris audit, dan deteksi di Analytics Service.
- **[DASHBOARD]** Tabel "Project Paling Banyak Bug (Top 5)" ditambahkan marker **SPAM** (badge merah + jumlah laporan spam) untuk project yang memiliki laporan spam.

### v0.9 — Sinkronisasi Dokumen & Optimasi Bulk Delete
- **[DOKUMENTASI]** README.md diselaraskan dengan struktur proyek saat ini: runtime admin-only, penghapusan fitur assignment/chat/feedback, serta struktur file terbaru (`ImportController`, `SqlImportParser`, `ProcessImportChunkJob`, `ReanalyzeBugsJob`).
- **[DOKUMENTASI]** Status implementasi diperbarui: parser `.sql`, job chunk, upload/progress, dan seluruh dashboard analitik terverifikasi ✅.
- **[PERFORMANCE]** `ImportController::deleteFromHistory()` dioptimasi dengan raw SQL `DELETE FROM bugs WHERE import_job_id = ?` untuk menghindari N+1 `forceDelete()`.
- **[PERFORMANCE]** `ImportController::forceDeleteSelected()` dioptimasi dengan raw SQL `DELETE FROM bugs WHERE import_job_id IN (...) AND deleted_at IS NOT NULL` + `ImportJob::whereIn()->forceDelete()` untuk bulk delete beberapa job sekaligus.
- **[HOUSEKEEPING]** `Gemini.md` dan `index.html` di root sudah dihapus.
- **[VISUAL]** Token Light Mode Profesional/Korporat sudah diterapkan di `resources/css/app.css`; sisa penyempurnaan visual masih dalam progres.

### v0.8 — Fondasi Import Queue & Actor String
- **[PERUBAHAN SKEMA]** `bugs.reported_by` dan `bugs.fixed_by` diubah menjadi string bebas nullable melalui migration forward-only; relasi `reporter()`/`fixer()` di `Bug.php` dihapus.
- **[FITUR BARU]** Tabel `import_jobs` dan model `ImportJob` dibuat sebagai fondasi progress tracking import `.sql`.
- **[QUEUE]** Import `.sql` akan memakai Laravel Queue driver `database`; `.env` sudah memakai `QUEUE_CONNECTION=database`, dan developer wajib menjalankan `php artisan queue:work`.
- **[PENYESUAIAN DATA]** Seeder menyimpan nama pelapor/perbaikan historis langsung sebagai string dan tidak lagi membuat akun user untuk nama bebas dari sumber.

### v0.7 — Peralihan ke Admin-Only Runtime
- **[PERUBAHAN ARSITEKTUR]** Flow `reporter` dan `mekanik` dihapus dari runtime web. Route, controller, view, middleware, dan chat/assignment terkait sudah dibersihkan.
- **[PENYESUAIAN AUTH]** Registrasi tetap dipertahankan, tetapi sekarang hanya membuat akun admin.
- **[PEMELIHARAAN]** `bug_chats` dijadwalkan di-drop lewat migration forward-only, dan `DatabaseSeeder.php` diselaraskan ke akun admin-only.
- **[STATUS]** Dashboard analitik dan service AI sengaja dipertahankan tanpa perubahan fungsional pada fase ini.

### v0.1 — Scaffold Awal
- Setup skema database 6 tabel, migration forward-only.
- Seeder import 24 baris data historis dari tabel `bug` milik database `mfg_record` (tempat magang).
- Akun dibuat untuk setiap nama unik di data historis: Admin, Fioni Agriyani, Maneng, manufacture, program, dan nilai anomali "1" (role default: mekanik).
- Auth 3 role (reporter, mekanik, admin) dengan RoleMiddleware.
- Python Analytics Service (FastAPI) dengan 4 modul: sentiment, spam_detection, severity_recommendation, damage_categorization — SEMUA dipanggil sinkron, tanpa Queue/Job.
- `BugAnalyticsService.php` sebagai HTTP Client sinkron ke Analytics Service, dengan fallback aman jika service down (tidak menggagalkan submit bug).

### v0.2 — Fitur Inti per Role
- `BugController`: submit bug (reporter), tampilkan queue OPEN, form tutup bug (mekanik) dengan analisis AI Tahap 2.
- `BugFeedbackController` (versi awal, KEMUDIAN DIGANTI — lihat v0.6): kirim & baca feedback mekanik → reporter.
- `DashboardController`: dashboard analitik dasar + export CSV.
- `ProjectController` & `SerialNumberController`: kelola master data (admin).

### v0.3 — Audit v1 & Temuan Bug
- Audit menyeluruh kesesuaian kode vs README.md dilakukan oleh Antigravity.
- Ditemukan bug KRITIS: mass-assignment `id` pada Project/SerialNumber seeder.
- Ditemukan gap UI: input filter tanggal hilang, distribusi sentimen/top produk/tren volume belum dirender di view meski query backend sudah ada.
- Ditemukan gap fitur: master data Device belum punya Controller/view.
- Status beberapa item dikoreksi: Analytics Penyebab Kerusakan ternyata SUDAH selesai (sebelumnya tercatat 📋).

### v0.4 — Penyelesaian Bug & Fitur Tertunda (Audit v2 — Terverifikasi)
- **[FIX]** Mass-assignment `id` pada `Project.php`/`SerialNumber.php` — `id` ditambahkan ke `$fillable`, terverifikasi langsung di kode.
- **[FIX]** Input rentang tanggal (`date_from`/`date_to`) ditambahkan ke filter dashboard, terverifikasi ada di `resources/views/dashboard/index.blade.php`.
- **[FIX]** `DeviceController` + view + route `/master/devices` dibuat, terverifikasi ada dan terdaftar di `routes/web.php`.
- **[SELESAI]** Summary cards Open/Closed terpisah, donut chart sentimen (ApexCharts), tabel Top 5 Project, line chart tren volume 15 hari, badge notifikasi feedback unread di sidebar — SEMUA terverifikasi nyata di kode (bukan klaim kosong).
- **[DITOLAK]** Tema visual "Cyberpunk/Neon" yang diimplementasikan AI coding agent secara sepihak (warna `neon-cyan`/`neon-pink`/`neon-amber`/`neon-green`, background `obsidian`) — **akan diganti** ke arah Light Mode Profesional/Korporat pada task berikutnya. Lihat section "🎨 Arah Visual/Styling" di atas.
- **[CATATAN]** Klaim laporan soal "eager-loading relasi feedbacks di controller" untuk notifikasi tidak akurat — implementasi sebenarnya query langsung di `layouts/app.blade.php`. Berfungsi baik, dicatat sebagai item rapikan teknis non-urgent.

### v0.5 — Spesifikasi Arah Visual Baru Ditetapkan
- Tema "Cyberpunk/Neon" resmi ditolak dan diganti dengan spesifikasi **Light Mode Profesional/Korporat**.
- Design token lengkap ditetapkan: biru korporat (`#2563EB`) sebagai satu-satunya warna aksen utama, 4 warna badge standar (success/warning/danger/neutral) untuk status & severity, background putih bersih tanpa gradient/glow.
- Status: spesifikasi sudah final di README.md, **BELUM dieksekusi ke kode**.

### v0.6 — Perubahan Arsitektur Besar & Konsolidasi Dokumen
- **[PERUBAHAN ARSITEKTUR]** Tabel `bug_feedback` DI-DROP, diganti `bug_chats` — fitur "feedback satu-arah" digantikan total dengan **Assignment + Chat dua arah**. `BugFeedbackController` digantikan `BugChatController`.
- **[FITUR BARU]** Kolom `assigned_to`/`assigned_at` pada `bugs` — mekanik bisa klaim bug OPEN secara eksklusif.
- **[PERUBAHAN SKEMA]** `severity_recommended` diubah dari `enum` ketat menjadi `varchar(50)` — mengatasi kegagalan insert saat AI mengembalikan nilai yang tidak persis cocok dengan 3 pilihan enum.
- **[VERIFIKASI]** Spesifikasi visual Light Mode Profesional (v0.5) dikonfirmasi **BELUM diimplementasikan** — `app.css` masih berisi token neon lama.
- **[KONSOLIDASI DOKUMEN]** Section "Dashboard Analitik (Mirroring SmartReport)" dan "Bug Diketahui & Perlu Diperbaiki" yang sebelumnya terpisah dan tumpang-tindih isinya **DIGABUNG** menjadi satu struktur tunggal di bawah "Requirement & Status Implementasi" — TIDAK ADA lagi section status implementasi yang terpisah-pisah. Catatan teknis (bug ringan, keterbatasan desain) dipindah ke section baru "📌 Catatan Teknis & Keterbatasan yang Diketahui".
- **[STATUS DIKOREKSI]** Seluruh item Dashboard Analitik diturunkan dari ✅ menjadi 🔄 — perlu verifikasi ulang kode karena kemungkinan ditulis ulang saat perubahan arsitektur Assignment+Chat.
- **[HOUSEKEEPING]** `Gemini.md` dan `index.html` di root ditandai untuk dihapus.

---

## 🚀 Cara Menjalankan (Development)

```bash
# Laravel
composer install
php artisan migrate
php artisan db:seed
php artisan serve

# Queue worker import SQL (terminal terpisah)
php artisan queue:work

# Python Analytics Service (terminal terpisah)
# PENTING: Jalankan di port 8001 agar tidak konflik dengan Laravel (port 8000)
cd analytics-service
pip install -r requirements.txt
uvicorn main:app --reload --port 8001
```

Akses aplikasi di `http://127.0.0.1:8000` (Laravel). Analytics Service berjalan di port `http://127.0.0.1:8001`.

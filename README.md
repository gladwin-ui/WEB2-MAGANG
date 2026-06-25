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
- **Analytics Engine:** Mikroservis Python (FastAPI) dengan pendekatan rule-based/keyword (sentiment, spam, severity recommendation, damage categorization) — dipanggil dari Laravel saat import berjalan.
- **Queue:** Laravel Queue driver `database` dipakai untuk import `.sql` volume besar. Tidak perlu Redis, tetapi developer wajib menjalankan `php artisan queue:work`.

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

### Assignment & Chat Mekanik ↔ Reporter

> **Catatan:** Konsep `bug_feedback` (pesan sekali kirim mekanik→reporter) yang sebelumnya direncanakan **telah digantikan total** dengan mekanisme Assignment + Chat dua arah. Tabel `bug_feedback` sudah di-drop, diganti `bug_chats`.

**Assignment (Claim Bug):**
- Bug berstatus `OPEN` terlihat oleh SEMUA mekanik di queue.
- Mekanik mana pun bisa **klaim/assign** bug tersebut ke dirinya sendiri (`assigned_to`, `assigned_at` pada tabel `bugs`).
- Begitu sebuah bug sudah di-assign, mekanik LAIN tidak bisa mengklaimnya lagi — mencegah dua mekanik menangani bug yang sama tanpa koordinasi.

**Chat (Dua Arah, Per-Bug):**
- Setiap bug punya ruang chat sendiri (tabel `bug_chats`: `bug_id`, `sender_id`, `message`).
- **Reporter** HANYA bisa membuka/mengirim chat JIKA bug miliknya SUDAH di-assign ke seorang mekanik.
- **Mekanik** HANYA bisa membuka/mengirim chat pada bug yang **dia sendiri** yang assign.
- Chat menggunakan pola **polling AJAX sederhana**, BUKAN WebSocket.

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
                      reported_by (string bebas), status (OPEN|CLOSED),
                      assigned_to (FK users, nullable), assigned_at (nullable),
                      fixed_by (string bebas, nullable), closed_at,
                      -- Hasil AI Tahap 1 (saat submit):
                      sentiment_label, sentiment_score, is_spam, spam_reason,
                      severity_recommended (varchar -- lihat Catatan Teknis),
                      severity_recommendation_reason,
                      -- Hasil AI Tahap 2 (saat ditutup):
                      damage_category
import_jobs        : id, filename, total_rows, processed_rows,
                      inserted_count, updated_count, skipped_count, failed_count,
                      status (pending|processing|completed|failed), error_message,
                      started_at, finished_at, timestamps
```

**Catatan:** `projects`, `devices`, dan `serial_numbers` adalah tabel master **MINIMAL/placeholder** — struktur tabel asli di database `mfg_record` tidak diketahui sepenuhnya. Data lama diimpor dengan nama generik ("Project #27", dst) dan **PERLU diedit manual** setelah data master asli didapat dari tempat magang.

`reported_by` dan `fixed_by` adalah **string bebas** dari sumber `mfg_record.bug`, bukan FK ke `users`. Field ini hanya informasional dan tidak dipakai untuk permission.

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
│   │   ├── BugController.php          <- Submit, queue, assign (mekanik), close
│   │   ├── BugChatController.php      <- Chat dua arah per-bug (show, send, poll)
│   │   ├── DashboardController.php    <- Analitik (admin)
│   │   ├── ProjectController.php      <- Kelola master project (admin)
│   │   ├── SerialNumberController.php <- Kelola master SN (admin)
│   │   └── DeviceController.php       <- Kelola master device (admin)
│   └── Middleware/
│       └── RoleMiddleware.php          <- Role-based access (reporter|mekanik|admin)
├── Models/
│   ├── User.php (dengan kolom role)
│   ├── Project.php
│   ├── Device.php
│   ├── SerialNumber.php
│   ├── Bug.php (relasi 'reporter', 'fixer', 'assignee' ke User; 'project', 'serialNumber', 'device'; 'chats')
│   └── BugChat.php (relasi 'sender' ke User, 'bug')
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

1. **Import `.sql` memakai Queue driver `database`.** Pemrosesan ribuan baris dan pemanggilan Analytics Service dilakukan oleh worker queue agar request upload tidak timeout.
2. **Severity manual TIDAK PERNAH di-override AI.** `severity` (reporter) dan `severity_recommended` (AI) adalah DUA KOLOM TERPISAH, keduanya disimpan dan ditampilkan berdampingan. Keputusan akhir tetap di tangan manusia.
3. **Konsep "Spam" dipertahankan** meski pelapor staf internal — staf internal juga bisa membuat laporan tidak substantif/asal-asalan.
4. **Assignment bersifat eksklusif (first-come-first-served).** Begitu satu mekanik mengklaim bug OPEN, mekanik lain tidak bisa mengklaimnya lagi. Chat per-bug hanya terbuka setelah assignment ada — reporter tidak bisa memulai percakapan sebelum ada mekanik yang menangani bug-nya.
5. **Tabel master (`projects`, `devices`, `serial_numbers`) bersifat sementara/placeholder** sampai struktur asli dari `mfg_record` didapatkan.
6. **`reported_by`/`fixed_by` adalah string bebas**, bukan relasi user. Jangan membuat akun `users` otomatis untuk nama dari dump SQL.

---

## ✅ Requirement & Status Implementasi

> Bagian ini mencerminkan **kondisi terkini** proyek (terakhir diperbarui setelah Audit Menyeluruh v3). Status: `✅ Selesai` / `🔄 Sedang dikerjakan` / `📋 Direncanakan, belum dikerjakan` / `🐛 Ada bug, perlu fix`. **Ini SATU-SATUNYA section status di README ini** — semua fitur (termasuk dashboard analitik) dan bug/catatan teknis terkait digabung di sini per kategori, TIDAK ADA section status terpisah lainnya.

### Fondasi
- ✅ Skema database (users extend, projects, devices, serial_numbers, bugs, bug_chats)
- ✅ Seeder import 24 baris data historis dari `mfg_record`
- ✅ Auth & runtime admin-only untuk aplikasi web; flow reporter/mekanik, chat, assignment, dan RoleMiddleware sudah dihapus
- ✅ Register tetap dipertahankan, tetapi hanya membuat akun admin
- ✅ Python Analytics Service (FastAPI) — sentiment, spam detection, severity recommendation, damage categorization
- ✅ Queue database siap dipakai untuk import `.sql`; `QUEUE_CONNECTION=database`
- ✅ Skema `reported_by` dan `fixed_by` sudah menjadi string bebas, bukan FK `users`

### Fitur Inti Admin
- ✅ Dashboard analitik admin tetap dipertahankan
- ✅ Kelola master data Project, Serial Number, & Device
- ✅ Detail bug historis tetap bisa dibaca dari dashboard/halaman detail
- ✅ Fondasi tracking import: tabel `import_jobs` + model `ImportJob`
- 📋 Parser `.sql` untuk dump `mfg_record.bug`
- 📋 Job chunk untuk upsert + trigger AI kondisional
- 📋 Controller upload + halaman progress polling `import_jobs/{id}`

### Dashboard Analitik Admin
> ⚠️ **PERLU VERIFIKASI ULANG** — fitur di bawah sebelumnya tercatat ✅ berdasarkan laporan Antigravity, namun karena `dashboard/index.blade.php` kemungkinan ditulis ulang sebagai bagian dari perubahan arsitektur Assignment+Chat (lihat Changelog v0.6), status berikut **TIDAK BOLEH diasumsikan otomatis masih utuh** — agent yang mengerjakan task berikutnya WAJIB mengecek ulang isi kode, bukan hanya membaca status di sini.
- 🔄 Export CSV (`DashboardController::exportCsv`)
- 🔄 Summary cards (Total Bug, Open, Closed, Critical, Rework Rate, Spam Blocked)
- 🔄 Tabel Audit Bug dengan filter (status/severity/project/tanggal) + pagination
- 🔄 Distribusi Sentimen (Positive/Neutral/Negative/Spam) — donut chart
- 🔄 Project Paling Banyak Bug (Top 5)
- 🔄 Tren Volume Laporan (line chart)
- 🔄 Analytics Penyebab Kerusakan (distribusi `damage_category`)

### 🎨 Arah Visual/Styling
- ❌ **DITOLAK & WAJIB DIGANTI:** Tema "Cyberpunk/Neon" (`neon-cyan`, `neon-pink`, `neon-amber`, `neon-green`, `obsidian`/`panel-bg`) — diimplementasikan sepihak oleh AI coding agent tanpa pernah disepakati. **Status verifikasi terbaru: token neon INI MASIH ADA di `resources/css/app.css`, BELUM diganti.**
- 📋 **Spesifikasi resmi pengganti — Light Mode Profesional/Korporat** (lihat detail design token lengkap di bawah). **BELUM diimplementasikan ke kode.**

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
- 🐛 `Gemini.md` di root — dokumen acuan LAMA yang sudah usang (isinya sudah digabung ke README.md ini). **HARUS DIHAPUS.**
- 🐛 `index.html` di root — file HTML statis tidak relevan, kemungkinan mockup yang tidak sengaja tersimpan. **HARUS DIHAPUS.**

### Belum Diputuskan / Direncanakan
- 📋 Mekanisme "lepas klaim" assignment — saat ini belum ada requirement untuk ini, belum diputuskan apakah dibutuhkan.

---

## 📌 Catatan Teknis & Keterbatasan yang Diketahui

*(Bukan bug yang menghalangi fungsi, tapi perlu diketahui untuk pengembangan lanjutan)*

1. **`severity_recommended` adalah `varchar(50)`, bukan `enum`.** Sengaja dilonggarkan karena Python Analytics Service kadang mengembalikan nilai yang tidak persis cocok dengan 3 pilihan enum, dan `enum` ketat MySQL menolak insert untuk nilai di luar daftar. Trade-off: kode yang MEMBACA kolom ini (badge, filter) harus defensif terhadap nilai yang mungkin tidak persis "Critical"/"Major"/"Minor".
2. **Belum ada mekanisme "lepas klaim" assignment.** Jika mekanik assign bug tapi tidak jadi menanganinya, saat ini TIDAK ADA cara mengembalikan bug ke status "belum di-assign" — bug tetap terkunci ke mekanik tersebut sampai di-CLOSE.

---

## 📝 Changelog

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

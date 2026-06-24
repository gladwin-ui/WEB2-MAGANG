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

### Fitur Assignment & Chat Mekanik ↔ Reporter

> **PERUBAHAN ARSITEKTUR (menggantikan konsep "Feedback satu-arah" sebelumnya):** Konsep `bug_feedback` (pesan sekali kirim mekanik→reporter) **digantikan total** dengan mekanisme **Assignment + Chat dua arah**. Tabel `bug_feedback` SUDAH DI-DROP, diganti tabel `bug_chats`.

**Assignment (Claim Bug):**
- Bug berstatus `OPEN` terlihat oleh SEMUA mekanik di queue.
- Mekanik mana pun bisa **klaim/assign** bug tersebut ke dirinya sendiri (`assigned_to`, `assigned_at` pada tabel `bugs`).
- Begitu sebuah bug sudah di-assign, mekanik LAIN tidak bisa mengklaimnya lagi (validasi `assigned_to` harus `null` sebelum assign berhasil) — mencegah dua mekanik menangani bug yang sama tanpa koordinasi.

**Chat (Dua Arah, Per-Bug):**
- Setiap bug punya ruang chat sendiri (tabel `bug_chats`: `bug_id`, `sender_id`, `message`).
- **Reporter** HANYA bisa membuka/mengirim chat JIKA bug miliknya SUDAH di-assign ke seorang mekanik (`assigned_to` tidak null).
- **Mekanik** HANYA bisa membuka/mengirim chat pada bug yang **dia sendiri** yang assign (`assigned_to === Auth::id()`).
- Chat menggunakan pola **polling AJAX sederhana** (endpoint `poll` mengambil pesan baru berdasarkan `last_id`), BUKAN WebSocket — cukup untuk skala penggunaan internal saat ini.

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
                      assigned_to (FK users, nullable), assigned_at (nullable),
                      fixed_by (FK users, nullable), closed_at,
                      -- Hasil AI Tahap 1 (saat submit):
                      sentiment_label, sentiment_score, is_spam, spam_reason,
                      severity_recommended (varchar, BUKAN enum -- lihat catatan di bawah),
                      severity_recommendation_reason,
                      -- Hasil AI Tahap 2 (saat ditutup):
                      damage_category
bug_chats          : id, bug_id (FK), sender_id (FK users), message
                      -- (MENGGANTIKAN tabel bug_feedback yang sudah di-drop)
```

**Catatan:** `projects`, `devices`, dan `serial_numbers` adalah tabel master **MINIMAL/placeholder** — struktur tabel asli di database `mfg_record` tidak diketahui sepenuhnya. Data lama diimpor dengan nama generik ("Project #27", dst) dan **PERLU diedit manual** setelah data master asli didapat dari tempat magang.

`reported_by`, `fixed_by`, dan `assigned_to` adalah **foreign key relasional** ke tabel `users` (BUKAN string bebas seperti pada data asli `bugcreatedby`/`bugfixby`).

`severity_recommended` sengaja diubah dari `enum('Critical','Major','Minor')` menjadi `varchar(50)` — Python Analytics Service kadang mengembalikan nilai yang tidak persis cocok dengan 3 pilihan enum (variasi kapitalisasi, atau nilai fallback), dan `enum` ketat di MySQL menolak insert untuk nilai di luar daftar. `varchar` lebih permisif terhadap variasi output AI, dengan trade-off validasi nilai jadi tanggung jawab kode aplikasi, bukan database.

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
│   │   ├── BugController.php          <- Submit, queue, assign (mekanik), close (reporter+mekanik)
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

1. **TIDAK ADA Queue/Job/Redis.** Semua pemanggilan ke Python Analytics Service bersifat SINKRON dalam siklus request yang sama. Keputusan SENGAJA karena mekanik membutuhkan visibilitas bug secara real-time.
2. **Severity manual TIDAK PERNAH di-override AI.** `severity` (reporter) dan `severity_recommended` (AI) adalah DUA KOLOM TERPISAH, keduanya disimpan dan ditampilkan berdampingan. Keputusan akhir tetap di tangan manusia.
3. **Konsep "Spam" dipertahankan** meski pelapor staf internal — staf internal juga bisa membuat laporan tidak substantif/asal-asalan.
4. **Assignment bersifat eksklusif (first-come-first-served).** Begitu satu mekanik mengklaim bug OPEN, mekanik lain tidak bisa mengklaimnya lagi. Tidak ada mekanisme "lepas klaim" saat ini (jika dibutuhkan, harus didiskusikan sebagai requirement baru).
5. **Chat hanya terbuka setelah assignment ada.** Reporter tidak bisa memulai percakapan sebelum ada mekanik yang menangani bug-nya — ini mencegah reporter "menyerbu" chat sebelum ada pihak yang relevan untuk merespons.
6. **Tabel master (`projects`, `devices`, `serial_numbers`) bersifat sementara/placeholder** sampai struktur asli dari `mfg_record` didapatkan.
7. **`reported_by`/`fixed_by`/`assigned_to` adalah foreign key relasional**, bukan string bebas — akun dibuat untuk setiap nama unik yang ditemukan di data historis (Admin, Fioni Agriyani, Maneng, manufacture, program, dan nilai anomali "1").

---

## ✅ Requirement & Status Implementasi

> Bagian ini mencerminkan **kondisi terkini** proyek (terakhir diperbarui setelah Audit Menyeluruh v3 — pasca perubahan arsitektur Assignment+Chat). Status: `✅ Selesai` / `🔄 Sedang dikerjakan` / `📋 Direncanakan, belum dikerjakan` / `🐛 Ada bug, perlu fix`.

### Fondasi
- ✅ Skema database (users extend, projects, devices, serial_numbers, bugs, bug_chats)
- ✅ Seeder import 24 baris data historis dari `mfg_record`
- ✅ Auth & 3 role (reporter, mekanik, admin) + RoleMiddleware
- ✅ Python Analytics Service (FastAPI) — sentiment, spam detection, severity recommendation, damage categorization
- ✅ Pemanggilan SINKRON ke Analytics Service (tanpa Queue/Job)

### Fitur Inti
- ✅ Submit bug oleh Reporter (`BugController::store`) dengan analisis AI Tahap 1 sinkron
- ✅ Queue bug OPEN untuk Mekanik (`BugController::queue`)
- ✅ Assignment/klaim bug eksklusif oleh Mekanik (`BugController::assign`)
- ✅ Form tutup bug oleh Mekanik (`root_cause`, `repair_action`, `is_rework`) dengan analisis AI Tahap 2 sinkron (`damage_category`)
- ✅ Chat dua arah Reporter ↔ Mekanik per-bug, dengan polling AJAX (`BugChatController`)
- ✅ Dashboard Analitik Admin dasar + Export CSV (`DashboardController::exportCsv`)
- ✅ Kelola master data Project, Serial Number, & Device (admin)

### Dashboard Analitik (Mirroring SmartReport)
> **PERLU DIVERIFIKASI ULANG** — fitur-fitur ini sebelumnya dicatat ✅ berdasarkan laporan Antigravity (Audit v2), TAPI implementasinya saat itu memakai tema visual neon yang sudah ditolak. Karena `dashboard/index.blade.php` kemungkinan telah ditulis ulang sebagai bagian dari perubahan arsitektur besar (Assignment+Chat), **status berikut perlu dicek ulang isinya**, bukan cuma asumsi "pasti masih ada":
- 🔄 Summary cards (Total Bug, Open, Closed, Critical, Rework Rate, Spam Blocked)
- 🔄 Distribusi Sentimen — donut chart
- 🔄 Project Paling Banyak Bug (Top 5)
- 🔄 Tren Volume Laporan (line chart)
- 🔄 Analytics Penyebab Kerusakan (distribusi `damage_category`)
- 🔄 Tabel Audit Bug dengan filter (status/severity/project/tanggal) + pagination

### 🎨 Arah Visual/Styling — SPESIFIKASI RESMI (Light Mode Profesional/Korporat)
- ❌ **MASIH BELUM DIIMPLEMENTASIKAN.** Verifikasi terbaru: `resources/css/app.css` MASIH berisi token lama (`--color-obsidian`, `--color-neon-cyan`, `--color-neon-pink`, `--color-neon-green`, `--color-neon-amber`, utility `shadow-neon-*`) dan BELUM memiliki token `--accent-primary: #2563EB` sesuai spesifikasi resmi. **Spesifikasi detail lihat di bawah** — token ini WAJIB diterapkan di `app.css` DAN seluruh view yang masih merujuk kelas `neon-*`/`obsidian`/`panel-bg`.

### Belum Dikerjakan
- 📋 Implementasi penuh spesifikasi visual Light Mode Profesional (lihat di atas)
- 📋 Mekanisme "lepas klaim" assignment (jika dibutuhkan ke depan — saat ini belum ada requirement untuk ini, BELUM diputuskan)

---

## 🧹 Pembersihan Repository (Housekeeping)

- 🐛 **`Gemini.md` di root proyek SUDAH USANG** — file ini adalah versi LAMA dokumen acuan, isinya sudah sepenuhnya digabung ke `README.md` ini sejak v0.3. **HARUS DIHAPUS** dari repository untuk mencegah AI coding agent membaca dokumen yang sudah tidak sinkron dengan kondisi terkini.
- 🐛 **`index.html` di root proyek TIDAK RELEVAN** — file HTML statis berjudul "Modern Admin Dashboard - Business" yang terdeteksi di root, kemungkinan mockup/referensi desain yang tidak sengaja ikut tersimpan ke repository. Proyek ini adalah aplikasi Laravel (server-rendered Blade), file HTML statis berdiri sendiri di root TIDAK memiliki fungsi dalam arsitektur ini. **HARUS DIHAPUS.**

### 🎨 Arah Visual/Styling — SPESIFIKASI RESMI (Light Mode Profesional/Korporat)

- ❌ **DITOLAK:** Tema "Cyberpunk/Neon" (warna `neon-cyan`, `neon-pink`, `neon-amber`, `neon-green`, background `obsidian`/`panel-bg`) yang diimplementasikan secara SEPIHAK oleh AI coding agent tanpa pernah didiskusikan/disepakati sebelumnya. **Tema ini WAJIB DIGANTI sepenuhnya** mengikuti spesifikasi di bawah.

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

📋 **Status implementasi spesifikasi ini:** belum dieksekusi — task berikutnya untuk Antigravity adalah menerapkan token di atas ke SELURUH view yang saat ini masih memakai kelas `neon-*`/`obsidian`/`panel-bg`, MENGGANTI TOTAL tanpa sisa.

---

## 🐛 Bug Diketahui & Perlu Diperbaiki

*(3 bug dari Audit v1 — mass-assignment, input tanggal, Device controller — SUDAH TERVERIFIKASI diperbaiki, lihat Changelog v0.4)*

### Catatan Teknis Ringan (Tidak Urgent)
1. **`severity_recommended` kini `varchar` bebas, bukan `enum`.** Trade-off yang disengaja (lihat catatan di "Skema Database") — pastikan kode yang MEMBACA kolom ini (badge, filter) tetap defensif terhadap nilai yang mungkin tidak persis "Critical"/"Major"/"Minor" (misal hasil AI yang typo/format beda).
2. **Belum ada mekanisme "lepas klaim" assignment.** Jika seorang mekanik assign bug tapi tidak jadi menanganinya (lupa, salah klik, dsb), saat ini TIDAK ADA cara mengembalikan bug itu ke status "belum di-assign" — bug tersebut akan tetap terkunci ke mekanik itu selamanya sampai di-CLOSE. Ini BUKAN bug, tapi gap requirement yang belum diputuskan apakah perlu ditangani.

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
- Status: spesifikasi sudah final di README.md, **BELUM dieksekusi ke kode** — menunggu task implementasi berikutnya.

### v0.6 — Audit Menyeluruh v3: Perubahan Arsitektur Besar Terdeteksi
- **[PERUBAHAN ARSITEKTUR]** Tabel `bug_feedback` DI-DROP, diganti `bug_chats` — fitur "feedback satu-arah" digantikan total dengan **Assignment + Chat dua arah**.
- **[FITUR BARU]** Kolom `assigned_to`/`assigned_at` pada `bugs` — mekanik bisa klaim bug OPEN secara eksklusif (`BugController::assign`).
- **[FITUR BARU]** `BugChatController` — chat per-bug dengan polling AJAX, akses dikunci: reporter butuh bug sudah di-assign, mekanik hanya bisa chat di bug yang dia assign sendiri.
- **[PERUBAHAN SKEMA]** `severity_recommended` diubah dari `enum` ketat menjadi `varchar(50)` — mengatasi kegagalan insert saat AI mengembalikan nilai yang tidak persis cocok dengan 3 pilihan enum.
- **[VERIFIKASI]** Spesifikasi visual Light Mode Profesional (v0.5) **DIKONFIRMASI BELUM diimplementasikan** — `resources/css/app.css` masih berisi token neon lama, belum ada `--accent-primary: #2563EB`.
- **[STATUS DIKOREKSI]** Seluruh item Dashboard Analitik (donut sentimen, top 5 project, tren volume, dst) yang sebelumnya tercatat ✅ di v0.4 **diturunkan ke 🔄** — perlu verifikasi ulang karena `dashboard/index.blade.php` kemungkinan ditulis ulang sebagai bagian dari perubahan arsitektur besar ini, status lama tidak bisa diasumsikan masih berlaku begitu saja.
- **[HOUSEKEEPING]** Ditemukan 2 file tidak relevan di root proyek: `Gemini.md` (dokumen acuan lama yang sudah usang) dan `index.html` (mockup HTML statis tidak terpakai) — keduanya ditandai untuk DIHAPUS.

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
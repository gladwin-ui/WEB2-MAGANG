# 🔧 Vibecoding Memory: BugTrack MFG (Manufacturing Bug Tracking System)

## 📌 Project Overview
Proyek ini adalah *aplikasi internal* berbasis **Laravel** untuk mengelola pelaporan dan penanganan bug/cacat produksi pada lingkungan manufaktur. Sistem ini mengadaptasi data nyata dari tabel `bug` pada database produksi (`mfg_record`) di tempat magang, dan dirancang untuk tiga peran pengguna: **Reporter** (pelapor bug), **Mekanik** (penangan/perbaikan), dan **Admin** (pemantau & analitik).

Berbeda dari proyek SmartReport (yang menyimulasikan ekosistem Big Data customer-facing dengan pipeline asinkron penuh), BugTrack MFG adalah **aplikasi produksi nyata** untuk staf internal, dengan kebutuhan **real-time** — laporan bug harus segera terlihat oleh mekanik tanpa delay antrean job, namun **tetap mempertahankan proses analisis AI/NLP** (sentiment, spam detection, rekomendasi severity, kategorisasi penyebab kerusakan) yang menjadi ciri khas arsitektur sebelumnya.

---

## 🛠️ Tech Stack & Strategi Arsitektur

* **Web Framework:** Laravel (Ingestion Gateway, Dashboard Presenter, & Role-based Access).
* **Database:** MySQL/MariaDB (kompatibel dengan sumber data asli `mfg_record`).
* **Analytics Engine:** Mikroservis Python (FastAPI) + pendekatan rule-based/keyword (sentiment, spam, severity recommendation, damage categorization) — dipanggil **SECARA SINKRON** dari Laravel, **BUKAN** via Queue/Job asinkron.
* **TIDAK ADA** Redis/Queue Worker — ini adalah perbedaan arsitektur paling mendasar dari SmartReport. Semua pemanggilan ke Analytics Engine terjadi dalam siklus request-response yang sama, karena mekanik membutuhkan visibilitas bug secara langsung/real-time.

---

## 🧩 Konsep Domain Utama

### Tiga Role Pengguna
1. **Reporter** — siapa saja yang menemukan & melaporkan bug (operator produksi, QA, dsb). Submit laporan baru, isi deskripsi, lampiran, lihat riwayat laporannya sendiri, dan menerima **feedback** dari mekanik.
2. **Mekanik** — menangani bug yang berstatus `OPEN` (queue kerja), mengisi `root_cause` dan `repair_action`, menandai `is_rework` jika ini perbaikan ulang, mengubah status menjadi `CLOSED`, dan dapat mengirim **feedback** langsung ke akun reporter terkait satu bug spesifik.
3. **Admin** — memantau dashboard analitik penuh (tanpa menangani bug secara langsung), mengelola data master (Project, Serial Number, Device).

### Dua Tahap Analisis AI (Keduanya SINKRON, Tanpa Job)

**Tahap 1 — Saat Bug Disubmit (oleh Reporter):**
Dari kolom `description`, Analytics Engine menghasilkan:
- `sentiment_label` (positive/neutral/negative/**spam**) & `sentiment_score`
- `is_spam` & `spam_reason` (deteksi laporan asal-asalan/tidak substantif dari staf internal)
- `severity_recommended` & `severity_recommendation_reason` — **REKOMENDASI AI yang TERPISAH** dari `severity` asli yang diisi manual oleh reporter. **Tidak pernah saling override** — keduanya disimpan dan ditampilkan berdampingan, agar admin/mekanik bisa membandingkan penilaian manusia vs AI.

**Tahap 2 — Saat Bug Ditutup (oleh Mekanik):**
Dari kolom `root_cause` + `repair_action`, Analytics Engine menghasilkan:
- `damage_category` — kategori penyebab kerusakan (contoh: "Overheat/Panas Berlebih", "Korosi/Kelembapan", "Hubungan Pendek/Short Circuit", "Kesalahan Pemasangan", "Kualitas Komponen", "Lain-lain"). Ini menjadi basis untuk fitur dashboard **"Penyebab Kerusakan Paling Sering Terjadi"**.

### Fitur Feedback Mekanik → Reporter
Mekanik dapat mengirim pesan bebas terkait satu bug spesifik, yang langsung masuk ke "kotak pesan"/inbox milik akun reporter terkait (tabel `bug_feedback`, dengan status `is_read`). Ini BUKAN bagian dari kolom `repair_action` (yang sifatnya dokumentasi teknis), melainkan komunikasi langsung yang ditujukan untuk dibaca reporter.

---

## 📐 Skema Database (Ringkasan)

```
users              : id, name, email, password, role (reporter|mekanik|admin)
projects           : id, name                              -- tabel master MINIMAL
devices            : id, name                               -- tabel master MINIMAL
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

**Catatan penting:** `projects`, `devices`, dan `serial_numbers` adalah tabel master **MINIMAL/placeholder** — struktur tabel asli di database `mfg_record` tidak diketahui sepenuhnya (hanya foreign key `idproject`, `id_sn`, `iddevice` yang terlihat dari tabel `bug`). Data lama diimpor dengan nama generik ("Project #27", dst) dan **PERLU diedit manual** setelah data master asli didapat dari tempat magang.

`reported_by` dan `fixed_by` adalah **foreign key relasional** ke tabel `users` (BUKAN string bebas seperti pada data asli `bugcreatedby`/`bugfixby`) — akun dibuat untuk setiap nama unik yang ditemukan di data lama (Admin, Fioni Agriyani, Maneng, manufacture, program, dan nilai anomali "1").

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
Response ke Mekanik SELESAI -- bug CLOSED dengan kategori penyebab
sudah tercatat untuk dashboard analitik Admin
```

---

## 📊 Dashboard Analitik Admin (Mirroring SmartReport, Diadaptasi)

Semua elemen dashboard yang ada di SmartReport dipertahankan dengan adaptasi konteks:
- Summary cards: Total Bug, Open, Closed, Critical (belum closed), Rework Rate (%)
- **Distribusi Sentimen** (Positive/Neutral/Negative/**Spam**) — DIPERTAHANKAN dari SmartReport meski pelapornya staf internal, karena staf internal juga berpotensi membuat laporan asal-asalan/tidak substantif.
- **Project Paling Banyak Bug** (Top 5) — analog dari "Produk Paling Bermasalah".
- Tren Volume Laporan (harian/mingguan).
- Tabel Audit Bug (filter status/severity/project/tanggal, pagination, export CSV).
- **BARU — Analytics Penyebab Kerusakan**: distribusi `damage_category`, menunjukkan penyebab apa yang paling sering menyebabkan kerusakan berdasarkan analisis `root_cause`/`repair_action` yang sudah ditutup mekanik.

---

## 📁 Struktur Repositori & Konvensi Kode (Laravel)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── BugController.php          <- Submit (reporter), Queue & Close (mekanik)
│   │   ├── DashboardController.php    <- Analitik (admin)
│   │   ├── ProjectController.php      <- Kelola master project (admin)
│   │   ├── SerialNumberController.php <- Kelola master SN (admin)
│   │   └── BugFeedbackController.php  <- Kotak pesan mekanik -> reporter
│   └── Middleware/                    <- Role-based access (reporter|mekanik|admin)
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
│   ├── severity_recommendation.py <- BARU: usulkan severity dari description
│   └── damage_categorization.py   <- BARU: kategorikan penyebab dari root_cause+repair_action
└── requirements.txt
```

---

## ⚠️ Keputusan Desain Penting (Jangan Diubah Tanpa Diskusi Ulang)

1. **TIDAK ADA Queue/Job/Redis.** Semua pemanggilan ke Python Analytics Service bersifat SINKRON dalam siklus request yang sama. Ini adalah keputusan SENGAJA karena mekanik membutuhkan visibilitas bug secara real-time, bukan demi kesederhanaan teknis semata.
2. **Severity manual TIDAK PERNAH di-override AI.** `severity` (diisi reporter) dan `severity_recommended` (hasil AI) adalah DUA KOLOM TERPISAH yang keduanya disimpan dan ditampilkan. AI hanya memberi rekomendasi, keputusan akhir tetap di tangan manusia (mekanik/admin).
3. **Konsep "Spam" dipertahankan** meski pelapor adalah staf internal, bukan customer publik — karena staf internal juga bisa membuat laporan tidak substantif/asal-asalan.
4. **Feedback mekanik→reporter terpisah dari `repair_action`.** `repair_action` adalah dokumentasi teknis perbaikan; `bug_feedback` adalah komunikasi personal yang ditujukan untuk dibaca reporter terkait.
5. **Tabel master (`projects`, `devices`, `serial_numbers`) bersifat sementara/placeholder** sampai struktur asli dari database `mfg_record` di tempat magang berhasil didapatkan.

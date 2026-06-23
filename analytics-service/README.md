# BugTrack MFG - Analytics AI Service

Microservice Python berbasis FastAPI untuk memproses analisis teks pada laporan bug manufaktur secara sinkron.

## Fitur Utama
1. **Analisis Sentimen**: Memetakan kata-kata teknis manufaktur ke sentimen laporan (Positive, Neutral, Negative, atau Spam).
2. **Deteksi Spam**: Mendeteksi jika laporan bug dibuat asal-asalan oleh staf internal (teks terlalu pendek, kata placeholder seperti 'test'/'asdf', atau pola huruf berulang).
3. **Rekomendasi Severity**: Memberi anjuran tingkat keparahan bug (Critical, Major, Minor) beserta penjelasannya berdasarkan kata kunci dalam deskripsi.
4. **Kategorisasi Penyebab Kerusakan**: Mengelompokkan jenis cacat produksi berdasarkan teks *root cause* dan *repair action* (contoh: "Overheat/Panas Berlebih", "Korosi/Kelembapan", "Hubungan Pendek/Short Circuit", dll).

## Persyaratan
- Python 3.8+
- FastAPI, Uvicorn, Pydantic

## Cara Menjalankan
1. Masuk ke direktori `analytics-service`.
2. Instal dependensi:
   ```bash
   pip install -r requirements.txt
   ```
3. Jalankan service:
   ```bash
   python main.py
   ```
   Service akan berjalan di `http://127.0.0.1:8000`.

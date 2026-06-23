@extends('layouts.app')

@section('title', 'Laporkan Bug Baru')

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('bugs.index') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.75rem;">
        <i class="bi bi-arrow-left"></i> Kembali ke Daftar
    </a>
    <h1 style="font-size: 2rem; font-weight: 800;">Laporkan Bug Baru</h1>
    <p style="color: var(--text-secondary);">Kirim laporan kegagalan/cacat produksi untuk dianalisis AI dan ditangani mekanik</p>
</div>

<div class="card" style="max-width: 800px;">
    <form action="{{ route('bugs.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="form-group">
            <label for="title" class="form-label">Judul Masalah / Bug Title *</label>
            <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: DCDC short hubung singkat pada unit TACA" value="{{ old('title') }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="project_id" class="form-label">Project / Kode Proyek</label>
                <select id="project_id" name="project_id">
                    <option value="">-- Pilih Project --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" {{ old('project_id') == $project->id ? 'selected' : '' }}>
                            {{ $project->name }} (ID: {{ $project->id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="severity" class="form-label">Severity Asli (Pilihan Reporter) *</label>
                <select id="severity" name="severity" required>
                    <option value="Minor" {{ old('severity') === 'Minor' ? 'selected' : '' }}>Minor (Kosmetik / Kerusakan Ringan)</option>
                    <option value="Major" {{ old('severity') === 'Major' || !old('severity') ? 'selected' : '' }}>Major (Fungsional Terganggu)</option>
                    <option value="Critical" {{ old('severity') === 'Critical' ? 'selected' : '' }}>Critical (Meledak, Terbakar, Hubung Singkat)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="serial_number_id" class="form-label">Serial Number Alat / ID SN</label>
                <select id="serial_number_id" name="serial_number_id">
                    <option value="">-- Pilih Serial Number --</option>
                    @foreach($serialNumbers as $sn)
                        <option value="{{ $sn->id }}" {{ old('serial_number_id') == $sn->id ? 'selected' : '' }}>
                            {{ $sn->sn_code }} [{{ strtoupper($sn->type) }}] (Project: #{{ $sn->project_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="reporter_type" class="form-label">Tipe Pelapor *</label>
                <select id="reporter_type" name="reporter_type" required>
                    <option value="produk" {{ old('reporter_type') === 'produk' ? 'selected' : '' }}>Produk (Unit Utama)</option>
                    <option value="sub" {{ old('reporter_type') === 'sub' ? 'selected' : '' }}>Sub (Komponen / Modul Pendukung)</option>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label for="product_version" class="form-label">Versi Produk / Firmware / Software</label>
                <input type="text" id="product_version" name="product_version" class="form-control" placeholder="Contoh: v.20260224" value="{{ old('product_version') }}">
            </div>

            <div class="form-group">
                <label for="environment" class="form-label">Kondisi Lingkungan Penggunaan / Envi</label>
                <input type="text" id="environment" name="environment" class="form-control" placeholder="Contoh: posko pos, suhu panas, berdebu" value="{{ old('environment') }}">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Masalah / Detail Cacat *</label>
            <textarea id="description" name="description" rows="4" placeholder="Jelaskan secara detail kegagalan komponen, kondisi fisik, atau indikator error yang menyala..." required>{{ old('description') }}</textarea>
            <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                * Deskripsi ini akan dianalisis secara sinkron oleh AI Engine untuk sentiment check, spam detection, dan anjuran severity.
            </small>
        </div>

        <div class="form-group">
            <label for="reproduce_steps" class="form-label">Langkah Menimbulkan Masalah / Steps to Reproduce</label>
            <textarea id="reproduce_steps" name="reproduce_steps" rows="3" placeholder="1. Nyalakan modul&#10;2. Hubungkan ke catu daya 12V&#10;3. Tunggu 5 menit..." >{{ old('reproduce_steps') }}</textarea>
        </div>

        <div class="form-group">
            <label for="expected_result" class="form-label">Hasil yang Diharapkan / Expected Result</label>
            <textarea id="expected_result" name="expected_result" rows="2" placeholder="Komponen berjalan normal tanpa mengeluarkan panas berlebih atau indikator led menyala hijau..." >{{ old('expected_result') }}</textarea>
        </div>

        <div class="form-group">
            <label for="attachment" class="form-label">Lampiran Media (Foto / Video Kerusakan / Log File)</label>
            <input type="file" id="attachment" name="attachment" class="form-control" accept="image/*,video/*,.txt,.log,.pdf">
            <small style="color: var(--text-muted); font-size: 0.8rem; margin-top: 0.25rem; display: block;">
                Ukuran file maksimal: 20MB. Format yang didukung: JPEG, PNG, MP4, TXT, LOG, PDF.
            </small>
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send-fill"></i> Kirim Laporan & Jalankan AI
            </button>
            <a href="{{ route('bugs.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Tutup Laporan Bug #' . $bug->id)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('bugs.show', $bug) }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.75rem;">
        <i class="bi bi-arrow-left"></i> Batal & Kembali
    </a>
    <h1 style="font-size: 2rem; font-weight: 800;">Tangani & Selesaikan Bug #{{ $bug->id }}</h1>
    <p style="color: var(--text-secondary);">Isi hasil audit perbaikan fisik/software untuk dianalisis oleh AI Engine</p>
</div>

<div class="content-grid">
    <div>
        <div class="card">
            <form action="{{ route('bugs.close', $bug) }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label for="root_cause" class="form-label">Penyebab Utama (Root Cause) *</label>
                    <textarea id="root_cause" name="root_cause" rows="4" placeholder="Jelaskan apa yang menyebabkan komponen ini rusak (contoh: panas berlebih karena kipas mati, korosi kelembapan posko, solderan retak di pad C5)..." required>{{ old('root_cause') }}</textarea>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        Tulis detail temuan kerusakan fisik secara objektif.
                    </small>
                </div>

                <div class="form-group">
                    <label for="repair_action" class="form-label">Tindakan Perbaikan (Repair Action) *</label>
                    <textarea id="repair_action" name="repair_action" rows="4" placeholder="Jelaskan tindakan perbaikan yang telah dilakukan (contoh: resoldering pad retak, ganti modul kapasitor 10uF, lapisi resin anti lembap)..." required>{{ old('repair_action') }}</textarea>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        Tindakan teknis yang dilakukan untuk memperbaiki dan mencegah bug muncul kembali.
                    </small>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 2rem; background-color: rgba(239, 68, 68, 0.05); padding: 0.75rem 1rem; border-radius: 0.5rem; border: 1px solid rgba(239, 68, 68, 0.1);">
                    <input type="checkbox" id="is_rework" name="is_rework" value="1" style="width: auto;">
                    <label for="is_rework" class="form-label" style="margin-bottom: 0; cursor: pointer; color: var(--color-critical); font-weight: 600;">
                        Tandai sebagai Rework (Perbaikan Ulang)
                    </label>
                </div>

                <div class="form-group" style="border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
                    <label for="feedback_message" class="form-label">Kirim Feedback Langsung ke Reporter (Opsional)</label>
                    <textarea id="feedback_message" name="feedback_message" rows="2" placeholder="Tulis pesan untuk dibaca oleh reporter (contoh: pastikan menutup unit dengan rapat agar air tidak masuk)...">{{ old('feedback_message') }}</textarea>
                    <small style="color: var(--text-muted); font-size: 0.8rem;">
                        Pesan ini akan langsung masuk ke kotak pesan/inbox komunikasi reporter untuk bug ini.
                    </small>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669)">
                        <i class="bi bi-shield-lock-fill"></i> Simpan & Tutup Bug (Jalankan AI)
                    </button>
                    <a href="{{ route('bugs.show', $bug) }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <div>
        <div class="card" style="border-top: 4px solid var(--color-major);">
            <h2 class="card-title"><i class="bi bi-info-circle-fill" style="color: var(--color-major)"></i> Detail Laporan Bug</h2>
            <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
                <div>
                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">JUDUL BUG</span>
                    <strong>{{ $bug->title }}</strong>
                </div>
                <div>
                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">SEVERITY LAPORAN</span>
                    @if($bug->severity === 'Critical')
                        <span class="badge badge-critical">{{ $bug->severity }}</span>
                    @elseif($bug->severity === 'Major')
                        <span class="badge badge-major">{{ $bug->severity }}</span>
                    @else
                        <span class="badge badge-minor">{{ $bug->severity }}</span>
                    @endif
                </div>
                <div>
                    <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">DESKRIPSI CACAT</span>
                    <div style="background-color: rgba(0,0,0,0.15); padding: 0.75rem; border-radius: 0.375rem; font-size: 0.85rem; margin-top: 0.25rem;">
                        {{ $bug->description }}
                    </div>
                </div>
                @if($bug->reproduce_steps)
                    <div>
                        <span style="color: var(--text-secondary); font-size: 0.8rem; display: block;">REPRODUCE STEPS</span>
                        <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ $bug->reproduce_steps }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

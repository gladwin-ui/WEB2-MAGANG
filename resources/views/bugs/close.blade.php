@extends('layouts.app')

@section('title', 'Tutup Laporan Bug #' . $bug->id)

@section('content')
<div class="space-y-6">
    <!-- Back link -->
    <div>
        <a href="{{ route('bugs.show', $bug) }}" class="text-xs text-slate-400 hover:text-white font-mono tracking-wider uppercase inline-flex items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> KEMBALI KE DETAIL
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-100 tracking-tight uppercase">Selesaikan & Tutup Tiket #{{ $bug->id }}</h1>
        <p class="text-xs text-slate-400 font-mono tracking-wider uppercase">LOG TEMUAN KERUSAKAN FISIK & TINDAKAN REPAIR PADA FLOORS PRODUKSI PT HARIFF</p>
    </div>

    <!-- Content Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Form block (Left 2 columns wide) -->
        <div class="lg:col-span-2">
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <form action="{{ route('bugs.close', $bug) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="root_cause" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Temuan Penyebab Utama (Root Cause) *</label>
                        <textarea id="root_cause" name="root_cause" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Jelaskan pemicu kerusakan fisik/software (contoh: pin kapasitor C4 mengalami hubung singkat karena tegangan induksi, modul terlalu panas karena kipas radiator tersumbat dust)..." required>{{ old('root_cause') }}</textarea>
                        <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">Tulis laporan analisis kerusakan fisik secara objektif</small>
                    </div>

                    <div>
                        <label for="repair_action" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Tindakan Perbaikan (Repair Action) *</label>
                        <textarea id="repair_action" name="repair_action" rows="4" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2.5 text-slate-200 text-sm focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Langkah perbaikan yang diambil (contoh: resoldering pad PCB, mengganti modul kapasitor 10uF 25V, membersihkan saringan radiator)..." required>{{ old('repair_action') }}</textarea>
                        <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">Log tindakan teknis penyelesaian masalah</small>
                    </div>

                    <div class="flex items-center gap-2 p-3 bg-rose-500/5 border border-rose-500/10 rounded-lg">
                        <input type="checkbox" id="is_rework" name="is_rework" value="1" class="rounded bg-slate-950 border-slate-850 text-indigo-650 focus:ring-0 focus:ring-offset-0">
                        <label for="is_rework" class="text-xs text-rose-450 font-mono tracking-wider cursor-pointer uppercase select-none font-bold">
                            TANDAI SEBAGAI REWORK (UNIT GAGAL BERULANG)
                        </label>
                    </div>

                    <div class="pt-6 border-t border-slate-800/80">
                        <label for="feedback_message" class="block text-xs font-mono tracking-wider text-slate-450 uppercase mb-2">Kirim Pesan Klarifikasi ke Reporter (Opsional)</label>
                        <textarea id="feedback_message" name="feedback_message" rows="2" class="w-full bg-slate-950 border border-slate-850 rounded-lg px-4 py-2 text-slate-200 text-xs focus:outline-none focus:border-indigo-500 transition-all placeholder-slate-800" placeholder="Contoh: Pastikan casing ditutup dengan sekrup kencang agar air tidak berembun kembali..."></textarea>
                        <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">Pesan ini akan otomatis masuk ke inbox reporter terkait tiket ini</small>
                    </div>

                    <div class="flex gap-3 pt-4 border-t border-slate-800">
                        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-emerald-500/10 transition-all transform active:scale-[0.98]">
                            <i class="bi bi-shield-lock-fill"></i> SELESAIKAN & JALANKAN AI STAGE 2
                        </button>
                        <a href="{{ route('bugs.show', $bug) }}" class="flex items-center gap-2 px-5 py-2.5 bg-slate-950 border border-slate-850 hover:bg-slate-900 text-slate-350 hover:text-slate-200 rounded-lg text-sm font-semibold transition-all">
                            BATAL
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ticket Snapshot (Right Column) -->
        <div class="space-y-6">
            <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-500 uppercase mb-4">Ringkasan Laporan Bug</h2>
                
                <div class="space-y-4 text-xs font-mono leading-relaxed">
                    <div>
                        <span class="text-slate-500 block">JUDUL BUG:</span>
                        <strong class="text-slate-200 text-sm font-sans font-bold leading-tight">{{ $bug->title }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">DERAJAT KEPARAHAN:</span>
                        @if($bug->severity === 'Critical')
                            <span class="inline-flex text-[9px] font-bold bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                        @elseif($bug->severity === 'Major')
                            <span class="inline-flex text-[9px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded uppercase">MAJOR</span>
                        @else
                            <span class="inline-flex text-[9px] font-bold bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded uppercase">MINOR</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block">SERIAL NUMBER:</span>
                        <strong class="text-slate-200 font-mono">{{ $bug->sn_code_snapshot ?? '-' }}</strong>
                    </div>
                    <div class="border-t border-slate-800/80 pt-3">
                        <span class="text-slate-500 block">DESKRIPSI:</span>
                        <p class="text-slate-400 font-sans mt-1 leading-normal">{{ $bug->description }}</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

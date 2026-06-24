@extends('layouts.app')

@section('title', 'Tutup Laporan Bug #' . $bug->id)

@section('content')
<div class="space-y-6">
    <!-- Back link -->
    <div>
        <a href="{{ route('bugs.show', $bug) }}" class="text-xs text-slate-500 hover:text-blue-600 font-mono tracking-wider uppercase inline-flex items-center gap-1 mb-2">
            <i class="bi bi-arrow-left"></i> KEMBALI KE DETAIL
        </a>
    </div>

    <!-- Header -->
    <div>
        <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Selesaikan & Tutup Tiket #{{ $bug->id }}</h1>
        <p class="text-xs text-slate-500 font-mono tracking-wider uppercase">LOG TEMUAN KERUSAKAN FISIK & TINDAKAN REPAIR PADA FLOORS PRODUKSI PT HARIFF</p>
    </div>

    <!-- Content Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Form block (Left 2 columns wide) -->
        <div class="lg:col-span-2">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <form action="{{ route('bugs.close', $bug) }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div>
                        <label for="root_cause" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-2">Temuan Penyebab Utama (Root Cause) *</label>
                        <textarea id="root_cause" name="root_cause" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Jelaskan pemicu kerusakan fisik/software (contoh: pin kapasitor C4 mengalami hubung singkat karena tegangan induksi, modul terlalu panas karena kipas radiator tersumbat dust)..." required>{{ old('root_cause') }}</textarea>
                        <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">Tulis laporan analisis kerusakan fisik secara objektif</small>
                    </div>

                    <div>
                        <label for="repair_action" class="block text-xs font-mono tracking-wider text-slate-600 uppercase mb-2">Tindakan Perbaikan (Repair Action) *</label>
                        <textarea id="repair_action" name="repair_action" rows="4" class="w-full bg-white border border-slate-200 rounded-lg px-4 py-2.5 text-slate-800 text-sm focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Langkah perbaikan yang diambil (contoh: resoldering pad PCB, mengganti modul kapasitor 10uF 25V, membersihkan saringan radiator)..." required>{{ old('repair_action') }}</textarea>
                        <small class="block text-[10px] text-slate-500 font-mono tracking-wide mt-1.5 uppercase">Log tindakan teknis penyelesaian masalah</small>
                    </div>

                    <div class="flex items-center gap-2 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <input type="checkbox" id="is_rework" name="is_rework" value="1" class="rounded bg-white border-slate-300 text-blue-600 focus:ring-0 focus:ring-offset-0">
                        <label for="is_rework" class="text-xs text-red-700 font-mono tracking-wider cursor-pointer uppercase select-none font-bold">
                            TANDAI SEBAGAI REWORK (UNIT GAGAL BERULANG)
                        </label>
                    </div>



                    <div class="flex gap-3 pt-4 border-t border-slate-200">
                        <button type="submit" class="flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white rounded-lg text-sm font-bold shadow-sm transition-all transform active:scale-[0.98]">
                            <i class="bi bi-shield-lock-fill"></i> SELESAIKAN & JALANKAN AI STAGE 2
                        </button>
                        <a href="{{ route('bugs.show', $bug) }}" class="flex items-center gap-2 px-5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-sm font-semibold transition-all shadow-sm">
                            BATAL
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Ticket Snapshot (Right Column) -->
        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-xs font-mono tracking-widest text-slate-600 uppercase mb-4">Ringkasan Laporan Bug</h2>
                
                <div class="space-y-4 text-xs font-mono leading-relaxed">
                    <div>
                        <span class="text-slate-500 block">JUDUL BUG:</span>
                        <strong class="text-slate-800 text-sm font-sans font-bold leading-tight">{{ $bug->title }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block">DERAJAT KEPARAHAN:</span>
                        @if($bug->severity === 'Critical')
                            <span class="inline-flex text-[9px] font-bold bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                        @elseif($bug->severity === 'Major')
                            <span class="inline-flex text-[9px] font-bold bg-yellow-50 text-yellow-750 border border-yellow-250 px-2 py-0.5 rounded uppercase">MAJOR</span>
                        @else
                            <span class="inline-flex text-[9px] font-bold bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">MINOR</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-slate-500 block">SERIAL NUMBER:</span>
                        <strong class="text-slate-800 font-mono">{{ $bug->sn_code_snapshot ?? '-' }}</strong>
                    </div>
                    <div class="border-t border-slate-200 pt-3">
                        <span class="text-slate-500 block">DESKRIPSI:</span>
                        <p class="text-slate-700 font-sans mt-1 leading-normal">{{ $bug->description }}</p>
                    </div>
                </div>
            </div>
        </div>

     </div>
</div>
@endsection

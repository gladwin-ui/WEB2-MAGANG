@extends('layouts.app')

@section('title', 'Riwayat Obrolan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="border-b border-slate-200 pb-4">
        <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Riwayat Obrolan</h1>
        <p class="text-xs text-slate-500 font-mono tracking-wider uppercase">Daftar percakapan koordinasi perbaikan defect modul produksi</p>
    </div>

    <!-- Chat Threads List -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden max-w-4xl">
        @if($bugs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                <i class="bi bi-chat-left-dots text-5xl mb-4 text-slate-300"></i>
                <p class="text-sm font-mono uppercase tracking-wider mb-2">Belum ada riwayat percakapan</p>
                <p class="text-xs text-slate-400 text-center">Pesan obrolan hanya terbuka setelah laporan bug Anda diambil oleh mekanik.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($bugs as $bug)
                    <div class="p-5 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 hover:bg-slate-50/50 transition-all">
                        <div class="flex-1 min-w-0 space-y-2">
                            <!-- Title & Status -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-xs font-mono font-bold text-blue-600">#{{ $bug->id }}</span>
                                <h3 class="text-sm font-bold text-slate-800 truncate max-w-md font-sans">
                                    {{ $bug->title }}
                                </h3>
                                @if($bug->status === 'OPEN')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.2 rounded uppercase">TERBUKA</span>
                                @else
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.2 rounded uppercase">DITUTUP</span>
                                @endif
                                <span class="text-[10px] font-mono text-slate-400">({{ $bug->project?->name ?? 'Project #' . $bug->project_id }})</span>
                            </div>

                            <!-- User Info -->
                            <div class="text-xs text-slate-500 font-mono">
                                @if(Auth::user()->role === 'reporter')
                                    Mekanik: <strong class="text-slate-700">{{ $bug->assignee->name }}</strong>
                                @else
                                    Reporter: <strong class="text-slate-700">{{ $bug->reporter->name }}</strong>
                                @endif
                            </div>

                            <!-- Last Message Preview -->
                            <div class="flex items-start gap-2 bg-slate-50 border border-slate-100 p-2.5 rounded-lg text-xs text-slate-650 max-w-2xl">
                                <i class="bi bi-chat-text text-slate-400 mt-0.5"></i>
                                <div class="truncate">
                                    @if($bug->latestChat)
                                        <strong class="text-slate-700">{{ $bug->latestChat->sender->name }}:</strong> 
                                        <span>{{ $bug->latestChat->message }}</span>
                                        <span class="block text-[10px] text-slate-400 mt-1 font-mono">{{ $bug->latestChat->created_at->format('d M Y, H:i') }}</span>
                                    @else
                                        <span class="text-slate-400 italic">Belum ada pesan terkirim. Klik tombol untuk memulai percakapan.</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Button -->
                        <div class="shrink-0">
                            <a href="{{ route('bugs.chat.show', $bug) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold shadow-sm transition-all active:scale-[0.98]">
                                <i class="bi bi-chat-dots-fill"></i> BUKA OBROLAN
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

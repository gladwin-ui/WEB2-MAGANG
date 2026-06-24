@extends('layouts.app')

@section('title', 'Riwayat Laporanku')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-100 tracking-tight">RIWAYAT LAPORANKU</h1>
            <p class="text-xs text-slate-400 font-mono tracking-wider uppercase">DAFTAR KERUSAKAN PRODUKSI YANG ANDA PILOTI</p>
        </div>
        <a href="{{ route('bugs.create') }}" class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-indigo-500/10 transition-all transform active:scale-[0.98]">
            <i class="bi bi-plus-circle-fill"></i> LAPORKAN BUG BARU
        </a>
    </div>

    <!-- Data Card -->
    <div class="bg-slate-900/40 border border-slate-800 rounded-xl p-6 shadow-xl backdrop-blur-sm">
        @if($bugs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-slate-500">
                <i class="bi bi-folder-x text-5xl mb-4 text-slate-600"></i>
                <p class="text-sm font-mono uppercase tracking-wider mb-4">Belum ada laporan kerusakan yang dikirim</p>
                <a href="{{ route('bugs.create') }}" class="px-4 py-2 bg-slate-950 border border-slate-850 hover:bg-slate-900 text-slate-350 hover:text-slate-200 rounded-lg text-xs font-semibold font-mono tracking-wider uppercase transition-all">
                    KIRIM LAPORAN PERTAMA
                </a>
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-800 text-[10px] font-mono tracking-widest text-slate-500 uppercase">
                            <th class="py-3 px-4">ID TIKET</th>
                            <th class="py-3 px-4">JUDUL BUG</th>
                            <th class="py-3 px-4">PROJECT</th>
                            <th class="py-3 px-4">SEVERITY</th>
                            <th class="py-3 px-4">SERIAL NUMBER</th>
                            <th class="py-3 px-4">STATUS</th>
                            <th class="py-3 px-4">TANGGAL LAPOR</th>
                            <th class="py-3 px-4">AI SPAM CHECK</th>
                            <th class="py-3 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/50 text-sm">
                        @foreach($bugs as $bug)
                            @php
                                $unread = $bug->feedbacks->where('to_user_id', auth()->id())->where('is_read', false)->count();
                            @endphp
                            <tr class="hover:bg-slate-900/20 transition-all">
                                <td class="py-3.5 px-4 font-mono text-xs text-indigo-400 font-bold flex items-center">
                                    #{{ $bug->id }}
                                    @if($unread > 0)
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-neon-pink shadow-neon-pink ml-1.5 animate-pulse" title="Ada feedback baru"></span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-200">{{ $bug->title }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-400">{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->severity === 'Critical')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-red-500/10 text-red-400 border border-red-500/20 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                                    @elseif($bug->severity === 'Major')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded uppercase">MAJOR</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded uppercase">MINOR</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-400">{{ $bug->sn_code_snapshot ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->status === 'OPEN')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-amber-500/10 text-amber-400 border border-amber-500/20 px-2 py-0.5 rounded uppercase">OPEN</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 px-2 py-0.5 rounded uppercase">CLOSED</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ $bug->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->sentiment_label === 'spam' || $bug->is_spam)
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-red-500/15 text-red-400 border border-red-500/30 px-2 py-0.5 rounded uppercase" title="Reason: {{ $bug->spam_reason }}">SPAM WARNING</span>
                                    @elseif($bug->sentiment_label)
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-emerald-500/10 text-emerald-450 border border-emerald-500/20 px-2 py-0.5 rounded uppercase">PASSED</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-mono bg-slate-950 text-slate-500 border border-slate-850 px-2 py-0.5 rounded uppercase">UNANALYZED</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <a href="{{ route('bugs.show', $bug) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-950 hover:bg-slate-900 border border-slate-800 text-slate-350 hover:text-slate-200 rounded text-xs font-mono font-bold transition-all relative">
                                        <i class="bi bi-eye"></i> DETAIL
                                        @if($unread > 0)
                                            <span class="absolute -top-1 -right-1 flex h-2 w-2">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-neon-pink opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-2 w-2 bg-neon-pink"></span>
                                            </span>
                                        @endif
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 border-t border-slate-800 pt-4">
                {{ $bugs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

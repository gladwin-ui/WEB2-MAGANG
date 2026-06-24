@extends('layouts.app')

@section('title', 'Riwayat Laporan')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight">RIWAYAT LAPORAN</h1>
            <p class="text-xs text-slate-500 font-mono tracking-wider uppercase">DAFTAR KERUSAKAN PRODUKSI</p>
        </div>
        <a href="{{ route('bugs.create') }}" class="flex items-center gap-2 px-4 py-2.5 btn-premium-gradient rounded-lg text-sm font-bold shadow-sm transition-all active:scale-[0.98]">
            <i class="bi bi-plus-circle-fill"></i> LAPORKAN BUG BARU
        </a>
    </div>

    <!-- Data Card -->
    <div class="premium-card premium-card-accent p-6">
        @if($bugs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                <i class="bi bi-folder-x text-5xl mb-4 text-slate-300"></i>
                <p class="text-sm font-mono uppercase tracking-wider mb-4">Belum ada laporan kerusakan yang dikirim</p>
                <a href="{{ route('bugs.create') }}" class="px-4 py-2 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-semibold font-mono tracking-wider uppercase transition-all">
                    KIRIM LAPORAN PERTAMA
                </a>
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-slate-50">
                            <th class="py-3 px-4">ID TIKET</th>
                            <th class="py-3 px-4">JUDUL BUG</th>
                            <th class="py-3 px-4">PROYEK</th>
                            <th class="py-3 px-4">TINGKAT KEPARAHAN</th>
                            <th class="py-3 px-4">NOMOR SERI</th>
                            <th class="py-3 px-4">STATUS</th>
                            <th class="py-3 px-4">TANGGAL LAPOR</th>
                            <th class="py-3 px-4 text-right w-[220px]">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($bugs as $bug)
                            <tr class="hover:bg-slate-50 transition-all">
                                <td class="py-3.5 px-4 font-mono text-xs text-blue-600 font-bold">
                                    #{{ $bug->id }}
                                </td>
                                <td class="py-3.5 px-4 font-bold text-slate-800">{{ $bug->title }}</td>
                                <td class="py-3.5 px-4 font-semibold text-slate-600">{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->severity === 'Critical')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                                    @elseif($bug->severity === 'Major')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-yellow-50 text-yellow-700 border border-yellow-250 px-2 py-0.5 rounded uppercase">MAJOR</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">MINOR</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $bug->sn_code_snapshot ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->status === 'OPEN')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-yellow-50 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded uppercase">OPEN</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">CLOSED</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ $bug->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-3.5 px-4 text-right align-middle">
                                    <div class="inline-flex flex-col sm:flex-row justify-end items-stretch sm:items-center gap-1.5 min-w-[180px]">
                                        <a href="{{ route('bugs.show', $bug) }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-blue-600 hover:text-blue-700 rounded text-xs font-mono font-bold transition-all shadow-sm whitespace-nowrap">
                                            <i class="bi bi-eye"></i> DETAIL
                                        </a>
                                        @if(!is_null($bug->assigned_to))
                                            <a href="{{ route('bugs.chat.show', $bug) }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded text-xs font-mono font-bold transition-all shadow-sm whitespace-nowrap">
                                                <i class="bi bi-chat-dots"></i> OBROLAN MEKANIK
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-6 border-t border-slate-200 pt-4">
                {{ $bugs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Queue Kerja Bug')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 tracking-tight uppercase">Queue Kerja Bug</h1>
            <p class="text-xs text-slate-500 font-mono tracking-wider uppercase">Daftar defect modul produksi PT HARIFF yang butuh tindakan mekanik</p>
        </div>
        
        <!-- Toggle Status Tabs -->
        <div class="bg-slate-100 border border-slate-200 rounded-lg p-1 flex gap-1 shadow-sm shrink-0">
            <a href="{{ route('bugs.index', ['status' => 'OPEN']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ $status === 'OPEN' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-650 hover:text-slate-900' }}">
                <i class="bi bi-clock-history"></i> OPEN QUEUE
            </a>
            <a href="{{ route('bugs.index', ['status' => 'CLOSED']) }}" class="flex items-center gap-2 px-3 py-1.5 rounded-md text-xs font-bold transition-all {{ $status === 'CLOSED' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-650 hover:text-slate-900' }}">
                <i class="bi bi-check-all"></i> CLOSED HISTORY
            </a>
        </div>
    </div>

    <!-- Data Panel -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        @if($bugs->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-slate-400">
                <i class="bi bi-check-circle-fill text-5xl mb-4 text-emerald-200"></i>
                <p class="text-sm font-mono uppercase tracking-wider text-slate-500">Tidak ada laporan dengan status {{ $status }}</p>
            </div>
        @else
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-slate-50">
                            <th class="py-3 px-4">ID TIKET</th>
                            <th class="py-3 px-4">JUDUL BUG</th>
                            <th class="py-3 px-4">PROJECT</th>
                            <th class="py-3 px-4">SEVERITY</th>
                            <th class="py-3 px-4">SERIAL NUMBER</th>
                            <th class="py-3 px-4">REPORTER</th>
                            <th class="py-3 px-4">TANGGAL LAPOR</th>
                            <th class="py-3 px-4">REKOMENDASI SEVERITY AI</th>
                            <th class="py-3 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-sm">
                        @foreach($bugs as $bug)
                            @php
                                $unread = $bug->feedbacks->where('to_user_id', auth()->id())->where('is_read', false)->count();
                            @endphp
                            <tr class="hover:bg-slate-50 transition-all {{ $bug->severity === 'Critical' && $bug->status === 'OPEN' ? 'bg-red-50/30' : '' }}">
                                <td class="py-3.5 px-4 font-mono text-xs text-blue-600 font-bold flex items-center">
                                    #{{ $bug->id }}
                                    @if($unread > 0)
                                        <span class="inline-block w-1.5 h-1.5 rounded-full bg-red-500 ml-1.5 animate-pulse" title="Ada feedback baru"></span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-slate-800">{{ $bug->title }}</span>
                                        @if($bug->is_rework)
                                            <span class="text-[8px] font-extrabold font-mono bg-purple-100 text-purple-700 border border-purple-200 px-1 py-0.2 rounded uppercase">REWORK</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-600">{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</td>
                                <td class="py-3.5 px-4">
                                    @if($bug->severity === 'Critical')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                                    @elseif($bug->severity === 'Major')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-yellow-50 text-yellow-750 border border-yellow-250 px-2 py-0.5 rounded uppercase">MAJOR</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">MINOR</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-600">{{ $bug->sn_code_snapshot ?? '-' }}</td>
                                <td class="py-3.5 px-4 text-slate-700">{{ $bug->reporter?->name ?? 'System' }}</td>
                                <td class="py-3.5 px-4 font-mono text-xs text-slate-500">{{ $bug->created_at->format('d M Y, H:i') }}</td>
                                <td class="py-3.5 px-4 font-mono">
                                    @if($bug->severity_recommended)
                                        @if($bug->severity_recommended === 'Critical')
                                            <span class="text-red-600 text-xs font-bold uppercase" title="{{ $bug->severity_recommendation_reason }}"><i class="bi bi-robot"></i> {{ $bug->severity_recommended }}</span>
                                        @elseif($bug->severity_recommended === 'Major')
                                            <span class="text-yellow-600 text-xs font-bold uppercase" title="{{ $bug->severity_recommendation_reason }}"><i class="bi bi-robot"></i> {{ $bug->severity_recommended }}</span>
                                        @else
                                            <span class="text-green-600 text-xs font-bold uppercase" title="{{ $bug->severity_recommendation_reason }}"><i class="bi bi-robot"></i> {{ $bug->severity_recommended }}</span>
                                        @endif
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="inline-flex gap-1.5">
                                        <a href="{{ route('bugs.show', $bug) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-white hover:bg-slate-50 border border-slate-200 text-blue-600 hover:text-blue-700 rounded text-xs font-mono font-bold transition-all relative shadow-sm">
                                            <i class="bi bi-eye"></i> DETAIL
                                            @if($unread > 0)
                                                <span class="absolute -top-1 -right-1 flex h-2 w-2">
                                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                </span>
                                            @endif
                                        </a>
                                        @if($bug->status === 'OPEN')
                                            <a href="{{ route('bugs.close.form', $bug) }}" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-xs font-mono font-bold transition-all shadow-sm">
                                                <i class="bi bi-hammer"></i> TANGANI
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

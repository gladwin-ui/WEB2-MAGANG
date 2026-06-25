@extends('layouts.app')

@section('title', 'Riwayat Import')

@section('content')
<div class="max-w-4xl mx-auto">

    {{-- Header --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mb-1">Riwayat Import</h1>
            <p class="text-sm text-slate-500">Seluruh riwayat proses import file .sql</p>
        </div>
        <a href="{{ route('import.upload') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
           style="background-color: #2563EB !important; color: #fff !important;">
            <i class="bi bi-cloud-upload"></i> Import Baru
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden">
        @if($jobs->count() === 0)
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <i class="bi bi-inbox text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-500 font-semibold text-sm mb-1">Belum ada riwayat import</p>
                <p class="text-slate-400 text-xs">Upload file .sql pertama Anda untuk memulai.</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs text-slate-500 uppercase tracking-wide font-semibold">
                        <th class="px-6 py-3 text-left">File</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Baris</th>
                        <th class="px-4 py-3 text-right">+Baru</th>
                        <th class="px-4 py-3 text-right">~Diperbarui</th>
                        <th class="px-4 py-3 text-right">Dilewati</th>
                        <th class="px-4 py-3 text-right">-Dihapus</th>
                        <th class="px-4 py-3 text-right">Gagal</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($jobs as $job)
                    <tr class="hover:bg-slate-50 transition-colors">
                        {{-- Filename --}}
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-file-earmark-code text-slate-400"></i>
                                <span class="font-medium text-slate-700 truncate max-w-[180px]" title="{{ $job->filename }}">
                                    {{ $job->filename }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 font-mono pl-5">
                                #{{ $job->id }}
                            </div>
                        </td>

                        {{-- Status badge --}}
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill text-xs font-semibold
                                @if($job->status === 'completed') bg-green-50 text-green-700
                                @elseif($job->status === 'failed') bg-red-50 text-red-700
                                @elseif($job->status === 'processing') bg-blue-50 text-blue-700
                                @else bg-slate-100 text-slate-500
                                @endif">
                                @if($job->status === 'completed')
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                @elseif($job->status === 'failed')
                                    <i class="bi bi-x-circle-fill"></i> Gagal
                                @elseif($job->status === 'processing')
                                    <i class="bi bi-arrow-repeat"></i> Berjalan
                                @else
                                    <i class="bi bi-clock"></i> Menunggu
                                @endif
                            </span>
                        </td>

                        {{-- Numeric columns --}}
                        <td class="px-4 py-3 text-right font-mono text-slate-600 text-xs">
                            {{ number_format($job->total_rows) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-blue-600 font-semibold text-xs">
                            +{{ number_format($job->inserted_count) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-amber-600 font-semibold text-xs">
                            ~{{ number_format($job->updated_count) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-400 text-xs">
                            {{ number_format($job->skipped_count) }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-red-500 text-xs">
                            {{ $job->deleted_count > 0 ? '-' . number_format($job->deleted_count) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono text-red-650 text-xs">
                            {{ $job->failed_count > 0 ? $job->failed_count : '—' }}
                        </td>

                        {{-- Time --}}
                        <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                            <div>{{ $job->created_at->format('d M Y') }}</div>
                            <div class="font-mono">{{ $job->created_at->format('H:i') }}</div>
                        </td>

                        {{-- Action --}}
                        <td class="px-4 py-3">
                            <a href="{{ route('import.progress', $job->id) }}"
                               class="text-xs text-blue-600 hover:text-blue-700 hover:underline font-semibold whitespace-nowrap">
                                Lihat →
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            @if($jobs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $jobs->links() }}
            </div>
            @endif
        @endif
    </div>

</div>
@endsection

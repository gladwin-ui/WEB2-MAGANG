@extends('layouts.app')

@section('title', 'Detail Bug #' . $bug->id)

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('dashboard') }}" class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm text-text-secondary hover:text-accent hover:border-accent transition-all" title="Kembali ke Dashboard">
                <i class="bi bi-arrow-left text-lg"></i>
            </a>
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-bug-fill text-xl text-accent"></i>
            </div>
            <div>
                <div class="flex items-center gap-2.5 flex-wrap">
                    <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Tiket Defect #{{ $bug->id }}</h1>
                    @if($bug->status === 'OPEN')
                        <span class="inline-flex items-center text-[10px] font-bold font-mono bg-red-500/10 text-red-600 border border-red-500/20 px-2.5 py-0.5 rounded-md uppercase">
                            <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> OPEN
                        </span>
                    @else
                        <span class="inline-flex items-center text-[10px] font-bold font-mono bg-emerald-500/10 text-emerald-600 border border-emerald-500/20 px-2.5 py-0.5 rounded-md uppercase">
                            CLOSED // RESOLVED
                        </span>
                    @endif

                    @if($bug->is_rework)
                        <span class="inline-flex items-center text-[10px] font-bold font-mono bg-purple-500/10 text-purple-600 border border-purple-500/20 px-2.5 py-0.5 rounded-md uppercase">REWORK</span>
                    @endif
                </div>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium font-mono">
                    DILAPORKAN OLEH: {{ strtoupper($bug->reported_by ?? 'SYSTEM') }} &middot; TANGGAL: {{ $bug->created_at->format('d M Y, H:i') }}
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-md font-bold text-slate-800 mb-6 border-b border-slate-200 pb-3 uppercase tracking-wide">
                    INFORMASI FISIK & KELENGKAPAN MODUL
                </h2>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-xs">
                    <div>
                        <span class="text-slate-500 block font-mono uppercase mb-0.5">Project</span>
                        <strong class="text-slate-800">{{ $bug->project?->name ?? ($bug->project_id ? 'Project #' . $bug->project_id : 'Tanpa Proyek') }}</strong>

                    </div>
                    <div>
                        <span class="text-slate-500 block font-mono uppercase mb-0.5">Serial Number</span>
                        <strong class="text-slate-800 font-mono">{{ $bug->sn_code_snapshot ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-mono uppercase mb-0.5">Tipe Laporan</span>
                        <strong class="text-slate-800 capitalize">{{ $bug->reporter_type }} level</strong>
                    </div>
                    <div>
                        <span class="text-slate-500 block font-mono uppercase mb-0.5">Versi Produk</span>
                        <strong class="text-slate-800">{{ $bug->product_version ?? '-' }}</strong>
                    </div>
                </div>

                <div class="space-y-4 text-sm">
                    <div>
                        <h3 class="text-xs text-slate-600 font-mono uppercase mb-1">Deskripsi Masalah / Deskripsi Cacat:</h3>
                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed whitespace-pre-line">{{ $bug->description ?? 'Tidak ada deskripsi.' }}</div>
                    </div>

                    @if($bug->reproduce_steps)
                        <div>
                            <h3 class="text-xs text-slate-600 font-mono uppercase mb-1">Langkah Reproduksi:</h3>
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed font-mono text-xs whitespace-pre-line">{{ $bug->reproduce_steps }}</div>
                        </div>
                    @endif

                    @if($bug->expected_result)
                        <div>
                            <h3 class="text-xs text-slate-600 font-mono uppercase mb-1">Ekspektasi Hasil:</h3>
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed whitespace-pre-line">{{ $bug->expected_result }}</div>
                        </div>
                    @endif

                    @if($bug->environment)
                        <div>
                            <h3 class="text-xs text-slate-600 font-mono uppercase mb-0.5">Lingkungan Uji (Environment):</h3>
                            <p class="font-medium text-slate-700">{{ $bug->environment }}</p>
                        </div>
                    @endif

                    @if($bug->attachment_path)
                        <div class="pt-2">
                            <h3 class="text-xs text-slate-650 font-mono uppercase mb-2">Lampiran Media Bukti Fisik:</h3>
                            @php
                                $ext = pathinfo($bug->attachment_path, PATHINFO_EXTENSION);
                                $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isVid = in_array(strtolower($ext), ['mp4', 'webm', 'ogg', 'mov']);
                            @endphp

                            @if($isImg)
                                <img src="{{ asset('storage/' . $bug->attachment_path) }}" alt="Lampiran" class="max-w-full max-h-96 rounded-lg border border-slate-200 shadow-sm">
                            @elseif($isVid)
                                <video controls class="max-w-full max-h-96 rounded-lg border border-slate-200 shadow-sm">
                                    <source src="{{ asset('storage/' . $bug->attachment_path) }}" type="video/{{ $ext === 'mov' ? 'mp4' : $ext }}">
                                    Browser tidak mendukung video player.
                                </video>
                            @else
                                <a href="{{ asset('storage/' . $bug->attachment_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-lg text-xs font-mono font-bold transition-all shadow-sm">
                                    <i class="bi bi-file-earmark-arrow-down-fill"></i> DOWNLOAD ATTACHMENT (.{{ $ext }})
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            @if($bug->status === 'CLOSED')
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm border-l-4 border-l-green-600">
                    <h2 class="text-md font-bold text-green-700 mb-6 flex items-center gap-2 uppercase tracking-wide">
                        <i class="bi bi-check-circle-fill text-green-600"></i> LOG PENYELESAIAN
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-xs">
                        <div>
                            <span class="text-slate-500 block font-mono uppercase mb-0.5">DITANGANI OLEH</span>
                            <strong class="text-slate-800 font-bold text-sm">{{ $bug->fixed_by ?? 'SYSTEM' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-mono uppercase mb-0.5">TANGGAL SELESAI</span>
                            <strong class="text-slate-800 font-mono text-sm">{{ $bug->closed_at ? $bug->closed_at->format('d M Y, H:i') : '-' }}</strong>
                        </div>
                    </div>

                    <div class="space-y-4 text-sm">
                        <div>
                            <h3 class="text-xs text-slate-650 font-mono uppercase mb-1">Temuan Penyebab Utama (Root Cause):</h3>
                            <div class="bg-green-50/50 border border-green-200 p-4 rounded-lg text-green-800 leading-relaxed whitespace-pre-line">{{ $bug->root_cause ?? '-' }}</div>
                        </div>
                        <div>
                            <h3 class="text-xs text-slate-650 font-mono uppercase mb-1">Tindakan Perbaikan yang Diambil (Repair Action):</h3>
                            <div class="bg-green-50/50 border border-green-200 p-4 rounded-lg text-green-800 leading-relaxed whitespace-pre-line">{{ $bug->repair_action ?? '-' }}</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm border-t-4 border-t-blue-600">
                <h2 class="text-md font-bold text-blue-600 mb-6 flex items-center gap-2 uppercase tracking-wide">
                    <i class="bi bi-robot"></i> DIAGNOSIS AI ENGINE
                </h2>

                <div class="space-y-5">
                    <div class="border-b border-slate-200 pb-4">
                        <span class="block text-xs font-mono text-slate-600 uppercase mb-2.5">Audit Derajat Keparahan:</span>
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <span class="block text-[10px] font-mono text-slate-500 uppercase mb-1">Reporter:</span>
                                @if($bug->severity === 'Critical')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                                @elseif($bug->severity === 'Major')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-yellow-50 text-yellow-700 border border-yellow-250 px-2 py-0.5 rounded uppercase">MAJOR</span>
                                @else
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">MINOR</span>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="block text-[10px] font-mono text-slate-500 uppercase mb-1">Rekomendasi AI:</span>
                                @if($bug->severity_recommended)
                                    @if($bug->severity_recommended === 'Critical')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">CRITICAL</span>
                                    @elseif($bug->severity_recommended === 'Major')
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-yellow-50 text-yellow-700 border border-yellow-250 px-2 py-0.5 rounded uppercase">MAJOR</span>
                                    @else
                                        <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">MINOR</span>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-500 font-mono">N/A</span>
                                @endif
                            </div>
                        </div>

                        @if($bug->severity_recommendation_reason)
                            <div class="mt-3 p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-600 leading-relaxed border-l-2 border-blue-600 font-mono">
                                <strong>ALASAN AI:</strong> {{ $bug->severity_recommendation_reason }}
                            </div>
                        @endif
                    </div>

                    <div class="border-b border-slate-200 pb-4">
                        <span class="block text-xs font-mono text-slate-650 uppercase mb-2">Sentimen Teks Deskripsi:</span>
                        @if($bug->sentiment_label)
                            <div class="flex items-center gap-3">
                                @if($bug->sentiment_label === 'positive')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">POSITIF</span>
                                @elseif($bug->sentiment_label === 'negative')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">NEGATIF</span>

                                @else
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded uppercase">NETRAL</span>
                                @endif
                                <span class="text-xs font-mono text-slate-600">Score: <strong>{{ number_format($bug->sentiment_score, 2) }}</strong></span>
                            </div>
                        @else
                            <span class="text-xs font-mono text-slate-400">MENUNGGU ANTRIAN AI</span>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', 'Detail Bug #' . $bug->id)

@section('content')
<div class="space-y-6">
    <!-- Back arrow and Action header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-200 pb-4">
        <div>
            <a href="{{ route('bugs.index') }}" class="text-xs text-slate-500 hover:text-blue-600 font-mono tracking-wider uppercase inline-flex items-center gap-1 mb-2">
                <i class="bi bi-arrow-left"></i> KEMBALI KE QUEUE
            </a>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-black text-slate-800 tracking-tight">TIKET DEFECT #{{ $bug->id }}</h1>
                @if($bug->status === 'OPEN')
                    <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2.5 py-0.5 rounded uppercase">
                        <span class="h-1.5 w-1.5 rounded-full bg-red-500 mr-1.5"></span> OPEN QUEUE
                    </span>
                @else
                    <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2.5 py-0.5 rounded uppercase">
                        CLOSED // RESOLVED
                    </span>
                @endif

                @if($bug->is_rework)
                    <span class="inline-flex text-[9px] font-bold font-mono bg-purple-100 text-purple-700 border border-purple-200 px-2.5 py-0.5 rounded uppercase">REWORK</span>
                @endif
            </div>
            <p class="text-xs text-slate-500 font-mono mt-1">DILAPORKAN OLEH: {{ strtoupper($bug->reporter?->name ?? 'System') }} // TANGGAL: {{ $bug->created_at->format('d M Y, H:i') }}</p>
        </div>

        @if($bug->status === 'OPEN' && (auth()->user()->role === 'mekanik' || auth()->user()->role === 'admin'))
            <a href="{{ route('bugs.close.form', $bug) }}" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm active:scale-[0.98]">
                <i class="bi bi-hammer"></i> SELESAIKAN & TUTUP BUG
            </a>
        @endif
    </div>

    <!-- Grid Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Side: Basic Info & Repair Log (2 Cols wide on desktop) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Technical details card -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-md font-bold text-slate-800 mb-6 border-b border-slate-200 pb-3 uppercase tracking-wide">
                    INFORMASI FISIK & KELENGKAPAN MODUL
                </h2>
                
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-xs">
                    <div>
                        <span class="text-slate-500 block font-mono uppercase mb-0.5">Project</span>
                        <strong class="text-slate-800">{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</strong>
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
                        <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed white-space-pre-line">{{ $bug->description ?? 'Tidak ada deskripsi.' }}</div>
                    </div>

                    @if($bug->reproduce_steps)
                        <div>
                            <h3 class="text-xs text-slate-600 font-mono uppercase mb-1">Langkah Reproduksi:</h3>
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed font-mono text-xs white-space-pre-line">{{ $bug->reproduce_steps }}</div>
                        </div>
                    @endif

                    @if($bug->expected_result)
                        <div>
                            <h3 class="text-xs text-slate-600 font-mono uppercase mb-1">Ekspektasi Hasil:</h3>
                            <div class="bg-slate-50 border border-slate-200 p-4 rounded-lg text-slate-700 leading-relaxed white-space-pre-line">{{ $bug->expected_result }}</div>
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

            <!-- Mechanic Resolution Log -->
            @if($bug->status === 'CLOSED')
                <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm border-l-4 border-l-green-600">
                    <h2 class="text-md font-bold text-green-700 mb-6 flex items-center gap-2 uppercase tracking-wide">
                        <i class="bi bi-check-circle-fill text-green-600"></i> LOG TINDAKAN PERBAIKAN MEKANIK
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 text-xs">
                        <div>
                            <span class="text-slate-500 block font-mono uppercase mb-0.5">DITANGANI OLEH</span>
                            <strong class="text-slate-800 font-bold text-sm">{{ $bug->fixer?->name ?? 'System' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-mono uppercase mb-0.5">TANGGAL SELESAI</span>
                            <strong class="text-slate-800 font-mono text-sm">{{ $bug->closed_at ? $bug->closed_at->format('d M Y, H:i') : '-' }}</strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block font-mono uppercase mb-0.5">AI DAMAGE CATEGORY</span>
                            <strong class="inline-flex mt-0.5 text-[10px] font-bold font-mono bg-blue-55 text-blue-700 border border-blue-200 px-2 py-0.5 rounded uppercase">
                                {{ $bug->damage_category ?? 'Lain-lain' }}
                            </strong>
                        </div>
                    </div>

                    <div class="space-y-4 text-sm">
                        <div>
                            <h3 class="text-xs text-slate-650 font-mono uppercase mb-1">Temuan Penyebab Utama (Root Cause):</h3>
                            <div class="bg-green-50/50 border border-green-200 p-4 rounded-lg text-green-800 leading-relaxed white-space-pre-line">{{ $bug->root_cause }}</div>
                        </div>
                        <div>
                            <h3 class="text-xs text-slate-650 font-mono uppercase mb-1">Tindakan Perbaikan yang Diambil (Repair Action):</h3>
                            <div class="bg-green-50/50 border border-green-200 p-4 rounded-lg text-green-800 leading-relaxed white-space-pre-line">{{ $bug->repair_action }}</div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Right Side: AI Analytics Box & Communication inbox -->
        <div class="space-y-6">
            
            <!-- AI Stage 1 box -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm border-t-4 border-t-blue-600">
                <h2 class="text-md font-bold text-blue-600 mb-6 flex items-center gap-2 uppercase tracking-wide">
                    <i class="bi bi-robot"></i> DIAGNOSIS AI ENGINE (STAGE 1)
                </h2>

                <div class="space-y-5">
                    <!-- Severity Audit -->
                    <div class="border-b border-slate-200 pb-4">
                        <span class="block text-xs font-mono text-slate-600 uppercase mb-2.5">Audit Derajat Keparahan:</span>
                        <div class="flex items-center justify-between">
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

                    <!-- Sentiment details -->
                    <div class="border-b border-slate-200 pb-4">
                        <span class="block text-xs font-mono text-slate-650 uppercase mb-2">Sentimen Teks Deskripsi:</span>
                        @if($bug->sentiment_label)
                            <div class="flex items-center gap-3">
                                @if($bug->sentiment_label === 'positive')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-green-50 text-green-700 border border-green-200 px-2 py-0.5 rounded uppercase">POSITIF</span>
                                @elseif($bug->sentiment_label === 'negative')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-red-50 text-red-700 border border-red-200 px-2 py-0.5 rounded uppercase">NEGATIF</span>
                                @elseif($bug->sentiment_label === 'spam')
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-purple-50 text-purple-700 border border-purple-200 px-2 py-0.5 rounded uppercase">SPAM</span>
                                @else
                                    <span class="inline-flex text-[9px] font-bold font-mono bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.5 rounded uppercase">NETRAL</span>
                                @endif
                                <span class="text-xs font-mono text-slate-600">Score: <strong>{{ number_format($bug->sentiment_score, 2) }}</strong></span>
                            </div>
                        @else
                            <span class="text-xs font-mono text-slate-400">MENUNGGU ANTRIAN AI</span>
                        @endif
                    </div>

                    <!-- Spam filter yield -->
                    <div>
                        <span class="block text-xs font-mono text-slate-650 uppercase mb-2">Spam / Keabsahan Laporan:</span>
                        @if($bug->sentiment_label)
                            @if($bug->is_spam)
                                <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-755 text-xs space-y-1">
                                    <div class="font-bold flex items-center gap-1.5 text-red-700"><i class="bi bi-shield-slash"></i> CRITICAL SPAM ALARM!</div>
                                    <p class="text-slate-600 font-mono text-[11px] leading-relaxed">{{ $bug->spam_reason }}</p>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-50 border border-green-200 rounded-lg text-green-750 text-xs font-mono font-bold">
                                    <i class="bi bi-shield-fill-check"></i> VALID (LAPORAN SERIUS)
                                </div>
                            @endif
                        @else
                            <span class="text-xs font-mono text-slate-400">N/A</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Feedback / Direct Message Box -->
            <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-md font-bold text-slate-800 mb-4 flex items-center gap-2 uppercase tracking-wide">
                    <i class="bi bi-chat-left-text-fill text-blue-600"></i> KOTAK KOMUNIKASI QA
                </h2>

                <div class="space-y-4 max-h-[300px] overflow-y-auto mb-4 pr-1">
                    @if($bug->feedbacks->isEmpty())
                        <div class="text-center text-xs font-mono text-slate-400 py-12 uppercase tracking-wider">
                            BELUM ADA PESAN YANG TERKIRIM
                        </div>
                    @else
                        @foreach($bug->feedbacks as $message)
                            @php
                                $isOutbound = ($message->from_user_id === auth()->id());
                            @endphp
                            <div class="p-3 rounded-lg border text-xs space-y-1 relative {{ $isOutbound ? 'bg-blue-50/50 border-blue-200 border-l-2 border-l-blue-600' : 'bg-slate-50 border-slate-250 border-l-2 border-l-purple-600' }}">
                                <div class="flex justify-between font-mono text-[10px] text-slate-500">
                                    <span class="font-bold text-slate-700">{{ $message->sender?->name ?? 'System' }}</span>
                                    <span>{{ $message->created_at->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-700 leading-normal">{{ $message->message }}</p>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Input Message Form -->
                <form action="{{ route('bugs.feedback.store', $bug) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <textarea name="message" rows="2" class="w-full bg-white border border-slate-200 rounded-lg p-2.5 text-slate-800 text-xs focus:outline-none focus:border-blue-600 transition-all placeholder-slate-400" placeholder="Kirim tanggapan atau instruksi perbaikan..." required></textarea>
                    </div>
                    <button type="submit" class="w-full flex items-center justify-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                        <i class="bi bi-send-fill"></i> KIRIM PESAN FEEDBACK
                    </button>
                </form>
            </div>

        </div>

    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('{{ route('bugs.feedback.read', $bug) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        });
    });
</script>
@endsection

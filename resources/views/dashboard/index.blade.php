@extends('layouts.app')

@section('title', 'Dashboard Analitik QA')

@section('content')
@if(!$hasImportedData)
{{-- ============================================================ --}}
{{-- EMPTY STATE: No completed SQL import yet                      --}}
{{-- ============================================================ --}}
<div class="flex flex-col items-center justify-center min-h-[70vh] text-center px-4">
    <div class="w-20 h-20 rounded-3xl bg-blue-50 flex items-center justify-center mb-6">
        <i class="bi bi-database-slash text-4xl text-blue-400"></i>
    </div>
    <h2 class="text-xl font-extrabold text-slate-800 mb-2">Dashboard Belum Ada Data</h2>
    <p class="text-sm text-slate-500 max-w-sm mb-2">
        Belum ada file <code class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-700">.sql</code> yang berhasil diimport.
        Dashboard analitik akan tampil setelah proses import pertama selesai.
    </p>
    <p class="text-xs text-slate-400 mb-8">
        Pastikan <code class="font-mono bg-slate-100 px-1 rounded">php artisan queue:work</code> sudah berjalan
        agar job import diproses.
    </p>
    <a href="{{ route('import.upload') }}"
       class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-bold text-white shadow-sm transition-colors"
       style="background-color: #0046BF;">
        <i class="bi bi-cloud-upload"></i>
        Import Data .sql Sekarang
    </a>
</div>
@else
@php
    $criticalOpenBugs = $criticalBugs;
    $spamCount = $spamBlocked;

    // Mapped damage categories
    $damageCategories = $damageDistribution->map(function($item) {
        return (object)[
            'damage_category' => $item->damage_category,
            'count'           => $item->total
        ];
    });

    // Mapped trend data
    $trendMap = $volumeTrend->pluck('total', 'date')->toArray();
    $trendData = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateStr = now()->subDays($i)->format('Y-m-d');
        $trendData[$dateStr] = $trendMap[$dateStr] ?? 0;
    }

    // Mapped top projects
    $mappedTopProjects = $topProjects->map(function($tp) {
        return (object)[
            'project_name' => $tp->project?->name ?? 'Unknown Proyek',
            'bug_count'    => $tp->total,
        ];
    });

    // Mapped bugs
    $bugs = $auditBugs;
@endphp
<div class="space-y-8">
    
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-slate-200 pb-5">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-800 tracking-tight uppercase">
                QC Defect & AI Diagnostics Dashboard
            </h1>
            <p class="text-xs md:text-sm text-slate-500 font-mono tracking-widest uppercase mt-1">
                PT HARIFF MANUFACTURING QUALITY CONTROL & AI ANALYTICS SYSTEM
            </p>
            @php
                $activeFile = $sqlFiles->firstWhere('id', $selectedJobId);
                $activeFileName = $activeFile ? $activeFile->filename : ($selectedJobId === 'all' ? 'Semua File SQL (Gabungan)' : 'File #' . $selectedJobId);
            @endphp
            <div class="mt-2 text-xs font-semibold text-blue-650 bg-blue-50 border border-blue-100 rounded px-2.5 py-1 inline-flex items-center gap-1.5 font-mono uppercase">
                <i class="bi bi-file-earmark-code"></i> Aktif: {{ $activeFileName }}
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.export', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-white hover:bg-slate-50 border border-slate-200 text-blue-600 hover:text-blue-700 rounded-lg text-xs font-mono font-bold tracking-wider uppercase transition-all shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet text-base"></i> EXPORT LOG (CSV)
            </a>
        </div>
    </div>

    <!-- ZONE 1: INTEGRATED HARDWARE KPI CARDS (TOP ROW) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
        
        <!-- Card 1: Total Hardware Bugs -->
        <div class="bg-white border border-slate-200 border-l-4 border-l-slate-400 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:bg-slate-50/50 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
            <div class="h-12 w-12 rounded-lg bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                <i class="bi bi-cpu text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">BUG TERDAFTAR</span>
                <span class="text-2xl font-black font-mono text-slate-800">{{ $totalBugs }} <span class="text-xs font-normal text-slate-500 font-sans">UNITS</span></span>
            </div>
        </div>

        <!-- Card 2: Open Bugs Queue -->
        <div class="bg-white border border-slate-200 border-l-4 border-l-amber-500 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:bg-slate-50/50 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
            <div class="h-12 w-12 rounded-lg bg-amber-50 flex items-center justify-center text-amber-600 shrink-0">
                <i class="bi bi-folder2-open text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">QUEUE OPEN</span>
                <span class="text-2xl font-black font-mono text-slate-800">{{ $openBugs }} <span class="text-xs font-normal text-slate-500 font-sans">BUGS</span></span>
            </div>
        </div>

        <!-- Card 3: Closed Bugs Logs -->
        <div class="bg-white border border-slate-200 border-l-4 border-l-green-500 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:bg-slate-50/50 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
            <div class="h-12 w-12 rounded-lg bg-green-50 flex items-center justify-center text-green-600 shrink-0">
                <i class="bi bi-check-circle text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">LOGS CLOSED</span>
                <span class="text-2xl font-black font-mono text-slate-800">{{ $closedBugs }} <span class="text-xs font-normal text-slate-500 font-sans">BUGS</span></span>
            </div>
        </div>

        <!-- Card 4: Active Critical Alerts -->
        <div class="bg-white border border-slate-200 border-l-4 border-l-red-600 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:bg-red-50/30 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
            <div class="h-12 w-12 rounded-lg bg-red-100 flex items-center justify-center text-red-600 shrink-0">
                <i class="bi bi-exclamation-triangle text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">ACTIVE CRITICALS</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-red-600">{{ $criticalOpenBugs }}</span>
                    @if($criticalOpenBugs > 0)
                        <span class="text-[9px] font-bold font-mono bg-red-600 text-white px-1.5 py-0.5 rounded uppercase tracking-wider">CRITICAL</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 5: AI Automation Ingestion -->
        <div class="bg-white border border-slate-200 border-l-4 border-l-indigo-500 rounded-xl p-5 flex items-center gap-4 shadow-sm hover:bg-slate-50/50 transition-all hover:-translate-y-1 hover:shadow-md duration-300">
            <div class="h-12 w-12 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-650 shrink-0">
                <i class="bi bi-shield-check text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">AI SPAM BLOCKED</span>
                <span class="text-2xl font-black font-mono text-slate-800">{{ $spamCount }} <span class="text-xs font-normal text-slate-500 font-sans">BLOCKED</span></span>
            </div>
        </div>
    </div>

    <!-- ZONE 2: CYBERNETIC ANALYTICS CORE (MIDDLE ROW) -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        
        <!-- Left Panel: Bugs by Severity Donut Chart (2/5 cols width) -->
        <div class="lg:col-span-2 premium-card premium-card-accent p-6">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-600 mb-6 uppercase flex items-center gap-2">
                <i class="bi bi-pie-chart text-blue-600"></i>
                DISTRIBUSI BUG BY SEVERITY
            </h2>
            
            <div class="flex flex-col items-center justify-center min-h-[300px]">
                <div id="severityChart" class="w-full"></div>
            </div>
        </div>

        <!-- Right Panel: Culpable Hardware Components Chart (3/5 cols width) -->
        <div class="lg:col-span-3 premium-card premium-card-accent p-6">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-600 mb-6 uppercase flex items-center gap-2">
                <i class="bi bi-bar-chart-steps text-blue-600"></i>
                ANALISIS AI KATEGORI PENYEBAB KERUSAKAN
            </h2>
            
            <div class="min-h-[300px]">
                <div id="componentsChart" class="w-full"></div>
            </div>
        </div>
    </div>

    <!-- ZONE 2.5: SYSTEM STABILITY & VOLUME DYNAMICS (NEW ROW) -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
        <!-- Volume Trend Line Chart (Card 1) -->
        <div class="premium-card premium-card-accent p-6">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-600 mb-6 uppercase flex items-center gap-2">
                <i class="bi bi-graph-up-arrow text-blue-600"></i>
                TREN VOLUME LAPORAN (7 HARI TERAKHIR)
            </h2>
            <div class="flex flex-col justify-center min-h-[290px]">
                <div id="volumeChart" class="w-full"></div>
            </div>
        </div>

        <!-- Top 5 Projects Table (Card 2) -->
        <div class="premium-card premium-card-accent p-6">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-600 mb-6 uppercase flex items-center gap-2">
                <i class="bi bi-trophy text-blue-600"></i>
                PROYEK PALING BANYAK BUG (TOP 5)
            </h2>
            <div class="overflow-x-auto min-h-[290px] flex flex-col justify-between">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-200 text-[9px] font-mono tracking-widest text-slate-500 uppercase">
                            <th class="py-2.5 px-3">PROJECT NAME</th>
                            <th class="py-2.5 px-3 text-right">BUG COUNT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs font-mono">
                        @forelse($mappedTopProjects as $tp)
                            <tr class="hover:bg-slate-50">
                                <td class="py-2.5 px-3 font-semibold text-slate-700 uppercase">
                                    {{ $tp->project_name }}
                                </td>
                                <td class="py-2.5 px-3 text-right text-blue-600 font-bold">{{ $tp->bug_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="py-4 px-3 text-center text-slate-450 uppercase">[NO PROJECT DATA]</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ZONE 3: LIVE FACTORY FEED DATA TABLE (BOTTOM SECTION) -->
    <div class="premium-card premium-card-accent p-6">
        
        <!-- Section Header -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 border-b border-slate-200 pb-5">
            <div>
                <h2 class="text-xs font-mono font-bold tracking-widest text-slate-700 uppercase flex items-center gap-2">
                    <i class="bi bi-activity text-blue-600"></i>
                    LIVE MANUFACTURING LOGS FEED
                </h2>
                <p class="text-[10px] text-slate-500 font-mono tracking-wider mt-1 uppercase">INTEGRATED PRODUCTION LINE DATABASE // STAGE 1 & 2 VERIFICATION</p>
            </div>
            
            <!-- Sleek Cyber Filter Panel -->
            <form action="{{ route('dashboard') }}" method="GET" class="w-full xl:w-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:flex xl:flex-wrap gap-2 text-xs font-mono w-full">
                    <select name="import_job_id" class="w-full xl:w-auto bg-white border rounded px-3 py-1.5 text-slate-700 focus:outline-none focus:border-blue-600 font-bold" style="border-color: #0046BF;">
                        <option value="all" {{ $selectedJobId === 'all' ? 'selected' : '' }}>[SEMUA FILE SQL]</option>
                        @foreach($sqlFiles as $file)
                            <option value="{{ $file->id }}" {{ $selectedJobId == $file->id ? 'selected' : '' }}>
                                {{ strtoupper($file->filename) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="project_id" class="w-full xl:w-auto bg-white border border-slate-200 rounded px-3 py-1.5 text-slate-700 focus:outline-none focus:border-blue-600">
                        <option value="">[SEMUA PROYEK]</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ strtoupper($p->name) }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="w-full xl:w-auto bg-white border border-slate-200 rounded px-3 py-1.5 text-slate-700 focus:outline-none focus:border-blue-600">
                        <option value="">[SEMUA STATUS]</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                    </select>

                    <select name="severity" class="w-full xl:w-auto bg-white border border-slate-200 rounded px-3 py-1.5 text-slate-700 focus:outline-none focus:border-blue-600">
                        <option value="">[SEMUA SEVERITY]</option>
                        <option value="Critical" {{ request('severity') === 'Critical' ? 'selected' : '' }}>CRITICAL</option>
                        <option value="Major" {{ request('severity') === 'Major' ? 'selected' : '' }}>MAJOR</option>
                        <option value="Minor" {{ request('severity') === 'Minor' ? 'selected' : '' }}>MINOR</option>
                    </select>

                    <select name="urgency_sort" class="w-full xl:w-auto bg-white border border-slate-200 rounded px-3 py-1.5 text-slate-700 focus:outline-none focus:border-blue-600">
                        <option value="">[URUTAN DEFAULT: TERBARU]</option>
                        <option value="desc" {{ request('urgency_sort') === 'desc' ? 'selected' : '' }}>URGENCY TERTINGGI KE TERENDAH</option>
                        <option value="asc" {{ request('urgency_sort') === 'asc' ? 'selected' : '' }}>URGENCY TERENDAH KE TERTINGGI</option>
                    </select>

                    <div class="w-full xl:w-auto flex items-center justify-between xl:justify-start gap-1.5 bg-white border border-slate-200 rounded px-2.5 py-1 text-slate-700">
                        <span class="text-[9px] text-slate-400 uppercase font-mono">FROM:</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-transparent border-none text-slate-800 text-xs focus:outline-none focus:ring-0 p-0 font-mono w-full xl:w-28" style="color-scheme: light;">
                    </div>

                    <div class="w-full xl:w-auto flex items-center justify-between xl:justify-start gap-1.5 bg-white border border-slate-200 rounded px-2.5 py-1 text-slate-700">
                        <span class="text-[9px] text-slate-400 uppercase font-mono">TO:</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-transparent border-none text-slate-800 text-xs focus:outline-none focus:ring-0 p-0 font-mono w-full xl:w-28" style="color-scheme: light;">
                    </div>

                    <button type="submit" class="w-full xl:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded font-bold transition-all shadow-sm">
                        EXECUTE
                    </button>
                    <a href="{{ route('dashboard') }}" class="w-full xl:w-auto text-center bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-1.5 rounded transition-all">
                        RESET
                    </a>
                </div>
            </form>
        </div>

        <!-- High Density Data Grid Table -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-slate-50">
                        <th class="py-3 px-4 font-bold">PROJECT & UNIT SN</th>
                        <th class="py-3 px-4 font-bold">BUG TITLE & ORIGIN</th>
                        <th class="py-3 px-4 font-bold">AI AUTOMATION DIAGNOSIS</th>
                        <th class="py-3 px-4 text-right font-bold">REWORK & STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs">
                    @forelse($bugs as $b)
                        @php
                            // Calculate Urgency Score dynamically based on severity and sentiment label
                            $isSpam = $b->is_spam || strtolower($b->sentiment_label) === 'spam';
                            
                            if ($isSpam) {
                                $urgencyScore = 0.0;
                            } else {
                                $sevWeight = $b->severity === 'Critical' ? 0.8 : ($b->severity === 'Major' ? 0.5 : 0.2);
                                // Treat missing sentiment_score as neutral (0.5) so unanalyzed bugs
                                // do not artificially score as highly urgent.
                                $sentScore = $b->sentiment_score !== null ? $b->sentiment_score : 0.5;
                                // Score formula: (severity weight + (1 - sentiment score)) / 2
                                $urgencyScore = round(($sevWeight + (1.0 - $sentScore)) / 2.0, 2);
                                $urgencyScore = min(1.0, max(0.0, $urgencyScore));
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition-all">
                            
                            <!-- Column 1: Project & Unit SN -->
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-700 font-sans tracking-wide uppercase">{{ $b->project?->name ?? 'Project #' . $b->project_id }}</span>
                                    <span class="inline-flex max-w-max bg-blue-50 text-blue-600 border border-blue-100 rounded px-2.5 py-0.5 font-mono text-[9px] font-bold tracking-widest uppercase transition-all shadow-sm">
                                        {{ $b->sn_code_snapshot ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Column 2: Bug Title & Origin -->
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-semibold text-slate-800 font-sans tracking-normal leading-snug">{{ $b->title }}</span>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        @if($b->reporter_type === 'produk')
                                            <span class="text-[9px] font-bold font-mono bg-blue-50 text-blue-600 border border-blue-100 px-2 py-0.2 rounded uppercase">PRODUK LEVEL</span>
                                        @else
                                            <span class="text-[9px] font-bold font-mono bg-slate-100 text-slate-600 border border-slate-200 px-2 py-0.2 rounded uppercase">SUB-PCB LEVEL</span>
                                        @endif

                                        @if($b->severity === 'Critical')
                                            <span class="text-[9px] font-bold font-mono bg-red-100 text-red-700 border border-red-200 px-2 py-0.2 rounded uppercase">CRITICAL</span>
                                        @elseif($b->severity === 'Major')
                                            <span class="text-[9px] font-bold font-mono bg-yellow-100 text-yellow-700 border border-yellow-200 px-2 py-0.2 rounded uppercase">MAJOR</span>
                                        @else
                                            <span class="text-[9px] font-bold font-mono bg-green-100 text-green-700 border border-green-200 px-2 py-0.2 rounded uppercase">MINOR</span>
                                        @endif

                                        @if($isSpam)
                                            <span class="inline-flex items-center gap-1 bg-red-100 text-red-700 border border-red-200 px-1.5 py-0.2 rounded text-[9px] font-bold font-mono uppercase tracking-wider" title="Laporan terdeteksi sebagai spam: {{ $b->spam_reason ?? 'Tidak substantif' }}">
                                                <i class="bi bi-exclamation-octagon"></i> SPAM
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Column 3: AI Automation Diagnosis -->
                            <td class="py-4 px-4">
                                <div class="space-y-2 max-w-md">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] text-slate-500 font-mono uppercase tracking-wider">Urgency:</span>
                                        @php
                                            $urgColor = 'text-blue-600';
                                            $urgBg = 'bg-blue-600';
                                            if ($urgencyScore >= 0.75) {
                                                $urgColor = 'text-red-600';
                                                $urgBg = 'bg-red-600';
                                            } elseif ($urgencyScore >= 0.45) {
                                                $urgColor = 'text-yellow-600';
                                                $urgBg = 'bg-yellow-600';
                                            }
                                        @endphp
                                        <span class="text-xs font-black font-mono {{ $urgColor }}">{{ number_format($urgencyScore, 2) }}</span>
                                        <div class="h-1.5 w-24 bg-slate-100 rounded-full overflow-hidden border border-slate-200 shadow-inner">
                                            <div class="h-full {{ $urgBg }} rounded-full" style="width: {{ $urgencyScore * 100 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Auto-category and expander details -->
                                    <div class="text-[10px] font-mono uppercase font-semibold text-slate-650">
                                        AI Category: <span class="text-blue-600">{{ $b->damage_category ?? ($b->status === 'CLOSED' ? 'UNANALYZED' : 'PENDING MECHANIC CLOSE') }}</span>
                                    </div>


                                </div>
                            </td>

                            <!-- Column 4: Rework Indicator & Status -->
                            <td class="py-4 px-4 text-right">
                                <div class="flex flex-col items-end gap-2.5">
                                    <div class="flex items-center gap-2">
                                        @if($b->is_rework)
                                            <span class="inline-flex bg-red-100 text-red-700 border border-red-200 px-2 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-widest">REWORK</span>
                                        @endif

                                        @if($b->status === 'CLOSED')
                                            <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 border border-green-200 px-2.5 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-wider">
                                                CLOSED // {{ strtoupper($b->fixed_by ?? 'System') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-yellow-100 text-yellow-700 border border-yellow-250 px-2.5 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-wider">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500"></span> OPEN QUEUE
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Reprocess AI Button -->
                                    <button onclick="reprocessAI({{ $b->id }}, this)" class="text-[9px] font-bold font-mono border border-slate-200 hover:border-blue-600 bg-slate-50 text-slate-600 hover:text-blue-600 px-2.5 py-1 rounded transition-all flex items-center gap-1 select-none active:scale-95 shadow-sm">
                                        <i class="bi bi-arrow-clockwise"></i> REPROCESS AI
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Per-Report Activity Log -->
                        <tr class="bg-slate-50/50">
                            <td colspan="4" class="py-0 px-4 border-b border-slate-100">
                                <details class="group py-2.5">
                                    <summary class="flex items-center gap-2 text-[10px] font-mono font-bold uppercase tracking-wider text-blue-600 cursor-pointer hover:underline select-none focus:outline-none">
                                        <i class="bi bi-journal-text"></i>
                                        Log Aktivitas Laporan
                                        <span class="text-slate-400 font-normal normal-case">— {{ $b->title }}</span>
                                    </summary>
                                    <div class="pl-5 pr-2 py-3">
                                        <div class="relative">
                                            <div class="absolute left-2 top-0 bottom-0 w-px bg-slate-200"></div>
                                            <div class="space-y-3">
                                                <!-- Reported event -->
                                                <div class="relative pl-7">
                                                    <div class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-blue-50 border border-blue-400 text-blue-600 flex items-center justify-center">
                                                        <i class="bi bi-plus-lg text-[8px]"></i>
                                                    </div>
                                                    <div class="text-[10px] font-mono uppercase tracking-wider text-blue-600 font-bold">Laporan Masuk</div>
                                                    <div class="text-[10px] text-slate-600 font-mono mt-0.5">
                                                        Oleh <span class="font-semibold text-slate-800">{{ $b->reported_by ?: 'Tidak diketahui' }}</span>
                                                        pada <span class="font-semibold text-slate-800">{{ $b->created_at->format('d M Y H:i') }}</span>
                                                    </div>
                                                </div>

                                                @if($b->status === 'CLOSED' && $b->closed_at)
                                                    <!-- Closed event -->
                                                    <div class="relative pl-7">
                                                        <div class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-green-50 border border-green-400 text-green-600 flex items-center justify-center">
                                                            <i class="bi bi-check-lg text-[8px]"></i>
                                                        </div>
                                                        <div class="text-[10px] font-mono uppercase tracking-wider text-green-600 font-bold">Pengerjaan Selesai</div>
                                                        <div class="text-[10px] text-slate-600 font-mono mt-0.5">
                                                            Oleh <span class="font-semibold text-slate-800">{{ $b->fixed_by ?: 'Tidak diketahui' }}</span>
                                                            pada <span class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($b->closed_at)->format('d M Y H:i') }}</span>
                                                        </div>
                                                        @if($b->root_cause || $b->repair_action)
                                                            <div class="mt-1.5 text-[10px] text-slate-600 font-mono">
                                                                <span class="text-yellow-600 font-semibold">Root Cause:</span> {{ $b->root_cause ?: '-' }}
                                                                <span class="mx-1.5 text-slate-300">|</span>
                                                                <span class="text-green-600 font-semibold">Repair Action:</span> {{ $b->repair_action ?: '-' }}
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 px-4 text-center text-slate-500 font-mono uppercase">
                                [TIDAK ADA DATA LOG DI LINE PRODUKSI YANG DITEMUKAN]
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- High Density Pagination -->
        <div class="mt-6 border-t border-slate-250 pt-4 text-slate-400 font-mono">
            {{ $bugs->links() }}
        </div>
    </div>
</div>
@endif
@endsection

@section('scripts')
@if($hasImportedData)
<script>
    async function reprocessAI(bugId, btnElement) {
        // Toggle icon spin animation
        const icon = btnElement.querySelector('i');
        if(icon) icon.classList.add('animate-spin');
        
        console.log(`[QA CONTROL LOG]: Sending reprocess request for Ticket #${bugId}`);
        
        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content;
            const res = await fetch(`/bugs/${bugId}/reprocess`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                }
            });

            const data = await res.json();

            if (res.ok && data.success) {
                alert(`[QA TERMINAL CONTROL]: Reprocessing AI algorithms for Bug Ticket #${bugId} succeeded.`);
                window.location.reload();
            } else {
                if(icon) icon.classList.remove('animate-spin');
                alert(data.message || 'Gagal memproses ulang AI.');
            }
        } catch (err) {
            if(icon) icon.classList.remove('animate-spin');
            console.error(err);
            alert('Terjadi kesalahan koneksi saat memproses ulang AI.');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const isDark = document.body.classList.contains('dark');
        const textPrimaryColor = isDark ? '#F9FAFB' : '#1E293B';
        const textSecondaryColor = isDark ? '#9CA3AF' : '#64748B';
        const borderColor = isDark ? '#374151' : '#E2E8F0';
        const strokeColor = isDark ? '#1F2937' : '#FFFFFF';
        const tooltipTheme = isDark ? 'dark' : 'light';

        // 1. Severity Donut Chart (Left Column)
        var severityCounts = {
            critical: {{ $severityCounts['critical'] ?? 0 }},
            major: {{ $severityCounts['major'] ?? 0 }},
            minor: {{ $severityCounts['minor'] ?? 0 }}
        };

        var severityOptions = {
            series: [severityCounts.critical, severityCounts.major, severityCounts.minor],
            chart: {
                type: 'donut',
                height: 290,
                background: 'transparent',
                foreColor: textSecondaryColor
            },
            labels: ['Critical Hazards', 'Major Defects', 'Minor Flaws'],
            colors: ['#DC2626', '#CA8A04', '#16A34A'], // corporate red, warning amber, success green
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: [strokeColor]
            },
            legend: {
                position: 'bottom',
                fontSize: '10px',
                fontFamily: 'Inter, sans-serif',
                markers: {
                    radius: 4,
                    width: 8,
                    height: 8
                },
                itemMargin: {
                    horizontal: 10,
                    vertical: 5
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        background: 'transparent',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '11px',
                                fontFamily: 'Inter, sans-serif',
                                color: textSecondaryColor,
                                offsetY: -8
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontFamily: 'Inter, sans-serif',
                                color: textPrimaryColor,
                                fontWeight: 'bold',
                                offsetY: 8,
                                formatter: function (val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'TOTAL LOGS',
                                color: textSecondaryColor,
                                fontSize: '10px',
                                fontFamily: 'Inter, sans-serif',
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce(function(a, b) {
                                        return a + b;
                                    }, 0);
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                theme: tooltipTheme
            }
        };

        var severityChart = new ApexCharts(document.querySelector("#severityChart"), severityOptions);
        severityChart.render();

        // 2. Culpable Hardware Components Bar Chart (Right Column)
        @php
            $catNames = $damageCategories->isNotEmpty() ? $damageCategories->pluck('damage_category')->toArray() : ['Hubungan Pendek/Short Circuit', 'Overheat/Panas Berlebih', 'Korosi/Kelembapan', 'Kesalahan Pemasangan', 'Kualitas Komponen'];
            $catCounts = $damageCategories->isNotEmpty() ? $damageCategories->pluck('count')->toArray() : [0, 0, 0, 0, 0];
        @endphp

        var componentsOptions = {
            series: [{
                name: 'Modul Cacat',
                data: {!! json_encode($catCounts) !!}
            }],
            chart: {
                type: 'bar',
                height: 290,
                background: 'transparent',
                foreColor: textSecondaryColor,
                toolbar: {
                    show: false
                }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    barHeight: '52%',
                    borderRadius: 4,
                    colors: {
                        backgroundBarColors: ['rgba(0,0,0,0.02)'],
                        backgroundBarRadius: 4
                    }
                }
            },
            colors: ['#0046BF'], // corporate primary blue
            stroke: {
                width: 1,
                colors: ['#0046BF']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'light',
                    type: "horizontal",
                    shadeIntensity: 0.5,
                    gradientToColors: ['#003693'], // darker blue
                    inverseColors: true,
                    opacityFrom: 0.9,
                    opacityTo: 0.6
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: true
                    }
                },
                yaxis: {
                    lines: {
                        show: false
                    }
                }
            },
            xaxis: {
                categories: {!! json_encode($catNames) !!},
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        fontSize: '9px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px',
                        fontFamily: 'Inter, sans-serif',
                        colors: textSecondaryColor
                    }
                }
            },
            tooltip: {
                theme: tooltipTheme
            }
        };

        var componentsChart = new ApexCharts(document.querySelector("#componentsChart"), componentsOptions);
        componentsChart.render();

        // 3. Volume Trend Line/Area Chart
        var trendData = {!! json_encode($trendData) !!};
        var trendKeys = Object.keys(trendData).map(function(date) {
            var d = new Date(date);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        var trendValues = Object.values(trendData);

        var volumeOptions = {
            series: [{
                name: 'Volume Laporan',
                data: trendValues
            }],
            chart: {
                type: 'area',
                height: 290,
                background: 'transparent',
                foreColor: textSecondaryColor,
                toolbar: {
                    show: false
                }
            },
            colors: ['#0046BF'], // corporate primary blue
            stroke: {
                curve: 'smooth',
                width: 3
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.35,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            grid: {
                borderColor: borderColor,
                strokeDashArray: 3,
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                }
            },
            xaxis: {
                categories: trendKeys,
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                },
                labels: {
                    style: {
                        fontSize: '9px',
                        fontFamily: 'Inter, sans-serif'
                    }
                }
            },
            yaxis: {
                min: 0,
                tickAmount: 4,
                labels: {
                    style: {
                        fontSize: '9px',
                        fontFamily: 'Inter, sans-serif'
                    },
                    formatter: function(val) {
                        return Math.floor(val);
                    }
                }
            },
            tooltip: {
                theme: tooltipTheme
            }
        };

        var volumeChart = new ApexCharts(document.querySelector("#volumeChart"), volumeOptions);
        volumeChart.render();

        // 4. Dynamic Theme-Changed Event Listener to update all charts on the fly
        document.addEventListener('theme-changed', function(e) {
            const newTheme = e.detail.theme;
            const newIsDark = newTheme === 'dark';
            
            const newTextPrimary = newIsDark ? '#F9FAFB' : '#1E293B';
            const newTextSecondary = newIsDark ? '#9CA3AF' : '#64748B';
            const newBorderColor = newIsDark ? '#374151' : '#E2E8F0';
            const newStrokeColor = newIsDark ? '#1F2937' : '#FFFFFF';
            
            severityChart.updateOptions({
                chart: { foreColor: newTextSecondary },
                stroke: { colors: [newStrokeColor] },
                plotOptions: {
                    pie: {
                        donut: {
                            labels: {
                                name: { color: newTextSecondary },
                                value: { color: newTextPrimary },
                                total: { color: newTextSecondary }
                            }
                        }
                    }
                },
                tooltip: { theme: newTheme }
            });

            componentsChart.updateOptions({
                chart: { foreColor: newTextSecondary },
                grid: { borderColor: newBorderColor },
                yaxis: {
                    labels: {
                        style: {
                            colors: newTextSecondary
                        }
                    }
                },
                tooltip: { theme: newTheme }
            });

            volumeChart.updateOptions({
                chart: { foreColor: newTextSecondary },
                grid: { borderColor: newBorderColor },
                tooltip: { theme: newTheme }
            });
        });
    });
</script>
@endif
@endsection

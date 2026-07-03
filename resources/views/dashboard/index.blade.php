@extends('layouts.app')

@section('title', 'Laporan Umum — Dashboard Analitik')

@section('content')
@if(!$hasImportedData)
{{-- Empty State --}}
<div class="flex flex-col items-center justify-center min-h-[70vh] text-center px-4">
    <div class="w-20 h-20 rounded-3xl bg-highlight flex items-center justify-center mb-6">
        <i class="bi bi-database-slash text-4xl text-blue-400"></i>
    </div>
    <h2 class="text-xl font-extrabold text-text-primary mb-2">Dashboard Belum Ada Data</h2>
    <p class="text-sm text-text-secondary max-w-sm mb-2">
        Belum ada file <code class="font-mono bg-bg-tertiary px-1.5 py-0.5 rounded text-slate-700">.sql</code> yang berhasil diimport.
        Dashboard analitik akan tampil setelah proses import pertama selesai.
    </p>
    <p class="text-xs text-text-muted mb-8">
        Pastikan <code class="font-mono bg-bg-tertiary px-1 rounded">php artisan queue:work</code> sudah berjalan.
    </p>
    <a href="{{ route('import.upload') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-bold text-white shadow-sm transition-colors" style="background-color: #0046BF;">
        <i class="bi bi-cloud-upload"></i> Import Data .sql Sekarang
    </a>
</div>
@else
@php
    // Mapped top projects (existing logic preserved)
    $mappedTopProjects = $topProjects->map(function($tp) {
        return (object)[
            'project_name' => $tp->project?->name ?? 'Unknown Proyek',
            'bug_count'    => $tp->total,
        ];
    });

    // Mapped trend data (existing logic preserved)
    $trendMap = $volumeTrend->pluck('total', 'date')->toArray();
    $trendData = [];
    for ($i = 6; $i >= 0; $i--) {
        $dateStr = now()->subDays($i)->format('Y-m-d');
        $trendData[$dateStr] = $trendMap[$dateStr] ?? 0;
    }
@endphp

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 pb-4 border-b border-border-default">
        <div>
            <h1 class="text-2xl font-extrabold text-text-primary tracking-tight">Laporan Umum</h1>
            <p class="text-sm text-text-secondary mt-1">Quality Control & AI Diagnostics — PT Hariff</p>
        </div>
        <a href="{{ route('dashboard.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-border-default rounded-lg text-xs font-semibold text-text-muted hover:bg-bg-tertiary transition-colors">
            <i class="bi bi-file-earmark-excel text-base text-green-600"></i> Export Excel
        </a>
    </div>

    <!-- 4-Component Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ============================================================ --}}
        {{-- [1] Jumlah Bug Terdaftar (Pie Chart: Open vs Close)          --}}
        {{-- ============================================================ --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-pie-chart text-accent"></i> Jumlah Bug Terdaftar
            </h3>

            <div class="relative" style="height: 260px;">
                <div id="chartJumlahBug" class="w-full h-full"></div>
            </div>

            {{-- Keterangan angka --}}
            <div class="grid grid-cols-3 gap-4 mt-6 pt-5 border-t border-border-strong">
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-text-primary">{{ number_format($totalBugs) }}</div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Total Bug</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-amber-600">{{ number_format($openBugs) }}</div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Bug Open</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-green-600">{{ number_format($closedBugs) }}</div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Bug Closed</div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- [3] Top 5 Produk Bermasalah (EXISTING — layout only)         --}}
        {{-- ============================================================ --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-trophy text-accent"></i> Proyek Paling Banyak Bug (Top 5)
            </h3>
            <div class="overflow-x-auto min-h-[280px]">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border-strong text-xs text-text-secondary font-semibold">
                            <th class="px-3 py-2.5 text-left">#</th>
                            <th class="px-3 py-2.5 text-left">Nama Proyek</th>
                            <th class="px-3 py-2.5 text-right">Jumlah Bug</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($mappedTopProjects as $idx => $tp)
                            <tr class="hover:bg-bg-tertiary transition-colors">
                                <td class="px-3 py-3 text-xs text-text-muted font-mono">{{ $idx + 1 }}</td>
                                <td class="px-3 py-3 font-medium text-text-primary">{{ $tp->project_name }}</td>
                                <td class="px-3 py-3 text-right font-bold text-accent font-mono">{{ $tp->bug_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-12 text-center text-text-muted text-sm">
                                    <i class="bi bi-inbox text-xl block mb-1"></i> Belum ada data proyek
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- [2] Distribusi Severity (2 Pie Chart: Closed & Open)         --}}
        {{-- ============================================================ --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-exclamation-triangle text-accent"></i> Distribusi Bug by Severity
            </h3>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-semibold text-text-muted text-center mb-2">Closed</p>
                    <div class="relative" style="height: 220px;">
                        <div id="chartSeverityClosed" class="w-full h-full"></div>
                    </div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-text-muted text-center mb-2">Open</p>
                    <div class="relative" style="height: 220px;">
                        <div id="chartSeverityOpen" class="w-full h-full"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- [4] Tren Volume Laporan 7 Hari (EXISTING — logic preserved)  --}}
        {{-- ============================================================ --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-graph-up-arrow text-accent"></i> Tren Volume Laporan (7 Hari)
            </h3>
            <div class="min-h-[280px]">
                <div id="volumeChart" class="w-full"></div>
            </div>
        </div>

    </div>

    {{-- ============================================================ --}}
    {{-- Filter & Audit Table Section                                 --}}
    {{-- ============================================================ --}}
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 pb-4 border-b border-border-strong">
            <div>
                <h2 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <i class="bi bi-list-ul text-accent"></i> Log Bug Manufaktur
                </h2>
                <p class="text-xs text-text-muted mt-1">Production line database — Stage 1 & 2 verification</p>
            </div>

            <!-- Filter Panel -->
            <form action="{{ route('dashboard') }}" method="GET" class="w-full xl:w-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:flex xl:flex-wrap gap-2 text-sm w-full">
                    <select name="import_job_id" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-blue-500 text-text-primary font-semibold focus:ring-1 focus:ring-blue-500">
                        <option value="all" {{ $selectedJobId === 'all' ? 'selected' : '' }}>Semua File SQL</option>
                        @foreach($sqlFiles as $file)
                            <option value="{{ $file->id }}" {{ $selectedJobId == $file->id ? 'selected' : '' }}>{{ $file->filename }}</option>
                        @endforeach
                    </select>

                    <select name="project_id" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Proyek</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Status</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>Open</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>Closed</option>
                    </select>

                    <select name="severity" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Severity</option>
                        <option value="Critical" {{ request('severity') === 'Critical' ? 'selected' : '' }}>Critical</option>
                        <option value="Major" {{ request('severity') === 'Major' ? 'selected' : '' }}>Major</option>
                        <option value="Minor" {{ request('severity') === 'Minor' ? 'selected' : '' }}>Minor</option>
                    </select>

                    <select name="urgency_sort" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Terbaru</option>
                        <option value="desc" {{ request('urgency_sort') === 'desc' ? 'selected' : '' }}>Urgency ↓</option>
                        <option value="asc" {{ request('urgency_sort') === 'asc' ? 'selected' : '' }}>Urgency ↑</option>
                    </select>

                    <div class="w-full xl:w-auto flex items-center gap-2 rounded-lg px-3 py-2 bg-bg-secondary border border-border-default">
                        <span class="text-xs text-text-muted">Dari:</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-transparent border-none text-sm text-text-primary focus:outline-none focus:ring-0 p-0 w-full xl:w-28">
                    </div>

                    <div class="w-full xl:w-auto flex items-center gap-2 rounded-lg px-3 py-2 bg-bg-secondary border border-border-default">
                        <span class="text-xs text-text-muted">Sampai:</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-transparent border-none text-sm text-text-primary focus:outline-none focus:ring-0 p-0 w-full xl:w-28">
                    </div>

                    <button type="submit" class="w-full xl:w-auto px-4 py-2 rounded-lg text-xs font-bold text-white transition-colors" style="background-color: #0046BF;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="{{ route('dashboard') }}" class="w-full xl:w-auto px-4 py-2 rounded-lg text-xs font-semibold text-text-muted border border-border-default hover:bg-bg-tertiary transition-colors text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-border-strong text-xs text-text-secondary font-semibold">
                        <th class="px-4 py-2.5 text-left">Proyek & SN</th>
                        <th class="px-4 py-2.5 text-left">Bug & Origin</th>
                        <th class="px-4 py-2.5 text-left">AI Diagnosis</th>
                        <th class="px-4 py-2.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-default">
                    @forelse($auditBugs as $b)
                        @php
                            $sevWeight = $b->severity === 'Critical' ? 0.8 : ($b->severity === 'Major' ? 0.5 : 0.2);
                            $sentScore = $b->sentiment_score !== null ? $b->sentiment_score : 0.5;
                            $urgencyScore = round(($sevWeight + (1.0 - $sentScore)) / 2.0, 2);
                            $urgencyScore = min(1.0, max(0.0, $urgencyScore));
                        @endphp
                        <tr class="hover:bg-bg-tertiary transition-colors">
                            <!-- Project & SN -->
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-semibold text-text-primary">{{ $b->project?->name ?? 'Project #' . $b->project_id }}</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-[10px] font-mono font-semibold bg-highlight text-highlight-text max-w-max">{{ $b->sn_code_snapshot ?? 'N/A' }}</span>
                                </div>
                            </td>

                            <!-- Bug Title & Severity -->
                            <td class="px-4 py-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-medium text-text-primary">{{ $b->title }}</span>
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @if($b->reporter_type === 'produk')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-highlight text-highlight-text">Produk</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-bg-tertiary text-text-muted">Sub-PCB</span>
                                        @endif

                                        @if($b->severity === 'Critical')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-red-50 text-red-700">Critical</span>
                                        @elseif($b->severity === 'Major')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-yellow-50 text-yellow-700">Major</span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-green-50 text-green-700">Minor</span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- AI Diagnosis -->
                            <td class="px-4 py-4">
                                <div class="space-y-2 max-w-md">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-text-muted">Urgency:</span>
                                        @php
                                            $urgColor = '#2563EB';
                                            if ($urgencyScore >= 0.75) $urgColor = '#DC2626';
                                            elseif ($urgencyScore >= 0.45) $urgColor = '#CA8A04';
                                        @endphp
                                        <span class="text-xs font-bold font-mono" style="color: {{ $urgColor }};">{{ number_format($urgencyScore, 2) }}</span>
                                        <div class="h-1.5 w-20 rounded-full overflow-hidden bg-bg-tertiary">
                                            <div class="h-full rounded-full" style="width: {{ $urgencyScore * 100 }}%; background-color: {{ $urgColor }};"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4 text-right">
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center gap-2">
                                            @if($b->is_rework)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-red-50 text-red-700">Rework</span>
                                            @endif

                                            @if($b->status === 'CLOSED')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-green-50 text-green-700">
                                                    Closed — {{ $b->fixed_by ?? 'System' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-pill text-xs font-semibold bg-yellow-50 text-yellow-700">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 inline-block"></span> Open
                                                </span>
                                            @endif
                                    </div>

                                    <button onclick="reprocessAI({{ $b->id }}, this)" class="text-[10px] text-accent hover:text-highlight-text font-semibold py-1 px-2 rounded-lg border border-border-default hover:bg-bg-tertiary transition-colors flex items-center gap-1">
                                        <i class="bi bi-arrow-clockwise"></i> Reprocess AI
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <!-- Activity Log -->
                        <tr>
                            <td colspan="4" class="py-0 px-4 border-b border-border-strong">
                                <details class="group py-2">
                                    <summary class="flex items-center gap-2 text-xs font-medium cursor-pointer select-none focus:outline-none text-accent">
                                        <i class="bi bi-journal-text"></i> Log Aktivitas
                                        <span class="text-text-muted font-normal">— {{ $b->title }}</span>
                                    </summary>
                                    <div class="pl-6 pr-2 py-3">
                                        <div class="relative">
                                            <div class="absolute left-2 top-0 bottom-0 w-px bg-border-strong"></div>
                                            <div class="space-y-3">
                                                <div class="relative pl-7">
                                                    <div class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-highlight border border-blue-400 flex items-center justify-center">
                                                        <i class="bi bi-plus-lg text-[8px] text-accent"></i>
                                                    </div>
                                                    <div class="text-xs font-semibold text-accent">Laporan Masuk</div>
                                                    <div class="text-xs text-text-secondary mt-0.5">
                                                        Oleh <span class="font-semibold text-text-primary">{{ $b->reported_by ?: 'Tidak diketahui' }}</span>
                                                        pada <span class="font-semibold text-text-primary">{{ $b->created_at->format('d M Y H:i') }}</span>
                                                    </div>
                                                </div>

                                                @if($b->status === 'CLOSED' && $b->closed_at)
                                                    <div class="relative pl-7">
                                                        <div class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-green-50 border border-green-400 flex items-center justify-center">
                                                            <i class="bi bi-check-lg text-[8px] text-green-600"></i>
                                                        </div>
                                                        <div class="text-xs font-semibold text-green-600">Pengerjaan Selesai</div>
                                                        <div class="text-xs text-text-secondary mt-0.5">
                                                            Oleh <span class="font-semibold text-text-primary">{{ $b->fixed_by ?: 'Tidak diketahui' }}</span>
                                                            pada <span class="font-semibold text-text-primary">{{ \Carbon\Carbon::parse($b->closed_at)->format('d M Y H:i') }}</span>
                                                        </div>
                                                        @if($b->root_cause || $b->repair_action)
                                                            <div class="mt-1.5 text-xs text-text-secondary">
                                                                <span class="text-yellow-600 font-semibold">Root Cause:</span> {{ $b->root_cause ?: '-' }}
                                                                <span class="mx-1.5 text-slate-300">|</span>
                                                                <span class="text-green-600 font-semibold">Repair:</span> {{ $b->repair_action ?: '-' }}
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
                            <td colspan="4" class="py-12 text-center text-text-muted">
                                <i class="bi bi-inbox text-3xl block mb-2"></i>
                                Tidak ada data log yang ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 pt-4 border-t border-border-strong text-text-muted">
            {{ $auditBugs->links() }}
        </div>
    </div>

</div>
@endif
@endsection

@section('scripts')
@if($hasImportedData)
<script>
    async function reprocessAI(bugId, btnElement) {
        const icon = btnElement.querySelector('i');
        if(icon) icon.classList.add('animate-spin');

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
                alert(`Reprocessing AI untuk Bug #${bugId} berhasil.`);
                window.location.reload();
            } else {
                if(icon) icon.classList.remove('animate-spin');
                alert(data.message || 'Gagal memproses ulang AI.');
            }
        } catch (err) {
            if(icon) icon.classList.remove('animate-spin');
            alert('Terjadi kesalahan koneksi.');
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        function isDarkMode() {
            return document.body.classList.contains('dark');
        }
        function getChartColors() {
            const dark = isDarkMode();
            return {
                textMuted:   dark ? '#7FA3C4' : '#4A7FA7',
                textPrimary: dark ? '#F6FAFD' : '#0A1931',
                gridLine:    dark ? '#1E3A5C' : '#E2E8F0',
                tooltipTheme: dark ? 'dark' : 'light',
            };
        }

        // Chart color palette — semantic, consistent both modes
        const colorOpen     = '#E8A33D'; // amber warm
        const colorClosed   = '#3E9B6F'; // calm green
        const colorCritical = '#D64550'; // soft red
        const colorMajor    = '#E8A33D'; // amber
        const colorMinor    = '#4A7FA7'; // brand blue
        const colorTrend    = '#4A7FA7'; // brand blue

        let chartJumlahBug, chartSeverityClosed, chartSeverityOpen, chartVolume;

        function makeJumlahBugOptions(c) {
            return {
                series: [{{ $openBugs }}, {{ $closedBugs }}],
                chart: { type: 'donut', height: 260, background: 'transparent', foreColor: c.textMuted },
                labels: ['Open', 'Closed'],
                colors: [colorOpen, colorClosed],
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: {
                    position: 'bottom', fontSize: '12px', fontFamily: 'Inter, sans-serif',
                    labels: { colors: c.textMuted },
                    markers: { radius: 4, width: 8, height: 8 },
                    itemMargin: { horizontal: 10, vertical: 5 },
                    formatter: function(name, opts) {
                        return name + ' — ' + opts.w.globals.series[opts.seriesIndex];
                    }
                },
                plotOptions: {
                    pie: { donut: { size: '72%', labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', fontFamily: 'Inter', color: c.textMuted, offsetY: -8 },
                        value: { show: true, fontSize: '28px', fontFamily: 'Inter', color: c.textPrimary, fontWeight: 'bold', offsetY: 8 },
                        total: {
                            show: true, label: 'Total', color: c.textMuted, fontSize: '11px', fontFamily: 'Inter',
                            formatter: function(w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); }
                        }
                    }}}
                },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        function makeSevOptions(data, height, c) {
            return {
                series: Object.values(data),
                chart: { type: 'donut', height: height, background: 'transparent', foreColor: c.textMuted },
                labels: Object.keys(data),
                colors: [colorCritical, colorMajor, colorMinor],
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: {
                    position: 'bottom', fontSize: '11px', fontFamily: 'Inter, sans-serif',
                    labels: { colors: c.textMuted },
                    markers: { radius: 4, width: 8, height: 8 },
                    itemMargin: { horizontal: 6, vertical: 3 },
                    formatter: function(name, opts) { return name + ': ' + opts.w.globals.series[opts.seriesIndex]; }
                },
                plotOptions: { pie: { donut: { size: '65%' } } },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        function makeVolumeOptions(trendKeys, trendValues, c) {
            return {
                series: [{ name: 'Volume', data: trendValues }],
                chart: { type: 'area', height: 280, background: 'transparent', foreColor: c.textMuted, toolbar: { show: false } },
                colors: [colorTrend],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05, stops: [0, 90, 100] }
                },
                grid: {
                    borderColor: c.gridLine, strokeDashArray: 3,
                    xaxis: { lines: { show: false } },
                    yaxis: { lines: { show: true } }
                },
                xaxis: {
                    categories: trendKeys, axisBorder: { show: false }, axisTicks: { show: false },
                    labels: { style: { fontSize: '11px', fontFamily: 'Inter', colors: c.textMuted } }
                },
                yaxis: {
                    min: 0, tickAmount: 4,
                    labels: { style: { fontSize: '11px', fontFamily: 'Inter', colors: c.textMuted }, formatter: v => Math.floor(v) }
                },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        // Initial render
        var c = getChartColors();
        var trendData = {!! json_encode($trendData) !!};
        var trendKeys = Object.keys(trendData).map(d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
        var trendValues = Object.values(trendData);
        var severityClosedData = @json($severityClosed);
        var severityOpenData   = @json($severityOpen);

        chartJumlahBug = new ApexCharts(document.querySelector("#chartJumlahBug"), makeJumlahBugOptions(c));
        chartJumlahBug.render();

        chartSeverityClosed = new ApexCharts(document.querySelector("#chartSeverityClosed"), makeSevOptions(severityClosedData, 220, c));
        chartSeverityClosed.render();

        chartSeverityOpen = new ApexCharts(document.querySelector("#chartSeverityOpen"), makeSevOptions(severityOpenData, 220, c));
        chartSeverityOpen.render();

        chartVolume = new ApexCharts(document.querySelector("#volumeChart"), makeVolumeOptions(trendKeys, trendValues, c));
        chartVolume.render();

        // Re-render charts on theme toggle
        document.addEventListener('theme-changed', function() {
            var nc = getChartColors();
            chartJumlahBug.destroy();
            chartJumlahBug = new ApexCharts(document.querySelector("#chartJumlahBug"), makeJumlahBugOptions(nc));
            chartJumlahBug.render();

            chartSeverityClosed.destroy();
            chartSeverityClosed = new ApexCharts(document.querySelector("#chartSeverityClosed"), makeSevOptions(severityClosedData, 220, nc));
            chartSeverityClosed.render();

            chartSeverityOpen.destroy();
            chartSeverityOpen = new ApexCharts(document.querySelector("#chartSeverityOpen"), makeSevOptions(severityOpenData, 220, nc));
            chartSeverityOpen.render();

            chartVolume.destroy();
            chartVolume = new ApexCharts(document.querySelector("#volumeChart"), makeVolumeOptions(trendKeys, trendValues, nc));
            chartVolume.render();
        });
    });
</script>
@endif
@endsection

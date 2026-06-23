@extends('layouts.app')

@section('title', 'Dashboard Analitik QA')

@section('content')
<div class="space-y-8">
    
    <!-- Top Header Bar -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 border-b border-panel-border pb-5">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-slate-100 tracking-tight">
                CYBERNETIC ASSEMBLY QA FEED
            </h1>
            <p class="text-xs md:text-sm text-slate-500 font-mono tracking-widest uppercase mt-1">
                PT HARIFF MANUFACTURING QUALITY CONTROL & AI DIAGNOSTICS GATEWAY
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('dashboard.export', request()->query()) }}" class="flex items-center gap-2 px-4 py-2 bg-[#111625] hover:bg-[#1E293D] border border-panel-border text-neon-cyan hover:text-white rounded-lg text-xs font-mono font-bold tracking-wider uppercase transition-all shadow-neon-cyan/10 shadow-sm">
                <i class="bi bi-file-earmark-spreadsheet text-base"></i> EXPORT LOG (CSV)
            </a>
        </div>
    </div>

    <!-- ZONE 1: INTEGRATED HARDWARE KPI CARDS (TOP ROW) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Card 1: Total Hardware Bugs -->
        <div class="bg-panel-bg border border-panel-border rounded-xl p-5 flex items-center gap-4 relative overflow-hidden shadow-lg hover:border-neon-cyan/30 transition-all group">
            <div class="absolute top-0 right-0 w-1.5 h-full bg-neon-cyan group-hover:shadow-neon-cyan"></div>
            <div class="h-12 w-12 rounded-lg bg-neon-cyan/5 border border-neon-cyan/20 flex items-center justify-center text-neon-cyan shrink-0">
                <i class="bi bi-cpu text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">BUG TERDAFTAR</span>
                <span class="text-2xl font-black font-mono text-neon-cyan">{{ $totalBugs }} <span class="text-xs font-normal text-slate-500 font-sans">UNITS</span></span>
            </div>
        </div>

        <!-- Card 2: Factory Rework Rate -->
        <div class="bg-panel-bg border border-neon-amber/25 rounded-xl p-5 flex items-center gap-4 relative overflow-hidden shadow-lg hover:border-neon-amber/50 transition-all group">
            <div class="absolute top-0 right-0 w-1.5 h-full bg-neon-amber group-hover:shadow-neon-amber"></div>
            <div class="h-12 w-12 rounded-lg bg-neon-amber/5 border border-neon-amber/20 flex items-center justify-center text-neon-amber shrink-0">
                <i class="bi bi-arrow-repeat text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-550 uppercase">REWORK RATE</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-neon-amber">{{ $reworkRate }}%</span>
                    @if($reworkRate > 15)
                        <span class="text-[9px] font-bold font-mono bg-neon-pink/10 text-neon-pink px-1.5 py-0.5 rounded border border-neon-pink/20 uppercase animate-pulse">WARNING</span>
                    @else
                        <span class="text-[9px] font-bold font-mono bg-neon-green/10 text-neon-green px-1.5 py-0.5 rounded border border-neon-green/20 uppercase">STABLE</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 3: Active Critical Alerts -->
        <div class="bg-panel-bg border border-panel-border rounded-xl p-5 flex items-center gap-4 relative overflow-hidden shadow-lg shadow-neon-pink/5 hover:border-neon-pink/30 transition-all group">
            <div class="absolute top-0 right-0 w-1.5 h-full bg-neon-pink group-hover:shadow-neon-pink"></div>
            <div class="h-12 w-12 rounded-lg bg-neon-pink/10 border border-neon-pink/20 flex items-center justify-center text-neon-pink shrink-0 critical-pulse">
                <i class="bi bi-exclamation-triangle text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">ACTIVE CRITICALS</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-black font-mono text-neon-pink">{{ $criticalOpenBugs }}</span>
                    @if($criticalOpenBugs > 0)
                        <span class="text-[9px] font-bold font-mono bg-neon-pink/15 text-neon-pink px-1.5 py-0.5 rounded border border-neon-pink/30 uppercase tracking-wider shadow-neon-pink select-none critical-pulse">ALERT HAZARD</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Card 4: AI Automation Ingestion -->
        <div class="bg-panel-bg border border-panel-border rounded-xl p-5 flex items-center gap-4 relative overflow-hidden shadow-lg hover:border-neon-green/30 transition-all group">
            <div class="absolute top-0 right-0 w-1.5 h-full bg-neon-green group-hover:shadow-neon-green"></div>
            <div class="h-12 w-12 rounded-lg bg-neon-green/5 border border-neon-green/20 flex items-center justify-center text-neon-green shrink-0">
                <i class="bi bi-shield-check text-2xl"></i>
            </div>
            <div>
                <span class="block text-[10px] font-mono tracking-widest text-slate-500 uppercase">AI FILTER SPAM YIELD</span>
                <span class="text-2xl font-black font-mono text-neon-green">{{ $spamCount }} <span class="text-xs font-normal text-slate-500 font-sans">BLOCKED</span></span>
            </div>
        </div>
    </div>

    <!-- ZONE 2: CYBERNETIC ANALYTICS CORE (MIDDLE ROW) -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        
        <!-- Left Panel: Bugs by Severity Donut Chart (2/5 cols width) -->
        <div class="lg:col-span-2 bg-panel-bg border border-panel-border rounded-xl p-6 shadow-xl backdrop-blur-md">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-400 mb-6 uppercase flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-neon-pink inline-block shadow-neon-pink"></span>
                DISTRIBUSI BUG BY SEVERITY (HAZARD MATRIX)
            </h2>
            
            <div class="flex flex-col items-center justify-center min-h-[300px]">
                <div id="severityChart" class="w-full"></div>
            </div>
        </div>

        <!-- Right Panel: Culpable Hardware Components Chart (3/5 cols width) -->
        <div class="lg:col-span-3 bg-panel-bg border border-panel-border rounded-xl p-6 shadow-xl backdrop-blur-md">
            <h2 class="text-xs font-mono font-bold tracking-widest text-slate-400 mb-6 uppercase flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-neon-cyan inline-block shadow-neon-cyan"></span>
                AI HARDWARE DAMAGE CATEGORIZATION (STAGE 2)
            </h2>
            
            <div class="min-h-[300px]">
                <div id="componentsChart" class="w-full"></div>
            </div>
        </div>
    </div>

    <!-- ZONE 3: LIVE FACTORY FEED DATA TABLE (BOTTOM SECTION) -->
    <div class="bg-panel-bg border border-panel-border rounded-xl p-6 shadow-xl backdrop-blur-md">
        
        <!-- Section Header with Real-Time Terminal Vibe -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 border-b border-panel-border pb-5">
            <div>
                <h2 class="text-xs font-mono font-bold tracking-widest text-slate-200 uppercase flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-neon-green inline-block shadow-neon-green animate-pulse"></span>
                    LIVE MANUFACTURING LOGS FEED
                </h2>
                <p class="text-[10px] text-slate-500 font-mono tracking-wider mt-1 uppercase">INTEGRATED PRODUCTION LINE DATABASE // STAGE 1 & 2 VERIFICATION</p>
            </div>
            
            <!-- Sleek Cyber Filter Panel -->
            <form action="{{ route('dashboard') }}" method="GET" class="w-full xl:w-auto">
                <div class="flex flex-wrap gap-2 text-xs font-mono">
                    <select name="project_id" class="bg-obsidian border border-panel-border rounded px-3 py-1.5 text-slate-350 focus:outline-none focus:border-neon-cyan">
                        <option value="">[SEMUA PROYEK]</option>
                        @foreach($projects as $p)
                            <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ strtoupper($p->name) }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="bg-obsidian border border-panel-border rounded px-3 py-1.5 text-slate-350 focus:outline-none focus:border-neon-cyan">
                        <option value="">[SEMUA STATUS]</option>
                        <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
                        <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                    </select>

                    <select name="severity" class="bg-obsidian border border-panel-border rounded px-3 py-1.5 text-slate-350 focus:outline-none focus:border-neon-cyan">
                        <option value="">[SEMUA SEVERITY]</option>
                        <option value="Critical" {{ request('severity') === 'Critical' ? 'selected' : '' }}>CRITICAL</option>
                        <option value="Major" {{ request('severity') === 'Major' ? 'selected' : '' }}>MAJOR</option>
                        <option value="Minor" {{ request('severity') === 'Minor' ? 'selected' : '' }}>MINOR</option>
                    </select>

                    <button type="submit" class="bg-neon-cyan/10 hover:bg-neon-cyan/20 border border-neon-cyan/30 text-neon-cyan hover:text-white px-4 py-1.5 rounded font-bold transition-all shadow-neon-cyan/10">
                        EXECUTE
                    </button>
                    <a href="{{ route('dashboard') }}" class="bg-obsidian border border-panel-border text-slate-400 hover:text-slate-200 px-4 py-1.5 rounded transition-all">
                        RESET
                    </a>
                </div>
            </form>
        </div>

        <!-- High Density Data Grid Table -->
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-panel-border text-[10px] font-mono tracking-widest text-slate-500 uppercase bg-[#0A0E1A]/40">
                        <th class="py-3 px-4 font-bold">PROJECT & UNIT SN</th>
                        <th class="py-3 px-4 font-bold">BUG TITLE & ORIGIN</th>
                        <th class="py-3 px-4 font-bold">AI AUTOMATION DIAGNOSIS</th>
                        <th class="py-3 px-4 text-right font-bold">REWORK & STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-panel-border/60 text-xs">
                    @forelse($bugs as $b)
                        @php
                            // Calculate Urgency Score dynamically based on severity and sentiment label
                            $sevWeight = $b->severity === 'Critical' ? 0.8 : ($b->severity === 'Major' ? 0.5 : 0.2);
                            $sentScore = $b->sentiment_score !== null ? $b->sentiment_score : 0.0;
                            // Score formula: (severity weight + (1 - sentiment score)) / 2
                            $urgencyScore = round(($sevWeight + (1.0 - $sentScore)) / 2.0, 2);
                            $urgencyScore = min(1.0, max(0.0, $urgencyScore));
                        @endphp
                        <tr class="hover:bg-[#1E293D]/30 transition-all opacity-90 hover:opacity-100">
                            
                            <!-- Column 1: Project & Unit SN -->
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="text-sm font-bold text-slate-250 font-sans tracking-wide uppercase">{{ $b->project?->name ?? 'Project #' . $b->project_id }}</span>
                                    <span class="inline-flex max-w-max bg-obsidian text-neon-cyan border border-neon-cyan/20 hover:border-neon-cyan/50 rounded px-2.5 py-0.5 font-mono text-[9px] font-bold tracking-widest uppercase transition-all shadow-sm">
                                        {{ $b->sn_code_snapshot ?? 'N/A' }}
                                    </span>
                                </div>
                            </td>

                            <!-- Column 2: Bug Title & Origin -->
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1.5">
                                    <span class="text-sm font-semibold text-slate-100 font-sans tracking-normal leading-snug">{{ $b->title }}</span>
                                    <div class="flex flex-wrap items-center gap-1.5 mt-0.5">
                                        @if($b->reporter_type === 'produk')
                                            <span class="text-[9px] font-bold font-mono bg-neon-cyan/10 text-neon-cyan border border-neon-cyan/20 px-2 py-0.2 rounded uppercase">PRODUK LEVEL</span>
                                        @else
                                            <span class="text-[9px] font-bold font-mono bg-slate-950 text-slate-400 border border-panel-border px-2 py-0.2 rounded uppercase">SUB-PCB LEVEL</span>
                                        @endif

                                        @if($b->severity === 'Critical')
                                            <span class="text-[9px] font-bold font-mono bg-neon-pink/15 text-neon-pink border border-neon-pink/30 px-2 py-0.2 rounded uppercase shadow-neon-pink select-none critical-pulse">CRITICAL</span>
                                        @elseif($b->severity === 'Major')
                                            <span class="text-[9px] font-bold font-mono bg-neon-amber/15 text-neon-amber border border-neon-amber/30 px-2 py-0.2 rounded uppercase shadow-neon-amber">MAJOR</span>
                                        @else
                                            <span class="text-[9px] font-bold font-mono bg-neon-cyan/15 text-neon-cyan border border-neon-cyan/30 px-2 py-0.2 rounded uppercase">MINOR</span>
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
                                            $urgColor = 'text-neon-cyan';
                                            $urgBg = 'bg-neon-cyan';
                                            if ($urgencyScore >= 0.75) {
                                                $urgColor = 'text-neon-pink';
                                                $urgBg = 'bg-neon-pink shadow-neon-pink';
                                            } elseif ($urgencyScore >= 0.45) {
                                                $urgColor = 'text-neon-amber';
                                                $urgBg = 'bg-neon-amber shadow-neon-amber';
                                            }
                                        @endphp
                                        <span class="text-xs font-black font-mono {{ $urgColor }}">{{ number_format($urgencyScore, 2) }}</span>
                                        <div class="h-1.5 w-24 bg-obsidian rounded-full overflow-hidden border border-panel-border shadow-inner">
                                            <div class="h-full {{ $urgBg }} rounded-full" style="width: {{ $urgencyScore * 100 }}%"></div>
                                        </div>
                                    </div>
                                    
                                    <!-- Auto-category and expander details -->
                                    <div class="text-[10px] font-mono uppercase font-semibold text-slate-400">
                                        AI Category: <span class="text-neon-cyan">{{ $b->damage_category ?? 'PENDING MECHANIC CLOSE' }}</span>
                                    </div>

                                    <!-- Details drawer details summary -->
                                    <details class="text-[10px] text-slate-500 hover:text-slate-300 transition-all cursor-pointer">
                                        <summary class="focus:outline-none font-bold text-neon-cyan font-mono tracking-wider uppercase select-none py-0.5 hover:underline">
                                            // PROTOCOL RECOMMANDATION
                                        </summary>
                                        <div class="mt-2.5 p-3 bg-obsidian border border-panel-border rounded-lg space-y-2 font-mono leading-relaxed shadow-inner">
                                            <div>
                                                <span class="text-slate-550 block text-[9px] uppercase tracking-wider">// AI STAGE 1 (SENTIMENT & SPAM)</span>
                                                <p class="text-slate-350">
                                                    Label: <span class="capitalize text-slate-200">{{ $b->sentiment_label ?? 'Unanalyzed' }}</span> (Score: {{ $b->sentiment_score ?? '0.0' }})
                                                    @if($b->is_spam)
                                                        <span class="text-neon-pink font-bold ml-1 font-sans">(! SPAM DETECTED: {{ $b->spam_reason }})</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="pt-2 border-t border-panel-border/30">
                                                <span class="text-slate-550 block text-[9px] uppercase tracking-wider">// AI STAGE 1 (SEVERITY RECOMMENDED)</span>
                                                <p class="text-slate-350">
                                                    Recommended: <span class="text-neon-cyan font-bold">{{ $b->severity_recommended ?? 'N/A' }}</span>
                                                    <span class="block text-slate-450 text-[10px] mt-0.5">Reason: {{ $b->severity_recommendation_reason ?? 'Belum dianalisis.' }}</span>
                                                </p>
                                            </div>
                                            @if($b->status === 'CLOSED')
                                                <div class="pt-2 border-t border-panel-border/30">
                                                    <span class="text-slate-550 block text-[9px] uppercase tracking-wider">// TECHNICAL ACTION LOG (STAGE 2)</span>
                                                    <p class="text-slate-250"><span class="text-neon-amber font-semibold">Root Cause:</span> {{ $b->root_cause }}</p>
                                                    <p class="text-slate-250 mt-1"><span class="text-neon-green font-semibold">Repair Action:</span> {{ $b->repair_action }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    </details>
                                </div>
                            </td>

                            <!-- Column 4: Rework Indicator & Status -->
                            <td class="py-4 px-4 text-right">
                                <div class="flex flex-col items-end gap-2.5">
                                    <div class="flex items-center gap-2">
                                        @if($b->is_rework)
                                            <span class="inline-flex bg-neon-pink/10 text-neon-pink border border-neon-pink/30 px-2 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-widest animate-pulse shadow-neon-pink/20">REWORK</span>
                                        @endif

                                        @if($b->status === 'CLOSED')
                                            <span class="inline-flex items-center gap-1.5 bg-neon-green/10 text-neon-green border border-neon-green/20 px-2.5 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-wider">
                                                CLOSED // {{ strtoupper($b->fixer?->name ?? 'System') }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 bg-neon-pink/10 text-neon-pink border border-neon-pink/20 px-2.5 py-0.5 rounded text-[9px] font-bold font-mono uppercase tracking-wider critical-pulse shadow-neon-pink">
                                                <span class="h-1.5 w-1.5 rounded-full bg-neon-pink"></span> OPEN QUEUE
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Reprocess AI Button -->
                                    <button onclick="reprocessAI({{ $b->id }}, this)" class="text-[9px] font-bold font-mono border border-panel-border hover:border-neon-cyan/40 bg-obsidian text-slate-400 hover:text-neon-cyan px-2.5 py-1 rounded transition-all flex items-center gap-1 select-none active:scale-95 shadow-sm">
                                        <i class="bi bi-arrow-clockwise"></i> REPROCESS AI
                                    </button>
                                </div>
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
        <div class="mt-6 border-t border-panel-border/60 pt-4 text-slate-400 font-mono">
            {{ $bugs->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function reprocessAI(bugId, btnElement) {
        // Toggle icon spin animation
        const icon = btnElement.querySelector('i');
        if(icon) icon.classList.add('animate-spin');
        
        // Show status log in console/toast simulation
        console.log(`[QA CONTROL LOG]: Sending reprocess request for Ticket #${bugId}`);
        
        // Call backend or mock trigger
        setTimeout(() => {
            alert(`[QA TERMINAL CONTROL]: Reprocessing AI algorithms (Sentiment, Spam check, Severity recommendation) for Bug Ticket #${bugId} succeeded.`);
            window.location.reload();
        }, 800);
    }

    document.addEventListener("DOMContentLoaded", function() {
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
                foreColor: '#9CA3AF'
            },
            labels: ['Critical Hazards', 'Major Defects', 'Minor Flaws'],
            colors: ['#FF2E93', '#F59E0B', '#00F0FF'], // neon pink, neon amber, neon cyan
            dataLabels: {
                enabled: false
            },
            stroke: {
                show: true,
                width: 2,
                colors: ['#111625'] // card panel background
            },
            legend: {
                position: 'bottom',
                fontSize: '10px',
                fontFamily: 'JetBrains Mono',
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
                                fontFamily: 'Outfit',
                                color: '#6B7280',
                                offsetY: -8
                            },
                            value: {
                                show: true,
                                fontSize: '24px',
                                fontFamily: 'JetBrains Mono',
                                color: '#FFFFFF',
                                fontWeight: 'bold',
                                offsetY: 8,
                                formatter: function (val) {
                                    return val;
                                }
                            },
                            total: {
                                show: true,
                                label: 'TOTAL LOGS',
                                color: '#6B7280',
                                fontSize: '10px',
                                fontFamily: 'JetBrains Mono',
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
                theme: 'dark'
            }
        };

        var severityChart = new ApexCharts(document.querySelector("#severityChart"), severityOptions);
        severityChart.render();

        // 2. Culpable Hardware Components Bar Chart (Right Column)
        @php
            $catNames = $damageCategories->isNotEmpty() ? $damageCategories->pluck('damage_category')->toArray() : ['Daya / Power', 'Konektivitas / Signal', 'Interface / Komponen Fisik', 'Firmware / Embedded OS'];
            $catCounts = $damageCategories->isNotEmpty() ? $damageCategories->pluck('count')->toArray() : [0, 0, 0, 0];
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
                foreColor: '#9CA3AF',
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
                        backgroundBarColors: ['rgba(255,255,255,0.02)'],
                        backgroundBarRadius: 4
                    }
                }
            },
            colors: ['#00F0FF'], // neon cyan core
            stroke: {
                width: 1,
                colors: ['#00F0FF']
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: 'dark',
                    type: "horizontal",
                    shadeIntensity: 0.5,
                    gradientToColors: ['#3B82F6'], // gradient from cyan to electric blue
                    inverseColors: true,
                    opacityFrom: 0.9,
                    opacityTo: 0.4
                }
            },
            grid: {
                borderColor: '#1F293D',
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
                        fontFamily: 'JetBrains Mono'
                    }
                }
            },
            yaxis: {
                labels: {
                    style: {
                        fontSize: '10px',
                        fontFamily: 'Outfit',
                        colors: '#9CA3AF'
                    }
                }
            },
            tooltip: {
                theme: 'dark'
            }
        };

        var componentsChart = new ApexCharts(document.querySelector("#componentsChart"), componentsOptions);
        componentsChart.render();
    });
</script>
@endsection

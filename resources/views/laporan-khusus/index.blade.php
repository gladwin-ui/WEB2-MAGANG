@extends('layouts.app')

@section('title', 'Laporan Khusus')

@section('content')
<div class="max-w-7xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-clipboard-data-fill text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Laporan Khusus</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Analisis Masalah & Root Cause Tersering per Produk</p>
            </div>
        </div>
    </div>

    {{-- ═══ BAGIAN 1: OVERVIEW — Rework Rate antar Produk (independent dari dropdown) ═══ --}}
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
        <h3 class="text-base font-bold text-text-primary mb-1 flex items-center gap-2">
            <i class="bi bi-arrow-repeat text-accent"></i> Rework Rate Tertinggi (Top 5 Produk)
        </h3>
        <p class="text-xs text-text-muted mb-4">Produk dengan persentase pengerjaan ulang terbesar — minimal 3 laporan agar tidak menyesatkan</p>
        @if($reworkRates->count() > 0)
            <div class="relative" style="height: 260px;">
                <div id="chartReworkRate" class="w-full h-full"></div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                <p class="text-sm text-text-muted">Belum ada produk dengan data rework yang cukup (minimal 3 laporan).</p>
            </div>
        @endif
    </div>

    {{-- ═══ BAGIAN 2: DETAIL PER PRODUK (mengikuti dropdown) ═══ --}}
    <div class="pt-2">
        <h2 class="text-lg font-bold text-text-primary flex items-center gap-2">
            <i class="bi bi-zoom-in text-accent"></i> Detail per Produk
        </h2>
    </div>

    {{-- Pilih Produk --}}
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
        <form method="GET" action="{{ route('laporan-khusus.index') }}" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="w-full sm:w-80">
                <label for="product_id" class="block text-xs font-semibold text-text-secondary mb-2">
                    <i class="bi bi-box-seam text-accent"></i> Pilih Produk
                </label>
                <select id="product_id" name="product_id" onchange="this.form.submit()"
                        class="w-full rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary font-semibold focus:ring-1 focus:ring-blue-500">
                    @forelse($products as $product)
                        <option value="{{ $product->id }}" {{ $selectedProductId == $product->id ? 'selected' : '' }}>
                            {{ $product->name ?? 'Project #' . $product->id }}
                        </option>
                    @empty
                        <option value="" disabled selected>Belum ada produk dengan laporan bug</option>
                    @endforelse
                </select>
            </div>
            @if($selectedProductId)
                <p class="text-xs text-text-muted pb-2">
                    <i class="bi bi-clipboard-data"></i>
                    {{ number_format($totalBugs) }} laporan dianalisis untuk produk ini
                </p>
            @endif
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Visualisasi 1: Masalah Tersering --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-1 flex items-center gap-2">
                <i class="bi bi-bug text-accent"></i> Masalah Tersering (Top 5)
            </h3>
            <p class="text-xs text-text-muted mb-4">Dikelompokkan dari judul & deskripsi laporan</p>
            @if(count($masalahTop5) > 0)
                <div class="relative" style="height: 280px;">
                    <div id="chartMasalah" class="w-full h-full"></div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                    <p class="text-sm text-text-muted">Belum ada data masalah untuk produk ini.</p>
                </div>
            @endif
        </div>

        {{-- Visualisasi 2: Root Cause Tersering --}}
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-1 flex items-center gap-2">
                <i class="bi bi-search text-accent"></i> Root Cause Tersering (Top 5)
            </h3>
            <p class="text-xs text-text-muted mb-4">Dikelompokkan dari akar masalah laporan closed</p>
            @if(count($rootCauseTop5) > 0)
                <div class="relative" style="height: 280px;">
                    <div id="chartRootCause" class="w-full h-full"></div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-16 text-center">
                    <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                    <p class="text-sm text-text-muted">Belum ada data root cause untuk produk ini.</p>
                </div>
            @endif
        </div>

    </div>

    {{-- Severity Mix produk terpilih --}}
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
        <h3 class="text-base font-bold text-text-primary mb-1 flex items-center gap-2">
            <i class="bi bi-exclamation-triangle text-accent"></i> Distribusi Severity Produk Ini
        </h3>
        <p class="text-xs text-text-muted mb-4">Komposisi tingkat keparahan laporan untuk produk terpilih</p>
        @php $totalSeverity = $severityMix->sum(); @endphp
        @if($totalSeverity > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                <div class="relative" style="height: 240px;">
                    <div id="chartSeverityMix" class="w-full h-full"></div>
                </div>
                {{-- Keterangan angka --}}
                <div class="space-y-3 max-w-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-border-strong">
                        <span class="flex items-center gap-2 text-sm text-text-secondary">
                            <span class="w-3 h-3 rounded-full" style="background:#DC2626"></span> Critical
                        </span>
                        <span class="font-bold text-text-primary font-mono">{{ $severityMix['Critical'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-border-strong">
                        <span class="flex items-center gap-2 text-sm text-text-secondary">
                            <span class="w-3 h-3 rounded-full" style="background:#CA8A04"></span> Major
                        </span>
                        <span class="font-bold text-text-primary font-mono">{{ $severityMix['Major'] }}</span>
                    </div>
                    <div class="flex items-center justify-between pb-3 border-b border-border-strong">
                        <span class="flex items-center gap-2 text-sm text-text-secondary">
                            <span class="w-3 h-3 rounded-full" style="background:#16A34A"></span> Minor
                        </span>
                        <span class="font-bold text-text-primary font-mono">{{ $severityMix['Minor'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-text-secondary">Total</span>
                        <span class="font-bold text-text-primary font-mono">{{ $totalSeverity }}</span>
                    </div>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                <p class="text-sm text-text-muted">Produk ini belum punya data severity.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
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

        // Gradasi biru brand — bar teratas paling pekat
        const barColorsLight = ['#0A1931', '#1A3D63', '#4A7FA7', '#6B9CC3', '#B3CFE5'];
        const barColorsDark  = ['#B3CFE5', '#8FB3D4', '#6B9CC3', '#4A7FA7', '#2C5A85'];

        const masalahData   = @json($masalahTop5);
        const rootCauseData = @json($rootCauseTop5);

        function makeBarOptions(data, c) {
            const colors = isDarkMode() ? barColorsDark : barColorsLight;
            return {
                series: [{ name: 'Jumlah Laporan', data: data.map(d => d.count) }],
                chart: {
                    type: 'bar', height: 280, background: 'transparent',
                    foreColor: c.textMuted, toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true, borderRadius: 6, barHeight: '60%',
                        distributed: true, dataLabels: { position: 'bottom' }
                    }
                },
                colors: colors,
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    offsetX: 8,
                    style: { fontSize: '11px', fontFamily: 'Inter, sans-serif', colors: [c.textPrimary], fontWeight: 600 },
                    formatter: function(val, opt) {
                        return opt.w.globals.labels[opt.dataPointIndex] + ' — ' + val;
                    },
                    dropShadow: { enabled: false }
                },
                xaxis: {
                    categories: data.map(d => d.label),
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: {
                        style: { fontSize: '11px', fontFamily: 'Inter', colors: c.textMuted },
                        formatter: v => Math.floor(v) === v ? v : ''
                    }
                },
                yaxis: { labels: { show: false } },
                grid: {
                    borderColor: c.gridLine, strokeDashArray: 3,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                },
                legend: { show: false },
                tooltip: {
                    theme: c.tooltipTheme,
                    y: { title: { formatter: () => 'Jumlah Laporan:' } }
                }
            };
        }

        // ── Rework Rate antar produk (horizontal bar, % ) ──
        const reworkData = @json($reworkRates);

        function makeReworkOptions(c) {
            const colors = isDarkMode() ? barColorsDark : barColorsLight;
            return {
                series: [{ name: 'Rework Rate', data: reworkData.map(d => parseFloat(d.rework_rate)) }],
                chart: {
                    type: 'bar', height: 260, background: 'transparent',
                    foreColor: c.textMuted, toolbar: { show: false }
                },
                plotOptions: {
                    bar: {
                        horizontal: true, borderRadius: 6, barHeight: '60%',
                        distributed: true, dataLabels: { position: 'bottom' }
                    }
                },
                colors: colors,
                dataLabels: {
                    enabled: true,
                    textAnchor: 'start',
                    offsetX: 8,
                    style: { fontSize: '11px', fontFamily: 'Inter, sans-serif', colors: [c.textPrimary], fontWeight: 600 },
                    formatter: function(val, opt) {
                        const d = reworkData[opt.dataPointIndex];
                        return (d.name ?? ('Project #' + d.id)) + ' — ' + d.rework_rate + '% (' + d.rework_count + '/' + d.total_bugs + ')';
                    },
                    dropShadow: { enabled: false }
                },
                xaxis: {
                    categories: reworkData.map(d => d.name ?? ('Project #' + d.id)),
                    min: 0, max: 100,
                    axisBorder: { show: false }, axisTicks: { show: false },
                    labels: {
                        style: { fontSize: '11px', fontFamily: 'Inter', colors: c.textMuted },
                        formatter: v => Math.round(v) + '%'
                    }
                },
                yaxis: { labels: { show: false } },
                grid: {
                    borderColor: c.gridLine, strokeDashArray: 3,
                    xaxis: { lines: { show: true } },
                    yaxis: { lines: { show: false } }
                },
                legend: { show: false },
                tooltip: {
                    theme: c.tooltipTheme,
                    y: {
                        title: { formatter: () => 'Rework Rate:' },
                        formatter: function(val, opt) {
                            const d = reworkData[opt.dataPointIndex];
                            return d.rework_rate + '% — ' + d.rework_count + ' dari ' + d.total_bugs + ' bug';
                        }
                    }
                }
            };
        }

        // ── Severity Mix produk terpilih (donut, warna badge standar) ──
        const severityMixData = @json($severityMix);
        const totalSev = Object.values(severityMixData).reduce((a, b) => a + b, 0);

        function makeSeverityMixOptions(c) {
            return {
                series: [severityMixData.Critical, severityMixData.Major, severityMixData.Minor],
                chart: { type: 'donut', height: 240, background: 'transparent', foreColor: c.textMuted },
                labels: ['Critical', 'Major', 'Minor'],
                colors: ['#DC2626', '#CA8A04', '#16A34A'],
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: {
                    position: 'bottom', fontSize: '11px', fontFamily: 'Inter, sans-serif',
                    labels: { colors: c.textMuted },
                    markers: { radius: 4, width: 8, height: 8 },
                    itemMargin: { horizontal: 8, vertical: 3 },
                    formatter: function(name, opts) { return name + ': ' + opts.w.globals.series[opts.seriesIndex]; }
                },
                plotOptions: {
                    pie: { donut: { size: '70%', labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', fontFamily: 'Inter', color: c.textMuted, offsetY: -8 },
                        value: { show: true, fontSize: '26px', fontFamily: 'Inter', color: c.textPrimary, fontWeight: 'bold', offsetY: 8 },
                        total: {
                            show: true, label: 'Total', color: c.textMuted, fontSize: '11px', fontFamily: 'Inter',
                            formatter: function(w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); }
                        }
                    }}}
                },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        let chartMasalah = null, chartRootCause = null, chartReworkRate = null, chartSeverityMix = null;

        function renderCharts() {
            const c = getChartColors();

            if (reworkData.length > 0) {
                if (chartReworkRate) chartReworkRate.destroy();
                chartReworkRate = new ApexCharts(document.querySelector("#chartReworkRate"), makeReworkOptions(c));
                chartReworkRate.render();
            }
            if (masalahData.length > 0) {
                if (chartMasalah) chartMasalah.destroy();
                chartMasalah = new ApexCharts(document.querySelector("#chartMasalah"), makeBarOptions(masalahData, c));
                chartMasalah.render();
            }
            if (rootCauseData.length > 0) {
                if (chartRootCause) chartRootCause.destroy();
                chartRootCause = new ApexCharts(document.querySelector("#chartRootCause"), makeBarOptions(rootCauseData, c));
                chartRootCause.render();
            }
            if (totalSev > 0) {
                if (chartSeverityMix) chartSeverityMix.destroy();
                chartSeverityMix = new ApexCharts(document.querySelector("#chartSeverityMix"), makeSeverityMixOptions(c));
                chartSeverityMix.render();
            }
        }

        renderCharts();
        document.addEventListener('theme-changed', renderCharts);
    });
</script>
@endsection

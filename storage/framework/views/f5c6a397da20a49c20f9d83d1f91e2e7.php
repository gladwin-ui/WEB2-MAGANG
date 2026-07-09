<?php $__env->startSection('title', 'Laporan Khusus'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-7xl mx-auto space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-2 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 shadow-sm"
                 style="background:linear-gradient(135deg,#1A3D63,#4A7FA7)">
                <i class="bi bi-clipboard-data-fill text-xl text-white"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Laporan Khusus</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Analisis Gejala Kerusakan &amp; Akar Penyebab Dominan per Produk</p>
            </div>
        </div>
        <div>
            <a href="<?php echo e(route('laporan-khusus.export', request()->query())); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow transition-all shrink-0">
                <i class="bi bi-file-earmark-excel-fill text-base"></i> Export Excel
            </a>
        </div>
    </div>

    
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card overflow-hidden">
        <div class="flex flex-col sm:flex-row sm:items-start gap-4 p-6 pb-4 border-b border-border-default">
            <div class="flex items-start gap-3 flex-1">
                <div class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center shadow-sm" style="background:#EBF3FB">
                    <i class="bi bi-arrow-repeat text-base" style="color:#1A3D63"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-base font-bold text-text-primary">Rework Rate Tertinggi</h3>
                    <p class="text-xs text-text-muted mt-0.5">Top 5 produk dengan persentase pengerjaan ulang terbesar — minimal 3 laporan agar tidak menyesatkan</p>
                </div>
            </div>
            <?php if($reworkRates->count() > 0): ?>
            <div class="flex-shrink-0 text-right rounded-lg px-4 py-2 border" style="background:#EBF3FB;border-color:#B3CFE5">
                <div class="text-xl font-extrabold" style="color:#1A3D63"><?php echo e($avgRework); ?>%</div>
                <div class="text-xs text-text-muted font-medium whitespace-nowrap">rata-rata rework</div>
                <div class="text-xs text-text-muted"><?php echo e($reworkRates->count()); ?> produk dianalisis</div>
            </div>
            <?php endif; ?>
        </div>

        <div class="p-6">
            <?php if($reworkRates->count() > 0): ?>
                <?php $maxRework = $reworkRates->max('rework_rate') ?: 1; ?>
                <div class="space-y-4">
                    <?php $__currentLoopData = $reworkRates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $rate     = (float) $item->rework_rate;
                            $pct      = round($rate / $maxRework * 100);
                            $isTop    = $i === 0;
                            $barColor = $rate >= 50 ? '#DC2626' : ($rate >= 30 ? '#CA8A04' : '#4A7FA7');
                            $riskBg   = $rate >= 50 ? '#FEE2E2' : ($rate >= 30 ? '#FEF9C3' : '#EBF3FB');
                            $riskText = $rate >= 50 ? '#DC2626' : ($rate >= 30 ? '#CA8A04' : '#1A3D63');
                            $riskLabel= $rate >= 50 ? 'Tinggi'  : ($rate >= 30 ? 'Sedang'  : 'Normal');
                        ?>
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                                 style="background:#EBF3FB;color:#4A7FA7">
                                <?php echo e($i + 1); ?>

                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1.5">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="text-sm font-semibold text-text-primary truncate"><?php echo e($item->name ?? ($item->id ? 'Project #' . $item->id : 'Tanpa Proyek')); ?></span>
                                    </div>
                                    <div class="flex items-center gap-2 flex-shrink-0 ml-2">
                                        <span class="text-xs font-mono text-text-muted"><?php echo e($item->rework_count); ?>/<?php echo e($item->total_bugs); ?></span>
                                        <span class="text-sm font-extrabold" style="color:<?php echo e($barColor); ?>"><?php echo e($rate); ?>%</span>
                                    </div>
                                </div>
                                <div class="h-2.5 rounded-full overflow-hidden" style="background:#EBF3FB">
                                    <div class="h-full rounded-full transition-all duration-700" style="width:<?php echo e($pct); ?>%;background:<?php echo e($barColor); ?>"></div>
                                </div>
                            </div>
                            <div class="flex-shrink-0 flex flex-col items-end gap-1">
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full" style="background:<?php echo e($riskBg); ?>;color:<?php echo e($riskText); ?>"><?php echo e($riskLabel); ?></span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="flex flex-col items-center justify-center py-12 text-center">
                    <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                    <p class="text-sm text-text-muted">Belum ada produk dengan data rework yang cukup (minimal 3 laporan).</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="flex items-center gap-2 pt-2">
        <div class="w-1 h-5 rounded-full" style="background:#1A3D63"></div>
        <h2 class="text-lg font-bold text-text-primary">Detail per Produk</h2>
    </div>

    
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-5">
        <form method="GET" action="<?php echo e(route('laporan-khusus.index')); ?>" class="flex flex-col sm:flex-row sm:items-end gap-4">
            <div class="flex-1 max-w-sm">
                <label for="product_id" class="block text-xs font-semibold text-text-secondary mb-2">
                    <i class="bi bi-box-seam mr-1" style="color:#4A7FA7"></i> Pilih Produk
                </label>
                <select id="product_id" name="product_id" onchange="this.form.submit()"
                        class="w-full sm:w-80 rounded-lg border border-border-default bg-bg-card text-text-primary px-4 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-blue-400">
                    
                    <option value="all" <?php echo e($selectedProductId === 'all' ? 'selected' : ''); ?>>
                        Semua Produk
                    </option>
                    <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <option value="<?php echo e($product->id); ?>" <?php echo e((string)$selectedProductId === (string)$product->id ? 'selected' : ''); ?>>
                            <?php echo e($product->name ?? ($product->id ? 'Project #' . $product->id : 'Tanpa Proyek')); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <option value="" disabled>Belum ada produk dengan laporan bug</option>
                    <?php endif; ?>
                </select>
            </div>
            <div class="pb-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold"
                      style="background:#EBF3FB;color:#1A3D63">
                    <i class="bi bi-clipboard-data text-xs"></i>
                    <?php if($selectedProductId === 'all'): ?>
                        <?php echo e(number_format($totalLaporan)); ?> laporan dari semua produk
                    <?php else: ?>
                        <?php echo e(number_format($totalLaporan)); ?> laporan dianalisis
                    <?php endif; ?>
                    <?php if($isSampled): ?> (sampel <?php echo e(number_format($sampleSize)); ?>) <?php endif; ?>
                </span>
            </div>
        </form>
    </div>

    <?php if($isSampled): ?>
        <div class="mt-4 mb-2 flex items-start gap-2.5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 p-4">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <p class="text-sm text-amber-700 dark:text-amber-300">
                Analisis berdasarkan sampel acak <strong><?php echo e(number_format($sampleSize)); ?></strong>
                dari total <strong><?php echo e(number_format($totalLaporan)); ?></strong> laporan,
                untuk menjaga performa. Hasil mencerminkan tren umum, bukan hitungan penuh.
            </p>
        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">

        
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card overflow-hidden">
            <div class="flex items-start gap-3 p-5 pb-4 border-b border-border-default">
                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center shadow-sm" style="background:#EBF3FB">
                    <i class="bi bi-bug-fill text-sm" style="color:#1A3D63"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-text-primary">Gejala Kerusakan Dominan <span class="font-normal text-text-muted">(Top 5)</span></h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Dikelompokkan secara analitik dari judul &amp; deskripsi laporan
                        <?php if($totalLaporan > 0): ?>
                            - dari <span class="font-semibold" style="color:#1A3D63"><?php echo e(number_format($totalLaporan)); ?></span> <?php if($selectedProductId === 'all'): ?> laporan semua produk <?php else: ?> laporan produk ini <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <div class="p-5">
                <?php if(count($masalahTop5) > 0): ?>
                    <?php
                        $masalahMax   = collect($masalahTop5)->max('count') ?: 1;
                        $masalahTotal = collect($masalahTop5)->sum('count');
                    ?>
                    <div class="space-y-3.5">
                        <?php $__currentLoopData = $masalahTop5; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pct   = round($item['count'] / $masalahMax * 100);
                                $pctOf = $masalahTotal > 0 ? round($item['count'] / $masalahTotal * 100) : 0;
                                $isTop = $i === 0;
                                $barBg = $isTop ? '#1A3D63' : '#4A7FA7';
                            ?>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                                     style="background:#EBF3FB;color:#4A7FA7">
                                    <?php echo e($i + 1); ?>

                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1.5 gap-2">
                                        <span class="text-sm <?php echo e($isTop ? 'font-bold' : 'font-medium'); ?> text-text-primary truncate" title="<?php echo e($item['label']); ?>"><?php echo e($item['label']); ?></span>
                                        <span class="flex-shrink-0 text-xs font-semibold text-text-muted"><?php echo e($item['count']); ?> <span class="text-[10px]">(<?php echo e($pctOf); ?>%)</span></span>
                                    </div>
                                    <div class="h-2 rounded-full overflow-hidden" style="background:#EBF3FB">
                                        <div class="h-full rounded-full transition-all duration-700" style="width:<?php echo e($pct); ?>%;background:<?php echo e($barBg); ?>"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-14 text-center">
                        <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                        <p class="text-sm text-text-muted">Belum ada data masalah untuk produk ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card overflow-hidden">
            <div class="flex items-start gap-3 p-5 pb-4 border-b border-border-default">
                <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center shadow-sm" style="background:#EBF3FB">
                    <i class="bi bi-search text-sm" style="color:#1A3D63"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-text-primary">Akar Penyebab Kerusakan Dominan <span class="font-normal text-text-muted">(Top 5)</span></h3>
                    <p class="text-xs text-text-muted mt-0.5">
                        Dikelompokkan secara analitik dari akar masalah (root cause) laporan
                        <?php if($totalLaporan > 0): ?>
                            - dari <span class="font-semibold" style="color:#1A3D63"><?php echo e(number_format($totalLaporan)); ?></span> <?php if($selectedProductId === 'all'): ?> laporan semua produk <?php else: ?> laporan produk ini <?php endif; ?>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="p-5">
                
                <div class="flex items-start gap-2.5 mb-4 rounded-lg px-3 py-2.5 text-xs"
                     style="background:#FFFBEB;border:1px solid #FDE68A;color:#92400E">
                    <i class="bi bi-info-circle-fill flex-shrink-0 mt-0.5" style="color:#CA8A04"></i>
                    <span>Data root cause saat ini masih berupa placeholder. Visualisasi akan bermakna setelah data produksi asli terisi.</span>
                </div>
                <?php if(count($rootCauseTop5) > 0): ?>
                    <?php
                        $rcMax   = collect($rootCauseTop5)->max('count') ?: 1;
                        $rcTotal = collect($rootCauseTop5)->sum('count');
                    ?>
                    <div class="space-y-3.5">
                        <?php $__currentLoopData = $rootCauseTop5; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $pct   = round($item['count'] / $rcMax * 100);
                                $pctOf = $rcTotal > 0 ? round($item['count'] / $rcTotal * 100) : 0;
                                $isTop = $i === 0;
                                $barBg = $isTop ? '#1A3D63' : '#4A7FA7';
                            ?>
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold"
                                     style="background:#EBF3FB;color:#4A7FA7">
                                    <?php echo e($i + 1); ?>

                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1.5 gap-2">
                                        <span class="text-sm <?php echo e($isTop ? 'font-bold' : 'font-medium'); ?> text-text-primary truncate" title="<?php echo e($item['label']); ?>"><?php echo e($item['label']); ?></span>
                                        <span class="flex-shrink-0 text-xs font-semibold text-text-muted"><?php echo e($item['count']); ?> <span class="text-[10px]">(<?php echo e($pctOf); ?>%)</span></span>
                                    </div>
                                    <div class="h-2 rounded-full overflow-hidden" style="background:#EBF3FB">
                                        <div class="h-full rounded-full transition-all duration-700" style="width:<?php echo e($pct); ?>%;background:<?php echo e($barBg); ?>"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                        <p class="text-sm text-text-muted">Belum ada data root cause untuk produk ini.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card overflow-hidden">
        <div class="flex items-start gap-3 p-5 pb-4 border-b border-border-default">
            <div class="flex-shrink-0 w-9 h-9 rounded-lg flex items-center justify-center shadow-sm" style="background:#EBF3FB">
                <i class="bi bi-exclamation-triangle-fill text-sm" style="color:#CA8A04"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-bold text-text-primary">Distribusi Severity</h3>
                <p class="text-xs text-text-muted mt-0.5">Komposisi tingkat keparahan laporan untuk produk terpilih</p>
            </div>
        </div>

        <?php $totalSeverity = $severityMix->sum(); ?>
        <?php if($totalSeverity > 0): ?>
            <div class="p-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 items-center">
                    <div class="relative" style="height: 240px;">
                        <div id="chartSeverityMix" class="w-full h-full"></div>
                    </div>
                    <div class="space-y-3 max-w-xs">
                        <?php
                            $sevItems = [
                                ['label' => 'Critical', 'color' => '#DC2626', 'bg' => '#FEE2E2', 'val' => $severityMix['Critical']],
                                ['label' => 'Major',    'color' => '#CA8A04', 'bg' => '#FEF9C3', 'val' => $severityMix['Major']],
                                ['label' => 'Minor',    'color' => '#16A34A', 'bg' => '#DCFCE7', 'val' => $severityMix['Minor']],
                            ];
                        ?>
                        <?php $__currentLoopData = $sevItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sev): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php $pctSev = $totalSeverity > 0 ? round($sev['val'] / $totalSeverity * 100) : 0; ?>
                            <div class="flex items-center justify-between py-2.5 border-b border-border-strong last:border-0">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:<?php echo e($sev['color']); ?>"></span>
                                    <span class="text-sm font-medium text-text-secondary"><?php echo e($sev['label']); ?></span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                                          style="background:<?php echo e($sev['bg']); ?>;color:<?php echo e($sev['color']); ?>"><?php echo e($pctSev); ?>%</span>
                                    <span class="font-bold text-text-primary font-mono w-8 text-right"><?php echo e($sev['val']); ?></span>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center justify-between pt-1">
                            <span class="text-sm font-semibold text-text-secondary">Total</span>
                            <span class="font-extrabold text-text-primary font-mono text-base"><?php echo e($totalSeverity); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="flex flex-col items-center justify-center py-12 text-center p-5">
                <i class="bi bi-inbox text-3xl text-text-muted mb-2"></i>
                <p class="text-sm text-text-muted">Produk ini belum punya data severity.</p>
            </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        function isDarkMode() { return document.body.classList.contains('dark'); }
        function getChartColors() {
            const dark = isDarkMode();
            return {
                textMuted:    dark ? '#7FA3C4' : '#4A7FA7',
                textPrimary:  dark ? '#F6FAFD' : '#0A1931',
                tooltipTheme: dark ? 'dark' : 'light',
            };
        }

        const severityMixData = <?php echo json_encode($severityMix->toArray(), 15, 512) ?>;
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
                        name:  { show: true, fontSize: '12px', fontFamily: 'Inter', color: c.textMuted, offsetY: -8 },
                        value: { show: true, fontSize: '26px', fontFamily: 'Inter', color: c.textPrimary, fontWeight: 'bold', offsetY: 8 },
                        total: { show: true, label: 'Total', color: c.textMuted, fontSize: '11px', fontFamily: 'Inter',
                                 formatter: function(w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); } }
                    }}}
                },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        let chartSeverityMix = null;
        function renderCharts() {
            const c = getChartColors();
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\MAGANG\WEB2-MAGANG\resources\views/laporan-khusus/index.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'Laporan Umum — Dashboard Analitik'); ?>

<?php $__env->startSection('content'); ?>
<?php if(!$hasImportedData): ?>

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
    <a href="<?php echo e(route('import.upload')); ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-bold text-white shadow-sm transition-colors" style="background-color: #0046BF;">
        <i class="bi bi-cloud-upload"></i> Import Data .sql Sekarang
    </a>
</div>
<?php else: ?>
<?php
    // Mapped top projects (existing logic preserved)
    $mappedTopProjects = $topProjects->map(function($tp) {
        return (object)[
            'project_name' => $tp->project?->name ?? ($tp->project_id ? 'Project #' . $tp->project_id : 'Project #N/A'),

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
?>

<div class="space-y-6">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-grid-1x2-fill text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Laporan Umum</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Quality Control & AI Diagnostics — Hariff Defense</p>
            </div>
        </div>
        <a href="<?php echo e(route('dashboard.export', request()->query())); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow-sm hover:shadow transition-all shrink-0">
            <i class="bi bi-file-earmark-excel-fill text-base"></i> Export Excel
        </a>
    </div>

    <!-- Executive KPI Summary Strip -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Bug -->
        <div class="bg-bg-secondary border border-border-default rounded-xl p-4 shadow-card hover:border-blue-500/50 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Total Defect</span>
                <div class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center shrink-0">
                    <i class="bi bi-bug-fill text-blue-500 text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-text-primary font-mono tracking-tight"><?php echo e(number_format($totalBugs)); ?></div>
                <div class="text-[11px] text-text-muted mt-1">Seluruh laporan tercatat</div>
            </div>
        </div>

        <!-- Card 2: Bug Open -->
        <div class="bg-bg-secondary border border-border-default rounded-xl p-4 shadow-card hover:border-amber-500/50 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Dalam Perbaikan</span>
                <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center shrink-0">
                    <i class="bi bi-exclamation-circle-fill text-amber-500 text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-amber-500 font-mono tracking-tight"><?php echo e(number_format($openBugs)); ?></div>
                <div class="text-[11px] text-text-muted mt-1">Status Open / Aktif</div>
            </div>
        </div>

        <!-- Card 3: Bug Closed -->
        <div class="bg-bg-secondary border border-border-default rounded-xl p-4 shadow-card hover:border-emerald-500/50 transition-all flex flex-col justify-between">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-text-secondary uppercase tracking-wider">Telah Selesai</span>
                <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center shrink-0">
                    <i class="bi bi-check-circle-fill text-emerald-500 text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-emerald-500 font-mono tracking-tight"><?php echo e(number_format($closedBugs)); ?></div>
                <div class="text-[11px] text-text-muted mt-1">Status Closed / Resolved</div>
            </div>
        </div>

        <!-- Card 4: Rework Rate (KPI Manufaktur) -->
        <div class="bg-bg-secondary border border-rose-500/30 rounded-xl p-4 shadow-card hover:border-rose-500 transition-all flex flex-col justify-between relative overflow-hidden">
            <div class="absolute -right-4 -bottom-4 w-16 h-16 bg-rose-500/5 rounded-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-rose-500 uppercase tracking-wider flex items-center gap-1">
                    <i class="bi bi-star-fill text-[10px]"></i> Rework Rate
                </span>
                <div class="w-8 h-8 rounded-lg bg-rose-500/10 flex items-center justify-center shrink-0">
                    <i class="bi bi-tools text-rose-500 text-base"></i>
                </div>
            </div>
            <div>
                <div class="text-2xl font-black text-rose-500 font-mono tracking-tight"><?php echo e($reworkRate); ?><span class="text-lg">%</span></div>
                <div class="text-[11px] text-text-muted mt-1">
                    <strong class="text-text-secondary"><?php echo e(number_format($reworkCount ?? 0)); ?> dari <?php echo e(number_format($totalBugs)); ?></strong> kasus butuh perakitan ulang
                </div>
            </div>
        </div>
    </div>

    <!-- 4-Component Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        
        
        
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-pie-chart text-accent"></i> Jumlah Bug Terdaftar
            </h3>

            <div class="relative" style="height: 260px;">
                <div id="chartJumlahBug" class="w-full h-full"></div>
            </div>

            
            <div class="grid grid-cols-3 gap-4 mt-6 pt-5 border-t border-border-strong">
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-text-primary"><?php echo e(number_format($totalBugs)); ?></div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Total Bug</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-amber-600"><?php echo e(number_format($openBugs)); ?></div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Bug Open</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-extrabold text-green-600"><?php echo e(number_format($closedBugs)); ?></div>
                    <div class="text-xs text-text-secondary mt-1 font-medium">Bug Closed</div>
                </div>
            </div>
        </div>

        
        
        
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
                    <tbody class="divide-y divide-border-default">
                        <?php $__empty_1 = true; $__currentLoopData = $mappedTopProjects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $tp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-bg-tertiary transition-colors">
                                <td class="px-3 py-3 text-xs text-text-muted font-mono"><?php echo e($idx + 1); ?></td>
                                <td class="px-3 py-3 font-medium text-text-primary"><?php echo e($tp->project_name); ?></td>
                                <td class="px-3 py-3 text-right font-bold text-accent font-mono"><?php echo e($tp->bug_count); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="3" class="py-12 text-center text-text-muted text-sm">
                                    <i class="bi bi-inbox text-xl block mb-1"></i> Belum ada data proyek
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        
        
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

        
        
        
        <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <h3 class="text-base font-bold text-text-primary mb-4 flex items-center gap-2">
                <i class="bi bi-graph-up-arrow text-accent"></i> Tren Volume Laporan (7 Hari)
            </h3>
            <div class="min-h-[280px]">
                <div id="volumeChart" class="w-full"></div>
            </div>
        </div>

        
        
        
        <div class="col-span-1 lg:col-span-2 bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6 pb-4 border-b border-border-strong">
                <h3 class="text-base font-bold text-text-primary flex items-center gap-2">
                    <i class="bi bi-diagram-3 text-accent"></i> Proporsi Defect (Assembly Stage)
                </h3>
                <div class="flex items-center gap-2 text-xs font-semibold text-text-secondary bg-bg-tertiary px-3 py-1.5 rounded-lg shrink-0">
                    <i class="bi bi-database-check text-accent"></i> <?php echo e(count($assemblyStageMap ?? [])); ?> Kategori
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                
                <div class="lg:col-span-5 relative flex items-center justify-center min-h-[260px]">
                    <div id="chartAssemblyStage" class="w-full"></div>
                </div>

                
                <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <?php
                        $totalStageCount = array_sum($assemblyStageMap ?? []) ?: 1;
                        $stageColors = ['#1A3D63', '#4A7FA7', '#E8A33D', '#3E9B6F', '#D64550', '#8B5CF6'];
                        $idxColor = 0;
                    ?>
                    <?php $__empty_1 = true; $__currentLoopData = ($assemblyStageMap ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stageName => $stageCount): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $pct = round(($stageCount / $totalStageCount) * 100, 1);
                            $color = $stageColors[$idxColor % count($stageColors)];
                            $idxColor++;
                        ?>
                        <div class="p-4 rounded-xl border border-border-default bg-bg-primary flex flex-col justify-between">
                            <div class="flex items-start justify-between gap-2 mb-3">
                                <div class="flex items-center gap-2 font-bold text-text-primary text-sm">
                                    <span class="w-3 h-3 rounded-full shrink-0" style="background-color: <?php echo e($color); ?>;"></span>
                                    <span class="truncate"><?php echo e($stageName); ?></span>
                                </div>
                                <span class="text-xs font-mono font-extrabold px-2 py-0.5 rounded bg-bg-tertiary text-text-primary">
                                    <?php echo e($pct); ?>%
                                </span>
                            </div>
                            <div class="flex items-end justify-between mt-2">
                                <div>
                                    <span class="text-2xl font-extrabold text-text-primary font-mono"><?php echo e(number_format($stageCount)); ?></span>
                                    <span class="text-xs text-text-muted ml-1 font-medium">Laporan</span>
                                </div>
                                <div class="w-24 bg-border-default h-1.5 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-500" style="width: <?php echo e($pct); ?>%; background-color: <?php echo e($color); ?>;"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="col-span-2 py-8 text-center text-text-muted text-sm">
                            Belum ada data kategori perakitan
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    
    
    
    <div class="bg-bg-secondary border border-border-default rounded-xl shadow-card p-6">
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-6 pb-4 border-b border-border-strong">
            <div>
                <h2 class="text-sm font-bold text-text-primary flex items-center gap-2">
                    <i class="bi bi-list-ul text-accent"></i> Log Bug Manufaktur
                </h2>
                <p class="text-xs text-text-muted mt-1">Production line database — Stage 1 & 2 verification</p>
            </div>

            <!-- Filter Panel -->
            <form action="<?php echo e(route('dashboard')); ?>" method="GET" class="w-full xl:w-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:flex xl:flex-wrap gap-2 text-sm w-full">
                    <select name="import_job_id" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-blue-500 text-text-primary font-semibold focus:ring-1 focus:ring-blue-500">
                        <option value="all" <?php echo e($selectedJobId === 'all' ? 'selected' : ''); ?>>Semua File SQL</option>
                        <?php $__currentLoopData = $sqlFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($file->id); ?>" <?php echo e($selectedJobId == $file->id ? 'selected' : ''); ?>><?php echo e($file->filename); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="project_id" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Proyek</option>
                        <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php echo e(request('project_id') == $p->id ? 'selected' : ''); ?>><?php echo e($p->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>

                    <select name="status" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Status</option>
                        <option value="OPEN" <?php echo e(request('status') === 'OPEN' ? 'selected' : ''); ?>>Open</option>
                        <option value="CLOSED" <?php echo e(request('status') === 'CLOSED' ? 'selected' : ''); ?>>Closed</option>
                    </select>

                    <select name="severity" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Semua Severity</option>
                        <option value="Critical" <?php echo e(request('severity') === 'Critical' ? 'selected' : ''); ?>>Critical</option>
                        <option value="Major" <?php echo e(request('severity') === 'Major' ? 'selected' : ''); ?>>Major</option>
                        <option value="Minor" <?php echo e(request('severity') === 'Minor' ? 'selected' : ''); ?>>Minor</option>
                    </select>

                    <select name="urgency_sort" class="w-full xl:w-auto rounded-lg px-3 py-2 text-sm bg-bg-secondary border border-border-default text-text-primary">
                        <option value="">Terbaru</option>
                        <option value="desc" <?php echo e(request('urgency_sort') === 'desc' ? 'selected' : ''); ?>>Urgency ↓</option>
                        <option value="asc" <?php echo e(request('urgency_sort') === 'asc' ? 'selected' : ''); ?>>Urgency ↑</option>
                    </select>

                    <div class="w-full xl:w-auto flex items-center gap-2 rounded-lg px-3 py-2 bg-bg-secondary border border-border-default">
                        <span class="text-xs text-text-muted">Dari:</span>
                        <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>" class="bg-transparent border-none text-sm text-text-primary focus:outline-none focus:ring-0 p-0 w-full xl:w-28">
                    </div>

                    <div class="w-full xl:w-auto flex items-center gap-2 rounded-lg px-3 py-2 bg-bg-secondary border border-border-default">
                        <span class="text-xs text-text-muted">Sampai:</span>
                        <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>" class="bg-transparent border-none text-sm text-text-primary focus:outline-none focus:ring-0 p-0 w-full xl:w-28">
                    </div>

                    <button type="submit" class="w-full xl:w-auto px-4 py-2 rounded-lg text-xs font-bold text-white transition-colors" style="background-color: #0046BF;">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                    <a href="<?php echo e(route('dashboard')); ?>" class="w-full xl:w-auto px-4 py-2 rounded-lg text-xs font-semibold text-text-muted border border-border-default hover:bg-bg-tertiary transition-colors text-center">
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
                        <th class="px-4 py-2.5 text-left">Urgency Score</th>
                        <th class="px-4 py-2.5 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-default">
                    <?php $__empty_1 = true; $__currentLoopData = $auditBugs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $sevWeight = $b->severity === 'Critical' ? 0.8 : ($b->severity === 'Major' ? 0.5 : 0.2);
                            $sentScore = $b->sentiment_score !== null ? $b->sentiment_score : 0.5;
                            $urgencyScore = round(($sevWeight + (1.0 - $sentScore)) / 2.0, 2);
                            $urgencyScore = min(1.0, max(0.0, $urgencyScore));
                        ?>
                        <tr class="hover:bg-bg-tertiary transition-colors">
                            <!-- Project & SN -->
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-col gap-1.5">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-xs font-mono font-extrabold text-blue-600 dark:text-blue-400">#BUG-<?php echo e($b->id); ?></span>
                                        <span class="text-xs font-semibold text-text-primary truncate max-w-[150px]"><?php echo e($b->project?->name ?? ($b->project_id ? 'Project #' . $b->project_id : 'Tanpa Proyek')); ?></span>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-[10px] font-mono font-semibold bg-highlight text-highlight-text max-w-max"><?php echo e($b->sn_code_snapshot ?? 'N/A'); ?></span>
                                        <?php if($b->product_version): ?>
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-mono font-medium bg-bg-tertiary text-text-muted border border-border-default">Ver: <?php echo e($b->product_version); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="text-[11px] text-text-muted flex items-center gap-1 mt-0.5">
                                        <i class="bi bi-clock"></i> <?php echo e($b->created_at->format('d M Y, H:i')); ?>

                                    </div>
                                </div>
                            </td>

                            <!-- Bug Title & Severity -->
                            <td class="px-4 py-4 align-top">
                                <div class="flex flex-col gap-1.5 max-w-lg">
                                    <span class="text-sm font-bold text-text-primary"><?php echo e($b->title); ?></span>
                                    
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        <?php if($b->reporter_type === 'produk'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-highlight text-highlight-text">Produk</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-bg-tertiary text-text-muted">Sub-PCB</span>
                                        <?php endif; ?>

                                        <?php if($b->severity === 'Critical'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-red-50 text-red-700 border border-red-200">Critical</span>
                                        <?php elseif($b->severity === 'Major'): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">Major</span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-green-50 text-green-700 border border-green-200">Minor</span>
                                        <?php endif; ?>

                                        <?php if($b->environment): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-medium bg-bg-tertiary text-text-secondary"><i class="bi bi-geo-alt mr-1"></i><?php echo e($b->environment); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($b->description): ?>
                                        <p class="text-xs text-text-secondary line-clamp-2 mt-0.5 leading-relaxed font-normal bg-bg-secondary/60 p-2 rounded-lg border border-border-default/50">
                                            <?php echo e($b->description); ?>

                                        </p>
                                    <?php endif; ?>

                                    <div class="text-[11px] text-text-muted flex items-center gap-1.5 mt-0.5">
                                        <span class="inline-flex items-center gap-1"><i class="bi bi-person-circle"></i> Pelapor: <strong class="text-text-secondary font-semibold"><?php echo e($b->reported_by ?: 'Sistem'); ?></strong></span>
                                    </div>
                                </div>
                            </td>

                            <!-- Urgency Score -->
                            <td class="px-4 py-4 align-top">
                                <div class="space-y-2 max-w-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-text-muted font-medium">Urgency:</span>
                                        <?php
                                            $urgColor = '#2563EB';
                                            if ($urgencyScore >= 0.75) $urgColor = '#DC2626';
                                            elseif ($urgencyScore >= 0.45) $urgColor = '#CA8A04';
                                        ?>
                                        <span class="text-xs font-bold font-mono" style="color: <?php echo e($urgColor); ?>;"><?php echo e(number_format($urgencyScore, 2)); ?></span>
                                        <div class="h-1.5 w-20 rounded-full overflow-hidden bg-bg-tertiary">
                                            <div class="h-full rounded-full" style="width: <?php echo e($urgencyScore * 100); ?>%; background-color: <?php echo e($urgColor); ?>;"></div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-4 text-right align-top">
                                <div class="flex flex-col items-end gap-2">
                                    <div class="flex items-center gap-1.5 flex-wrap justify-end">
                                        <?php if($b->is_rework): ?>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-pill text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><i class="bi bi-arrow-repeat mr-1"></i>Rework</span>
                                        <?php endif; ?>

                                        <?php if($b->status === 'CLOSED'): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-pill text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                                <i class="bi bi-check-circle-fill mr-1"></i> Closed
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill text-xs font-semibold bg-yellow-50 text-yellow-700 border border-yellow-200">
                                                <span class="h-1.5 w-1.5 rounded-full bg-yellow-500 inline-block animate-pulse"></span> Open
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if($b->status === 'CLOSED' && ($b->fixed_by || $b->repair_action)): ?>
                                        <div class="text-[11px] text-text-secondary text-right max-w-[200px] bg-bg-tertiary/70 px-2.5 py-1.5 rounded-lg border border-border-default/60">
                                            <?php if($b->fixed_by): ?>
                                                <div class="font-semibold text-text-primary flex items-center justify-end gap-1"><i class="bi bi-person-check-fill text-green-600"></i> <?php echo e($b->fixed_by); ?></div>
                                            <?php endif; ?>
                                            <?php if($b->repair_action): ?>
                                                <div class="text-[10px] text-text-muted truncate mt-0.5" title="<?php echo e($b->repair_action); ?>"><i class="bi bi-wrench mr-0.5"></i> <?php echo e($b->repair_action); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <button onclick="reprocessAI(<?php echo e($b->id); ?>, this)" class="text-[10px] text-accent hover:text-highlight-text font-semibold py-1 px-2.5 rounded-lg border border-border-default hover:bg-bg-tertiary transition-colors flex items-center gap-1 shadow-sm">
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
                                        <span class="text-text-muted font-normal">— <?php echo e($b->title); ?></span>
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
                                                        Oleh <span class="font-semibold text-text-primary"><?php echo e($b->reported_by ?: 'Tidak diketahui'); ?></span>
                                                        pada <span class="font-semibold text-text-primary"><?php echo e($b->created_at->format('d M Y H:i')); ?></span>
                                                    </div>
                                                </div>

                                                <?php if($b->status === 'CLOSED' && $b->closed_at): ?>
                                                    <div class="relative pl-7">
                                                        <div class="absolute left-0 top-0.5 h-4 w-4 rounded-full bg-green-50 border border-green-400 flex items-center justify-center">
                                                            <i class="bi bi-check-lg text-[8px] text-green-600"></i>
                                                        </div>
                                                        <div class="text-xs font-semibold text-green-600">Pengerjaan Selesai</div>
                                                        <div class="text-xs text-text-secondary mt-0.5">
                                                            Oleh <span class="font-semibold text-text-primary"><?php echo e($b->fixed_by ?: 'Tidak diketahui'); ?></span>
                                                            pada <span class="font-semibold text-text-primary"><?php echo e(\Carbon\Carbon::parse($b->closed_at)->format('d M Y H:i')); ?></span>
                                                        </div>
                                                        <?php if($b->root_cause || $b->repair_action): ?>
                                                            <div class="mt-1.5 text-xs text-text-secondary">
                                                                <span class="text-yellow-600 font-semibold">Root Cause:</span> <?php echo e($b->root_cause ?: '-'); ?>

                                                                <span class="mx-1.5 text-slate-300">|</span>
                                                                <span class="text-green-600 font-semibold">Repair:</span> <?php echo e($b->repair_action ?: '-'); ?>

                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="py-12 text-center text-text-muted">
                                <i class="bi bi-inbox text-3xl block mb-2"></i>
                                Tidak ada data log yang ditemukan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6 pt-4 border-t border-border-strong text-text-muted">
            <?php echo e($auditBugs->links()); ?>

        </div>
    </div>

</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<?php if($hasImportedData): ?>
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

        let chartJumlahBug, chartSeverityClosed, chartSeverityOpen, chartVolume, chartAssemblyStage;

        function makeJumlahBugOptions(c) {
            return {
                series: [<?php echo e($openBugs); ?>, <?php echo e($closedBugs); ?>],
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

        var assemblyStageData = <?php echo json_encode($assemblyStageMap ?? [], 15, 512) ?>;
        var stagePalette = ['#1A3D63', '#4A7FA7', '#E8A33D', '#3E9B6F', '#D64550', '#8B5CF6'];

        function makeAssemblyStageOptions(data, c) {
            var keys = Object.keys(data);
            var vals = Object.values(data);
            return {
                series: vals.length > 0 ? vals : [1],
                chart: { type: 'donut', height: 260, background: 'transparent', foreColor: c.textMuted },
                labels: keys.length > 0 ? keys : ['Kosong'],
                colors: stagePalette.slice(0, Math.max(1, keys.length)),
                dataLabels: { enabled: false },
                stroke: { show: false },
                legend: {
                    position: 'bottom', fontSize: '12px', fontFamily: 'Inter, sans-serif',
                    labels: { colors: c.textMuted },
                    markers: { radius: 4, width: 8, height: 8 },
                    itemMargin: { horizontal: 10, vertical: 5 },
                    formatter: function(name, opts) {
                        return name + ' — ' + (opts.w.globals.series[opts.seriesIndex] || 0);
                    }
                },
                plotOptions: {
                    pie: { donut: { size: '70%', labels: {
                        show: true,
                        name: { show: true, fontSize: '12px', fontFamily: 'Inter', color: c.textMuted, offsetY: -8 },
                        value: { show: true, fontSize: '26px', fontFamily: 'Inter', color: c.textPrimary, fontWeight: 'bold', offsetY: 8 },
                        total: {
                            show: true, label: 'Total Laporan', color: c.textMuted, fontSize: '11px', fontFamily: 'Inter',
                            formatter: function(w) { return w.globals.seriesTotals.reduce((a, b) => a + b, 0); }
                        }
                    }}}
                },
                tooltip: { theme: c.tooltipTheme }
            };
        }

        // Initial render
        var c = getChartColors();
        var trendData = <?php echo json_encode($trendData); ?>;
        var trendKeys = Object.keys(trendData).map(d => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }));
        var trendValues = Object.values(trendData);
        var severityClosedData = <?php echo json_encode($severityClosed, 15, 512) ?>;
        var severityOpenData   = <?php echo json_encode($severityOpen, 15, 512) ?>;

        chartJumlahBug = new ApexCharts(document.querySelector("#chartJumlahBug"), makeJumlahBugOptions(c));
        chartJumlahBug.render();

        chartSeverityClosed = new ApexCharts(document.querySelector("#chartSeverityClosed"), makeSevOptions(severityClosedData, 220, c));
        chartSeverityClosed.render();

        chartSeverityOpen = new ApexCharts(document.querySelector("#chartSeverityOpen"), makeSevOptions(severityOpenData, 220, c));
        chartSeverityOpen.render();

        chartVolume = new ApexCharts(document.querySelector("#volumeChart"), makeVolumeOptions(trendKeys, trendValues, c));
        chartVolume.render();

        chartAssemblyStage = new ApexCharts(document.querySelector("#chartAssemblyStage"), makeAssemblyStageOptions(assemblyStageData, c));
        chartAssemblyStage.render();

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

            chartAssemblyStage.destroy();
            chartAssemblyStage = new ApexCharts(document.querySelector("#chartAssemblyStage"), makeAssemblyStageOptions(assemblyStageData, nc));
            chartAssemblyStage.render();
        });
    });
</script>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\MAGANG\WEB2-MAGANG\resources\views/dashboard/index.blade.php ENDPATH**/ ?>
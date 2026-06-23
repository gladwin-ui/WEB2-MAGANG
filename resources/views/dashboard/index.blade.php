@extends('layouts.app')

@section('title', 'Dashboard Analitik')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800;">Dashboard Analitik</h1>
        <p style="color: var(--text-secondary);">Real-time monitoring pelaporan defect & analisis AI sistem manufaktur</p>
    </div>
    
    <a href="{{ route('dashboard.export', request()->query()) }}" class="btn btn-secondary" style="border-color: var(--color-minor); color: #34d399; background-color: rgba(16, 185, 129, 0.05);">
        <i class="bi bi-file-earmark-spreadsheet-fill"></i> Ekspor ke CSV
    </a>
</div>

<!-- 1. KPI Summary Cards -->
<div class="metrics-grid">
    <div class="metric-card">
        <span class="metric-label">Total Bug Terdaftar</span>
        <span class="metric-value">{{ $totalBugs }}</span>
    </div>
    <div class="metric-card">
        <span class="metric-label">Bug Aktif (Open)</span>
        <span class="metric-value" style="color: var(--color-major);">{{ $openBugs }}</span>
    </div>
    <div class="metric-card closed">
        <span class="metric-label">Bug Diperbaiki (Closed)</span>
        <span class="metric-value" style="color: var(--color-minor);">{{ $closedBugs }}</span>
    </div>
    <div class="metric-card critical">
        <span class="metric-label">Critical Pending</span>
        <span class="metric-value" style="color: var(--color-critical);">{{ $criticalOpenBugs }}</span>
    </div>
    <div class="metric-card rework">
        <span class="metric-label">Rework Rate</span>
        <span class="metric-value" style="color: var(--color-spam);">{{ $reworkRate }}%</span>
    </div>
</div>

<!-- 2. AI & Volume Analytics Charts -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; @media (max-width: 1024px) { grid-template-columns: 1fr; }">
    <!-- Volume Trend (14 Days) -->
    <div class="card">
        <h2 class="card-title"><i class="bi bi-graph-up" style="color: var(--color-info);"></i> Tren Volume Pelaporan (14 Hari Terakhir)</h2>
        @php
            $maxTrend = max(1, max($trendData));
        @endphp
        <div class="chart-container">
            @foreach($trendData as $date => $count)
                @php
                    $percent = ($count / $maxTrend) * 100;
                    $dayStr = Carbon\Carbon::parse($date)->format('d/m');
                @endphp
                <div class="chart-bar-wrapper">
                    <div class="chart-bar" style="height: {{ max(5, $percent) }}px;">
                        <span class="chart-bar-value">{{ $count }}</span>
                    </div>
                    <span class="chart-label" title="{{ $date }}">{{ $dayStr }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Damage Categories breakdown -->
    <div class="card">
        <h2 class="card-title"><i class="bi bi-pie-chart-fill" style="color: var(--color-spam);"></i> Analisis Penyebab Kerusakan (AI Stage 2)</h2>
        @if($damageCategories->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.9rem;">
                Belum ada data perbaikan terdaftar untuk dianalisis.
            </div>
        @else
            @php
                $totalClosed = max(1, $closedBugs);
            @endphp
            <div class="pie-sim-container">
                @foreach($damageCategories as $cat)
                    @php
                        $pct = round(($cat->count / $totalClosed) * 100, 1);
                    @endphp
                    <div class="pie-sim-row">
                        <div class="pie-sim-label" title="{{ $cat->damage_category }}" style="width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $cat->damage_category }}
                        </div>
                        <div class="pie-sim-bar-bg">
                            <div class="pie-sim-bar-fg" style="width: {{ $pct }}%; background-color: var(--color-info);"></div>
                        </div>
                        <div class="pie-sim-val">{{ $cat->count }} ({{ $pct }}%)</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; @media (max-width: 1024px) { grid-template-columns: 1fr; }">
    <!-- Sentiment Distribution -->
    <div class="card">
        <h2 class="card-title"><i class="bi bi-emoji-smile" style="color: var(--color-minor);"></i> Distribusi Sentimen Laporan (AI Stage 1)</h2>
        @php
            $totalSentiment = array_sum($sentiments);
            $totalSentiment = $totalSentiment > 0 ? $totalSentiment : 1;
        @endphp
        <div class="pie-sim-container">
            @foreach(['positive' => 'Positif', 'neutral' => 'Netral', 'negative' => 'Negatif', 'spam' => 'Spam Suspect', 'Unanalyzed' => 'Belum Dianalisis'] as $key => $label)
                @php
                    $count = $sentiments[$key] ?? 0;
                    $pct = round(($count / $totalSentiment) * 100, 1);
                    $color = 'var(--text-muted)';
                    if($key === 'positive') $color = 'var(--color-minor)';
                    if($key === 'negative') $color = 'var(--color-critical)';
                    if($key === 'spam') $color = 'var(--color-spam)';
                    if($key === 'neutral') $color = 'var(--color-major)';
                @endphp
                <div class="pie-sim-row">
                    <div class="pie-sim-label">{{ $label }}</div>
                    <div class="pie-sim-bar-bg">
                        <div class="pie-sim-bar-fg" style="width: {{ $pct }}%; background-color: {{ $color }};"></div>
                    </div>
                    <div class="pie-sim-val">{{ $count }} ({{ $pct }}%)</div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top 5 Projects with bugs -->
    <div class="card">
        <h2 class="card-title"><i class="bi bi-diagram-3-fill" style="color: var(--color-major);"></i> Project Paling Banyak Bug (Top 5)</h2>
        @if($topProjects->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-muted); font-size: 0.9rem;">
                Belum ada data project terdaftar.
            </div>
        @else
            @php
                $maxBugs = max(1, $topProjects->first()->bug_count ?? 1);
            @endphp
            <div class="pie-sim-container">
                @foreach($topProjects as $proj)
                    @php
                        $pct = round(($proj->bug_count / $maxBugs) * 100, 1);
                    @endphp
                    <div class="pie-sim-row">
                        <div class="pie-sim-label" style="width: 140px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $proj->project_name }}</div>
                        <div class="pie-sim-bar-bg">
                            <div class="pie-sim-bar-fg" style="width: {{ $pct }}%; background-color: var(--color-major);"></div>
                        </div>
                        <div class="pie-sim-val">{{ $proj->bug_count }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<!-- 3. Bug Audit Log Table & Filters -->
<div class="card">
    <h2 class="card-title"><i class="bi bi-search"></i> Audit Log & Filter Laporan</h2>
    
    <!-- Filter form -->
    <form action="{{ route('dashboard') }}" method="GET" style="margin-bottom: 2rem; background-color: rgba(0,0,0,0.15); padding: 1.25rem; border-radius: 0.5rem; border: 1px solid var(--border-color);">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label for="project_id" class="form-label" style="font-size: 0.8rem;">Project</label>
                <select id="project_id" name="project_id" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">Semua Project</option>
                    @foreach($projects as $p)
                        <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="status" class="form-label" style="font-size: 0.8rem;">Status</label>
                <select id="status" name="status" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">Semua Status</option>
                    <option value="OPEN" {{ request('status') === 'OPEN' ? 'selected' : '' }}>OPEN</option>
                    <option value="CLOSED" {{ request('status') === 'CLOSED' ? 'selected' : '' }}>CLOSED</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="severity" class="form-label" style="font-size: 0.8rem;">Severity</label>
                <select id="severity" name="severity" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;">
                    <option value="">Semua Severity</option>
                    <option value="Critical" {{ request('severity') === 'Critical' ? 'selected' : '' }}>Critical</option>
                    <option value="Major" {{ request('severity') === 'Major' ? 'selected' : '' }}>Major</option>
                    <option value="Minor" {{ request('severity') === 'Minor' ? 'selected' : '' }}>Minor</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_from" class="form-label" style="font-size: 0.8rem;">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" value="{{ request('date_from') }}">
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label for="date_to" class="form-label" style="font-size: 0.8rem;">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" class="form-control" style="padding: 0.5rem 0.75rem; font-size: 0.85rem;" value="{{ request('date_to') }}">
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary btn-sm" style="padding: 0.6rem 1rem; flex-grow: 1; justify-content: center;">
                    <i class="bi bi-filter"></i> Filter
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm" style="padding: 0.6rem 1rem; justify-content: center;">
                    Reset
                </a>
            </div>
        </div>
    </form>

    <!-- Table -->
    <div class="table-container">
        @if($bugs->isEmpty())
            <div style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                <p>Tidak ditemukan data bug yang sesuai dengan kriteria filter.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Project</th>
                        <th>Severity</th>
                        <th>Status</th>
                        <th>Reporter</th>
                        <th>Fixer</th>
                        <th>Penyebab (AI)</th>
                        <th>Tanggal Lapor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bugs as $b)
                        <tr>
                            <td>#{{ $b->id }}</td>
                            <td style="font-weight: 600;">
                                {{ $b->title }}
                                @if($b->is_rework)
                                    <span class="badge badge-spam" style="font-size: 0.6rem;">REWORK</span>
                                @endif
                            </td>
                            <td>{{ $b->project?->name ?? 'Project #' . $b->project_id }}</td>
                            <td>
                                @if($b->severity === 'Critical')
                                    <span class="badge badge-critical">{{ $b->severity }}</span>
                                @elseif($b->severity === 'Major')
                                    <span class="badge badge-major">{{ $b->severity }}</span>
                                @else
                                    <span class="badge badge-minor">{{ $b->severity }}</span>
                                @endif
                            </td>
                            <td>
                                @if($b->status === 'OPEN')
                                    <span class="badge badge-open">OPEN</span>
                                @else
                                    <span class="badge badge-closed">CLOSED</span>
                                @endif
                            </td>
                            <td>{{ $b->reporter?->name ?? 'System' }}</td>
                            <td>{{ $b->fixer?->name ?? '-' }}</td>
                            <td>
                                @if($b->damage_category)
                                    <span class="badge" style="background-color: rgba(6, 182, 212, 0.1); color: var(--color-info);">{{ $b->damage_category }}</span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td>{{ $b->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ route('bugs.show', $b) }}" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-eye"></i> Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 1.5rem;">
                {{ $bugs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

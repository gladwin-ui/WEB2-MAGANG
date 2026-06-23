@extends('layouts.app')

@section('title', 'Queue Kerja Bug')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800;">Queue Kerja Bug</h1>
        <p style="color: var(--text-secondary);">Daftar kerusakan produksi yang butuh perbaikan teknis</p>
    </div>
    
    <!-- Status Switch Tabs -->
    <div style="background-color: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: 0.5rem; padding: 0.25rem; display: flex; gap: 0.25rem;">
        <a href="{{ route('bugs.index', ['status' => 'OPEN']) }}" class="btn {{ $status === 'OPEN' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            <i class="bi bi-clock-history"></i> OPEN QUEUE
        </a>
        <a href="{{ route('bugs.index', ['status' => 'CLOSED']) }}" class="btn {{ $status === 'CLOSED' ? 'btn-primary' : 'btn-secondary' }} btn-sm">
            <i class="bi bi-check-all"></i> CLOSED HISTORY
        </a>
    </div>
</div>

<div class="card">
    <div class="table-container">
        @if($bugs->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <i class="bi bi-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--color-minor);"></i>
                <p>Tidak ada laporan bug dalam status {{ $status }}.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul Bug</th>
                        <th>Project</th>
                        <th>Severity</th>
                        <th>SN Snapshot</th>
                        <th>Reporter</th>
                        <th>Tanggal Lapor</th>
                        <th>Rekomendasi Severity AI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bugs as $bug)
                        <tr style="{{ $bug->severity === 'Critical' && $bug->status === 'OPEN' ? 'background-color: rgba(239, 68, 68, 0.02);' : '' }}">
                            <td>#{{ $bug->id }}</td>
                            <td style="font-weight: 600;">
                                {{ $bug->title }}
                                @if($bug->is_rework)
                                    <span class="badge badge-spam" style="font-size: 0.65rem; margin-left: 0.25rem;">REWORK</span>
                                @endif
                                @if($bug->is_spam)
                                    <span class="badge" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-spam); font-size: 0.65rem; border: 1px solid rgba(139, 92, 246, 0.2)">SPAM SUSPECT</span>
                                @endif
                            </td>
                            <td>{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</td>
                            <td>
                                @if($bug->severity === 'Critical')
                                    <span class="badge badge-critical">{{ $bug->severity }}</span>
                                @elseif($bug->severity === 'Major')
                                    <span class="badge badge-major">{{ $bug->severity }}</span>
                                @else
                                    <span class="badge badge-minor">{{ $bug->severity }}</span>
                                @endif
                            </td>
                            <td style="font-family: monospace;">{{ $bug->sn_code_snapshot ?? '-' }}</td>
                            <td>{{ $bug->reporter?->name ?? 'System' }}</td>
                            <td>{{ $bug->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($bug->severity_recommended)
                                    @if($bug->severity_recommended === 'Critical')
                                        <span class="badge badge-critical" title="{{ $bug->severity_recommendation_reason }}">{{ $bug->severity_recommended }}</span>
                                    @elseif($bug->severity_recommended === 'Major')
                                        <span class="badge badge-major" title="{{ $bug->severity_recommendation_reason }}">{{ $bug->severity_recommended }}</span>
                                    @else
                                        <span class="badge badge-minor" title="{{ $bug->severity_recommendation_reason }}">{{ $bug->severity_recommended }}</span>
                                    @endif
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.8rem;">-</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <a href="{{ route('bugs.show', $bug) }}" class="btn btn-secondary btn-sm">
                                        <i class="bi bi-eye"></i> Detail
                                    </a>
                                    @if($bug->status === 'OPEN')
                                        <a href="{{ route('bugs.close.form', $bug) }}" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #10b981, #059669)">
                                            <i class="bi bi-hammer"></i> Tangani
                                        </a>
                                    @endif
                                </div>
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

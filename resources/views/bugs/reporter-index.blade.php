@extends('layouts.app')

@section('title', 'Riwayat Laporanku')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <div>
        <h1 style="font-size: 2rem; font-weight: 800;">Riwayat Laporanku</h1>
        <p style="color: var(--text-secondary);">Daftar kerusakan produksi yang Anda laporkan</p>
    </div>
    <a href="{{ route('bugs.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill"></i> Laporkan Bug Baru
    </a>
</div>

<div class="card">
    <div class="table-container">
        @if($bugs->isEmpty())
            <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                <i class="bi bi-folder-x" style="font-size: 3rem; margin-bottom: 1rem; display: block; color: var(--text-muted);"></i>
                <p>Anda belum pernah mengirim laporan bug.</p>
                <a href="{{ route('bugs.create') }}" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">
                    Kirim Laporan Pertama
                </a>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Judul</th>
                        <th>Project</th>
                        <th>Severity</th>
                        <th>SN Code Snapshot</th>
                        <th>Status</th>
                        <th>Tanggal Lapor</th>
                        <th>Analisis AI</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bugs as $bug)
                        <tr>
                            <td>#{{ $bug->id }}</td>
                            <td style="font-weight: 600;">{{ $bug->title }}</td>
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
                            <td>
                                @if($bug->status === 'OPEN')
                                    <span class="badge badge-open">OPEN</span>
                                @else
                                    <span class="badge badge-closed">CLOSED</span>
                                @endif
                            </td>
                            <td>{{ $bug->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                @if($bug->is_spam)
                                    <span class="badge badge-spam" title="Spam: {{ $bug->spam_reason }}">Spam Check Failed</span>
                                @elseif($bug->sentiment_label === 'negative')
                                    <span class="badge badge-negative">Negatif</span>
                                @elseif($bug->sentiment_label === 'positive')
                                    <span class="badge badge-positive">Positif</span>
                                @elseif($bug->sentiment_label === 'neutral')
                                    <span class="badge badge-neutral">Netral</span>
                                @else
                                    <span class="badge badge-secondary" style="background-color: rgba(255,255,255,0.05); color: var(--text-muted);">Antrean AI</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('bugs.show', $bug) }}" class="btn btn-secondary btn-sm">
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

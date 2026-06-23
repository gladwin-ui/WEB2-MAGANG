@extends('layouts.app')

@section('title', 'Detail Bug #' . $bug->id)

@section('content')
<div style="margin-bottom: 2rem;">
    <a href="{{ route('bugs.index') }}" style="color: var(--text-secondary); text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.25rem; margin-bottom: 0.75rem;">
        <i class="bi bi-arrow-left"></i> Kembali ke Queue
    </a>
    <div style="display: flex; justify-content: space-between; align-items: flex-start; wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                <h1 style="font-size: 2rem; font-weight: 800; line-height: 1.1;">#{{ $bug->id }} - {{ $bug->title }}</h1>
                @if($bug->status === 'OPEN')
                    <span class="badge badge-open">OPEN QUEUE</span>
                @else
                    <span class="badge badge-closed">CLOSED / FIXED</span>
                @endif
                
                @if($bug->is_rework)
                    <span class="badge badge-spam">REWORK</span>
                @endif
            </div>
            <p style="color: var(--text-secondary);">Dilaporkan oleh <strong style="color: var(--text-primary);">{{ $bug->reporter?->name ?? 'System' }}</strong> pada {{ $bug->created_at->format('d F Y \p\u\k\u\l H:i') }}</p>
        </div>
        
        @if($bug->status === 'OPEN' && (auth()->user()->role === 'mekanik' || auth()->user()->role === 'admin'))
            <a href="{{ route('bugs.close.form', $bug) }}" class="btn btn-primary" style="background: linear-gradient(135deg, #10b981, #059669)">
                <i class="bi bi-hammer"></i> Selesaikan & Tutup Bug
            </a>
        @endif
    </div>
</div>

<div class="content-grid">
    <!-- Left Column: Bug Information -->
    <div>
        <div class="card">
            <h2 class="card-title">Informasi Dasar & Teknis</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Project</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $bug->project?->name ?? 'Project #' . $bug->project_id }}</div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Serial Number (Snapshot)</div>
                    <div style="font-weight: 600; font-size: 1.1rem; font-family: monospace;">{{ $bug->sn_code_snapshot ?? '-' }}</div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Tipe Pelapor</div>
                    <div style="font-weight: 600; font-size: 1.1rem; text-transform: capitalize;">{{ $bug->reporter_type }}</div>
                </div>
                <div>
                    <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Versi Produk</div>
                    <div style="font-weight: 600; font-size: 1.1rem;">{{ $bug->product_version ?? '-' }}</div>
                </div>
            </div>

            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div>
                    <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Deskripsi Kerusakan / Description</h3>
                    <div style="background-color: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem; white-space: pre-line;">{{ $bug->description ?? 'Tidak ada deskripsi.' }}</div>
                </div>

                @if($bug->reproduce_steps)
                    <div>
                        <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Langkah Menimbulkan Masalah / Reproduce</h3>
                        <div style="background-color: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem; white-space: pre-line;">{{ $bug->reproduce_steps }}</div>
                    </div>
                @endif

                @if($bug->expected_result)
                    <div>
                        <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Hasil yang Diharapkan / Expected</h3>
                        <div style="background-color: rgba(0,0,0,0.2); padding: 1rem; border-radius: 0.5rem; white-space: pre-line;">{{ $bug->expected_result }}</div>
                    </div>
                @endif

                @if($bug->environment)
                    <div>
                        <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Kondisi Lingkungan / Environment</h3>
                        <div style="font-weight: 500;">{{ $bug->environment }}</div>
                    </div>
                @endif

                @if($bug->attachment_path)
                    <div>
                        <h3 style="font-size: 1rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Lampiran Dokumen/Media</h3>
                        <div>
                            @php
                                $ext = pathinfo($bug->attachment_path, PATHINFO_EXTENSION);
                                $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                                $isVid = in_array(strtolower($ext), ['mp4', 'webm', 'ogg', 'mov']);
                            @endphp

                            @if($isImg)
                                <img src="{{ asset('storage/' . $bug->attachment_path) }}" alt="Lampiran" style="max-width: 100%; max-height: 400px; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                            @elseif($isVid)
                                <video controls style="max-width: 100%; max-height: 400px; border-radius: 0.5rem; border: 1px solid var(--border-color);">
                                    <source src="{{ asset('storage/' . $bug->attachment_path) }}" type="video/{{ $ext === 'mov' ? 'mp4' : $ext }}">
                                    Browser Anda tidak mendukung pemutar video.
                                </video>
                            @else
                                <a href="{{ asset('storage/' . $bug->attachment_path) }}" target="_blank" class="btn btn-secondary btn-sm">
                                    <i class="bi bi-file-earmark-arrow-down-fill"></i> Download Lampiran (.{{ $ext }})
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Technical Resolution Section (Only visible if closed) -->
        @if($bug->status === 'CLOSED')
            <div class="card" style="border-left: 4px solid var(--color-minor);">
                <h2 class="card-title" style="color: var(--color-minor);"><i class="bi bi-check-circle-fill"></i> Detail Perbaikan / Resolution</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Ditangani Oleh</div>
                        <div style="font-weight: 600;">{{ $bug->fixer?->name ?? 'System' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Waktu Selesai</div>
                        <div style="font-weight: 600;">{{ $bug->closed_at ? $bug->closed_at->format('d M Y, H:i') : '-' }}</div>
                    </div>
                    <div>
                        <div style="font-size: 0.8rem; color: var(--text-secondary); text-transform: uppercase;">Penyebab Kerusakan (AI)</div>
                        <div style="font-weight: 600;"><span class="badge" style="background-color: rgba(6, 182, 212, 0.1); color: var(--color-info); border: 1px solid rgba(6, 182, 212, 0.2)">{{ $bug->damage_category ?? 'Lain-lain' }}</span></div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    <div>
                        <h3 style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Penyebab Utama (Root Cause)</h3>
                        <div style="background-color: rgba(16, 185, 129, 0.05); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.1); white-space: pre-line;">{{ $bug->root_cause }}</div>
                    </div>
                    <div>
                        <h3 style="font-size: 0.95rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Tindakan Perbaikan (Repair Action)</h3>
                        <div style="background-color: rgba(16, 185, 129, 0.05); padding: 1rem; border-radius: 0.5rem; border: 1px solid rgba(16, 185, 129, 0.1); white-space: pre-line;">{{ $bug->repair_action }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: AI Analysis & Feedback -->
    <div>
        <!-- AI Stage 1 Box -->
        <div class="card" style="border-top: 4px solid var(--color-primary);">
            <h2 class="card-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-robot" style="color: var(--color-primary);"></i>
                <span>Analisis AI (Stage 1)</span>
            </h2>

            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <!-- Severity comparison -->
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Perbandingan Severity:</div>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">Reporter:</span>
                            @if($bug->severity === 'Critical')
                                <span class="badge badge-critical">{{ $bug->severity }}</span>
                            @elseif($bug->severity === 'Major')
                                <span class="badge badge-major">{{ $bug->severity }}</span>
                            @else
                                <span class="badge badge-minor">{{ $bug->severity }}</span>
                            @endif
                        </div>
                        <div style="text-align: right;">
                            <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">AI Recommendation:</span>
                            @if($bug->severity_recommended)
                                @if($bug->severity_recommended === 'Critical')
                                    <span class="badge badge-critical">{{ $bug->severity_recommended }}</span>
                                @elseif($bug->severity_recommended === 'Major')
                                    <span class="badge badge-major">{{ $bug->severity_recommended }}</span>
                                @else
                                    <span class="badge badge-minor">{{ $bug->severity_recommended }}</span>
                                @endif
                            @else
                                <span style="color: var(--text-muted); font-size: 0.8rem;">Belum dianalisis</span>
                            @endif
                        </div>
                    </div>
                    @if($bug->severity_recommendation_reason)
                        <div style="background-color: rgba(255, 255, 255, 0.02); padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.8rem; margin-top: 0.75rem; color: var(--text-secondary); border-left: 2px solid var(--color-primary);">
                            <strong>Alasan:</strong> {{ $bug->severity_recommendation_reason }}
                        </div>
                    @endif
                </div>

                <!-- Sentiment analysis -->
                <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem;">
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Analisis Sentimen Laporan:</div>
                    @if($bug->sentiment_label)
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            @if($bug->sentiment_label === 'positive')
                                <span class="badge badge-positive">Positif</span>
                            @elseif($bug->sentiment_label === 'negative')
                                <span class="badge badge-negative">Negatif</span>
                            @elseif($bug->sentiment_label === 'spam')
                                <span class="badge badge-spam">Spam</span>
                            @else
                                <span class="badge badge-neutral">Netral</span>
                            @endif
                            <span style="font-size: 0.85rem; color: var(--text-secondary);">Score: <strong>{{ $bug->sentiment_score }}</strong></span>
                        </div>
                    @else
                        <span style="color: var(--text-muted); font-size: 0.8rem;">Belum dianalisis</span>
                    @endif
                </div>

                <!-- Spam detection check -->
                <div>
                    <div style="font-size: 0.85rem; color: var(--text-secondary); margin-bottom: 0.25rem;">Spam Check:</div>
                    @if($bug->sentiment_label)
                        @if($bug->is_spam)
                            <div style="background-color: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); padding: 0.75rem; border-radius: 0.5rem; color: var(--color-critical); font-size: 0.85rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                <div style="font-weight: 600;"><i class="bi bi-shield-x"></i> AI Mendeteksi Spam!</div>
                                <div style="color: var(--text-secondary); font-size: 0.8rem;">{{ $bug->spam_reason }}</div>
                            </div>
                        @else
                            <div style="background-color: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); padding: 0.5rem 0.75rem; border-radius: 0.5rem; color: var(--color-minor); font-size: 0.85rem; display: inline-flex; align-items: center; gap: 0.5rem;">
                                <i class="bi bi-shield-check"></i> Laporan Lolos Spam Check (Laporan Serius)
                            </div>
                        @endif
                    @else
                        <span style="color: var(--text-muted); font-size: 0.8rem;">Belum dianalisis</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Feedback Communication Box -->
        <div class="card">
            <h2 class="card-title"><i class="bi bi-chat-left-text-fill"></i> Komunikasi / Feedback</h2>
            
            <!-- Message List -->
            <div class="feedback-timeline">
                @if($bug->feedbacks->isEmpty())
                    <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 2rem 0;">
                        Belum ada komunikasi personal terkait laporan ini.
                    </div>
                @else
                    @foreach($bug->feedbacks as $message)
                        @php
                            $isOutbound = ($message->from_user_id === auth()->id());
                        @endphp
                        <div class="feedback-bubble {{ $isOutbound ? 'outbound' : 'inbound' }}">
                            <div class="feedback-meta">
                                <span class="feedback-sender">{{ $message->sender?->name ?? 'System' }}</span>
                                <span>{{ $message->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="feedback-body">{{ $message->message }}</div>
                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Message Form (visible if user can chat) -->
            <form action="{{ route('bugs.feedback.store', $bug) }}" method="POST">
                @csrf
                <div class="form-group" style="margin-bottom: 1rem;">
                    <textarea name="message" rows="2" placeholder="Tulis pesan/klarifikasi..." class="form-control" style="font-size: 0.85rem;" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-sm" style="width: 100%; justify-content: center;">
                    <i class="bi bi-send"></i> Kirim Pesan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Asynchronously mark messages as read when loading the page
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

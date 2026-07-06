@extends('layouts.app')

@section('title', 'Progres Import #' . $importJob->id)

@section('styles')
<style>
    /* Progress bar track */
    .progress-track {
        background-color: #E2E8F0;
        border-radius: 999px;
        overflow: hidden;
        height: 12px;
    }
    /* Progress fill */
    .progress-fill {
        height: 100%;
        border-radius: 999px;
        background-color: #0046BF;
        transition: width 0.5s ease;
        min-width: 4px;
    }
    .progress-fill.completed {
        background-color: #16A34A;
    }
    .progress-fill.failed {
        background-color: #DC2626;
    }

    /* Counter cards */
    .stat-card {
        background: #fff;
        border: 1px solid #E2E8F0;
        border-radius: 12px;
        padding: 1rem 1.25rem;
        text-align: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }
    .stat-number {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        color: #1E293B;
    }
    .stat-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #64748B;
        margin-top: 4px;
    }

    /* Status dot pulse for active state */
    @keyframes pulse-dot {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.4; }
    }
    .pulse-dot { animation: pulse-dot 1.4s ease-in-out infinite; }

    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    .fade-in { animation: fadeIn 0.4s ease; }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <a href="{{ route('import.upload') }}" class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm text-text-secondary hover:text-accent hover:border-accent transition-all" title="Kembali">
                <i class="bi bi-arrow-left text-lg"></i>
            </a>
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-cpu-fill text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Progres Import</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium font-mono">Job #{{ $importJob->id }} &middot; {{ $importJob->filename }}</p>
            </div>
        </div>
    </div>

    {{-- Table name warning (flash) --}}
    @if(session('table_warning'))
        <div class="mb-5 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-circle-fill text-base shrink-0 mt-0.5"></i>
            <div>{{ session('table_warning') }}</div>
        </div>
    @endif

    {{-- Parse warnings --}}
    @if(session('parse_warnings') && count(session('parse_warnings')) > 0)
        <div class="mb-5 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm">
            <p class="font-semibold mb-1 flex items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill"></i> Peringatan Parse
            </p>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach(session('parse_warnings') as $warn)
                    <li>{{ $warn }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main progress card --}}
    <div id="progress-card" class="bg-white border border-slate-200 rounded-xl shadow-card p-6 mb-5">

        {{-- Status row --}}
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <span id="status-dot" class="w-2.5 h-2.5 rounded-full bg-blue-500 pulse-dot inline-block"></span>
                <span id="status-label" class="text-sm font-bold text-slate-700 capitalize">{{ $importJob->status }}</span>
            </div>
            <span id="percentage-label" class="text-2xl font-extrabold text-slate-800">0%</span>
        </div>

        {{-- Progress bar --}}
        <div class="progress-track mb-2">
            <div id="progress-fill" class="progress-fill" style="width: 0%"></div>
        </div>
        <div class="flex justify-between text-xs text-slate-400 mb-6">
            <span id="processed-label">0 diproses</span>
            <span id="total-label">dari {{ $importJob->total_rows }} baris</span>
        </div>

        {{-- Stat counters --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="stat-card">
                <div id="count-inserted" class="stat-number text-blue-600">0</div>
                <div class="stat-label">Baru (INSERT)</div>
            </div>
            <div class="stat-card">
                <div id="count-skipped" class="stat-number text-slate-500">0</div>
                <div class="stat-label">Dilewati</div>
            </div>
            <div class="stat-card">
                <div id="count-failed" class="stat-number text-red-600">0</div>
                <div class="stat-label">Gagal</div>
            </div>
        </div>
    </div>

    {{-- Completion summary (hidden until done) --}}
    <div id="completion-banner" class="hidden mb-5 p-5 rounded-xl border fade-in">
        <div class="flex items-start gap-3">
            <div id="banner-icon" class="text-2xl"></div>
            <div class="flex-1">
                <h3 id="banner-title" class="font-bold text-sm mb-1"></h3>
                <p id="banner-desc" class="text-xs leading-relaxed"></p>
            </div>
        </div>
    </div>

    {{-- FK warnings (shown when data is loaded) --}}
    <div id="fk-warnings-box" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-5 text-sm">
        <p class="font-semibold text-yellow-800 mb-2 flex items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill"></i> Data Master Tidak Ditemukan
        </p>
        <p class="text-xs text-yellow-700 mb-2">Baris dengan referensi yang tidak dikenali tetap diimport dengan kolom FK diset null.</p>
        <ul id="fk-warnings-list" class="text-xs text-yellow-700 space-y-1 list-disc list-inside"></ul>
    </div>

    {{-- Action buttons --}}
    <div id="action-buttons" class="hidden flex gap-3">
        <a href="{{ route('import.upload') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 border border-slate-200 text-slate-600 text-sm font-semibold rounded-lg hover:bg-slate-50 transition-colors">
            <i class="bi bi-cloud-upload"></i> Import Lagi
        </a>
        <a href="{{ route('dashboard') }}" class="flex-1 flex items-center justify-center gap-2 px-4 py-2.5 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors"
            style="background-color: #0046BF !important; color: #fff !important;">
            <i class="bi bi-speedometer2"></i> Ke Dashboard
        </a>
    </div>

    {{-- Queue worker reminder (always visible) --}}
    <div class="mt-6 p-4 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-500 flex items-start gap-2">
        <i class="bi bi-terminal shrink-0 mt-0.5"></i>
        <div>
            Jika progres tidak bergerak setelah beberapa detik, pastikan
            <code class="font-mono bg-slate-200 px-1 rounded text-slate-700">php artisan queue:work</code>
            sudah berjalan di terminal terpisah.
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    const JOB_ID      = {{ $importJob->id }};
    const STATUS_URL  = '{{ route('import.status', $importJob->id) }}';
    const TOTAL_ROWS  = {{ $importJob->total_rows }};

    const fillEl           = document.getElementById('progress-fill');
    const percentageLabel  = document.getElementById('percentage-label');
    const processedLabel   = document.getElementById('processed-label');
    const statusLabel      = document.getElementById('status-label');
    const statusDot        = document.getElementById('status-dot');
    const completionBanner = document.getElementById('completion-banner');
    const actionButtons    = document.getElementById('action-buttons');
    const fkBox            = document.getElementById('fk-warnings-box');
    const fkList           = document.getElementById('fk-warnings-list');

    const fkLabels = {
        'project_id_unknown':       'project_id tidak ditemukan di master proyek lokal',
        'serial_number_id_unknown': 'serial_number_id tidak ditemukan di master nomor seri lokal',
        'device_id_unknown':        'device_id tidak ditemukan di master perangkat lokal',
    };

    let pollingInterval = null;

    function formatNum(n) {
        return Number(n).toLocaleString('id-ID');
    }

    function updateUI(data) {
        const pct = data.percentage;

        // Progress bar
        fillEl.style.width = pct + '%';
        percentageLabel.textContent = pct + '%';
        processedLabel.textContent  = formatNum(data.processed_rows) + ' diproses';

        // Counters
        document.getElementById('count-inserted').textContent = formatNum(data.inserted_count);
        document.getElementById('count-skipped').textContent  = formatNum(data.skipped_count);
        document.getElementById('count-failed').textContent   = formatNum(data.failed_count);

        // Status
        statusLabel.textContent = data.status;

        // FK warnings
        if (data.fk_warnings && Object.keys(data.fk_warnings).length > 0) {
            fkList.innerHTML = '';
            for (const [key, count] of Object.entries(data.fk_warnings)) {
                const label = fkLabels[key] || key;
                const li = document.createElement('li');
                li.textContent = `${count} baris: ${label}`;
                fkList.appendChild(li);
            }
            fkBox.classList.remove('hidden');
        }

        // Completed
        if (data.status === 'completed') {
            clearInterval(pollingInterval);
            fillEl.classList.add('completed');
            statusDot.classList.remove('pulse-dot');
            statusDot.style.backgroundColor = '#16A34A';

            completionBanner.className = 'mb-5 p-5 rounded-xl border fade-in bg-green-50 border-green-200';
            completionBanner.classList.remove('hidden');
            document.getElementById('banner-icon').innerHTML = '<i class="bi bi-check-circle-fill text-green-600"></i>';
            document.getElementById('banner-title').className = 'font-bold text-sm mb-1 text-green-800';
            document.getElementById('banner-desc').className = 'text-xs leading-relaxed text-green-700';

            if (data.filename === 'Re-Analysis') {
                document.getElementById('banner-title').textContent = 'Re-Analisis Selesai';
                document.getElementById('banner-desc').innerHTML =
                    `Re-analisis AI selesai. ` +
                    `<strong>${formatNum(data.failed_count)}</strong> data gagal dianalisis.`;
            } else {
                document.getElementById('banner-title').textContent = 'Import Selesai';
                document.getElementById('banner-desc').innerHTML =
                    `<strong>${formatNum(data.inserted_count)}</strong> baris baru ditambahkan, ` +
                    `<strong>${formatNum(data.skipped_count)}</strong> dilewati (ID sudah ada), ` +
                    `<strong>${formatNum(data.failed_count)}</strong> gagal diproses.`;
            }

            actionButtons.classList.remove('hidden');
        }

        // Failed
        if (data.status === 'failed') {
            clearInterval(pollingInterval);
            fillEl.classList.add('failed');
            statusDot.classList.remove('pulse-dot');
            statusDot.style.backgroundColor = '#DC2626';

            completionBanner.className = 'mb-5 p-5 rounded-xl border fade-in bg-red-50 border-red-200';
            completionBanner.classList.remove('hidden');
            document.getElementById('banner-icon').innerHTML = '<i class="bi bi-x-circle-fill text-red-600"></i>';
            document.getElementById('banner-title').className = 'font-bold text-sm mb-1 text-red-800';
            document.getElementById('banner-title').textContent = 'Import Gagal';
            document.getElementById('banner-desc').className = 'text-xs leading-relaxed text-red-700';
            document.getElementById('banner-desc').textContent =
                'Job mengalami error. Cek log Laravel untuk detail. Baris yang sudah berhasil diproses sebelum error tetap tersimpan.';

            actionButtons.classList.remove('hidden');
        }
    }

    function poll() {
        fetch(STATUS_URL, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(data => { updateUI(data); })
            .catch(err => { console.warn('Polling error:', err); });
    }

    // Start polling immediately, then every 3 seconds
    poll();
    pollingInterval = setInterval(poll, 3000);
</script>
@endsection

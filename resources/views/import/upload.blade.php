@extends('layouts.app')

@section('title', 'Import Data .sql')

@section('styles')
<style>
    /* Drop zone base */
    #drop-zone {
        transition: all 0.25s ease;
    }
    #drop-zone.drag-over {
        border-color: #2563EB !important;
        background-color: #EFF6FF !important;
    }
    /* Progress ring animation for file selected state */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .file-preview-card { animation: fadeInUp 0.3s ease; }
</style>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Page header --}}
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight mb-1">Import Data .sql</h1>
            <p class="text-sm text-slate-500">
                Upload file dump MySQL dari sistem produksi. Data diproses dalam antrian background.
            </p>
        </div>
        @if($trashedCount > 0)
            <div class="shrink-0">
                <a href="{{ route('import.trash') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-all shadow-sm">
                    <i class="bi bi-trash text-slate-500"></i>
                    Keranjang Sampah
                    <span class="bg-slate-200 text-slate-800 px-1.5 py-0.5 rounded-full text-[10px] font-bold">{{ $trashedCount }}</span>
                </a>
            </div>
        @endif
    </div>

    {{-- Error banner --}}
    @if($errors->any())
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-base shrink-0 mt-0.5"></i>
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table name warning (carried from previous session via flash) --}}
    @if(session('table_warning'))
        <div class="mb-5 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-circle-fill text-base shrink-0 mt-0.5"></i>
            <div>{{ session('table_warning') }}</div>
        </div>
    @endif

    {{-- Upload form card --}}
    <div class="bg-white border border-slate-200 rounded-xl shadow-card p-6 mb-6">
        <form id="upload-form" action="{{ route('import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Overwrite Warning Alert --}}
            <div class="mb-5 p-4 rounded-xl bg-amber-50 border border-amber-250 text-amber-800 text-sm flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-base shrink-0 mt-0.5 text-amber-600"></i>
                <div>
                    <strong class="font-bold block mb-1">Peringatan Import & Overwrite:</strong>
                    Sistem mendeteksi nama file SQL yang Anda upload. Jika sebelumnya Anda sudah pernah mengimport file dengan nama yang sama, seluruh data bug dari import sebelumnya akan secara otomatis ditimpa (dipindahkan ke Keranjang Sampah) sebelum file baru diproses.
                </div>
            </div>

            {{-- Drop zone --}}
            <div
                id="drop-zone"
                class="border-2 border-dashed border-slate-300 rounded-xl p-10 flex flex-col items-center justify-center text-center cursor-pointer hover:border-blue-400 hover:bg-slate-50 transition-all"
                onclick="document.getElementById('sql-file-input').click()"
            >
                <div class="w-14 h-14 rounded-2xl bg-blue-50 flex items-center justify-center mb-4">
                    <i class="bi bi-file-earmark-code text-3xl text-blue-500"></i>
                </div>
                <p class="text-slate-700 font-semibold text-sm mb-1">Seret &amp; lepas file .sql di sini</p>
                <p class="text-slate-400 text-xs mb-4">atau klik untuk memilih dari komputer</p>
                <span class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="bi bi-folder2-open"></i> Pilih File
                </span>
                <p class="text-slate-400 text-xs mt-4">Format: <code class="font-mono bg-slate-100 px-1 rounded">.sql</code> &nbsp;·&nbsp; Maks. 20 MB</p>
            </div>

            {{-- Hidden file input --}}
            <input
                type="file"
                id="sql-file-input"
                name="sql_file"
                accept=".sql,text/plain,application/sql"
                class="hidden"
                onchange="handleFileSelected(this)"
            >

            {{-- File preview (hidden until file is selected) --}}
            <div id="file-preview" class="hidden file-preview-card mt-4 p-4 rounded-xl bg-slate-50 border border-slate-200 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                    <i class="bi bi-file-earmark-code text-blue-600 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div id="file-name" class="text-sm font-semibold text-slate-800 truncate"></div>
                    <div id="file-size" class="text-xs text-slate-500 mt-0.5"></div>
                </div>
                <button
                    type="button"
                    onclick="clearFile()"
                    class="p-1.5 rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors"
                    title="Hapus pilihan"
                >
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>

            {{-- Submit button --}}
            <div class="mt-6">
                <button
                    id="submit-btn"
                    type="submit"
                    disabled
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background-color: #2563EB !important; color: #fff !important; border-color: #2563EB !important;"
                >
                    <i class="bi bi-cloud-upload text-base"></i>
                    Proses Import
                </button>
            </div>
        </form>
    </div>

    {{-- How it works info box --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
        <h3 class="text-sm font-bold text-blue-800 mb-2 flex items-center gap-2">
            <i class="bi bi-info-circle-fill"></i> Cara Kerja Import
        </h3>
        <ol class="text-xs text-blue-700 space-y-1.5 list-decimal list-inside">
            <li>File .sql diparse secara aman — SQL-nya <strong>tidak</strong> dieksekusi langsung ke database.</li>
            <li>Setiap baris dicocokkan dengan data yang sudah ada berdasarkan <code class="font-mono bg-blue-100 px-1 rounded">id</code>.</li>
            <li>Baris baru → INSERT; baris berubah → UPDATE; baris identik → dilewati (efisien).</li>
            <li>Analisis AI dijalankan otomatis hanya jika kolom <code class="font-mono bg-blue-100 px-1 rounded">description</code>, <code class="font-mono bg-blue-100 px-1 rounded">root_cause</code>, atau <code class="font-mono bg-blue-100 px-1 rounded">repair_action</code> berubah.</li>
            <li>Pemrosesan berjalan di <strong>background</strong> — Anda bisa memantau progres setelah upload.</li>
        </ol>
        <div class="mt-3 pt-3 border-t border-blue-200 text-xs text-blue-600 flex items-center gap-1.5">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>Pastikan <code class="font-mono bg-blue-100 px-1 rounded">php artisan queue:work</code> sudah berjalan di terminal terpisah.</span>
        </div>
    </div>

    {{-- Recent import history --}}
    @if($recentJobs->count() > 0)
    <div class="bg-white border border-slate-200 rounded-xl shadow-card">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-sm">Riwayat Import Terakhir</h3>
            <a href="{{ route('import.history') }}" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">
                Lihat semua →
            </a>
        </div>
        <div class="divide-y divide-slate-100">
            @foreach($recentJobs as $job)
            <div class="flex items-center gap-4 px-6 py-3 hover:bg-slate-50 transition-colors">
                {{-- Status badge --}}
                <span class="shrink-0 inline-flex items-center gap-1 px-2 py-0.5 rounded-pill text-xs font-semibold
                    @if($job->status === 'completed') bg-green-50 text-green-700
                    @elseif($job->status === 'failed') bg-red-50 text-red-700
                    @elseif($job->status === 'processing') bg-blue-50 text-blue-700
                    @else bg-slate-100 text-slate-500
                    @endif">
                    @if($job->status === 'completed') <i class="bi bi-check-circle-fill"></i>
                    @elseif($job->status === 'failed') <i class="bi bi-x-circle-fill"></i>
                    @elseif($job->status === 'processing') <i class="bi bi-arrow-repeat"></i>
                    @else <i class="bi bi-clock"></i>
                    @endif
                    {{ ucfirst($job->status) }}
                </span>

                {{-- File info --}}
                <div class="flex-1 min-w-0">
                    <div class="text-sm text-slate-700 font-medium truncate">{{ $job->filename }}</div>
                    <div class="text-xs text-slate-400">
                        {{ $job->total_rows }} baris &middot;
                        +{{ $job->inserted_count }} baru &middot;
                        ~{{ $job->updated_count }} diperbarui &middot;
                        {{ $job->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Action button --}}
                @if(in_array($job->status, ['pending', 'processing']))
                    <a href="{{ route('import.progress', $job->id) }}" class="text-xs text-blue-600 hover:underline shrink-0">
                        Pantau
                    </a>
                @elseif($job->status === 'completed')
                    <a href="{{ route('import.progress', $job->id) }}" class="text-xs text-slate-400 hover:text-slate-600 shrink-0">
                        Lihat
                    </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Danger Zone: Reset Total --}}
    <div class="bg-red-50/50 border border-red-200 rounded-xl p-6 mt-8">
        <h3 class="text-sm font-bold text-red-800 uppercase tracking-wider mb-2">Zona Bahaya (Danger Zone)</h3>
        <p class="text-xs text-slate-500 mb-4">
            Mengosongkan seluruh data bug dari dashboard. Seluruh data bug akan dipindahkan ke keranjang sampah (soft-delete).
        </p>

        @if($hasActiveJob)
            <div class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg flex items-center gap-2">
                <i class="bi bi-info-circle-fill"></i>
                Fitur reset dinonaktifkan karena sedang ada proses import yang berjalan di background.
            </div>
            <button disabled class="mt-3 px-4 py-2 bg-red-300 text-white text-xs font-semibold rounded-lg cursor-not-allowed">
                Kosongkan Data Dashboard
            </button>
        @else
            <form id="reset-form" action="{{ route('import.reset') }}" method="POST" onsubmit="return confirmReset(event)">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-lg transition-colors shadow-sm">
                    Kosongkan Data Dashboard
                </button>
            </form>
        @endif
    </div>

</div>
@endsection

@section('scripts')
<script>
    const dropZone   = document.getElementById('drop-zone');
    const fileInput  = document.getElementById('sql-file-input');
    const preview    = document.getElementById('file-preview');
    const fileNameEl = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');
    const submitBtn  = document.getElementById('submit-btn');

    // ---- Drag & Drop events ----------------------------------------
    ['dragenter', 'dragover'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropZone.addEventListener(evt, e => {
            e.preventDefault();
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', e => {
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const dt = new DataTransfer();
            dt.items.add(files[0]);
            fileInput.files = dt.files;
            showPreview(files[0]);
        }
    });

    // ---- File input change -----------------------------------------
    function handleFileSelected(input) {
        if (input.files.length > 0) {
            showPreview(input.files[0]);
        }
    }

    function showPreview(file) {
        // Validate extension client-side
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'sql') {
            alert('File harus berekstensi .sql');
            clearFile();
            return;
        }

        const sizeKB = (file.size / 1024).toFixed(1);
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        const sizeLabel = file.size >= 1024 * 1024
            ? `${sizeMB} MB`
            : `${sizeKB} KB`;

        fileNameEl.textContent = file.name;
        fileSizeEl.textContent = `Ukuran: ${sizeLabel}`;
        preview.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.style.opacity = '1';
    }

    function clearFile() {
        fileInput.value = '';
        preview.classList.add('hidden');
        submitBtn.disabled = true;
    }

    // ---- Prevent double-submit on form submit ----------------------
    document.getElementById('upload-form').addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Mengupload & Memproses...';
    });

    // ---- Reset confirmation prompt ---------------------------------
    function confirmReset(event) {
        event.preventDefault();
        const typed = prompt('PENTING: Anda akan mengosongkan seluruh data bug di dashboard.\nTindakan ini memindahkan semua data ke Keranjang Sampah.\n\nKetik kata kunci "RESET" untuk mengonfirmasi tindakan ini:');
        if (typed && typed.trim().toUpperCase() === 'RESET') {
            const form = document.getElementById('reset-form');
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'confirmation';
            input.value = typed.trim();
            form.appendChild(input);
            form.submit();
            return true;
        }
        alert('Tindakan dibatalkan.');
        return false;
    }
</script>
@endsection

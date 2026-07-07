<?php $__env->startSection('title', 'Import Data .sql'); ?>

<?php $__env->startSection('styles'); ?>
<style>
    /* Drop zone base */
    #drop-zone {
        transition: all 0.25s ease;
    }
    #drop-zone.drag-over {
        border-color: #0046BF !important;
        background-color: #EFF6FF !important;
    }
    /* Progress ring animation for file selected state */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .file-preview-card { animation: fadeInUp 0.3s ease; }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl mx-auto space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-cloud-upload-fill text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Import Data .sql</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Unggah file dump MySQL (.sql) atau batch export manufaktur</p>
            </div>
        </div>
        <a href="<?php echo e(route('import.history')); ?>" class="inline-flex items-center gap-2 px-4 py-2.5 bg-bg-secondary hover:bg-bg-tertiary border border-border-default text-text-primary font-semibold text-xs rounded-xl shadow-sm transition-all shrink-0">
            <i class="bi bi-clock-history text-accent"></i> Riwayat Import
        </a>
    </div>

    
    <?php if($errors->any()): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-base shrink-0 mt-0.5"></i>
            <ul class="list-disc list-inside space-y-0.5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <?php if(session('table_warning')): ?>
        <div class="mb-5 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-yellow-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-circle-fill text-base shrink-0 mt-0.5"></i>
            <div><?php echo e(session('table_warning')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="bg-white border border-slate-200 rounded-xl shadow-card p-6 mb-6">
        <form id="upload-form" action="<?php echo e(route('import.process')); ?>" method="POST" enctype="multipart/form-data">
            <?php echo csrf_field(); ?>

            
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

            
            <input
                type="file"
                id="sql-file-input"
                name="sql_file"
                accept=".sql,text/plain,application/sql"
                class="hidden"
                onchange="handleFileSelected(this)"
            >

            
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

            
            <div class="mt-6">
                <button
                    id="submit-btn"
                    type="submit"
                    disabled
                    class="w-full flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    style="background-color: #0046BF !important; color: #fff !important; border-color: #0046BF !important;"
                >
                    <i class="bi bi-cloud-upload text-base"></i>
                    Proses Import
                </button>
            </div>
        </form>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
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

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\MAGANG\WEB2-MAGANG\resources\views/import/upload.blade.php ENDPATH**/ ?>
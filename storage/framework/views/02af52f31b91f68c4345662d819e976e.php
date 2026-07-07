<?php $__env->startSection('title', 'Riwayat Import'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">

    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-6 border-b border-border-strong">
        <div class="flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-xl bg-bg-secondary border border-border-default flex items-center justify-center shrink-0 shadow-sm">
                <i class="bi bi-clock-history text-xl text-accent"></i>
            </div>
            <div>
                <h1 class="text-xl md:text-2xl font-black text-text-primary tracking-tight">Riwayat Import</h1>
                <p class="text-xs md:text-sm text-text-secondary mt-0.5 font-medium">Seluruh riwayat proses import file .sql dan log eksekusi</p>
            </div>
        </div>
        <a href="<?php echo e(route('import.upload')); ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-accent hover:bg-accent-hover text-white font-semibold text-xs rounded-xl shadow-sm transition-all shrink-0">
            <i class="bi bi-cloud-upload-fill text-base"></i> Import Baru
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="mb-5 p-4 rounded-xl bg-green-50 border border-green-200 text-green-700 text-sm flex items-start gap-3">
            <i class="bi bi-check-circle-fill text-base shrink-0 mt-0.5 text-green-600"></i>
            <div><?php echo e(session('success')); ?></div>
        </div>
    <?php endif; ?>
    <?php if($errors->has('delete')): ?>
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-sm flex items-start gap-3">
            <i class="bi bi-exclamation-triangle-fill text-base shrink-0 mt-0.5"></i>
            <div><?php echo e($errors->first('delete')); ?></div>
        </div>
    <?php endif; ?>

    
    <div class="bg-white border border-slate-200 rounded-xl shadow-card overflow-hidden overflow-x-auto w-full">
        <?php if($jobs->count() === 0): ?>
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <i class="bi bi-inbox text-2xl text-slate-400"></i>
                </div>
                <p class="text-slate-500 font-semibold text-sm mb-1">Belum ada riwayat import</p>
                <p class="text-slate-400 text-xs">Upload file .sql pertama Anda untuk memulai.</p>
            </div>
        <?php else: ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50 text-xs text-slate-500 uppercase tracking-wide font-semibold">
                        <th class="px-6 py-3 text-left">File</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-right">Baris</th>
                        <th class="px-4 py-3 text-right">+Baru</th>
                        <th class="px-4 py-3 text-right">Dilewati</th>
                        <th class="px-4 py-3 text-right">Gagal</th>
                        <th class="px-4 py-3 text-left">Waktu</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-slate-50 transition-colors" id="job-row-<?php echo e($job->id); ?>">
                        
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <i class="bi bi-file-earmark-code text-slate-400"></i>
                                <span class="font-medium text-slate-700 truncate max-w-[180px]" title="<?php echo e($job->filename); ?>">
                                    <?php echo e($job->filename); ?>

                                </span>
                            </div>
                            <div class="text-xs text-slate-400 mt-0.5 font-mono pl-5">
                                #<?php echo e($job->id); ?>

                            </div>
                        </td>

                        
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-pill text-xs font-semibold
                                <?php if($job->status === 'completed'): ?> bg-green-50 text-green-700
                                <?php elseif($job->status === 'failed'): ?> bg-red-50 text-red-700
                                <?php elseif($job->status === 'processing'): ?> bg-blue-50 text-blue-700
                                <?php else: ?> bg-slate-100 text-slate-500
                                <?php endif; ?>">
                                <?php if($job->status === 'completed'): ?>
                                    <i class="bi bi-check-circle-fill"></i> Selesai
                                <?php elseif($job->status === 'failed'): ?>
                                    <i class="bi bi-x-circle-fill"></i> Gagal
                                <?php elseif($job->status === 'processing'): ?>
                                    <i class="bi bi-arrow-repeat"></i> Berjalan
                                <?php else: ?>
                                    <i class="bi bi-clock"></i> Menunggu
                                <?php endif; ?>
                            </span>
                        </td>

                        
                        <td class="px-4 py-3 text-right font-mono text-slate-600 text-xs">
                            <?php echo e(number_format($job->total_rows)); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono text-blue-600 font-semibold text-xs">
                            +<?php echo e(number_format($job->inserted_count)); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono text-slate-500 font-semibold text-xs">
                            <?php echo e(number_format($job->skipped_count)); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono text-red-600 font-semibold text-xs">
                            <?php echo e(number_format($job->failed_count)); ?>

                        </td>

                        
                        <td class="px-4 py-3 text-xs text-slate-400 whitespace-nowrap">
                            <div><?php echo e($job->created_at->format('d M Y')); ?></div>
                            <div class="font-mono"><?php echo e($job->created_at->format('H:i')); ?></div>
                        </td>

                        
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?php echo e(route('import.progress', $job->id)); ?>"
                                   class="text-xs text-blue-600 hover:text-blue-700 hover:underline font-semibold whitespace-nowrap">
                                    Lihat &rarr;
                                </a>
                                <?php if(!in_array($job->status, ['pending', 'processing'])): ?>
                                    <button type="button"
                                        class="text-xs text-red-600 hover:text-red-700 font-semibold whitespace-nowrap px-2 py-1 rounded hover:bg-red-50 transition-colors"
                                        onclick="openDeleteModal(
                                            <?php echo e($job->id); ?>,
                                            '<?php echo e(addslashes($job->filename)); ?>',
                                            <?php echo e($job->inserted_count + $job->updated_count); ?>,
                                            '<?php echo e(route('import.history.delete', $job->id)); ?>'
                                        )">
                                        <i class="bi bi-trash3"></i> Hapus
                                    </button>
                                <?php else: ?>
                                    <span class="text-xs text-slate-300 font-mono px-2">&mdash;</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>

            
            <?php if($jobs->hasPages()): ?>
            <div class="px-6 py-4 border-t border-slate-100">
                <?php echo e($jobs->links()); ?>

            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

</div>


<div id="delete-modal"
     class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm hidden"
     role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 overflow-hidden"
         style="animation: fadeInUp 0.2s ease;">

        
        <div class="flex items-start gap-4 p-6 border-b border-red-100 bg-red-50">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                <i class="bi bi-exclamation-triangle-fill text-red-600 text-lg"></i>
            </div>
            <div>
                <h3 id="modal-title" class="text-base font-bold text-red-700">Hapus File dari Riwayat</h3>
                <p class="text-xs text-red-500 mt-0.5">Tindakan ini <strong>tidak dapat dibatalkan</strong></p>
            </div>
        </div>

        
        <div class="p-6 space-y-4">
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 space-y-2 text-sm">
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Nama File:</span>
                    <span id="modal-filename" class="font-mono font-semibold text-slate-800 text-right max-w-[200px] truncate text-xs"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Import Job ID:</span>
                    <span id="modal-jobid" class="font-mono font-bold text-slate-700"></span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500 font-medium">Data Bug Terdampak:</span>
                    <span id="modal-bugcount" class="font-mono font-bold text-red-600"></span>
                </div>
            </div>

            <div class="flex items-start gap-3 p-3 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-800">
                <i class="bi bi-shield-exclamation shrink-0 mt-0.5 text-amber-600"></i>
                <span>Semua data bug dari file ini akan <strong>dihapus secara permanen</strong> dari database dan tidak bisa dipulihkan.</span>
            </div>
        </div>

        
        <div class="px-6 pb-6 flex justify-end gap-3">
            <button type="button" onclick="closeDeleteModal()"
                class="px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                Batal
            </button>
            <button type="button" id="modal-confirm-btn" onclick="confirmDelete()"
                class="px-4 py-2 text-sm font-bold text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                <i class="bi bi-trash3-fill"></i>
                <span id="modal-confirm-text">Ya, Hapus Selamanya</span>
            </button>
        </div>
    </div>
</div>


<form id="delete-fallback-form" method="POST" style="display:none;">
    <?php echo csrf_field(); ?>
    <?php echo method_field('DELETE'); ?>
</form>

<?php $__env->startPush('scripts'); ?>
<script>
let _deleteUrl = null;
let _deleteJobId = null;

function openDeleteModal(jobId, filename, bugCount, deleteUrl) {
    _deleteUrl   = deleteUrl;
    _deleteJobId = jobId;

    document.getElementById('modal-filename').textContent  = filename;
    document.getElementById('modal-jobid').textContent     = '#' + jobId;
    document.getElementById('modal-bugcount').textContent  = bugCount.toLocaleString() + ' baris bug';

    const btn = document.getElementById('modal-confirm-btn');
    btn.disabled = false;
    document.getElementById('modal-confirm-text').textContent = 'Ya, Hapus Selamanya';

    document.getElementById('delete-modal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDeleteModal() {
    document.getElementById('delete-modal').classList.add('hidden');
    document.body.style.overflow = '';
    _deleteUrl   = null;
    _deleteJobId = null;
}

async function confirmDelete() {
    if (!_deleteUrl) return;

    const btn  = document.getElementById('modal-confirm-btn');
    const text = document.getElementById('modal-confirm-text');
    btn.disabled = true;
    text.textContent = 'Menghapus...';

    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.content
                   || document.querySelector('input[name="_token"]')?.value;

        const res  = await fetch(_deleteUrl, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN':  token,
                'Accept':        'application/json',
                'Content-Type':  'application/json',
            }
        });

        const data = await res.json();

        if (res.ok && data.success) {
            const row = document.getElementById('job-row-' + _deleteJobId);
            if (row) {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity    = '0';
                row.style.transform  = 'translateX(20px)';
                setTimeout(() => row.remove(), 320);
            }
            closeDeleteModal();
            showToast(data.message, 'success');
        } else {
            closeDeleteModal();
            showToast(data.message || 'Terjadi kesalahan saat menghapus.', 'error');
        }
    } catch (err) {
        // Network error or non-JSON response: submit form as fallback
        const form   = document.getElementById('delete-fallback-form');
        form.action  = _deleteUrl;
        form.submit();
    }
}

// Close on backdrop click
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) closeDeleteModal();
});

// Close on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeDeleteModal();
});

function showToast(message, type) {
    const bg    = type === 'success' ? '#16a34a' : '#dc2626';
    const icon  = type === 'success' ? 'check-circle-fill' : 'exclamation-circle-fill';
    const toast = document.createElement('div');
    toast.style.cssText = `position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;align-items:center;gap:10px;padding:14px 20px;border-radius:12px;background:${bg};color:#fff;font-size:13px;font-weight:600;box-shadow:0 8px 30px rgba(0,0,0,0.18);opacity:0;transform:translateY(10px);transition:opacity 0.3s,transform 0.3s;`;
    toast.innerHTML = `<i class="bi bi-${icon}"></i> ${message}`;
    document.body.appendChild(toast);

    requestAnimationFrame(() => {
        toast.style.opacity   = '1';
        toast.style.transform = 'translateY(0)';
    });
    setTimeout(() => {
        toast.style.opacity   = '0';
        toast.style.transform = 'translateY(10px)';
        setTimeout(() => toast.remove(), 300);
    }, 4500);
}
</script>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\MAGANG\WEB2-MAGANG\resources\views/import/history.blade.php ENDPATH**/ ?>
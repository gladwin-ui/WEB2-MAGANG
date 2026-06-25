<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImportChunkJob;
use App\Jobs\ReanalyzeBugsJob;
use App\Models\ImportJob;
use App\Models\Bug;
use App\Services\SqlImportParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * ImportController
 *
 * Handles the SQL dump import workflow:
 *   GET  /import          → show the upload form (with drag & drop UI)
 *   POST /import          → receive the .sql file, parse it, dispatch chunk jobs
 *   GET  /import/{id}/status → JSON polling endpoint for progress bar
 *   GET  /import/history  → list of past ImportJob records
 */
class ImportController extends Controller
{
    /**
     * Maximum allowed upload size in bytes.
     * PHP upload limits (upload_max_filesize / post_max_size) must be set
     * to at least this value in php.ini. Recommended: 20M in php.ini.
     */
    private const MAX_FILE_BYTES = 20 * 1024 * 1024; // 20 MB

    /**
     * Number of parsed rows dispatched per queue job chunk.
     */
    private const CHUNK_SIZE = 50;

    // ----------------------------------------------------------------
    // Display the upload form.
    // ----------------------------------------------------------------
    public function showUploadForm()
    {
        $recentJobs = ImportJob::orderByDesc('created_at')->take(10)->get();
        $hasActiveJob = ImportJob::whereIn('status', ['pending', 'processing'])->exists();
        $trashedCount = ImportJob::onlyTrashed()->count();

        return view('import.upload', compact('recentJobs', 'hasActiveJob', 'trashedCount'));
    }

    // ----------------------------------------------------------------
    // Handle file upload, parse, and dispatch jobs.
    // ----------------------------------------------------------------
    public function upload(Request $request)
    {
        // ---- Validate -----------------------------------------------
        $request->validate([
            'sql_file' => [
                'required',
                'file',
                'mimetypes:text/plain,application/sql,application/octet-stream,text/x-sql,text/x-mysqldump',
                'max:' . (self::MAX_FILE_BYTES / 1024), // Laravel's max is in KB
            ],
        ], [
            'sql_file.required'  => 'Silakan pilih file .sql untuk diupload.',
            'sql_file.file'      => 'Upload harus berupa file.',
            'sql_file.mimetypes' => 'Tipe file tidak dikenali. Pastikan file berekstensi .sql.',
            'sql_file.max'       => 'Ukuran file melebihi batas maksimal ' . (self::MAX_FILE_BYTES / 1024 / 1024) . ' MB.',
        ]);

        $file = $request->file('sql_file');

        // ---- Extension check (belt-and-suspenders) ------------------
        if (strtolower($file->getClientOriginalExtension()) !== 'sql') {
            return back()->withErrors(['sql_file' => 'File harus berekstensi .sql.']);
        }

        // ---- Read file content --------------------------------------
        $sqlContent = file_get_contents($file->getRealPath());

        if ($sqlContent === false || strlen($sqlContent) === 0) {
            return back()->withErrors(['sql_file' => 'File tidak dapat dibaca atau kosong.']);
        }

        // ---- Parse --------------------------------------------------
        $parser = new SqlImportParser();
        $parseResult = $parser->parse($sqlContent);

        // If no rows found, abort early.
        if (empty($parseResult['rows'])) {
            $errors = $parseResult['parse_errors'];
            $errorMsg = implode(' ', $errors) ?: 'File .sql tidak mengandung baris data yang dapat diproses.';
            return back()->withErrors(['sql_file' => $errorMsg]);
        }

        // ---- Warn if table name doesn't look like "bug" -------------
        $tableWarning = null;
        if (!$parseResult['table_looks_like_bug']) {
            $tableWarning = 'Peringatan: nama tabel yang terdeteksi adalah "'
                . htmlspecialchars($parseResult['detected_table'])
                . '" (tidak mengandung kata "bug"). Pastikan file yang Anda upload adalah dump dari tabel bug yang benar.';
        }

        $rows      = $parseResult['rows'];
        $totalRows = count($rows);

        // ---- Store .sql file for audit trail (optional) -------------
        $storedFilename = now()->format('Ymd_His') . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
        Storage::disk('local')->put("imports/{$storedFilename}", $sqlContent);

        $originalFilename = $file->getClientOriginalName();

        // Overwrite previous active imports with the same filename
        $existingJobs = ImportJob::where('filename', $originalFilename)->get();
        if ($existingJobs->isNotEmpty()) {
            DB::transaction(function () use ($existingJobs) {
                foreach ($existingJobs as $oldJob) {
                    // Soft-delete all bugs associated with this job ID
                    Bug::where('import_job_id', $oldJob->id)->delete();
                    // Soft-delete the job record itself
                    $oldJob->delete();
                }
            });
        }

        // ---- Create ImportJob record --------------------------------
        $importJob = ImportJob::create([
            'filename'       => $originalFilename,
            'total_rows'     => $totalRows,
            'processed_rows' => 0,
            'inserted_count' => 0,
            'updated_count'  => 0,
            'skipped_count'  => 0,
            'deleted_count'  => 0,
            'failed_count'   => 0,
            'status'         => 'pending',
            'error_message'  => null,
            'started_at'     => null,
            'finished_at'    => null,
        ]);

        // ---- Dispatch chunk jobs ------------------------------------
        $allIds = array_filter(array_column($rows, 'id'));
        $chunks = array_chunk($rows, self::CHUNK_SIZE);

        foreach ($chunks as $index => $chunk) {
            ProcessImportChunkJob::dispatch($importJob->id, $chunk, $index, $allIds);
        }

        Log::info("ImportJob #{$importJob->id} created: {$totalRows} rows in " . count($chunks) . " chunks.");

        // ---- Redirect to progress page ------------------------------
        return redirect()
            ->route('import.progress', ['id' => $importJob->id])
            ->with('table_warning', $tableWarning)
            ->with('parse_warnings', $parseResult['parse_errors'])
            ->with('success', "File berhasil diupload. {$totalRows} baris sedang diproses dalam " . count($chunks) . " job...");
    }

    // ----------------------------------------------------------------
    // Show the progress page (polling UI).
    // ----------------------------------------------------------------
    public function progress(int $id)
    {
        $importJob = ImportJob::findOrFail($id);
        return view('import.progress', compact('importJob'));
    }

    // ----------------------------------------------------------------
    // JSON endpoint for AJAX polling.
    // ----------------------------------------------------------------
    public function status(int $id)
    {
        $importJob = ImportJob::findOrFail($id);

        $percentage = $importJob->total_rows > 0
            ? min(100, (int) round(($importJob->processed_rows / $importJob->total_rows) * 100))
            : 0;

        // Parse FK warnings from error_message JSON for display.
        $fkWarnings = [];
        if (!empty($importJob->error_message)) {
            $decoded = json_decode($importJob->error_message, true);
            if (isset($decoded['fk_warnings'])) {
                $fkWarnings = $decoded['fk_warnings'];
            }
        }

        return response()->json([
            'id'              => $importJob->id,
            'filename'        => $importJob->filename,
            'status'          => $importJob->status,
            'total_rows'      => $importJob->total_rows,
            'processed_rows'  => $importJob->processed_rows,
            'inserted_count'  => $importJob->inserted_count,
            'updated_count'   => $importJob->updated_count,
            'skipped_count'   => $importJob->skipped_count,
            'deleted_count'   => $importJob->deleted_count,
            'failed_count'    => $importJob->failed_count,
            'percentage'      => $percentage,
            'fk_warnings'     => $fkWarnings,
            'started_at'      => $importJob->started_at?->toIso8601String(),
            'finished_at'     => $importJob->finished_at?->toIso8601String(),
        ]);
    }

    // ----------------------------------------------------------------
    // List all past import jobs.
    // ----------------------------------------------------------------
    public function history()
    {
        $jobs = ImportJob::orderByDesc('created_at')->paginate(15);
        return view('import.history', compact('jobs'));
    }

    // ----------------------------------------------------------------
    // Reset/Soft-delete all bugs.
    // ----------------------------------------------------------------
    public function reset(Request $request)
    {
        // Prevent race condition if any import is running
        $activeJobExists = ImportJob::whereIn('status', ['pending', 'processing'])->exists();
        if ($activeJobExists) {
            return back()->withErrors(['reset' => 'Tidak dapat mengosongkan dashboard karena sedang ada proses import yang berjalan.']);
        }

        $request->validate([
            'confirmation' => 'required|string',
        ]);

        if (strtoupper($request->confirmation) !== 'RESET') {
            return back()->withErrors(['reset' => 'Konfirmasi kata kunci salah. Silakan ketik "RESET" untuk mengonfirmasi.']);
        }

        $count = Bug::count();
        if ($count > 0) {
            // Group active bugs under a virtual ImportJob
            $importJob = ImportJob::create([
                'filename'       => 'Reset Dashboard (Batch ' . now()->format('Y-m-d H:i:s') . ')',
                'total_rows'     => $count,
                'processed_rows' => $count,
                'inserted_count' => 0,
                'updated_count'  => $count,
                'skipped_count'  => 0,
                'deleted_count'  => 0,
                'failed_count'   => 0,
                'status'         => 'completed',
                'started_at'     => now(),
                'finished_at'    => now(),
            ]);

            DB::transaction(function () use ($importJob) {
                // Update bugs to point to this import job
                Bug::whereNull('deleted_at')->update(['import_job_id' => $importJob->id]);
                // Soft-delete bugs
                Bug::query()->delete();
                // Soft-delete the import job
                $importJob->delete();
            });
        }

        Log::info("Dashboard reset: {$count} bugs soft-deleted.");

        return back()->with('success', "Dashboard berhasil dikosongkan. {$count} data bug dikelompokkan dan dipindahkan ke Sampah (Trash).");
    }

    // ----------------------------------------------------------------
    // View soft-deleted bugs in Trash.
    // ----------------------------------------------------------------
    public function trash()
    {
        $jobs = ImportJob::onlyTrashed()->orderByDesc('deleted_at')->paginate(15);
        return view('import.trash', compact('jobs'));
    }

    // ----------------------------------------------------------------
    // Restore a single soft-deleted ImportJob and its bugs.
    // ----------------------------------------------------------------
    public function restore(int $id)
    {
        $job = ImportJob::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($job) {
            $job->restore();
            Bug::onlyTrashed()->where('import_job_id', $job->id)->restore();
        });

        return back()->with('success', "File/Batch '{$job->filename}' berhasil dipulihkan.");
    }

    // ----------------------------------------------------------------
    // Restore all soft-deleted ImportJobs and their bugs.
    // ----------------------------------------------------------------
    public function restoreAll()
    {
        $trashedJobs = ImportJob::onlyTrashed()->get();
        if ($trashedJobs->isEmpty()) {
            return back()->with('info', 'Tidak ada data di Sampah yang dapat dipulihkan.');
        }

        DB::transaction(function () use ($trashedJobs) {
            foreach ($trashedJobs as $job) {
                $job->restore();
                Bug::onlyTrashed()->where('import_job_id', $job->id)->restore();
            }
        });

        return back()->with('success', "Semua data bug berhasil dipulihkan ke dashboard.");
    }

    // ----------------------------------------------------------------
    // Force delete (permanently delete) a SQL file and all its bugs.
    // ----------------------------------------------------------------
    public function forceDelete(int $id)
    {
        $job = ImportJob::onlyTrashed()->findOrFail($id);

        DB::transaction(function () use ($job) {
            Bug::onlyTrashed()->where('import_job_id', $job->id)->forceDelete();
            $job->forceDelete();
        });

        return back()->with('success', "File/Batch '{$job->filename}' dan data bug terkait berhasil dihapus selamanya.");
    }

    // ----------------------------------------------------------------
    // Permanently delete selected soft-deleted ImportJobs and their bugs.
    // ----------------------------------------------------------------
    public function forceDeleteSelected(Request $request)
    {
        $request->validate([
            'selected_jobs'   => 'required|array|min:1',
            'selected_jobs.*' => 'integer|distinct',
        ], [
            'selected_jobs.required' => 'Pilih setidaknya satu file atau batch untuk dihapus selamanya.',
        ]);

        $selectedIds = $request->input('selected_jobs', []);
        $jobs = ImportJob::onlyTrashed()->whereIn('id', $selectedIds)->get();

        if ($jobs->isEmpty()) {
            return back()->withErrors(['selected_jobs' => 'Tidak ada file/batch yang valid dipilih untuk dihapus.']);
        }

        $deletedCount = $jobs->count();

        DB::transaction(function () use ($jobs) {
            foreach ($jobs as $job) {
                Bug::onlyTrashed()->where('import_job_id', $job->id)->forceDelete();
                $job->forceDelete();
            }
        });

        return back()->with('success', "{$deletedCount} file/batch terpilih berhasil dihapus selamanya.");
    }

    // ----------------------------------------------------------------
    // Trigger batch re-analysis of pending bugs.
    // ----------------------------------------------------------------
    public function reanalyze()
    {
        // Prevent race condition if any import is running
        $activeJobExists = ImportJob::whereIn('status', ['pending', 'processing'])->exists();
        if ($activeJobExists) {
            return back()->withErrors(['reanalyze' => 'Tidak dapat melakukan re-analisis karena sedang ada proses import yang berjalan.']);
        }

        // Find bugs needing analysis
        $bugs = Bug::all()->filter(function($bug) {
            $needStage1 = empty($bug->sentiment_label) && !empty($bug->description);
            $needStage2 = empty($bug->damage_category) && !empty($bug->root_cause) && !empty($bug->repair_action);
            return $needStage1 || $needStage2;
        });

        $bugIds = $bugs->pluck('id')->toArray();
        $totalRows = count($bugIds);

        if ($totalRows === 0) {
            return back()->with('info', 'Semua data bug sudah teranalisis. Tidak ada baris tertunda.');
        }

        // Create ImportJob record
        $importJob = ImportJob::create([
            'filename'       => 'Re-Analysis',
            'total_rows'     => $totalRows,
            'processed_rows' => 0,
            'inserted_count' => 0,
            'updated_count'  => 0,
            'skipped_count'  => 0,
            'deleted_count'  => 0,
            'failed_count'   => 0,
            'status'         => 'pending',
            'error_message'  => null,
            'started_at'     => null,
            'finished_at'    => null,
        ]);

        // Dispatch chunks
        $chunks = array_chunk($bugIds, 20); // 20 per chunk
        foreach ($chunks as $index => $chunk) {
            ReanalyzeBugsJob::dispatch($importJob->id, $chunk, $index);
        }

        return redirect()
            ->route('import.progress', ['id' => $importJob->id])
            ->with('success', "Proses re-analisis dimulai. {$totalRows} baris sedang dianalisis ulang oleh sistem AI...");
    }
}

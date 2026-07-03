<?php

namespace App\Jobs;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Models\Project;
use App\Models\SerialNumber;
use App\Models\Device;
use App\Services\BugAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * ProcessImportChunkJob
 *
 * Processes a chunk of rows parsed from an uploaded .sql dump file.
 * Each job handles up to CHUNK_SIZE rows and performs simple insert-only logic:
 *
 *   - INSERT  : row id does not exist locally → insert + run AI analysis.
 *   - SKIP    : row id already exists locally → do nothing (no update).
 *   - FAIL    : row missing required fields or causes a DB exception.
 *
 * Foreign-key resolution (project_id, serial_number_id, device_id):
 *   If the referenced master-data record does not exist locally, the FK
 *   column is set to null (not a failure). The count of such "unknown FK"
 *   rows is accumulated in ImportJob.error_message as a JSON summary.
 *
 * Per-row failures (unparseable data, unexpected exceptions) increment
 * failed_count and are logged but do NOT abort the chunk.
 */
class ProcessImportChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Maximum number of retry attempts if the job itself fails
     * (distinct from per-row failures which are handled internally).
     */
    public int $tries = 3;

    /**
     * Timeout in seconds for this job.
     * 50 rows × ~1 s AI call = ~50 s; add headroom.
     */
    public int $timeout = 300;

    // ----------------------------------------------------------------
    // AI is only run on INSERT. There is no UPDATE path.
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    // All columns sourced from the .sql dump.
    // Columns NOT in this list are internal BugTrack columns (AI results,
    // assigned_to, etc.) and are never overwritten by the import.
    // ----------------------------------------------------------------
    private const SOURCE_COLUMNS = [
        'id',
        'project_id',
        'title',
        'severity',
        'serial_number_id',
        'sn_code_snapshot',
        'reporter_type',
        'device_id',
        'description',
        'product_version',
        'environment',
        'reproduce_steps',
        'root_cause',
        'repair_action',
        'is_rework',
        'attachment_path',
        'expected_result',
        'reported_by',
        'status',
        'fixed_by',
        'closed_at',
        'created_at',
        'updated_at',
    ];

    public function __construct(
        private readonly int   $importJobId,
        private readonly array $rows,        // chunk of parsed associative rows
        private readonly int   $chunkIndex,  // zero-based chunk index (for logging)
        private readonly array $allIds = [], // all unique bug IDs in the full SQL file
    ) {}

    public function handle(BugAnalyticsService $analytics): void
    {
        $importJob = ImportJob::find($this->importJobId);

        if (!$importJob) {
            Log::error("ProcessImportChunkJob: ImportJob #{$this->importJobId} not found. Aborting chunk {$this->chunkIndex}.");
            return;
        }

        // Mark the import job as "processing" on the first chunk that runs.
        if ($importJob->status === 'pending') {
            $importJob->update([
                'status'     => 'processing',
                'started_at' => now(),
            ]);
        }

        // Counters for this chunk.
        $inserted  = 0;
        $skipped   = 0;
        $failed    = 0;

        // Accumulated FK-not-found warnings: ['project_id' => 3, ...]
        $fkWarnings = [];

        // Pre-load valid master-data IDs for FK resolution.
        $validProjectIds      = Project::pluck('id')->flip()->all();
        $validSerialNumberIds = SerialNumber::pluck('id')->flip()->all();
        $validDeviceIds       = Device::pluck('id')->flip()->all();

        foreach ($this->rows as $rawRow) {
            try {
                $row = $this->normalizeRow($rawRow);

                // --------------------------------------------------------
                // Mandatory field check (title must be present).
                // --------------------------------------------------------
                if (empty($row['id']) || empty($row['title'])) {
                    $failed++;
                    Log::warning("ImportJob #{$this->importJobId} chunk {$this->chunkIndex}: row missing id or title, skipping.", ['row' => $rawRow]);
                    continue;
                }

                // --------------------------------------------------------
                // FK resolution: set null + record warning if unknown.
                // --------------------------------------------------------
                $row = $this->resolveForeignKeys($row, $validProjectIds, $validSerialNumberIds, $validDeviceIds, $fkWarnings);

                // --------------------------------------------------------
                // Load existing bug (if any) in a single query.
                // --------------------------------------------------------
                $existing = Bug::withTrashed()->find($row['id']);

                if ($existing !== null) {
                    if ($existing->trashed()) {
                        // A trashed (previously deleted) bug exists. Permanently remove
                        // it so the re-imported row can be inserted fresh and counted
                        // as INSERT — no "memory" of the old record remains.
                        $existing->forceDelete();
                    } else {
                        // ---- SKIP ---------------------------------------
                        // Active existing row: leave it untouched (no update).
                        $skipped++;
                        continue;
                    }
                }

                // ---- INSERT ---------------------------------------------
                $bugData = $this->buildBugData($row);
                $bugData['import_job_id'] = $this->importJobId;
                $bugData['is_rework'] = (bool) ($row['is_rework'] ?? false);

                $bug = Bug::create($bugData);

                // Run AI analysis on newly inserted rows.
                $this->runStage1IfNeeded($bug, $analytics);

                $inserted++;

            } catch (\Throwable $e) {
                $failed++;
                Log::error(
                    "ImportJob #{$this->importJobId} chunk {$this->chunkIndex}: unhandled exception on row.",
                    ['exception' => $e->getMessage(), 'row_id' => $rawRow['id'] ?? 'unknown']
                );
            }
        }

        // ----------------------------------------------------------------
        // Atomically increment the ImportJob counters.
        // ----------------------------------------------------------------
        DB::transaction(function () use ($importJob, $inserted, $skipped, $failed, $fkWarnings) {
            $importJob->refresh(); // get latest values

            $newProcessed = $importJob->processed_rows + $inserted + $skipped + $failed;
            $newInserted  = $importJob->inserted_count  + $inserted;
            $newSkipped   = $importJob->skipped_count   + $skipped;
            $newFailed    = $importJob->failed_count    + $failed;

            // Merge FK warnings into the existing error_message JSON log.
            $errorLog = $this->mergeErrorLog($importJob->error_message, $fkWarnings);

            // Determine whether all chunks have been processed.
            $allDone = $newProcessed >= $importJob->total_rows;

            $importJob->update([
                'processed_rows' => $newProcessed,
                'inserted_count' => $newInserted,
                'skipped_count'  => $newSkipped,
                'failed_count'   => $newFailed,
                'error_message'  => $errorLog ?: null,
                'status'         => $allDone ? 'completed' : $importJob->status,
                'finished_at'    => $allDone ? now()        : $importJob->finished_at,
            ]);
        });
    }

    /**
     * If the job itself fails after all retries, mark the ImportJob as failed.
     */
    public function failed(\Throwable $exception): void
    {
        $importJob = ImportJob::find($this->importJobId);
        if ($importJob && !in_array($importJob->status, ['completed'], true)) {
            $importJob->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_message' => 'Job chunk gagal setelah ' . $this->tries . ' percobaan: ' . $exception->getMessage(),
            ]);
        }
    }

    // ====================================================================
    // Private helpers
    // ====================================================================

    /**
     * Normalize raw parsed values: cast types, coerce empty strings to null,
     * handle datetime strings, etc.
     */
    private function normalizeRow(array $raw): array
    {
        $row = [];

        foreach (self::SOURCE_COLUMNS as $col) {
            $val = $raw[$col] ?? null;

            // Coerce empty strings to null for nullable text columns.
            if ($val === '') {
                $val = null;
            }

            // Parse datetime columns.
            if (in_array($col, ['closed_at', 'created_at', 'updated_at'], true)) {
                $val = $val !== null ? $this->parseDatetime($val) : null;
            }

            // Boolean columns.
            if ($col === 'is_rework') {
                $val = (bool)(int)($val ?? 0);
            }

            $row[$col] = $val;
        }

        return $row;
    }

    /**
     * Resolve project_id, serial_number_id, device_id against local master data.
     * Unknown IDs are replaced with null and recorded as warnings.
     */
    private function resolveForeignKeys(
        array $row,
        array $validProjectIds,
        array $validSerialNumberIds,
        array $validDeviceIds,
        array &$fkWarnings
    ): array {
        if (!empty($row['project_id']) && !isset($validProjectIds[$row['project_id']])) {
            $fkWarnings['project_id_unknown'] = ($fkWarnings['project_id_unknown'] ?? 0) + 1;
            $row['project_id'] = null;
        }

        if (!empty($row['serial_number_id']) && !isset($validSerialNumberIds[$row['serial_number_id']])) {
            $fkWarnings['serial_number_id_unknown'] = ($fkWarnings['serial_number_id_unknown'] ?? 0) + 1;
            $row['serial_number_id'] = null;
        }

        if (!empty($row['device_id']) && !isset($validDeviceIds[$row['device_id']])) {
            $fkWarnings['device_id_unknown'] = ($fkWarnings['device_id_unknown'] ?? 0) + 1;
            $row['device_id'] = null;
        }

        return $row;
    }

    /**
     * Build the full data array for Bug::create(), sourcing only from
     * SOURCE_COLUMNS. AI columns are initialized to null/false.
     */
    private function buildBugData(array $row): array
    {
        return [
            'id'                           => $row['id'],
            'project_id'                   => $row['project_id'],
            'title'                        => $row['title'],
            'severity'                     => $row['severity'] ?? 'Major',
            'serial_number_id'             => $row['serial_number_id'],
            'sn_code_snapshot'             => $row['sn_code_snapshot'],
            'reporter_type'                => $row['reporter_type'] ?? 'produk',
            'device_id'                    => $row['device_id'],
            'description'                  => $row['description'],
            'product_version'              => $row['product_version'],
            'environment'                  => $row['environment'],
            'reproduce_steps'              => $row['reproduce_steps'],
            'root_cause'                   => $row['root_cause'],
            'repair_action'                => $row['repair_action'],
            'is_rework'                    => $row['is_rework'] ?? false,
            'attachment_path'              => $row['attachment_path'],
            'expected_result'              => $row['expected_result'],
            'reported_by'                  => $row['reported_by'],
            'status'                       => $row['status'] ?? 'OPEN',
            'fixed_by'                     => $row['fixed_by'],
            'closed_at'                    => $row['closed_at'],
            'created_at'                   => $row['created_at'],
            'updated_at'                   => $row['updated_at'],

            // AI columns — init to null; populated by runStage1/Stage2 below.
            'sentiment_label'              => null,
            'sentiment_score'              => null,
            'severity_recommended'         => null,
            'severity_recommendation_reason' => null,
        ];
    }

    /**
     * Run AI Stage 1 (sentiment, spam, severity_recommended) if description is present.
     */
    private function runStage1IfNeeded(Bug $bug, BugAnalyticsService $analytics): void
    {
        if (empty($bug->description)) {
            return;
        }

        $result = $analytics->analyzeBugReport($bug);

        if (!empty($result)) {
            $bug->update([
                'sentiment_label'              => $result['sentiment_label']              ?? null,
                'sentiment_score'              => $result['sentiment_score']              ?? null,
                'severity_recommended'         => $result['severity_recommended']         ?? null,
                'severity_recommendation_reason' => $result['severity_recommendation_reason'] ?? null,
            ]);
        }
    }



    /**
     * Parse a datetime string safely; returns null on failure.
     */
    private function parseDatetime(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Merge a new set of FK warnings into the existing JSON error_message log
     * stored in ImportJob. Keeps a running tally across chunks.
     *
     * The error_message column stores a JSON object like:
     * {
     *   "fk_warnings": {
     *     "project_id_unknown": 3,
     *     "serial_number_id_unknown": 1
     *   }
     * }
     */
    private function mergeErrorLog(?string $existing, array $newWarnings): string
    {
        $log = [];

        if (!empty($existing)) {
            $decoded = json_decode($existing, true);
            if (is_array($decoded)) {
                $log = $decoded;
            }
        }

        if (!empty($newWarnings)) {
            foreach ($newWarnings as $key => $count) {
                $log['fk_warnings'][$key] = ($log['fk_warnings'][$key] ?? 0) + $count;
            }
        }

        return json_encode($log, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}

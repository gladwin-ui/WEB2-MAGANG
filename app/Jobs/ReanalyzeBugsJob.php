<?php

namespace App\Jobs;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Services\BugAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReanalyzeBugsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Tries count
     */
    public int $tries = 3;

    /**
     * Timeout
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private readonly int   $importJobId,
        private readonly array $bugIds,
        private readonly int   $chunkIndex,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(BugAnalyticsService $analytics): void
    {
        $importJob = ImportJob::find($this->importJobId);
        if (!$importJob) {
            Log::error("ReanalyzeBugsJob: ImportJob #{$this->importJobId} not found. Aborting.");
            return;
        }

        if ($importJob->status === 'pending') {
            $importJob->update([
                'status'     => 'processing',
                'started_at' => now(),
            ]);
        }

        $processed = 0;
        $updated   = 0;
        $failed    = 0;

        foreach ($this->bugIds as $id) {
            try {
                $bug = Bug::find($id);
                if (!$bug) {
                    $processed++;
                    continue;
                }

                $needStage1 = empty($bug->sentiment_label) && !empty($bug->description);
                $needStage2 = empty($bug->damage_category) && !empty($bug->root_cause) && !empty($bug->repair_action);

                if (!$needStage1 && !$needStage2) {
                    $processed++;
                    continue;
                }

                $isUpdated = false;

                if ($needStage1) {
                    $res1 = $analytics->analyzeBugReport($bug);
                    if (!empty($res1)) {
                        $bug->sentiment_label                  = $res1['sentiment_label'] ?? null;
                        $bug->sentiment_score                  = $res1['sentiment_score'] ?? null;
                        $bug->is_spam                          = (bool) ($res1['is_spam'] ?? false);
                        $bug->spam_reason                      = $res1['spam_reason'] ?? null;
                        $bug->severity_recommended             = $res1['severity_recommended'] ?? null;
                        $bug->severity_recommendation_reason   = $res1['severity_recommendation_reason'] ?? null;
                        $isUpdated = true;
                    } else {
                        $failed++;
                        continue;
                    }
                }

                if ($needStage2) {
                    $res2 = $analytics->analyzeDamageCause($bug->root_cause, $bug->repair_action);
                    if (!empty($res2['damage_category'])) {
                        $bug->damage_category = $res2['damage_category'];
                        $isUpdated = true;
                    } else {
                        $failed++;
                        continue;
                    }
                }

                if ($isUpdated) {
                    $bug->save();
                    $updated++;
                }

                $processed++;

            } catch (\Throwable $e) {
                $failed++;
                Log::error("ReanalyzeBugsJob: Exception processing bug #{$id}: " . $e->getMessage());
            }
        }

        // Increment stats atomically
        DB::transaction(function () use ($importJob, $processed, $updated, $failed) {
            $importJob->refresh();
            $newProcessed = $importJob->processed_rows + $processed;
            $newUpdated   = $importJob->updated_count   + $updated;
            $newFailed    = $importJob->failed_count    + $failed;

            $allDone = $newProcessed >= $importJob->total_rows;

            $importJob->update([
                'processed_rows' => $newProcessed,
                'updated_count'  => $newUpdated,
                'failed_count'   => $newFailed,
                'status'         => $allDone ? 'completed' : $importJob->status,
                'finished_at'    => $allDone ? now()        : $importJob->finished_at,
            ]);
        });
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $importJob = ImportJob::find($this->importJobId);
        if ($importJob && !in_array($importJob->status, ['completed'], true)) {
            $importJob->update([
                'status'        => 'failed',
                'finished_at'   => now(),
                'error_message' => 'Re-analisis gagal setelah percobaan ulang: ' . $exception->getMessage(),
            ]);
        }
    }
}

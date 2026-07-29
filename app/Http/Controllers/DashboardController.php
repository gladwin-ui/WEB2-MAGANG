<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Models\Project;
use App\Models\BugAiCache;
use App\Services\BugAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DashboardController extends Controller
{
    /**
     * Helper to map logical columns to physical columns based on app mode.
     */
    private function col(string $logical): string
    {
        if (config('app.mode') !== 'readonly') {
            return $logical;
        }

        $map = [
            'status'        => 'bugstatus',
            'project_id'    => 'idproject',
            'title'         => 'bug_title',
            'description'   => 'bugdesc',
            'root_cause'    => 'rootcause',
            'reported_by'   => 'bugcreatedby',
            'reporter_type' => 'tipe_pelapor',
            'severity'      => 'severity',
            'is_rework'     => 'is_rework',
            'created_at'    => 'created_at',
        ];

        return $map[$logical] ?? $logical;
    }

    /**
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        // Get all completed active import jobs
        $sqlFiles = ImportJob::where('status', 'completed')
            ->whereNull('deleted_at')  // Explicit: exclude soft-deleted jobs
            ->orderByDesc('created_at')
            ->get();

        $hasImportedData = config('app.mode') === 'readonly'
            ? Bug::exists()
            : ($sqlFiles->isNotEmpty() || Bug::exists());

        if ($hasImportedData) {
            $analytics = app(BugAnalyticsService::class);

            if (config('app.mode') === 'readonly') {
                // READONLY: analisis + simpan ke cache (bug_ai_cache), TIDAK tulis tabel bug
                $cachedIds = BugAiCache::pluck('bug_id')->toArray();
                $unanalyzedBugs = Bug::whereNotIn('idbug', $cachedIds ?: [0])
                    ->limit(500)
                    ->get();

                foreach ($unanalyzedBugs as $bug) {
                    $analytics->getOrAnalyze($bug);
                }
            } else {
                // IMPORT: perilaku lama (simpan ke tabel bugs)
                $unanalyzedBugs = Bug::whereNull('sentiment_label')
                    ->whereNotNull('title')
                    ->limit(500)
                    ->get();

                if ($unanalyzedBugs->isNotEmpty()) {
                    foreach ($unanalyzedBugs as $bug) {
                        $res1 = $analytics->analyzeBugReport($bug);
                        if (!empty($res1)) {
                            try {
                                $bug->sentiment_label                  = $res1['sentiment_label'] ?? null;
                                $bug->sentiment_score                  = $res1['sentiment_score'] ?? null;
                                $bug->severity_recommended             = $res1['severity_recommended'] ?? null;
                                $bug->severity_recommendation_reason   = $res1['severity_recommendation_reason'] ?? null;
                                $bug->save();
                            } catch (\Exception $e) {
                                \Log::info('Read-only mode: skip AI save for bug #' . $bug->id);
                            }
                        }
                    }
                }
            }
        }

        if (!$hasImportedData) {
            return view('dashboard.index', [
                'hasImportedData'      => false,
                'totalBugs'            => 0,
                'openBugs'             => 0,
                'closedBugs'           => 0,
                'criticalBugs'         => 0,
                'reworkRate'           => 0,
                'reworkCount'          => 0,
                'topProjects'          => collect(),
                'volumeTrend'          => collect(),
                'auditBugs'            => Bug::whereRaw('1=0')->paginate(20),
                'projects'             => collect(),
                'sqlFiles'             => collect(),
                'selectedJobId'        => 'all',
                'severityCounts'       => [
                    'critical' => 0,
                    'major'    => 0,
                    'minor'    => 0,
                ],
                'severityOpen'         => ['Critical' => 0, 'Major' => 0, 'Minor' => 0],
                'severityClosed'       => ['Critical' => 0, 'Major' => 0, 'Minor' => 0],
            ]);
        }

        // Default ke 'all' (Semua Sumber Data: baik SQL maupun DB lokal)
        $selectedJobId = $request->input('import_job_id', 'all');

        /**
         * Helper: apply import_job_id scope to a query builder.
         * - specific job  → WHERE import_job_id = $selectedJobId
         * - 'local'       → WHERE import_job_id IS NULL (khusus data database lokal)
         * - 'all'         → Semua data di tabel bugs (tanpa filter import_job_id)
         */
        $applyJobFilter = function ($query) use ($selectedJobId) {
            if (config('app.mode') === 'readonly') {
                return $query;
            }
            if ($selectedJobId !== 'all' && $selectedJobId !== 'local') {
                $query->where('import_job_id', $selectedJobId);
            } elseif ($selectedJobId === 'local') {
                $query->whereNull('import_job_id');
            }
            return $query;
        };

        // Prepare physical column names
        $statusCol = $this->col('status');
        $projectCol = $this->col('project_id');
        $reporterCol = $this->col('reporter_type');
        $severityCol = $this->col('severity');
        $isReworkCol = $this->col('is_rework');
        $createdAtCol = $this->col('created_at');
        $isReadonly = config('app.mode') === 'readonly';

        // 1. totalBugs
        $totalBugs = $applyJobFilter(Bug::query())->count();

        // 2. openBugs
        $openBugs = $applyJobFilter(
            $isReadonly
                ? Bug::whereRaw("UPPER($statusCol) = 'OPEN'")
                : Bug::where('status', 'OPEN')
        )->count();

        // 3. closedBugs
        $closedBugs = $applyJobFilter(
            $isReadonly
                ? Bug::whereRaw("UPPER($statusCol) = 'CLOSED'")
                : Bug::where('status', 'CLOSED')
        )->count();

        // 4. criticalBugs
        $criticalBugs = $applyJobFilter(Bug::where($severityCol, 'Critical'))->count();

        // 5. reworkRate
        $reworkCount = $applyJobFilter(Bug::where($isReworkCol, true))->count();
        $reworkRate = $totalBugs > 0
                        ? round($reworkCount / $totalBugs * 100, 1)
                        : 0;

        // 6. topProjects
        $topProjectsQuery = Bug::query();
        if (!$isReadonly) {
            $topProjectsQuery->with('project');
        }
        $topProjects = $applyJobFilter($topProjectsQuery)
            ->groupBy($projectCol)
            ->selectRaw("$projectCol as project_id, count(*) as total")
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // 7. volumeTrend
        $volumeTrend = $applyJobFilter(
                Bug::selectRaw("DATE($createdAtCol) as date, count(*) as total")
                   ->where($createdAtCol, '>=', now()->subDays(7))
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // 8. Severity Map
        $sevMap = $applyJobFilter(Bug::query())
            ->groupBy($severityCol)
            ->selectRaw("$severityCol as severity, count(*) as count")
            ->pluck('count', 'severity')
            ->toArray();
        $severityCounts = [
            'critical' => $sevMap['Critical'] ?? 0,
            'major'    => $sevMap['Major']    ?? 0,
            'minor'    => $sevMap['Minor']    ?? 0,
        ];

        // 9. Severity breakdown by status (for dual pie chart)
        $sevOpenMap = $applyJobFilter(
                $isReadonly
                    ? Bug::whereRaw("UPPER($statusCol) = 'OPEN'")
                    : Bug::where('status', 'OPEN')
            )
            ->groupBy($severityCol)
            ->selectRaw("$severityCol as severity, count(*) as count")
            ->pluck('count', 'severity')
            ->toArray();
        $severityOpen = [
            'Critical' => $sevOpenMap['Critical'] ?? 0,
            'Major'    => $sevOpenMap['Major']    ?? 0,
            'Minor'    => $sevOpenMap['Minor']    ?? 0,
        ];

        $sevClosedMap = $applyJobFilter(
                $isReadonly
                    ? Bug::whereRaw("UPPER($statusCol) = 'CLOSED'")
                    : Bug::where('status', 'CLOSED')
            )
            ->groupBy($severityCol)
            ->selectRaw("$severityCol as severity, count(*) as count")
            ->pluck('count', 'severity')
            ->toArray();
        $severityClosed = [
            'Critical' => $sevClosedMap['Critical'] ?? 0,
            'Major'    => $sevClosedMap['Major']    ?? 0,
            'Minor'    => $sevClosedMap['Minor']    ?? 0,
        ];

        // 10. Dynamic Assembly Stage breakdown from database (reporter_type column)
        $rawStageMap = $applyJobFilter(Bug::query())
            ->selectRaw("COALESCE(NULLIF(TRIM($reporterCol), ''), 'Tidak Diketahui') as stage, count(*) as count")
            ->groupBy('stage')
            ->orderByDesc('count')
            ->pluck('count', 'stage')
            ->toArray();

        $assemblyStageMap = [];
        foreach ($rawStageMap as $stage => $count) {
            $label = match(strtolower($stage)) {
                'produk' => 'Unit Jadi (Produk)',
                'sub'    => 'Sub-Komponen (PCB)',
                default  => ucwords($stage),
            };
            $assemblyStageMap[$label] = ($assemblyStageMap[$label] ?? 0) + $count;
        }

        // 11. Audit Table with Filter
        $auditQuery = Bug::query();
        if ($isReadonly) {
            $auditQuery->with(['project', 'device']);
        } else {
            $auditQuery->with(['project', 'device']);
        }

        $query = $applyJobFilter($auditQuery);

        if ($request->status) {
            if ($isReadonly) {
                $query->whereRaw("UPPER($statusCol) = ?", [strtoupper($request->status)]);
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->severity) {
            $query->where($severityCol, $request->severity);
        }
        if ($request->project_id) {
            $query->where($projectCol, $request->project_id);
        }
        if ($request->date_from) {
            $query->whereDate($createdAtCol, '>=', $request->date_from);
        }
        if ($request->date_to) {
            $query->whereDate($createdAtCol, '<=', $request->date_to);
        }

        $urgencySort = $request->input('urgency_sort');
        if (in_array($urgencySort, ['desc', 'asc'], true)) {
            if ($isReadonly) {
                // JOIN to cache for sentiment on readonly mode
                $query->leftJoin('bug_ai_cache', 'bug.idbug', '=', 'bug_ai_cache.bug_id')
                    ->selectRaw("
                        bug.*,
                        ROUND(
                            (
                                CASE
                                    WHEN bug.severity = 'Critical' THEN 0.8
                                    WHEN bug.severity = 'Major' THEN 0.5
                                    ELSE 0.2
                                END
                                + (1 - COALESCE(bug_ai_cache.sentiment_score, 0.5))
                            ) / 2,
                        2) AS urgency_score
                    ");
            } else {
                $query->selectRaw("
                    bugs.*,
                    ROUND(
                        (
                            CASE
                                WHEN bugs.severity = 'Critical' THEN 0.8
                                WHEN bugs.severity = 'Major' THEN 0.5
                                ELSE 0.2
                            END
                            + (1 - COALESCE(bugs.sentiment_score, 0.5))
                        ) / 2,
                    2) AS urgency_score
                ");
            }

            if ($urgencySort === 'desc') {
                $query->orderByDesc('urgency_score')->orderByDesc($createdAtCol);
            } else {
                $query->orderBy('urgency_score')->orderByDesc($createdAtCol);
            }
        } else {
            // Default: newest reports first
            if ($isReadonly) {
                $query->orderByDesc('idbug');
            } else {
                $query->latest();
            }
        }

        $auditBugs = $query->paginate(10)->withQueryString();

        // Fetch projects for filter dropdown (hanya yang memiliki laporan bug aktif)
        $projects = Project::whereIn('id', function ($q) use ($isReadonly, $projectCol) {
            $table = $isReadonly ? 'bug' : 'bugs';
            $q->select($projectCol)->from($table)
              ->whereNotNull($projectCol)
              ->distinct();
            if (!$isReadonly) {
                $q->whereNull('deleted_at');
            }
        })->orderBy('id')->get();

        return view('dashboard.index', compact(
            'hasImportedData',
            'totalBugs', 'openBugs', 'closedBugs', 'criticalBugs', 'reworkRate', 'reworkCount',
            'topProjects', 'volumeTrend',
            'auditBugs', 'projects', 'sqlFiles', 'selectedJobId', 'severityCounts',
            'severityOpen', 'severityClosed', 'assemblyStageMap'
        ));
    }

    /**
     * Export bug reports to Excel.
     */
    public function exportExcel(Request $request)
    {
        $selectedJobId = $request->input('import_job_id', 'all');
        $isReadonly = config('app.mode') === 'readonly';
        $statusCol = $this->col('status');
        $projectCol = $this->col('project_id');
        $severityCol = $this->col('severity');
        $createdAtCol = $this->col('created_at');

        $auditQuery = Bug::with(['project', 'serialNumber']);
        if (!$isReadonly) {
            if ($selectedJobId !== 'all' && $selectedJobId !== 'local') {
                $auditQuery->where('import_job_id', $selectedJobId);
            } elseif ($selectedJobId === 'local') {
                $auditQuery->whereNull('import_job_id');
            }
        }

        // Apply same filters as dashboard
        if ($request->filled('project_id')) {
            $auditQuery->where($projectCol, $request->project_id);
        }
        if ($request->filled('status')) {
            if ($isReadonly) {
                $auditQuery->whereRaw("UPPER($statusCol) = ?", [strtoupper($request->status)]);
            } else {
                $auditQuery->where('status', $request->status);
            }
        }
        if ($request->filled('severity')) {
            $auditQuery->where($severityCol, $request->severity);
        }
        if ($request->filled('date_from')) {
            $auditQuery->where($createdAtCol, '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $auditQuery->where($createdAtCol, '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        if ($isReadonly) {
            $bugs = $auditQuery->orderBy('idbug', 'desc')->get();
        } else {
            $bugs = $auditQuery->orderBy('created_at', 'desc')->get();
        }
        
        $export = new \App\Exports\LaporanUmumExport($bugs);
        $spreadsheet = $export->generate();
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);

        return Response::stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            "Content-Type"        => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=Laporan_Umum_ManufakTrack_" . date('Ymd_His') . ".xlsx",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ]);
    }
}

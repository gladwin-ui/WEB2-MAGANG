<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Models\Project;
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
     * Display the analytics dashboard.
     */
    public function index(Request $request)
    {
        // Get all completed active import jobs
        $sqlFiles = ImportJob::where('status', 'completed')
            ->whereNull('deleted_at')  // Explicit: exclude soft-deleted jobs
            ->orderByDesc('created_at')
            ->get();

        $hasImportedData = $sqlFiles->isNotEmpty();

        if ($hasImportedData) {
            $analytics = app(BugAnalyticsService::class);

            // Auto-analyze bugs missing sentiment
            $unanalyzedBugs = Bug::where(function($q) {
                    $q->whereNull('sentiment_label');
                })
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
                            // Skip simpan AI kalau bugs adalah VIEW (read-only)
                            // Dashboard tetap tampil dengan data dari VIEW
                            \Log::info('Read-only mode: skip AI save for bug #' . $bug->id);
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

        // Determine selected SQL file
        $selectedJobId = $request->input('import_job_id');
        if ($selectedJobId === null) {
            $latestJob = $sqlFiles->first();
            $selectedJobId = $latestJob ? (string)$latestJob->id : 'all';
        }

        // IDs of all active (non-deleted) completed jobs — used when filter is 'all'
        // This ensures 'all' means "all ACTIVE files combined", not "every bug in DB"
        $activeJobIds = $sqlFiles->pluck('id');

        /**
         * Helper: apply import_job_id scope to a query builder.
         * - specific job  → WHERE import_job_id = $selectedJobId
         * - 'all'         → WHERE import_job_id IN ($activeJobIds)
         */
        $applyJobFilter = function ($query) use ($selectedJobId, $activeJobIds) {
            if ($selectedJobId !== 'all') {
                $query->where('import_job_id', $selectedJobId);
            } else {
                $query->whereIn('import_job_id', $activeJobIds);
            }
            return $query;
        };

        // 1. totalBugs
        $totalBugs = $applyJobFilter(Bug::query())->count();

        // 2. openBugs
        $openBugs = $applyJobFilter(Bug::where('status', 'OPEN'))->count();

        // 3. closedBugs
        $closedBugs = $applyJobFilter(Bug::where('status', 'CLOSED'))->count();

        // 4. criticalBugs
        $criticalBugs = $applyJobFilter(Bug::where('severity', 'Critical'))->count();

        // 5. reworkRate
        $reworkCount = $applyJobFilter(Bug::where('is_rework', true))->count();
        $reworkRate = $totalBugs > 0
                        ? round($reworkCount / $totalBugs * 100, 1)
                        : 0;







        $topProjects = $applyJobFilter(Bug::with('project'))
            ->groupBy('project_id')
            ->selectRaw('project_id, count(*) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $volumeTrend = $applyJobFilter(
                Bug::selectRaw('DATE(created_at) as date, count(*) as total')
                   ->where('created_at', '>=', now()->subDays(7))
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Severity Map
        $sevMap = $applyJobFilter(Bug::query())
            ->groupBy('severity')
            ->selectRaw('severity, count(*) as count')
            ->pluck('count', 'severity')
            ->toArray();
        $severityCounts = [
            'critical' => $sevMap['Critical'] ?? 0,
            'major'    => $sevMap['Major']    ?? 0,
            'minor'    => $sevMap['Minor']    ?? 0,
        ];

        // Severity breakdown by status (for dual pie chart)
        $sevOpenMap = $applyJobFilter(Bug::where('status', 'OPEN'))
            ->groupBy('severity')
            ->selectRaw('severity, count(*) as count')
            ->pluck('count', 'severity')
            ->toArray();
        $severityOpen = [
            'Critical' => $sevOpenMap['Critical'] ?? 0,
            'Major'    => $sevOpenMap['Major']    ?? 0,
            'Minor'    => $sevOpenMap['Minor']    ?? 0,
        ];

        $sevClosedMap = $applyJobFilter(Bug::where('status', 'CLOSED'))
            ->groupBy('severity')
            ->selectRaw('severity, count(*) as count')
            ->pluck('count', 'severity')
            ->toArray();
        $severityClosed = [
            'Critical' => $sevClosedMap['Critical'] ?? 0,
            'Major'    => $sevClosedMap['Major']    ?? 0,
        ];

        // Dynamic Assembly Stage breakdown from database (reporter_type column)
        $rawStageMap = $applyJobFilter(Bug::query())
            ->selectRaw("COALESCE(NULLIF(TRIM(reporter_type), ''), 'Tidak Diketahui') as stage, count(*) as count")
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

        // Tabel Audit dengan filter
        $query = $applyJobFilter(Bug::with(['project', 'device']));
        if ($request->status)     $query->where('status', $request->status);
        if ($request->severity)   $query->where('severity', $request->severity);
        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->date_from)  $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)    $query->whereDate('created_at', '<=', $request->date_to);

        $urgencySort = $request->input('urgency_sort');
        if (in_array($urgencySort, ['desc', 'asc'], true)) {
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

            if ($urgencySort === 'desc') {
                $query->orderByDesc('urgency_score')->orderByDesc('created_at');
            } else {
                $query->orderBy('urgency_score')->orderByDesc('created_at');
            }
        } else {
            // Default: laporan terbaru di atas
            $query->latest();
        }

        $auditBugs = $query->paginate(10)->withQueryString();

        // Fetch projects for filter dropdown (hanya yang memiliki laporan bug aktif)
        $projects = Project::whereIn('id', function ($q) {
            $q->select('project_id')->from('bugs')
              ->whereNull('deleted_at')
              ->whereNotNull('project_id')
              ->distinct();
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
        $selectedJobId = $request->input('import_job_id');
        if ($selectedJobId === null) {
            $latestJob = ImportJob::where('status', 'completed')->whereNull('deleted_at')->orderByDesc('created_at')->first();
            $selectedJobId = $latestJob ? (string)$latestJob->id : 'all';
        }

        $auditQuery = Bug::with(['project', 'serialNumber']);
        if ($selectedJobId !== 'all') {
            $auditQuery->where('import_job_id', $selectedJobId);
        }

        // Apply same filters as dashboard
        if ($request->filled('project_id')) {
            $auditQuery->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $auditQuery->where('status', $request->status);
        }
        if ($request->filled('severity')) {
            $auditQuery->where('severity', $request->severity);
        }
        if ($request->filled('date_from')) {
            $auditQuery->where('created_at', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $auditQuery->where('created_at', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $bugs = $auditQuery->orderBy('created_at', 'desc')->get();
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

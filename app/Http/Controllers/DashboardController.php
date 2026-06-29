<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Models\Project;
use App\Services\BugAnalyticsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

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

            // 1. Auto-analyze Stage 2 (damage category) for CLOSED bugs (critical for chart/table)
            $unanalyzedStage2 = Bug::whereNull('damage_category')
                ->where('status', 'CLOSED')
                ->where(function($q) {
                    $q->whereNotNull('root_cause')->where('root_cause', '!=', '')
                      ->orWhereNotNull('repair_action')->where('repair_action', '!=', '');
                })
                ->limit(500)
                ->get();

            if ($unanalyzedStage2->isNotEmpty()) {
                foreach ($unanalyzedStage2 as $bug) {
                    $rootCause    = $bug->root_cause    ?? '';
                    $repairAction = $bug->repair_action ?? '';
                    if (!empty(trim($rootCause)) || !empty(trim($repairAction))) {
                        $res2 = $analytics->analyzeDamageCause($rootCause, $repairAction);
                        if (!empty($res2['damage_category'])) {
                            $bug->damage_category = $res2['damage_category'];
                            $bug->save();
                        }
                    }
                }
            }

            // 2. Auto-analyze Stage 1 (sentiment/spam/severity)
            $unanalyzedStage1 = Bug::whereNull('sentiment_label')
                ->whereNotNull('description')
                ->where('description', '!=', '')
                ->limit(500)
                ->get();

            if ($unanalyzedStage1->isNotEmpty()) {
                foreach ($unanalyzedStage1 as $bug) {
                    $res1 = $analytics->analyzeBugReport($bug);
                    if (!empty($res1)) {
                        $bug->sentiment_label                  = $res1['sentiment_label'] ?? null;
                        $bug->sentiment_score                  = $res1['sentiment_score'] ?? null;
                        $bug->is_spam                          = (bool) ($res1['is_spam'] ?? false);
                        $bug->spam_reason                      = $res1['spam_reason'] ?? null;
                        $bug->severity_recommended             = $res1['severity_recommended'] ?? null;
                        $bug->severity_recommendation_reason   = $res1['severity_recommendation_reason'] ?? null;
                        $bug->save();
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
                'spamBlocked'          => 0,
                'damageDistribution'   => collect(),
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
        $reworkRate = $closedBugs > 0
                        ? round($reworkCount / $closedBugs * 100, 1)
                        : 0;

        // 6. spamBlocked
        $spamBlocked = $applyJobFilter(Bug::where('is_spam', true))->count();

        // Charts (Top 5 damage categories, excluding 'Lain-lain')
        $damageDistribution = $applyJobFilter(
            Bug::whereNotNull('damage_category')
               ->where('damage_category', '!=', 'Lain-lain')
        )
            ->groupBy('damage_category')
            ->selectRaw('damage_category, count(*) as total')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

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
                CASE
                    WHEN bugs.is_spam = 1 OR LOWER(COALESCE(bugs.sentiment_label, '')) = 'spam' THEN 0
                    ELSE ROUND(
                        (
                            CASE
                                WHEN bugs.severity = 'Critical' THEN 0.8
                                WHEN bugs.severity = 'Major' THEN 0.5
                                ELSE 0.2
                            END
                            + (1 - COALESCE(bugs.sentiment_score, 0.5))
                        ) / 2,
                    2)
                END AS urgency_score
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

        $auditBugs = $query->paginate(20)->withQueryString();

        // Fetch projects for filter dropdown
        $projects = Project::orderBy('name')->get();

        return view('dashboard.index', compact(
            'hasImportedData',
            'totalBugs', 'openBugs', 'closedBugs', 'criticalBugs', 'reworkRate', 'spamBlocked',
            'damageDistribution', 'topProjects', 'volumeTrend',
            'auditBugs', 'projects', 'sqlFiles', 'selectedJobId', 'severityCounts'
        ));
    }

    /**
     * Export bug reports to CSV.
     */
    public function exportCsv(Request $request)
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

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=bugs_report_export_" . date('Ymd_His') . ".csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use($bugs) {
            $file = fopen('php://output', 'w');
            
            // Add BOM for Excel compatibility with UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Header
            fputcsv($file, [
                'ID Bug', 'Project', 'Title', 'Severity', 'SN Code Snapshot',
                'Reporter Type', 'Description', 'Version', 'Environment',
                'Root Cause', 'Repair Action', 'Rework?', 'Status',
                'Reported By', 'Fixed By', 'Closed At', 'Created At',
                'Sentiment Label', 'Sentiment Score', 'Spam?', 'Spam Reason',
                'Severity Recommended', 'Severity Rec Reason', 'Damage Category'
            ]);

            foreach ($bugs as $bug) {
                fputcsv($file, [
                    $bug->id,
                    $bug->project?->name ?? 'Project #' . $bug->project_id,
                    $bug->title,
                    $bug->severity,
                    $bug->sn_code_snapshot,
                    $bug->reporter_type,
                    $bug->description,
                    $bug->product_version,
                    $bug->environment,
                    $bug->root_cause,
                    $bug->repair_action,
                    $bug->is_rework ? 'Yes' : 'No',
                    $bug->status,
                    $bug->reported_by ?? 'System',
                    $bug->fixed_by ?? '',
                    $bug->closed_at ? $bug->closed_at->format('Y-m-d H:i:s') : '',
                    $bug->created_at->format('Y-m-d H:i:s'),
                    $bug->sentiment_label,
                    $bug->sentiment_score,
                    $bug->is_spam ? 'Yes' : 'No',
                    $bug->spam_reason,
                    $bug->severity_recommended,
                    $bug->severity_recommendation_reason,
                    $bug->damage_category
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\ImportJob;
use App\Models\Project;
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
            ->orderByDesc('created_at')
            ->get();

        $hasImportedData = $sqlFiles->isNotEmpty();

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
                'sentimentDistribution'=> collect(),
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

        // 1. totalBugs
        $totalBugsQuery = Bug::query();
        if ($selectedJobId !== 'all') {
            $totalBugsQuery->where('import_job_id', $selectedJobId);
        }
        $totalBugs = $totalBugsQuery->count();

        // 2. openBugs
        $openBugsQuery = Bug::where('status', 'OPEN');
        if ($selectedJobId !== 'all') {
            $openBugsQuery->where('import_job_id', $selectedJobId);
        }
        $openBugs = $openBugsQuery->count();

        // 3. closedBugs
        $closedBugsQuery = Bug::where('status', 'CLOSED');
        if ($selectedJobId !== 'all') {
            $closedBugsQuery->where('import_job_id', $selectedJobId);
        }
        $closedBugs = $closedBugsQuery->count();

        // 4. criticalBugs
        $criticalBugsQuery = Bug::where('severity', 'Critical');
        if ($selectedJobId !== 'all') {
            $criticalBugsQuery->where('import_job_id', $selectedJobId);
        }
        $criticalBugs = $criticalBugsQuery->count();

        // 5. reworkRate
        $reworkQuery = Bug::where('is_rework', true);
        if ($selectedJobId !== 'all') {
            $reworkQuery->where('import_job_id', $selectedJobId);
        }
        $reworkCount = $reworkQuery->count();
        $reworkRate = $closedBugs > 0
                        ? round($reworkCount / $closedBugs * 100, 1)
                        : 0;

        // 6. spamBlocked
        $spamQuery = Bug::where('is_spam', true);
        if ($selectedJobId !== 'all') {
            $spamQuery->where('import_job_id', $selectedJobId);
        }
        $spamBlocked = $spamQuery->count();

        // Charts
        $damageQuery = Bug::whereNotNull('damage_category');
        if ($selectedJobId !== 'all') {
            $damageQuery->where('import_job_id', $selectedJobId);
        }
        $damageDistribution = $damageQuery->groupBy('damage_category')
                                          ->selectRaw('damage_category, count(*) as total')
                                          ->get();

        $sentimentQuery = Bug::whereNotNull('sentiment_label');
        if ($selectedJobId !== 'all') {
            $sentimentQuery->where('import_job_id', $selectedJobId);
        }
        $sentimentDistribution = $sentimentQuery->groupBy('sentiment_label')
                                                ->selectRaw('sentiment_label, count(*) as total')
                                                ->get();

        $topProjectsQuery = Bug::with('project');
        if ($selectedJobId !== 'all') {
            $topProjectsQuery->where('import_job_id', $selectedJobId);
        }
        $topProjects = $topProjectsQuery->groupBy('project_id')
                                        ->selectRaw('project_id, count(*) as total')
                                        ->orderByDesc('total')
                                        ->limit(5)
                                        ->get();

        $volumeQuery = Bug::selectRaw('DATE(created_at) as date, count(*) as total')
                          ->where('created_at', '>=', now()->subDays(15));
        if ($selectedJobId !== 'all') {
            $volumeQuery->where('import_job_id', $selectedJobId);
        }
        $volumeTrend = $volumeQuery->groupBy('date')
                                   ->orderBy('date')
                                   ->get();

        // Severity Map
        $sevQuery = Bug::query();
        if ($selectedJobId !== 'all') {
            $sevQuery->where('import_job_id', $selectedJobId);
        }
        $sevMap = $sevQuery->groupBy('severity')
                           ->selectRaw('severity, count(*) as count')
                           ->pluck('count', 'severity')
                           ->toArray();
        $severityCounts = [
            'critical' => $sevMap['Critical'] ?? 0,
            'major'    => $sevMap['Major']    ?? 0,
            'minor'    => $sevMap['Minor']    ?? 0,
        ];

        // Tabel Audit dengan filter
        $query = Bug::with(['project', 'device']);
        if ($selectedJobId !== 'all') {
            $query->where('import_job_id', $selectedJobId);
        }
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
                            + (1 - COALESCE(bugs.sentiment_score, 0))
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
            'damageDistribution', 'sentimentDistribution', 'topProjects', 'volumeTrend',
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
            $latestJob = ImportJob::where('status', 'completed')->orderByDesc('created_at')->first();
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

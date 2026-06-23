<?php

namespace App\Http\Controllers;

use App\Models\Bug;
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
        // 1. Summary Cards Metrics
        $totalBugs = Bug::count();
        $openBugs = Bug::where('status', 'OPEN')->count();
        $closedBugs = Bug::where('status', 'CLOSED')->count();
        $criticalOpenBugs = Bug::where('status', 'OPEN')->where('severity', 'Critical')->count();
        
        $reworkCount = Bug::where('is_rework', true)->count();
        $reworkRate = $totalBugs > 0 ? round(($reworkCount / $totalBugs) * 100, 1) : 0.0;

        // 2. Sentiment Distribution
        $sentiments = Bug::selectRaw('sentiment_label, count(*) as count')
            ->groupBy('sentiment_label')
            ->get()
            ->mapWithKeys(function ($item) {
                $label = $item->sentiment_label ?? 'Unanalyzed';
                return [$label => $item->count];
            })->toArray();

        // 3. Top 5 Projects with most bugs
        $topProjects = Bug::join('projects', 'bugs.project_id', '=', 'projects.id')
            ->selectRaw('projects.name as project_name, count(bugs.id) as bug_count')
            ->groupBy('projects.id', 'projects.name')
            ->orderBy('bug_count', 'desc')
            ->limit(5)
            ->get();

        // 4. Damage Category Distribution
        $damageCategories = Bug::where('status', 'CLOSED')
            ->whereNotNull('damage_category')
            ->selectRaw('damage_category, count(*) as count')
            ->groupBy('damage_category')
            ->orderBy('count', 'desc')
            ->get();

        // 5. Volume Trend (Last 14 Days)
        $volumeTrend = Bug::selectRaw('DATE(created_at) as date, count(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => $item->count];
            })->toArray();

        // Fill in missing dates for trend chart
        $trendData = [];
        for ($i = 14; $i >= 0; $i--) {
            $dateStr = Carbon::now()->subDays($i)->format('Y-m-d');
            $trendData[$dateStr] = $volumeTrend[$dateStr] ?? 0;
        }

        // 6. Bug Audit Table with Filters & Pagination
        $projects = Project::orderBy('name')->get();
        
        $auditQuery = Bug::with(['project', 'serialNumber', 'reporter', 'fixer']);

        // Apply filters
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

        $bugs = $auditQuery->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('dashboard.index', compact(
            'totalBugs', 'openBugs', 'closedBugs', 'criticalOpenBugs', 'reworkRate',
            'sentiments', 'topProjects', 'damageCategories', 'trendData',
            'projects', 'bugs'
        ));
    }

    /**
     * Export bug reports to CSV.
     */
    public function exportCsv(Request $request)
    {
        $auditQuery = Bug::with(['project', 'serialNumber', 'reporter', 'fixer']);

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
                    $bug->reporter?->name ?? 'System',
                    $bug->fixer?->name ?? '',
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

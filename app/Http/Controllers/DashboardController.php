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
        // Summary Cards
        $totalBugs    = Bug::count();
        $openBugs     = Bug::where('status', 'OPEN')->count();
        $closedBugs   = Bug::where('status', 'CLOSED')->count();
        $criticalBugs = Bug::where('severity', 'Critical')->count();
        $reworkRate   = $closedBugs > 0
                        ? round(Bug::where('is_rework', true)->count() / $closedBugs * 100, 1)
                        : 0;
        $spamBlocked  = Bug::where('is_spam', true)->count();

        // Charts
        $damageDistribution  = Bug::whereNotNull('damage_category')
                                  ->groupBy('damage_category')
                                  ->selectRaw('damage_category, count(*) as total')
                                  ->get();

        $sentimentDistribution = Bug::whereNotNull('sentiment_label')
                                    ->groupBy('sentiment_label')
                                    ->selectRaw('sentiment_label, count(*) as total')
                                    ->get();

        $topProjects = Bug::with('project')
                          ->groupBy('project_id')
                          ->selectRaw('project_id, count(*) as total')
                          ->orderByDesc('total')
                          ->limit(5)
                          ->get();

        $volumeTrend = Bug::selectRaw('DATE(created_at) as date, count(*) as total')
                          ->where('created_at', '>=', now()->subDays(15))
                          ->groupBy('date')
                          ->orderBy('date')
                          ->get();

        // Tabel Audit dengan filter
        $query = Bug::with(['reporter', 'project', 'device']);
        if ($request->status)     $query->where('status', $request->status);
        if ($request->severity)   $query->where('severity', $request->severity);
        if ($request->project_id) $query->where('project_id', $request->project_id);
        if ($request->date_from)  $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->date_to)    $query->whereDate('created_at', '<=', $request->date_to);
        $auditBugs = $query->latest()->paginate(20)->withQueryString();

        // Fetch projects for filter dropdown
        $projects = Project::orderBy('name')->get();

        return view('dashboard.index', compact(
            'totalBugs', 'openBugs', 'closedBugs', 'criticalBugs', 'reworkRate', 'spamBlocked',
            'damageDistribution', 'sentimentDistribution', 'topProjects', 'volumeTrend',
            'auditBugs', 'projects'
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

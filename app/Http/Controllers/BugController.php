<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\Project;
use App\Models\Device;
use App\Models\SerialNumber;
use App\Services\BugAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BugController extends Controller
{
    protected BugAnalyticsService $analyticsService;

    public function __construct(BugAnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Bug::with(['project', 'serialNumber', 'reporter', 'fixer', 'feedbacks']);

        if ($user->role === 'reporter') {
            // Reporter only sees their own reported bugs
            $query->where('reported_by', $user->id);
            $bugs = $query->orderBy('created_at', 'desc')->paginate(10);
            return view('bugs.reporter-index', compact('bugs'));
        } elseif ($user->role === 'mekanik') {
            // Mechanic sees all bugs, can filter by status (default to OPEN)
            $status = $request->input('status', 'OPEN');
            $query->where('status', $status);
            $bugs = $query->orderBy('created_at', 'desc')->paginate(10);
            return view('bugs.mekanik-queue', compact('bugs', 'status'));
        } else {
            // Admin gets redirected to dashboard, or can audit
            return redirect()->route('dashboard');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (Auth::user()->role !== 'reporter' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $projects = Project::orderBy('name')->get();
        $devices = Device::orderBy('name')->get();
        $serialNumbers = SerialNumber::orderBy('sn_code')->get();

        return view('bugs.create', compact('projects', 'devices', 'serialNumbers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'reporter' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
            'severity' => 'required|in:Critical,Major,Minor',
            'serial_number_id' => 'nullable|exists:serial_numbers,id',
            'reporter_type' => 'required|in:produk,sub',
            'device_id' => 'nullable|exists:devices,id',
            'description' => 'nullable|string',
            'product_version' => 'nullable|string|max:100',
            'environment' => 'nullable|string|max:255',
            'reproduce_steps' => 'nullable|string',
            'expected_result' => 'nullable|string',
            'attachment' => 'nullable|file|max:20480', // 20MB limit for logs/images/videos
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('bug_attachments', 'public');
        }

        // Get serial number snapshot if selected
        $snCodeSnapshot = null;
        if ($request->serial_number_id) {
            $sn = SerialNumber::find($request->serial_number_id);
            $snCodeSnapshot = $sn?->sn_code;
        }

        // 1. Save core bug info (OPEN status)
        $bug = Bug::create([
            'title' => $request->title,
            'project_id' => $request->project_id,
            'severity' => $request->severity,
            'serial_number_id' => $request->serial_number_id,
            'sn_code_snapshot' => $snCodeSnapshot,
            'reporter_type' => $request->reporter_type,
            'device_id' => $request->device_id,
            'description' => $request->description,
            'product_version' => $request->product_version,
            'environment' => $request->environment,
            'reproduce_steps' => $request->reproduce_steps,
            'expected_result' => $request->expected_result,
            'attachment_path' => $attachmentPath,
            'reported_by' => Auth::id(),
            'status' => 'OPEN',
        ]);

        // 2. Call Python Analytics Service SYNCHRONOUSLY
        $aiAnalysis = $this->analyticsService->analyzeBugReport($request->description ?? '');

        // 3. Update Bug record with AI Stage 1 outputs
        $bug->update([
            'sentiment_label' => $aiAnalysis['sentiment_label'] ?? null,
            'sentiment_score' => $aiAnalysis['sentiment_score'] ?? null,
            'is_spam' => $aiAnalysis['is_spam'] ?? false,
            'spam_reason' => $aiAnalysis['spam_reason'] ?? null,
            'severity_recommended' => $aiAnalysis['severity_recommended'] ?? null,
            'severity_recommendation_reason' => $aiAnalysis['severity_recommendation_reason'] ?? null,
        ]);

        $message = 'Laporan bug berhasil disubmit!';
        if ($bug->is_spam) {
            $message .= ' (AI mendeteksi kemungkinan spam/laporan asal-asalan: ' . $bug->spam_reason . ')';
        }

        return redirect()->route('bugs.index')->with('success', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bug $bug)
    {
        $bug->load(['project', 'serialNumber', 'device', 'reporter', 'fixer', 'feedbacks.sender']);
        return view('bugs.show', compact('bug'));
    }

    /**
     * Show the form to close the bug.
     */
    public function showCloseForm(Bug $bug)
    {
        if (Auth::user()->role !== 'mekanik' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($bug->status === 'CLOSED') {
            return redirect()->route('bugs.show', $bug)->with('error', 'Bug sudah ditutup.');
        }

        return view('bugs.close', compact('bug'));
    }

    /**
     * Close the bug (update status, save mechanics inputs, run synchronous damage categorization).
     */
    public function close(Request $request, Bug $bug)
    {
        if (Auth::user()->role !== 'mekanik' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'root_cause' => 'required|string',
            'repair_action' => 'required|string',
            'is_rework' => 'nullable|boolean',
            'feedback_message' => 'nullable|string', // Optional feedback to reporter
        ]);

        // 1. Call Python Analytics Service for Stage 2 (Damage Categorization)
        $aiAnalysis = $this->analyticsService->analyzeDamageCause(
            $request->root_cause,
            $request->repair_action
        );

        // 2. Save technical closing actions and AI results
        $bug->update([
            'status' => 'CLOSED',
            'root_cause' => $request->root_cause,
            'repair_action' => $request->repair_action,
            'is_rework' => $request->has('is_rework'),
            'fixed_by' => Auth::id(),
            'closed_at' => Carbon::now(),
            'damage_category' => $aiAnalysis['damage_category'] ?? 'Lain-lain',
        ]);

        // 3. Send feedback message to reporter if provided
        if ($request->feedback_message) {
            $bug->feedbacks()->create([
                'from_user_id' => Auth::id(),
                'to_user_id' => $bug->reported_by,
                'message' => $request->feedback_message,
                'is_read' => false,
            ]);
        }

        return redirect()->route('bugs.show', $bug)->with('success', 'Laporan bug berhasil ditutup dan dianalisis!');
    }
}

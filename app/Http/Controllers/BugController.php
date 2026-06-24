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
        if ($user->role === 'reporter') {
            return redirect()->action([self::class, 'myBugs']);
        } elseif ($user->role === 'mekanik') {
            return redirect()->action([self::class, 'queue']);
        } else {
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
        // 1. Validasi request
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'severity' => 'required|in:Critical,Major,Minor',
            'serial_number_id' => 'required|exists:serial_numbers,id',
            'device_id' => 'required|exists:devices,id',
        ]);

        // 2. Buat record bug baru dengan status = 'OPEN' dan reported_by = Auth::id()
        $bug = new Bug();
        $bug->title = $request->title;
        $bug->description = $request->description;
        $bug->severity = $request->severity;
        $bug->serial_number_id = $request->serial_number_id;
        $bug->device_id = $request->device_id;
        $bug->status = 'OPEN';
        $bug->reported_by = Auth::id();

        // Assign optional/supplementary form fields if present
        if ($request->filled('project_id')) {
            $bug->project_id = $request->project_id;
        }
        if ($request->filled('reporter_type')) {
            $bug->reporter_type = $request->reporter_type;
        }
        if ($request->filled('product_version')) {
            $bug->product_version = $request->product_version;
        }
        if ($request->filled('environment')) {
            $bug->environment = $request->environment;
        }
        if ($request->filled('reproduce_steps')) {
            $bug->reproduce_steps = $request->reproduce_steps;
        }
        if ($request->filled('expected_result')) {
            $bug->expected_result = $request->expected_result;
        }

        if ($request->hasFile('attachment')) {
            $bug->attachment_path = $request->file('attachment')->store('bug_attachments', 'public');
        }

        if ($request->serial_number_id) {
            $sn = SerialNumber::find($request->serial_number_id);
            $bug->sn_code_snapshot = $sn?->sn_code;
        }

        // 3. Simpan bug ke DB ($bug->save())
        $bug->save();

        // 4. Panggil BugAnalyticsService::analyzeBugReport($bug) secara sinkron
        $aiAnalysis = $this->analyticsService->analyzeBugReport($bug);

        // 5. Update bug dengan hasil AI: sentiment_label, sentiment_score, is_spam, spam_reason, severity_recommended, severity_recommendation_reason
        // 6. Jika Analytics Service down/error, biarkan kolom AI tetap null — jangan gagalkan proses simpan
        $bug->sentiment_label = $aiAnalysis['sentiment_label'] ?? null;
        $bug->sentiment_score = $aiAnalysis['sentiment_score'] ?? null;
        $bug->is_spam = $aiAnalysis['is_spam'] ?? false;
        $bug->spam_reason = $aiAnalysis['spam_reason'] ?? null;
        $bug->severity_recommended = $aiAnalysis['severity_recommended'] ?? null;
        $bug->severity_recommendation_reason = $aiAnalysis['severity_recommendation_reason'] ?? null;
        $bug->save();

        // 7. Return redirect ke halaman riwayat bug Reporter dengan pesan sukses
        return redirect()->action([self::class, 'myBugs'])->with('success', 'Laporan bug berhasil disubmit!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Bug $bug)
    {
        $bug->load(['project', 'serialNumber', 'device', 'reporter', 'fixer', 'assignee', 'chats.sender']);
        return view('bugs.show', compact('bug'));
    }

    /**
     * Show the form to close the bug.
     */
    public function showClose(Bug $bug)
    {
        if (Auth::user()->role !== 'mekanik' && Auth::user()->role !== 'admin') {
            abort(403);
        }

        if ($bug->status !== 'OPEN') {
            return redirect()->route('bugs.show', $bug)->with('error', 'Bug sudah ditutup.');
        }

        return view('bugs.close', compact('bug'));
    }

    /**
     * Close the bug (update status, save mechanics inputs, run synchronous damage categorization).
     */
    public function close(Request $request, Bug $bug)
    {
        // 1. Ambil bug by ID, pastikan statusnya 'OPEN' — jika bukan, abort(403)
        if ($bug->status !== 'OPEN') {
            abort(403);
        }

        // 2. Validasi request (root_cause required, repair_action required, is_rework boolean)
        $request->validate([
            'root_cause' => 'required',
            'repair_action' => 'required',
            'is_rework' => 'boolean',
        ]);

        // 3. Update bug: root_cause, repair_action, is_rework dari request; fixed_by = Auth::id(); closed_at = now(); status = 'CLOSED'
        $bug->root_cause = $request->root_cause;
        $bug->repair_action = $request->repair_action;
        $bug->is_rework = $request->boolean('is_rework');
        $bug->fixed_by = Auth::id();
        $bug->closed_at = now();
        $bug->status = 'CLOSED';

        // 4. Simpan ($bug->save())
        $bug->save();

        // 5. Panggil BugAnalyticsService::analyzeDamageCause($bug->root_cause, $bug->repair_action) secara sinkron
        $aiAnalysis = $this->analyticsService->analyzeDamageCause($bug->root_cause, $bug->repair_action);

        // 6. Update bug dengan hasil AI: damage_category
        // 7. Jika Analytics Service down/error, damage_category tetap null — jangan gagalkan proses close
        $bug->damage_category = $aiAnalysis['damage_category'] ?? null;
        $bug->save();

        // 8. Return redirect ke halaman queue Mekanik dengan pesan sukses
        return redirect()->action([self::class, 'queue'])->with('success', 'Bug berhasil ditutup!');
    }

    /**
     * Show reporter's bug reports history.
     */
    public function myBugs(Request $request)
    {
        $user = Auth::user();
        $bugs = Bug::with(['project', 'serialNumber', 'reporter', 'fixer', 'assignee'])
            ->where('reported_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('bugs.reporter-index', compact('bugs'));
    }

    /**
     * Show mechanics bugs queue.
     */
    public function queue(Request $request)
    {
        $status = $request->input('status', 'OPEN');
        $bugs = Bug::with(['project', 'serialNumber', 'reporter', 'fixer', 'assignee'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        return view('bugs.mekanik-queue', compact('bugs', 'status'));
    }

    /**
     * Assign an OPEN bug to the currently logged-in mechanic.
     */
    public function assign(Bug $bug)
    {
        if ($bug->status !== 'OPEN') {
            return back()->with('error', 'Bug ini sudah tidak tersedia.');
        }

        if (!is_null($bug->assigned_to)) {
            return back()->with('error', 'Bug ini sudah diambil oleh mekanik lain.');
        }

        $bug->assigned_to = Auth::id();
        $bug->assigned_at = now();
        $bug->save();

        return back()->with('success', 'Bug berhasil di-assign ke kamu.');
    }
}

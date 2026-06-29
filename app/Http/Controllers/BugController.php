<?php

namespace App\Http\Controllers;

use App\Models\Bug;

class BugController extends Controller
{
    /**
     * Admin-only bug index. The legacy workflow list is retired, so this
     * route now routes users directly to the analytics dashboard.
     */
    public function index()
    {
        return redirect()->route('dashboard');
    }

    /**
     * Show a single bug detail for admin review.
     */
    public function show(Bug $bug)
    {
        $bug->load(['project', 'serialNumber', 'device']);

        return view('bugs.show', compact('bug'));
    }

    /**
     * Reprocess AI analysis for a single bug.
     */
    public function reprocess(Bug $bug, \App\Services\BugAnalyticsService $analytics)
    {
        // Stage 1 AI Analysis (if description is present)
        if (!empty($bug->description)) {
            $result1 = $analytics->analyzeBugReport($bug);
            if (!empty($result1)) {
                $bug->update([
                    'sentiment_label'              => $result1['sentiment_label']              ?? null,
                    'sentiment_score'              => $result1['sentiment_score']              ?? null,
                    'is_spam'                      => $result1['is_spam']                      ?? false,
                    'spam_reason'                  => $result1['spam_reason']                  ?? null,
                    'severity_recommended'         => $result1['severity_recommended']         ?? null,
                    'severity_recommendation_reason' => $result1['severity_recommendation_reason'] ?? null,
                ]);
            }
        }

        // Stage 2 AI Analysis (if root cause or repair action is present)
        $rootCause    = $bug->root_cause    ?? '';
        $repairAction = $bug->repair_action ?? '';
        if (!empty(trim($rootCause)) || !empty(trim($repairAction))) {
            $result2 = $analytics->analyzeDamageCause($rootCause, $repairAction);
            if (!empty($result2['damage_category'])) {
                $bug->update(['damage_category' => $result2['damage_category']]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Analisis AI berhasil diproses ulang.',
            'bug' => $bug->fresh()
        ]);
    }
}

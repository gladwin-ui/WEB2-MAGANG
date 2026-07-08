<?php

namespace App\Http\Controllers;

use App\Models\Bug;

class BugController extends Controller
{

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
        // Stage 1 AI Analysis (if title or description is present)
        if (!empty($bug->title) || !empty($bug->description)) {
            $result1 = $analytics->analyzeBugReport($bug);
            if (!empty($result1)) {
                $bug->update([
                    'sentiment_label' => $result1['sentiment_label'] ?? null,
                    'sentiment_score' => $result1['sentiment_score'] ?? null,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Analisis AI berhasil diproses ulang.',
            'bug' => $bug->fresh()
        ]);
    }
}

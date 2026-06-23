<?php

namespace App\Http\Controllers;

use App\Models\Bug;
use App\Models\BugFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BugFeedbackController extends Controller
{
    /**
     * Store feedback message.
     */
    public function store(Request $request, Bug $bug)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = Auth::id();
        $recipientId = null;

        // Determine who gets the message:
        // If current user is reporter, send to the mechanic (fixed_by) or default to Admin (ID 1) if not closed/fixed yet.
        // If current user is mechanic/admin, send to the reporter (reported_by).
        if ($userId === $bug->reported_by) {
            $recipientId = $bug->fixed_by ?? 1; // Default to Admin if not resolved
        } else {
            $recipientId = $bug->reported_by;
        }

        $bug->feedbacks()->create([
            'from_user_id' => $userId,
            'to_user_id' => $recipientId,
            'message' => $request->message,
            'is_read' => false,
        ]);

        return redirect()->route('bugs.show', $bug)->with('success', 'Pesan berhasil dikirim.');
    }

    /**
     * Mark all feedback messages as read for a specific bug.
     */
    public function markAsRead(Bug $bug)
    {
        BugFeedback::where('bug_id', $bug->id)
            ->where('to_user_id', Auth::id())
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}

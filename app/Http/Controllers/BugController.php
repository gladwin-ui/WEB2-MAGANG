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
}

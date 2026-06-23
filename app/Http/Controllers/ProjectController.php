<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::orderBy('name')->paginate(15);
        return view('master.projects.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name',
        ]);

        Project::create([
            'name' => $request->name,
        ]);

        return redirect()->route('master.projects.index')->with('success', 'Project baru berhasil ditambahkan.');
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:projects,name,' . $project->id,
        ]);

        $project->update([
            'name' => $request->name,
        ]);

        return redirect()->route('master.projects.index')->with('success', 'Project berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('master.projects.index')->with('success', 'Project berhasil dihapus.');
    }
}

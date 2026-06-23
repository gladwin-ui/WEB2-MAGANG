<?php

namespace App\Http\Controllers;

use App\Models\SerialNumber;
use App\Models\Project;
use Illuminate\Http\Request;

class SerialNumberController extends Controller
{
    public function index()
    {
        $serialNumbers = SerialNumber::with('project')->orderBy('sn_code')->paginate(15);
        $projects = Project::orderBy('name')->get();
        return view('master.serial_numbers.index', compact('serialNumbers', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'sn_code' => 'required|string|max:255',
            'type' => 'required|in:unit,sub',
        ]);

        SerialNumber::create([
            'project_id' => $request->project_id,
            'sn_code' => $request->sn_code,
            'type' => $request->type,
        ]);

        return redirect()->route('master.serial_numbers.index')->with('success', 'Serial Number baru berhasil ditambahkan.');
    }

    public function update(Request $request, SerialNumber $serialNumber)
    {
        $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'sn_code' => 'required|string|max:255',
            'type' => 'required|in:unit,sub',
        ]);

        $serialNumber->update([
            'project_id' => $request->project_id,
            'sn_code' => $request->sn_code,
            'type' => $request->type,
        ]);

        return redirect()->route('master.serial_numbers.index')->with('success', 'Serial Number berhasil diperbarui.');
    }

    public function destroy(SerialNumber $serialNumber)
    {
        $serialNumber->delete();
        return redirect()->route('master.serial_numbers.index')->with('success', 'Serial Number berhasil dihapus.');
    }
}

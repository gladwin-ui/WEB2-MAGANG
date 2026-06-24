<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = Device::orderBy('name')->paginate(15);
        return view('master.devices.index', compact('devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:devices,name',
        ]);

        Device::create([
            'name' => $request->name,
        ]);

        return redirect()->route('master.devices.index')->with('success', 'Device baru berhasil ditambahkan.');
    }

    public function update(Request $request, Device $device)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:devices,name,' . $device->id,
        ]);

        $device->update([
            'name' => $request->name,
        ]);

        return redirect()->route('master.devices.index')->with('success', 'Device berhasil diperbarui.');
    }

    public function destroy(Device $device)
    {
        $device->delete();
        return redirect()->route('master.devices.index')->with('success', 'Device berhasil dihapus.');
    }
}

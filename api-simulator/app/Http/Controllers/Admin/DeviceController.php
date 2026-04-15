<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $devices = Device::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('device_name', 'like', '%' . $search . '%')
                        ->orWhere('device_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('device_value')
            ->orderBy('device_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.devices.index', [
            'devices' => $devices,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:100'],
            'device_value' => ['required', 'string', 'max:50'],
        ]);

        Device::create($validated);

        return redirect()
            ->route('admin.devices')
            ->with('success', 'Device created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $device = Device::findOrFail($id);

        $validated = $request->validate([
            'device_name' => ['required', 'string', 'max:100'],
            'device_value' => ['required', 'string', 'max:50'],
        ]);

        $device->update($validated);

        return redirect()
            ->route('admin.devices')
            ->with('success', 'Device updated successfully.');
    }

    public function block(int $id)
    {
        $device = Device::findOrFail($id);
        $device->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.devices')
            ->with('success', 'Device blocked successfully.');
    }

    public function unblock(int $id)
    {
        $device = Device::findOrFail($id);
        $device->update(['status' => 'active']);

        return redirect()
            ->route('admin.devices')
            ->with('success', 'Device unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $device = Device::findOrFail($id);
        $device->delete();

        return redirect()
            ->route('admin.devices')
            ->with('success', 'Device deleted successfully.');
    }
}

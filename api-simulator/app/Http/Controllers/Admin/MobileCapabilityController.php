<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileCapability;
use Illuminate\Http\Request;

class MobileCapabilityController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $capabilities = MobileCapability::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('capability_name', 'like', '%' . $search . '%')
                        ->orWhere('capability_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('capability_value')
            ->orderBy('capability_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.mobile-capabilities.index', [
            'capabilities' => $capabilities,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'capability_name' => ['required', 'string', 'max:100'],
            'capability_value' => ['required', 'string', 'max:50'],
        ]);

        MobileCapability::create($validated);

        return redirect()
            ->route('admin.mobile-capabilities')
            ->with('success', 'Mobile Capability created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $capability = MobileCapability::findOrFail($id);

        $validated = $request->validate([
            'capability_name' => ['required', 'string', 'max:100'],
            'capability_value' => ['required', 'string', 'max:50'],
        ]);

        $capability->update($validated);

        return redirect()
            ->route('admin.mobile-capabilities')
            ->with('success', 'Mobile Capability updated successfully.');
    }

    public function block(int $id)
    {
        $capability = MobileCapability::findOrFail($id);
        $capability->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.mobile-capabilities')
            ->with('success', 'Mobile Capability blocked successfully.');
    }

    public function unblock(int $id)
    {
        $capability = MobileCapability::findOrFail($id);
        $capability->update(['status' => 'active']);

        return redirect()
            ->route('admin.mobile-capabilities')
            ->with('success', 'Mobile Capability unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $capability = MobileCapability::findOrFail($id);
        $capability->delete();

        return redirect()
            ->route('admin.mobile-capabilities')
            ->with('success', 'Mobile Capability deleted successfully.');
    }
}

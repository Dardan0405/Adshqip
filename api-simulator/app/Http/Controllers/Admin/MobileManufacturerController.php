<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileManufacturer;
use Illuminate\Http\Request;

class MobileManufacturerController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $manufacturers = MobileManufacturer::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('manufacturer_name', 'like', '%' . $search . '%')
                        ->orWhere('manufacturer_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('manufacturer_value')
            ->orderBy('manufacturer_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.mobile-manufacturers.index', [
            'manufacturers' => $manufacturers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'manufacturer_name' => ['required', 'string', 'max:100'],
            'manufacturer_value' => ['required', 'string', 'max:50'],
        ]);

        MobileManufacturer::create($validated);

        return redirect()
            ->route('admin.mobile-manufacturers')
            ->with('success', 'Mobile Manufacturer created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $manufacturer = MobileManufacturer::findOrFail($id);

        $validated = $request->validate([
            'manufacturer_name' => ['required', 'string', 'max:100'],
            'manufacturer_value' => ['required', 'string', 'max:50'],
        ]);

        $manufacturer->update($validated);

        return redirect()
            ->route('admin.mobile-manufacturers')
            ->with('success', 'Mobile Manufacturer updated successfully.');
    }

    public function block(int $id)
    {
        $manufacturer = MobileManufacturer::findOrFail($id);
        $manufacturer->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.mobile-manufacturers')
            ->with('success', 'Mobile Manufacturer blocked successfully.');
    }

    public function unblock(int $id)
    {
        $manufacturer = MobileManufacturer::findOrFail($id);
        $manufacturer->update(['status' => 'active']);

        return redirect()
            ->route('admin.mobile-manufacturers')
            ->with('success', 'Mobile Manufacturer unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $manufacturer = MobileManufacturer::findOrFail($id);
        $manufacturer->delete();

        return redirect()
            ->route('admin.mobile-manufacturers')
            ->with('success', 'Mobile Manufacturer deleted successfully.');
    }
}

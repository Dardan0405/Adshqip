<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarrierIspConnection;
use Illuminate\Http\Request;

class CarrierIspConnectionController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $connections = CarrierIspConnection::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('carrier_name', 'like', '%' . $search . '%')
                        ->orWhere('country', 'like', '%' . $search . '%')
                        ->orWhere('start_ip', 'like', '%' . $search . '%')
                        ->orWhere('end_ip', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('country')
            ->orderBy('carrier_name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.carrier-isp-connections.index', [
            'connections' => $connections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'carrier_name' => ['required', 'string', 'max:100'],
            'start_ip' => ['required', 'ip'],
            'end_ip' => ['required', 'ip'],
            'country' => ['required', 'string', 'max:10'],
        ]);

        CarrierIspConnection::create($validated);

        return redirect()
            ->route('admin.carrier-isp-connections')
            ->with('success', 'ISP/Connection created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $connection = CarrierIspConnection::findOrFail($id);

        $validated = $request->validate([
            'carrier_name' => ['required', 'string', 'max:100'],
            'start_ip' => ['required', 'ip'],
            'end_ip' => ['required', 'ip'],
            'country' => ['required', 'string', 'max:10'],
        ]);

        $connection->update($validated);

        return redirect()
            ->route('admin.carrier-isp-connections')
            ->with('success', 'ISP/Connection updated successfully.');
    }

    public function block(int $id)
    {
        $connection = CarrierIspConnection::findOrFail($id);
        $connection->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.carrier-isp-connections')
            ->with('success', 'ISP/Connection blocked successfully.');
    }

    public function unblock(int $id)
    {
        $connection = CarrierIspConnection::findOrFail($id);
        $connection->update(['status' => 'active']);

        return redirect()
            ->route('admin.carrier-isp-connections')
            ->with('success', 'ISP/Connection unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $connection = CarrierIspConnection::findOrFail($id);
        $connection->delete();

        return redirect()
            ->route('admin.carrier-isp-connections')
            ->with('success', 'ISP/Connection deleted successfully.');
    }
}

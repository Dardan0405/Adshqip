<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConnectionType;
use Illuminate\Http\Request;

class ConnectionTypeController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $connections = ConnectionType::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('connection_name', 'like', '%' . $search . '%')
                        ->orWhere('connection_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('connection_name')
            ->orderBy('connection_value')
            ->paginate(20)
            ->withQueryString();

        return view('admin.connection-types.index', [
            'connections' => $connections,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'connection_name' => ['required', 'string', 'max:100'],
            'connection_value' => ['required', 'string', 'max:50'],
        ]);

        ConnectionType::create($validated);

        return redirect()
            ->route('admin.connection-types')
            ->with('success', 'Connection created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $connection = ConnectionType::findOrFail($id);

        $validated = $request->validate([
            'connection_name' => ['required', 'string', 'max:100'],
            'connection_value' => ['required', 'string', 'max:50'],
        ]);

        $connection->update($validated);

        return redirect()
            ->route('admin.connection-types')
            ->with('success', 'Connection updated successfully.');
    }

    public function block(int $id)
    {
        $connection = ConnectionType::findOrFail($id);
        $connection->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.connection-types')
            ->with('success', 'Connection blocked successfully.');
    }

    public function unblock(int $id)
    {
        $connection = ConnectionType::findOrFail($id);
        $connection->update(['status' => 'active']);

        return redirect()
            ->route('admin.connection-types')
            ->with('success', 'Connection unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $connection = ConnectionType::findOrFail($id);
        $connection->delete();

        return redirect()
            ->route('admin.connection-types')
            ->with('success', 'Connection deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Browser;
use Illuminate\Http\Request;

class BrowserController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $browsers = Browser::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('browser_name', 'like', '%' . $search . '%')
                        ->orWhere('browser_code', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('browser_name')
            ->orderBy('browser_code')
            ->paginate(20)
            ->withQueryString();

        return view('admin.browsers.index', [
            'browsers' => $browsers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'browser_name' => ['required', 'string', 'max:100'],
            'browser_code' => ['required', 'string', 'max:100'],
        ]);

        Browser::create($validated);

        return redirect()
            ->route('admin.browsers')
            ->with('success', 'Browser created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $browser = Browser::findOrFail($id);

        $validated = $request->validate([
            'browser_name' => ['required', 'string', 'max:100'],
            'browser_code' => ['required', 'string', 'max:100'],
        ]);

        $browser->update($validated);

        return redirect()
            ->route('admin.browsers')
            ->with('success', 'Browser updated successfully.');
    }

    public function block(int $id)
    {
        $browser = Browser::findOrFail($id);
        $browser->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.browsers')
            ->with('success', 'Browser blocked successfully.');
    }

    public function unblock(int $id)
    {
        $browser = Browser::findOrFail($id);
        $browser->update(['status' => 'active']);

        return redirect()
            ->route('admin.browsers')
            ->with('success', 'Browser unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $browser = Browser::findOrFail($id);
        $browser->delete();

        return redirect()
            ->route('admin.browsers')
            ->with('success', 'Browser deleted successfully.');
    }
}

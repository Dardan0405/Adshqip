<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrowserLanguage;
use Illuminate\Http\Request;

class BrowserLanguageController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));

        $browserLanguages = BrowserLanguage::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('language_name', 'like', '%' . $search . '%')
                        ->orWhere('language_value', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('language_name')
            ->orderBy('language_value')
            ->paginate(20)
            ->withQueryString();

        return view('admin.browser-languages.index', [
            'browserLanguages' => $browserLanguages,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'language_name' => ['required', 'string', 'max:100'],
            'language_value' => ['required', 'string', 'max:20'],
        ]);

        BrowserLanguage::create($validated);

        return redirect()
            ->route('admin.browser-languages')
            ->with('success', 'Browser Language created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $browserLanguage = BrowserLanguage::findOrFail($id);

        $validated = $request->validate([
            'language_name' => ['required', 'string', 'max:100'],
            'language_value' => ['required', 'string', 'max:20'],
        ]);

        $browserLanguage->update($validated);

        return redirect()
            ->route('admin.browser-languages')
            ->with('success', 'Browser Language updated successfully.');
    }

    public function block(int $id)
    {
        $browserLanguage = BrowserLanguage::findOrFail($id);
        $browserLanguage->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.browser-languages')
            ->with('success', 'Browser Language blocked successfully.');
    }

    public function unblock(int $id)
    {
        $browserLanguage = BrowserLanguage::findOrFail($id);
        $browserLanguage->update(['status' => 'active']);

        return redirect()
            ->route('admin.browser-languages')
            ->with('success', 'Browser Language unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $browserLanguage = BrowserLanguage::findOrFail($id);
        $browserLanguage->delete();

        return redirect()
            ->route('admin.browser-languages')
            ->with('success', 'Browser Language deleted successfully.');
    }
}

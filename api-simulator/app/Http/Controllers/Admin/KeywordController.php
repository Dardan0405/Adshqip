<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\Keyword;
use Illuminate\Http\Request;

class KeywordController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $status = $request->input('status');
        $category = $request->input('category');

        $keywords = Keyword::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('keyword', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%');
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($category, fn ($q) => $q->where('category', $category))
            ->orderBy('keyword')
            ->paginate(20)
            ->withQueryString();

        // Get unique categories for filter dropdown
        $categories = Keyword::select('category')
            ->whereNotNull('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Stats
        $totalKeywords = Keyword::count();
        $activeKeywords = Keyword::where('status', 'active')->count();
        $totalMatches = Keyword::sum('match_count');

        return view('admin.keywords.index', [
            'keywords' => $keywords,
            'categories' => $categories,
            'totalKeywords' => $totalKeywords,
            'activeKeywords' => $activeKeywords,
            'totalMatches' => $totalMatches,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        // Check for duplicate keyword
        if (Keyword::where('keyword', $validated['keyword'])->exists()) {
            return redirect()
                ->route('admin.keywords')
                ->with('error', 'This keyword already exists.');
        }

        Keyword::create($validated);

        return redirect()
            ->route('admin.keywords')
            ->with('success', 'Keyword created successfully.');
    }

    public function show(int $id)
    {
        $keyword = Keyword::findOrFail($id);

        // Get campaigns targeting this keyword
        $campaigns = Campaign::whereJsonContains('targeting_keywords', $keyword->keyword)
            ->where('is_deleted', false)
            ->get(['id', 'name', 'status']);

        if (request()->expectsJson()) {
            return response()->json([
                'keyword' => $keyword,
                'campaigns' => $campaigns,
            ]);
        }

        return view('admin.keywords.show', [
            'keyword' => $keyword,
            'campaigns' => $campaigns,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $keyword = Keyword::findOrFail($id);

        $validated = $request->validate([
            'keyword' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        // Check for duplicate keyword (excluding current)
        $exists = Keyword::where('keyword', $validated['keyword'])
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'This keyword already exists.'], 422);
            }
            return redirect()
                ->route('admin.keywords')
                ->with('error', 'This keyword already exists.');
        }

        $keyword->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Keyword updated successfully.']);
        }

        return redirect()
            ->route('admin.keywords')
            ->with('success', 'Keyword updated successfully.');
    }

    public function block(int $id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->update(['status' => 'inactive']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Keyword blocked successfully.']);
        }

        return redirect()
            ->route('admin.keywords')
            ->with('success', 'Keyword blocked successfully.');
    }

    public function unblock(int $id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->update(['status' => 'active']);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Keyword unblocked successfully.']);
        }

        return redirect()
            ->route('admin.keywords')
            ->with('success', 'Keyword unblocked successfully.');
    }

    public function destroy(int $id)
    {
        $keyword = Keyword::findOrFail($id);
        $keyword->delete();

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Keyword deleted successfully.']);
        }

        return redirect()
            ->route('admin.keywords')
            ->with('success', 'Keyword deleted successfully.');
    }

    /**
     * Get all active keywords as JSON (for campaign form).
     */
    public function listActive()
    {
        $keywords = Keyword::where('status', 'active')
            ->orderBy('keyword')
            ->get(['id', 'keyword', 'category', 'description']);

        return response()->json($keywords);
    }

    /**
     * Bulk import keywords from a textarea (one per line).
     */
    public function bulkImport(Request $request)
    {
        $validated = $request->validate([
            'keywords_text' => ['required', 'string'],
            'category' => ['nullable', 'string', 'max:100'],
        ]);

        $lines = array_filter(
            array_map('trim', preg_split('/[\r\n]+/', $validated['keywords_text']))
        );

        $imported = 0;
        $skipped = 0;

        foreach ($lines as $line) {
            if (strlen($line) > 255 || strlen($line) < 1) {
                $skipped++;
                continue;
            }

            if (Keyword::where('keyword', $line)->exists()) {
                $skipped++;
                continue;
            }

            Keyword::create([
                'keyword' => $line,
                'category' => $validated['category'] ?? null,
                'status' => 'active',
            ]);
            $imported++;
        }

        return redirect()
            ->route('admin.keywords')
            ->with('success', "Imported {$imported} keywords. Skipped {$skipped} (duplicates or invalid).");
    }

    /**
     * Export keywords as CSV.
     */
    public function export(Request $request)
    {
        $keywords = Keyword::orderBy('keyword')->get();

        $filename = 'keywords_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($keywords) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Keyword', 'Category', 'Description', 'Status', 'Match Count', 'Last Matched', 'Created At']);

            foreach ($keywords as $kw) {
                fputcsv($file, [
                    $kw->id,
                    $kw->keyword,
                    $kw->category ?? '',
                    $kw->description ?? '',
                    $kw->status,
                    $kw->match_count,
                    $kw->last_matched_at?->format('Y-m-d H:i:s') ?? '',
                    $kw->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

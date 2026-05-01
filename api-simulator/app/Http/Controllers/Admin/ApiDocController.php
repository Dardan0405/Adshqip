<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiDoc;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiDocController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $status = $request->input('status');

        $apiDocs = ApiDoc::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('endpoint_path', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($category, fn ($query) => $query->where('category', $category))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_published', $status === 'published'))
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $categories = ApiDoc::query()
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->filter()
            ->values();

        $stats = [
            'total' => ApiDoc::count(),
            'published' => ApiDoc::where('is_published', true)->count(),
            'draft' => ApiDoc::where('is_published', false)->count(),
            'protected' => ApiDoc::where('auth_required', true)->count(),
        ];

        return view('admin.api-docs.index', compact('apiDocs', 'categories', 'stats'));
    }

    public function store(Request $request)
    {
        ApiDoc::create($this->validatedPayload($request));

        return redirect()->route('admin.api-docs')->with('success', 'API doc created successfully.');
    }

    public function update(Request $request, ApiDoc $apiDoc)
    {
        $apiDoc->update($this->validatedPayload($request, $apiDoc->id));

        return redirect()->route('admin.api-docs')->with('success', 'API doc updated successfully.');
    }

    public function publish(ApiDoc $apiDoc)
    {
        $apiDoc->update(['is_published' => true]);

        return redirect()->route('admin.api-docs')->with('success', 'API doc published successfully.');
    }

    public function unpublish(ApiDoc $apiDoc)
    {
        $apiDoc->update(['is_published' => false]);

        return redirect()->route('admin.api-docs')->with('success', 'API doc unpublished successfully.');
    }

    public function destroy(ApiDoc $apiDoc)
    {
        $apiDoc->delete();

        return redirect()->route('admin.api-docs')->with('success', 'API doc deleted successfully.');
    }

    protected function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'slug' => ['nullable', 'string', 'max:100', 'unique:aq_api_docs,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'http_method' => ['required', 'string', 'in:GET,POST,PUT,PATCH,DELETE,OPTIONS'],
            'endpoint_path' => ['required', 'string', 'max:255'],
            'auth_required' => ['nullable', 'boolean'],
            'required_permission' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string'],
            'headers_example' => ['nullable', 'string'],
            'request_example' => ['nullable', 'string'],
            'response_example' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'title' => $validated['title'],
            'category' => $validated['category'],
            'http_method' => strtoupper($validated['http_method']),
            'endpoint_path' => $validated['endpoint_path'],
            'auth_required' => (bool) ($validated['auth_required'] ?? false),
            'required_permission' => $validated['required_permission'] ?? null,
            'description' => $validated['description'] ?? null,
            'headers_example' => $validated['headers_example'] ?? null,
            'request_example' => $validated['request_example'] ?? null,
            'response_example' => $validated['response_example'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}

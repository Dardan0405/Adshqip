<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Category::with('parent')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('iab_code', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $categories = $query->paginate(20)->withQueryString();

        // Get parent categories for dropdown
        $parentCategories = Category::whereNull('parent_id')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:aq_categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'iab_code' => ['nullable', 'string', 'max:20'],
        ]);

        // Generate slug from name
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;

        // Ensure unique slug
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        Category::create([
            'parent_id' => $validated['parent_id'] ?: null,
            'name' => $validated['name'],
            'slug' => $slug,
            'iab_code' => $validated['iab_code'] ?? null,
            'status' => 'active',
        ]);

        return redirect()->route('admin.categories')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'parent_id' => ['nullable', 'integer', 'exists:aq_categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'iab_code' => ['nullable', 'string', 'max:20'],
        ]);

        // Prevent self-reference
        if ($validated['parent_id'] == $id) {
            return redirect()->back()->with('error', 'Category cannot be its own parent.');
        }

        // Generate new slug if name changed
        if ($category->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $originalSlug = $slug;
            $counter = 1;

            while (Category::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $category->slug = $slug;
        }

        $category->parent_id = $validated['parent_id'] ?: null;
        $category->name = $validated['name'];
        $category->iab_code = $validated['iab_code'] ?? null;
        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if category has children
        if ($category->children()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category with subcategories. Delete subcategories first.');
        }

        $category->delete();

        return redirect()->route('admin.categories')->with('success', 'Category deleted successfully.');
    }

    public function block($id)
    {
        $category = Category::findOrFail($id);
        $category->status = 'inactive';
        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category blocked successfully.');
    }

    public function unblock($id)
    {
        $category = Category::findOrFail($id);
        $category->status = 'active';
        $category->save();

        return redirect()->route('admin.categories')->with('success', 'Category unblocked successfully.');
    }

    public function show($id)
    {
        $category = Category::with(['parent', 'children'])->findOrFail($id);

        return response()->json($category);
    }
}

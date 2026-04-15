<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ParentCategoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Category::whereNull('parent_id')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $categories = $query->paginate(20)->withQueryString();

        return view('admin.parent-categories.index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        // Generate slug from name
        $slug = Str::slug($validated['name']);
        $originalSlug = $slug;
        $counter = 1;

        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        Category::create([
            'parent_id' => null,
            'name' => $validated['name'],
            'slug' => $slug,
            'status' => 'active',
        ]);

        return redirect()->route('admin.parent-categories')->with('success', 'Parent Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::whereNull('parent_id')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

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

        $category->name = $validated['name'];
        $category->save();

        return redirect()->route('admin.parent-categories')->with('success', 'Parent Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::whereNull('parent_id')->findOrFail($id);

        if ($category->children()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete parent category with subcategories.');
        }

        $category->delete();

        return redirect()->route('admin.parent-categories')->with('success', 'Parent Category deleted successfully.');
    }

    public function block($id)
    {
        $category = Category::whereNull('parent_id')->findOrFail($id);
        $category->status = 'inactive';
        $category->save();

        return redirect()->route('admin.parent-categories')->with('success', 'Parent Category blocked successfully.');
    }

    public function unblock($id)
    {
        $category = Category::whereNull('parent_id')->findOrFail($id);
        $category->status = 'active';
        $category->save();

        return redirect()->route('admin.parent-categories')->with('success', 'Parent Category unblocked successfully.');
    }
}

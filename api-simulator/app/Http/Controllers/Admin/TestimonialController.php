<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $featured = $request->input('featured');

        $query = Testimonial::query()
            ->when($search, function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('author_company', 'like', "%{$search}%")
                  ->orWhere('quote', 'like', "%{$search}%");
            })
            ->when($featured !== null && $featured !== '', function ($q) use ($featured) {
                $q->where('is_featured', $featured === '1');
            })
            ->ordered();

        $testimonials = $query->paginate(20)->withQueryString();

        return view('admin.testimonials.index', [
            'testimonials' => $testimonials,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:100'],
            'author_title' => ['nullable', 'string', 'max:100'],
            'author_company' => ['nullable', 'string', 'max:100'],
            'author_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'quote' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $avatarUrl = null;
        if ($request->hasFile('author_avatar')) {
            $path = $request->file('author_avatar')->store('testimonials', 'public');
            $avatarUrl = '/storage/' . $path;
        }

        Testimonial::create([
            'author_name' => $validated['author_name'],
            'author_title' => $validated['author_title'] ?? null,
            'author_company' => $validated['author_company'] ?? null,
            'author_avatar_url' => $avatarUrl,
            'quote' => $validated['quote'],
            'rating' => $validated['rating'] ?? 5,
            'is_featured' => $validated['is_featured'] ?? false,
            'is_published' => true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial created successfully.');
    }

    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        return response()->json($testimonial);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'author_name' => ['required', 'string', 'max:100'],
            'author_title' => ['nullable', 'string', 'max:100'],
            'author_company' => ['nullable', 'string', 'max:100'],
            'author_avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
            'quote' => ['required', 'string'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_featured' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $avatarUrl = $testimonial->author_avatar_url;
        if ($request->hasFile('author_avatar')) {
            // Delete old avatar if it exists and is stored locally
            if ($testimonial->author_avatar_url && str_starts_with($testimonial->author_avatar_url, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $testimonial->author_avatar_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('author_avatar')->store('testimonials', 'public');
            $avatarUrl = '/storage/' . $path;
        }

        $testimonial->update([
            'author_name' => $validated['author_name'],
            'author_title' => $validated['author_title'] ?? $testimonial->author_title,
            'author_company' => $validated['author_company'] ?? $testimonial->author_company,
            'author_avatar_url' => $avatarUrl,
            'quote' => $validated['quote'],
            'rating' => $validated['rating'] ?? $testimonial->rating,
            'is_featured' => $validated['is_featured'] ?? $testimonial->is_featured,
            'sort_order' => $validated['sort_order'] ?? $testimonial->sort_order,
        ]);

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial updated successfully.');
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        // Delete avatar file if stored locally
        if ($testimonial->author_avatar_url && str_starts_with($testimonial->author_avatar_url, '/storage/')) {
            $path = str_replace('/storage/', '', $testimonial->author_avatar_url);
            Storage::disk('public')->delete($path);
        }

        $testimonial->delete();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial deleted successfully.');
    }

    public function publish($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_published = true;
        $testimonial->save();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial published successfully.');
    }

    public function unpublish($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_published = false;
        $testimonial->save();

        return redirect()->route('admin.testimonials')->with('success', 'Testimonial unpublished successfully.');
    }

    public function toggleFeatured($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->is_featured = !$testimonial->is_featured;
        $testimonial->save();

        $status = $testimonial->is_featured ? 'featured' : 'unfeatured';
        return redirect()->route('admin.testimonials')->with('success', "Testimonial {$status} successfully.");
    }
}

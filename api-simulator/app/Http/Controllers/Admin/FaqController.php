<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category = $request->input('category');
        $language = $request->input('language');

        $query = Faq::query()
            ->when($search, function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                  ->orWhere('answer', 'like', "%{$search}%");
            })
            ->when($category, function ($q) use ($category) {
                $q->where('category', $category);
            })
            ->when($language, function ($q) use ($language) {
                $q->where('language', $language);
            })
            ->ordered();

        $faqs = $query->paginate(20)->withQueryString();

        $categories = Faq::select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        $languages = Faq::select('language')
            ->distinct()
            ->orderBy('language')
            ->pluck('language');

        return view('admin.faqs.index', [
            'faqs' => $faqs,
            'categories' => $categories,
            'languages' => $languages,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['required', 'string'],
            'language' => ['required', 'string', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        Faq::create([
            'category' => $validated['category'],
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'language' => $validated['language'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_published' => true,
        ]);

        return redirect()->route('admin.faqs')->with('success', 'FAQ created successfully.');
    }

    public function show($id)
    {
        $faq = Faq::findOrFail($id);

        return response()->json($faq);
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string', 'max:1000'],
            'answer' => ['required', 'string'],
            'language' => ['required', 'string', 'max:5'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $faq->update([
            'category' => $validated['category'],
            'question' => $validated['question'],
            'answer' => $validated['answer'],
            'language' => $validated['language'],
            'sort_order' => $validated['sort_order'] ?? $faq->sort_order,
        ]);

        return redirect()->route('admin.faqs')->with('success', 'FAQ updated successfully.');
    }

    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->route('admin.faqs')->with('success', 'FAQ deleted successfully.');
    }

    public function publish($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_published = true;
        $faq->save();

        return redirect()->route('admin.faqs')->with('success', 'FAQ published successfully.');
    }

    public function unpublish($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->is_published = false;
        $faq->save();

        return redirect()->route('admin.faqs')->with('success', 'FAQ unpublished successfully.');
    }

    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'orders' => ['required', 'array'],
            'orders.*.id' => ['required', 'integer', 'exists:aq_faq,id'],
            'orders.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['orders'] as $order) {
            Faq::where('id', $order['id'])->update(['sort_order' => $order['sort_order']]);
        }

        return response()->json(['success' => true]);
    }
}

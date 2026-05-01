<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaseStudyController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $caseStudies = CaseStudy::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('industry', 'like', "%{$search}%")
                        ->orWhere('metric_value', 'like', "%{$search}%");
                });
            })
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_published', $status === 'published'))
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return view('admin.case-studies.index', compact('caseStudies'));
    }

    public function store(Request $request)
    {
        CaseStudy::create($this->validatedPayload($request));

        return redirect()->route('admin.case-studies')->with('success', 'Case study created successfully.');
    }

    public function update(Request $request, CaseStudy $caseStudy)
    {
        $caseStudy->update($this->validatedPayload($request, $caseStudy->id));

        return redirect()->route('admin.case-studies')->with('success', 'Case study updated successfully.');
    }

    public function publish(CaseStudy $caseStudy)
    {
        $caseStudy->update(['is_published' => true]);

        return redirect()->route('admin.case-studies')->with('success', 'Case study published successfully.');
    }

    public function unpublish(CaseStudy $caseStudy)
    {
        $caseStudy->update(['is_published' => false]);

        return redirect()->route('admin.case-studies')->with('success', 'Case study unpublished successfully.');
    }

    public function destroy(CaseStudy $caseStudy)
    {
        $caseStudy->delete();

        return redirect()->route('admin.case-studies')->with('success', 'Case study deleted successfully.');
    }

    protected function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'slug' => ['nullable', 'string', 'max:80', 'unique:aq_case_studies,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'title' => ['required', 'string', 'max:160'],
            'audience_type' => ['required', 'in:publisher,advertiser,both'],
            'industry' => ['nullable', 'string', 'max:100'],
            'metric_value' => ['required', 'string', 'max:40'],
            'metric_label' => ['nullable', 'string', 'max:80'],
            'description' => ['required', 'string'],
            'content' => ['nullable', 'string'],
            'company_name' => ['required', 'string', 'max:120'],
            'logo_url' => ['nullable', 'string', 'max:500'],
            'accent_color' => ['nullable', 'string', 'max:20'],
            'chart_type' => ['required', 'in:comparison,line,bar'],
            'before_label' => ['nullable', 'string', 'max:60'],
            'before_value' => ['nullable', 'string', 'max:60'],
            'after_label' => ['nullable', 'string', 'max:60'],
            'after_value' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'is_published' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        return [
            'slug' => Str::slug($validated['slug'] ?: $validated['title']),
            'title' => $validated['title'],
            'audience_type' => $validated['audience_type'],
            'industry' => $validated['industry'] ?? null,
            'metric_value' => $validated['metric_value'],
            'metric_label' => $validated['metric_label'] ?? null,
            'description' => $validated['description'],
            'content' => $validated['content'] ?? null,
            'company_name' => $validated['company_name'],
            'client_name' => $validated['company_name'],
            'logo_url' => $validated['logo_url'] ?? null,
            'accent_color' => $validated['accent_color'] ?? '#e11d48',
            'chart_type' => $validated['chart_type'],
            'before_label' => $validated['before_label'] ?? null,
            'before_value' => $validated['before_value'] ?? null,
            'after_label' => $validated['after_label'] ?? null,
            'after_value' => $validated['after_value'] ?? null,
            'cta_url' => $validated['cta_url'] ?? null,
            'is_published' => (bool) ($validated['is_published'] ?? false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}

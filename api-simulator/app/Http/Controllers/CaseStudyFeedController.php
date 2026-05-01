<?php

namespace App\Http\Controllers;

use App\Models\CaseStudy;

class CaseStudyFeedController extends Controller
{
    public function index()
    {
        $caseStudies = CaseStudy::query()
            ->published()
            ->ordered()
            ->get()
            ->map(fn (CaseStudy $caseStudy) => $this->serializeCaseStudy($caseStudy))
            ->values();

        return response()
            ->json(['case_studies' => $caseStudies])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }

    public function show(string $slug)
    {
        $caseStudy = CaseStudy::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()
            ->json(['case_study' => $this->serializeCaseStudy($caseStudy, true)])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }

    public function options()
    {
        return response('', 204)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }

    protected function serializeCaseStudy(CaseStudy $caseStudy, bool $includeContent = false): array
    {
        $baseUrl = config('app.url', request()->getSchemeAndHttpHost());
        $logoUrl = $caseStudy->logo_url;

        if ($logoUrl && str_starts_with($logoUrl, '/storage/')) {
            $logoUrl = $baseUrl . $logoUrl;
        }

        $data = [
            'id' => $caseStudy->id,
            'slug' => $caseStudy->slug,
            'title' => $caseStudy->title,
            'audience_type' => $caseStudy->audience_type,
            'industry' => $caseStudy->industry,
            'metric_value' => $caseStudy->metric_value,
            'metric_label' => $caseStudy->metric_label,
            'description' => $caseStudy->description,
            'company_name' => $caseStudy->company_name,
            'logo_url' => $logoUrl,
            'accent_color' => $caseStudy->accent_color,
            'chart_type' => $caseStudy->chart_type,
            'before_label' => $caseStudy->before_label,
            'before_value' => $caseStudy->before_value,
            'after_label' => $caseStudy->after_label,
            'after_value' => $caseStudy->after_value,
            'cta_url' => $caseStudy->cta_url,
        ];

        if ($includeContent) {
            $data['content'] = $caseStudy->content ?: $caseStudy->description;
        }

        return $data;
    }
}

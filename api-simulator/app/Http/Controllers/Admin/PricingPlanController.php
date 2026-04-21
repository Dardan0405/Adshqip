<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PricingPlanController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20);

        $stats = [
            'total' => PricingPlan::count(),
            'active' => PricingPlan::where('status', 'active')->count(),
            'popular' => PricingPlan::where('is_popular', true)->count(),
            'enterprise' => PricingPlan::where('is_enterprise', true)->count(),
        ];

        return view('admin.pricing-plans.index', compact('plans', 'stats'));
    }

    public function store(Request $request)
    {
        $payload = $this->validatedPayload($request);

        PricingPlan::create($payload);

        return redirect()
            ->route('admin.pricing-plans')
            ->with('success', 'Pricing plan created successfully.');
    }

    public function update(Request $request, PricingPlan $pricingPlan)
    {
        $payload = $this->validatedPayload($request, $pricingPlan->id);

        $pricingPlan->update($payload);

        return redirect()
            ->route('admin.pricing-plans')
            ->with('success', 'Pricing plan updated successfully.');
    }

    public function block(PricingPlan $pricingPlan)
    {
        $pricingPlan->update(['status' => 'inactive']);

        return redirect()
            ->route('admin.pricing-plans')
            ->with('success', 'Pricing plan blocked successfully.');
    }

    public function unblock(PricingPlan $pricingPlan)
    {
        $pricingPlan->update(['status' => 'active']);

        return redirect()
            ->route('admin.pricing-plans')
            ->with('success', 'Pricing plan unblocked successfully.');
    }

    protected function validatedPayload(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'slug' => ['required', 'string', 'max:50', 'unique:aq_pricing_plans,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'target_audience' => ['required', 'in:advertiser,publisher,both'],
            'price_monthly' => ['nullable', 'numeric', 'min:0'],
            'price_yearly' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'features_input' => ['required', 'string'],
            'impressions_limit' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_popular' => ['nullable', 'boolean'],
            'is_enterprise' => ['nullable', 'boolean'],
        ]);

        return [
            'slug' => Str::slug($validated['slug']),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'target_audience' => $validated['target_audience'],
            'price_monthly' => $validated['price_monthly'] ?? null,
            'price_yearly' => $validated['price_yearly'] ?? null,
            'currency' => strtoupper($validated['currency']),
            'features' => collect(preg_split('/[\r\n]+/', trim($validated['features_input'])))
                ->map(fn ($feature) => trim((string) $feature))
                ->filter()
                ->values()
                ->all(),
            'impressions_limit' => $validated['impressions_limit'] ?? null,
            'is_popular' => (bool) ($validated['is_popular'] ?? false),
            'is_enterprise' => (bool) ($validated['is_enterprise'] ?? false),
            'status' => 'active',
            'sort_order' => $validated['sort_order'] ?? 0,
        ];
    }
}

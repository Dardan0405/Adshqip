<?php

namespace App\Http\Controllers;

use App\Models\PricingPlan;

class PricingPlanFeedController extends Controller
{
    public function index()
    {
        $plans = PricingPlan::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(function (PricingPlan $plan) {
                return [
                    'id' => $plan->id,
                    'slug' => $plan->slug,
                    'name' => $plan->name,
                    'description' => $plan->description,
                    'target_audience' => $plan->target_audience,
                    'price_monthly' => $plan->price_monthly !== null ? (float) $plan->price_monthly : null,
                    'price_yearly' => $plan->price_yearly !== null ? (float) $plan->price_yearly : null,
                    'currency' => $plan->currency,
                    'features' => collect($plan->features ?? [])
                        ->flatMap(fn ($feature) => preg_split('/(?:\r\n|\r|\n|&#10;)+/', (string) $feature))
                        ->map(fn ($feature) => trim(html_entity_decode((string) $feature)))
                        ->filter()
                        ->values()
                        ->all(),
                    'impressions_limit' => $plan->impressions_limit,
                    'is_popular' => (bool) $plan->is_popular,
                    'is_enterprise' => (bool) $plan->is_enterprise,
                ];
            })
            ->values();

        return response()
            ->json([
                'plans' => $plans,
            ])
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Origin');
    }
}

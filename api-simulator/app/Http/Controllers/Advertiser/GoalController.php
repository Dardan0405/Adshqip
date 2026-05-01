<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\ConversionGoal;
use App\Models\PixelTracker;
use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GoalController extends Controller
{
    private const PIXEL_TYPES = ['html_pixel', 's2s_pixel', 'mobile_s2s'];

    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;

        $query = ConversionGoal::query()
            ->with('pixelTracker')
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('goal_key', 'like', "%{$search}%")
                    ->orWhere('goal_type', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('goal_type')) {
            $query->where('goal_type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $goals = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $availableTrackers = PixelTracker::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->whereIn('type', self::PIXEL_TYPES)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'pixel_code', 'fire_count']);

        $campaigns = Campaign::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'pixel_tracker_id']);

        $goalPixelIds = ConversionGoal::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->whereNotNull('pixel_tracker_id')
            ->pluck('pixel_tracker_id');

        $linkedCampaignIds = $campaigns
            ->whereIn('pixel_tracker_id', $goalPixelIds)
            ->pluck('id');

        $totalConversions = $linkedCampaignIds->isEmpty()
            ? 0
            : (int) StatDaily::query()->whereIn('campaign_id', $linkedCampaignIds)->sum('conversions');

        $statsQuery = ConversionGoal::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false);

        return view('advertiser.tracking.goals', [
            'goals' => $goals,
            'availableTrackers' => $availableTrackers,
            'campaigns' => $campaigns,
            'totalGoals' => (clone $statsQuery)->count(),
            'activeGoals' => (clone $statsQuery)->where('status', 'active')->count(),
            'totalPixelFires' => (int) PixelTracker::query()
                ->whereIn('id', $goalPixelIds)
                ->sum('fire_count'),
            'totalConversions' => $totalConversions,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request) {
            $tracker = $this->resolveTracker($request);

            $goal = ConversionGoal::create([
                'advertiser_id' => $request->user()->id,
                'pixel_tracker_id' => $tracker?->id,
                'name' => $request->name,
                'goal_key' => $this->normalizeGoalKey($request->goal_key ?: $request->name),
                'goal_type' => $request->goal_type,
                'default_value' => $request->default_value ?? 0,
                'currency' => strtoupper($request->currency ?: 'USD'),
                'counting_method' => $request->counting_method,
                'attribution_window_days' => $request->attribution_window_days,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            if ($tracker) {
                $tracker->update([
                    'pixel_goal' => $goal->goal_key,
                    'category' => $goal->goal_type,
                    'status' => $goal->status,
                    'is_active' => $goal->status === 'active',
                ]);
            }

            $this->syncCampaign($request, $tracker);
        });

        return redirect()
            ->route('advertiser.tracking.goals')
            ->with('success', 'Goal created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $goal = $this->goalForUser($request, $id);
        $validator = $this->validator($request, $goal);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::transaction(function () use ($request, $goal) {
            $tracker = $this->resolveTracker($request, $goal);
            $goalKey = $this->normalizeGoalKey($request->goal_key ?: $request->name);

            $goal->update([
                'pixel_tracker_id' => $tracker?->id,
                'name' => $request->name,
                'goal_key' => $goalKey,
                'goal_type' => $request->goal_type,
                'default_value' => $request->default_value ?? 0,
                'currency' => strtoupper($request->currency ?: 'USD'),
                'counting_method' => $request->counting_method,
                'attribution_window_days' => $request->attribution_window_days,
                'status' => $request->status,
                'description' => $request->description,
            ]);

            if ($tracker) {
                $tracker->update([
                    'pixel_goal' => $goalKey,
                    'category' => $request->goal_type,
                    'status' => $request->status,
                    'is_active' => $request->status === 'active',
                ]);
            }

            $this->syncCampaign($request, $tracker);
        });

        return redirect()
            ->route('advertiser.tracking.goals')
            ->with('success', 'Goal updated successfully.');
    }

    public function code(Request $request, int $id)
    {
        $goal = $this->goalForUser($request, $id);
        $tracker = $goal->pixelTracker;

        if (!$tracker) {
            return response()->json(['message' => 'This goal has no linked tracker.'], 404);
        }

        $code = $tracker->pixel_code;
        $jsUrl = url("/track/pixel/{$code}/pixel.js");
        $gifUrl = url("/track/pixel/{$code}/pixel.gif");
        $postbackUrl = url("/track/pixel/{$code}/postback");

        $snippet = match ($tracker->type) {
            'html_pixel' => "<script async src=\"{$jsUrl}?goal={$goal->goal_key}&value={$goal->default_value}\"></script>\n<noscript><img src=\"{$gifUrl}?goal={$goal->goal_key}&value={$goal->default_value}\" width=\"1\" height=\"1\" style=\"display:none\" alt=\"\" /></noscript>",
            'mobile_s2s' => $postbackUrl . "?goal={$goal->goal_key}&device_id={device_id}&click_id={click_id}&payout={$goal->default_value}",
            default => $postbackUrl . "?goal={$goal->goal_key}&click_id={click_id}&payout={$goal->default_value}",
        };

        return response()->json([
            'name' => $goal->name,
            'goal_key' => $goal->goal_key,
            'pixel_code' => $code,
            'snippet' => $snippet,
            'test_url' => $tracker->type === 'html_pixel'
                ? $gifUrl . "?goal={$goal->goal_key}&value={$goal->default_value}"
                : $postbackUrl . "?goal={$goal->goal_key}&click_id=test&payout={$goal->default_value}",
        ]);
    }

    public function destroy(Request $request, int $id)
    {
        $goal = $this->goalForUser($request, $id);
        $goal->update(['is_deleted' => true, 'status' => 'archived']);

        return redirect()
            ->route('advertiser.tracking.goals')
            ->with('success', 'Goal archived successfully.');
    }

    private function validator(Request $request, ?ConversionGoal $goal = null)
    {
        $advertiserId = $request->user()->id;
        $goalKey = $this->normalizeGoalKey($request->goal_key ?: $request->name);

        $request->merge(['goal_key' => $goalKey]);

        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'goal_key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z0-9_\\-]+$/',
                Rule::unique('aq_conversion_goals', 'goal_key')
                    ->where('advertiser_id', $advertiserId)
                    ->where('is_deleted', false)
                    ->ignore($goal?->id),
            ],
            'goal_type' => ['required', 'in:purchase,lead,signup,install,pageview,custom'],
            'default_value' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'currency' => ['nullable', 'string', 'size:3'],
            'counting_method' => ['required', 'in:every,once_per_click,once_per_user'],
            'attribution_window_days' => ['required', 'integer', 'min:1', 'max:365'],
            'status' => ['required', 'in:active,paused,archived'],
            'description' => ['nullable', 'string', 'max:1000'],
            'tracker_mode' => ['required', 'in:create,existing,none'],
            'tracker_type' => ['required_if:tracker_mode,create', 'nullable', 'in:' . implode(',', self::PIXEL_TYPES)],
            'pixel_tracker_id' => ['required_if:tracker_mode,existing', 'nullable', 'integer'],
            'campaign_id' => ['nullable', 'integer'],
        ]);
    }

    private function resolveTracker(Request $request, ?ConversionGoal $goal = null): ?PixelTracker
    {
        if ($request->tracker_mode === 'none') {
            return null;
        }

        if ($request->tracker_mode === 'existing') {
            return PixelTracker::query()
                ->where('advertiser_id', $request->user()->id)
                ->where('is_deleted', false)
                ->whereIn('type', self::PIXEL_TYPES)
                ->findOrFail($request->pixel_tracker_id);
        }

        $existingTracker = $goal?->pixelTracker;
        if ($existingTracker && in_array($existingTracker->type, self::PIXEL_TYPES, true)) {
            $existingTracker->update([
                'name' => $request->name . ' Goal',
                'type' => $request->tracker_type,
                'tracking_url' => $this->trackingUrl($existingTracker->pixel_code, $request->tracker_type),
            ]);

            return $existingTracker;
        }

        $pixelCode = 'PX' . strtoupper(Str::random(10));

        return PixelTracker::create([
            'advertiser_id' => $request->user()->id,
            'name' => $request->name . ' Goal',
            'description' => $request->description,
            'type' => $request->tracker_type,
            'pixel_goal' => $request->goal_key,
            'category' => $request->goal_type,
            'pixel_code' => $pixelCode,
            'tracking_url' => $this->trackingUrl($pixelCode, $request->tracker_type),
            'status' => $request->status,
            'is_active' => $request->status === 'active',
        ]);
    }

    private function syncCampaign(Request $request, ?PixelTracker $tracker): void
    {
        if (!$tracker || !$request->filled('campaign_id')) {
            return;
        }

        Campaign::query()
            ->where('advertiser_id', $request->user()->id)
            ->where('pixel_tracker_id', $tracker->id)
            ->update(['pixel_tracker_id' => null]);

        Campaign::query()
            ->where('id', $request->campaign_id)
            ->where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->firstOrFail()
            ->update(['pixel_tracker_id' => $tracker->id]);
    }

    private function goalForUser(Request $request, int $id): ConversionGoal
    {
        return ConversionGoal::query()
            ->with('pixelTracker')
            ->where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->findOrFail($id);
    }

    private function normalizeGoalKey(string $value): string
    {
        return Str::of($value)->lower()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')->limit(100, '')->toString() ?: 'goal';
    }

    private function trackingUrl(string $pixelCode, string $type): string
    {
        return $type === 'html_pixel'
            ? url("/track/pixel/{$pixelCode}/pixel.gif")
            : url("/track/pixel/{$pixelCode}/postback");
    }
}

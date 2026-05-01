<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\PixelTracker;
use App\Models\StatDaily;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ConversionTrackingController extends Controller
{
    private const TYPES = ['html_pixel', 's2s_pixel', 'mobile_s2s'];

    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;

        $query = PixelTracker::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->whereIn('type', self::TYPES);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pixel_code', 'like', "%{$search}%")
                    ->orWhere('pixel_goal', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $trackers = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        $campaigns = Campaign::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'pixel_tracker_id']);

        $trackerIds = PixelTracker::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->whereIn('type', self::TYPES)
            ->pluck('id');

        $linkedCampaignIds = $campaigns
            ->whereIn('pixel_tracker_id', $trackerIds)
            ->pluck('id');

        $conversionCount = $linkedCampaignIds->isEmpty()
            ? 0
            : (int) StatDaily::query()
                ->whereIn('campaign_id', $linkedCampaignIds)
                ->sum('conversions');

        $statsQuery = PixelTracker::query()
            ->where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->whereIn('type', self::TYPES);

        return view('advertiser.tracking.conversions', [
            'trackers' => $trackers,
            'campaigns' => $campaigns,
            'totalTrackers' => (clone $statsQuery)->count(),
            'activeTrackers' => (clone $statsQuery)->where('status', 'active')->count(),
            'totalFires' => (int) (clone $statsQuery)->sum('fire_count'),
            'conversionCount' => $conversionCount,
        ]);
    }

    public function store(Request $request)
    {
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pixelCode = 'PX' . strtoupper(Str::random(10));
        $tracker = PixelTracker::create([
            'advertiser_id' => $request->user()->id,
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'pixel_goal' => $request->pixel_goal,
            'category' => $request->category,
            'pixel_code' => $pixelCode,
            'tracking_url' => $this->trackingUrl($pixelCode, $request->type),
            'append_code' => $request->append_code,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
        ]);

        $this->syncCampaign($request, $tracker);

        return redirect()
            ->route('advertiser.tracking.conversions')
            ->with('success', 'Conversion tracker created successfully.');
    }

    public function update(Request $request, int $id)
    {
        $tracker = $this->trackerForUser($request, $id);
        $validator = $this->validator($request);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $tracker->update([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'pixel_goal' => $request->pixel_goal,
            'category' => $request->category,
            'tracking_url' => $this->trackingUrl($tracker->pixel_code, $request->type),
            'append_code' => $request->append_code,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
        ]);

        $this->syncCampaign($request, $tracker);

        return redirect()
            ->route('advertiser.tracking.conversions')
            ->with('success', 'Conversion tracker updated successfully.');
    }

    public function code(Request $request, int $id)
    {
        $tracker = $this->trackerForUser($request, $id);
        $code = $tracker->pixel_code;
        $jsUrl = url("/track/pixel/{$code}/pixel.js");
        $gifUrl = url("/track/pixel/{$code}/pixel.gif");
        $postbackUrl = url("/track/pixel/{$code}/postback");

        $snippet = match ($tracker->type) {
            'html_pixel' => "<script async src=\"{$jsUrl}\"></script>\n<noscript><img src=\"{$gifUrl}\" width=\"1\" height=\"1\" style=\"display:none\" alt=\"\" /></noscript>"
                . ($tracker->append_code ? "\n" . $tracker->append_code : ''),
            'mobile_s2s' => $postbackUrl . '?device_id={device_id}&click_id={click_id}&payout={payout}',
            default => $postbackUrl . '?click_id={click_id}&payout={payout}',
        };

        return response()->json([
            'name' => $tracker->name,
            'type' => $tracker->type,
            'pixel_code' => $code,
            'tracking_url' => $tracker->type === 'html_pixel' ? $gifUrl : $postbackUrl,
            'snippet' => $snippet,
            'test_url' => $tracker->type === 'html_pixel' ? $gifUrl : $postbackUrl . '?click_id=test&payout=0',
        ]);
    }

    public function link(Request $request, int $id)
    {
        $tracker = $this->trackerForUser($request, $id);

        $request->validate([
            'campaign_id' => ['required', 'integer'],
        ]);

        $this->syncCampaign($request, $tracker);

        return redirect()
            ->route('advertiser.tracking.conversions')
            ->with('success', 'Tracker linked to campaign successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $tracker = $this->trackerForUser($request, $id);

        Campaign::query()
            ->where('advertiser_id', $request->user()->id)
            ->where('pixel_tracker_id', $tracker->id)
            ->update(['pixel_tracker_id' => null]);

        $tracker->update(['is_deleted' => true, 'is_active' => false, 'status' => 'archived']);

        return redirect()
            ->route('advertiser.tracking.conversions')
            ->with('success', 'Conversion tracker archived successfully.');
    }

    private function validator(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'type' => ['required', 'in:' . implode(',', self::TYPES)],
            'pixel_goal' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,paused,archived'],
            'append_code' => ['nullable', 'string'],
            'campaign_id' => ['nullable', 'integer'],
        ]);
    }

    private function syncCampaign(Request $request, PixelTracker $tracker): void
    {
        if (!$request->filled('campaign_id')) {
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

    private function trackerForUser(Request $request, int $id): PixelTracker
    {
        return PixelTracker::query()
            ->where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->whereIn('type', self::TYPES)
            ->findOrFail($id);
    }

    private function trackingUrl(string $pixelCode, string $type): string
    {
        return $type === 'html_pixel'
            ? url("/track/pixel/{$pixelCode}/pixel.gif")
            : url("/track/pixel/{$pixelCode}/postback");
    }
}

<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\PixelTracker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PixelTrackerController extends Controller
{
    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;
        $query = PixelTracker::where('advertiser_id', $advertiserId)->where('is_deleted', false);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pixel_code', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $pixels = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statsQuery = PixelTracker::where('advertiser_id', $advertiserId)->where('is_deleted', false);

        return view('advertiser.network.pixel-trackers.index', [
            'pixels' => $pixels,
            'totalPixels' => (clone $statsQuery)->count(),
            'htmlCount' => (clone $statsQuery)->where('type', 'html_pixel')->count(),
            's2sCount' => (clone $statsQuery)->where('type', 's2s_pixel')->count(),
            'activeCount' => (clone $statsQuery)->where('status', 'active')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:html_pixel,s2s_pixel,mobile_s2s'],
            'pixel_goal' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,paused,archived'],
            'append_code' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pixelCode = 'PX' . strtoupper(Str::random(10));
        $trackingUrl = match ($request->type) {
            'html_pixel' => url("/track/pixel/{$pixelCode}/pixel.gif"),
            's2s_pixel', 'mobile_s2s' => url("/track/pixel/{$pixelCode}/postback"),
        };

        PixelTracker::create([
            'advertiser_id' => $request->user()->id,
            'name' => $request->name,
            'type' => $request->type,
            'pixel_goal' => $request->pixel_goal,
            'category' => $request->category,
            'pixel_code' => $pixelCode,
            'tracking_url' => $trackingUrl,
            'append_code' => $request->append_code,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
        ]);

        return redirect()->route('advertiser.network.pixel-trackers')->with('success', 'Pixel tracker created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $pixel = PixelTracker::where('advertiser_id', $request->user()->id)->where('is_deleted', false)->findOrFail($id);

        return response()->json($pixel->only([
            'id',
            'name',
            'type',
            'pixel_goal',
            'category',
            'status',
            'append_code',
            'pixel_code',
            'tracking_url',
        ]));
    }

    public function update(Request $request, int $id)
    {
        $pixel = PixelTracker::where('advertiser_id', $request->user()->id)->where('is_deleted', false)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:html_pixel,s2s_pixel,mobile_s2s'],
            'pixel_goal' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:active,paused,archived'],
            'append_code' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $validator->errors()->first()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $pixel->update([
            'name' => $request->name,
            'type' => $request->type,
            'pixel_goal' => $request->pixel_goal,
            'category' => $request->category,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
            'append_code' => $request->append_code,
        ]);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Pixel tracker updated successfully.'])
            : redirect()->route('advertiser.network.pixel-trackers')->with('success', 'Pixel tracker updated successfully.');
    }

    public function getCode(Request $request, int $id)
    {
        $pixel = PixelTracker::where('advertiser_id', $request->user()->id)->where('is_deleted', false)->findOrFail($id);
        $code = $pixel->pixel_code;
        $jsUrl = url("/track/pixel/{$code}/pixel.js");
        $gifUrl = url("/track/pixel/{$code}/pixel.gif");
        $postbackUrl = url("/track/pixel/{$code}/postback");

        return response()->json([
            'id' => $pixel->id,
            'name' => $pixel->name,
            'type' => $pixel->type,
            'pixel_code' => $code,
            'tracking_url' => $pixel->type === 'html_pixel' ? $gifUrl : $postbackUrl,
            'html_code' => $pixel->type === 'html_pixel'
                ? "<script async src=\"{$jsUrl}\"></script><noscript><img src=\"{$gifUrl}\" width=\"1\" height=\"1\" style=\"display:none\" /></noscript>"
                : $postbackUrl . '?click_id={click_id}&payout={payout}',
        ]);
    }

    public function linkCampaign(Request $request, int $id)
    {
        $pixel = PixelTracker::where('advertiser_id', $request->user()->id)->where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'campaign_id' => ['required', 'integer'],
            'campaign_type' => ['required', 'in:network'],
        ]);

        Campaign::where('id', $request->campaign_id)
            ->where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->firstOrFail()
            ->update(['pixel_tracker_id' => $pixel->id]);

        return response()->json(['success' => true, 'message' => 'Pixel tracker linked to campaign successfully.']);
    }

    public function destroy(Request $request, int $id)
    {
        PixelTracker::where('advertiser_id', $request->user()->id)
            ->where('is_deleted', false)
            ->findOrFail($id)
            ->update(['is_deleted' => true]);

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Pixel tracker deleted successfully.'])
            : redirect()->route('advertiser.network.pixel-trackers')->with('success', 'Pixel tracker deleted successfully.');
    }
}

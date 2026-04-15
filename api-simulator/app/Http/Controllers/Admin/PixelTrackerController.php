<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\DirectCampaign;
use App\Models\Keyword;
use App\Models\PixelTracker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PixelTrackerController extends Controller
{
    public function index(Request $request)
    {
        $query = PixelTracker::with(['advertiser.profile'])->where('is_deleted', false);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pixel_code', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhereHas('advertiser', function ($subQ) use ($search) {
                      $subQ->where('email', 'like', "%{$search}%")
                           ->orWhereHas('profile', function ($profQ) use ($search) {
                               $profQ->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('company_name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $pixels = $query->orderBy('created_at', 'desc')->paginate(20);

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get();

        $totalPixels = PixelTracker::where('is_deleted', false)->count();
        $htmlCount = PixelTracker::where('is_deleted', false)->where('type', 'html_pixel')->count();
        $s2sCount = PixelTracker::where('is_deleted', false)->where('type', 's2s_pixel')->count();
        $mobileS2sCount = PixelTracker::where('is_deleted', false)->where('type', 'mobile_s2s')->count();
        $activeCount = PixelTracker::where('is_deleted', false)->where('status', 'active')->count();

        return view('admin.pixel-trackers.index', compact(
            'pixels',
            'advertisers',
            'totalPixels',
            'htmlCount',
            's2sCount',
            'mobileS2sCount',
            'activeCount'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advertiser_id' => 'required|exists:aq_users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:html_pixel,s2s_pixel,mobile_s2s',
            'pixel_goal' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:active,paused,archived',
            'append_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pixelCode = 'PX' . strtoupper(Str::random(10));

        // Build tracking URL based on type (local endpoints)
        $trackingUrl = match ($request->type) {
            'html_pixel' => url("/track/pixel/{$pixelCode}/pixel.gif"),
            's2s_pixel' => url("/track/pixel/{$pixelCode}/postback"),
            'mobile_s2s' => url("/track/pixel/{$pixelCode}/postback"),
        };

        PixelTracker::create([
            'advertiser_id' => $request->advertiser_id,
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

        return redirect()->route('admin.pixel-trackers')->with('success', 'Pixel tracker created successfully.');
    }

    /**
     * Show pixel tracker details (for edit modal - AJAX)
     */
    public function show($id)
    {
        $pixel = PixelTracker::with(['advertiser.profile'])->where('is_deleted', false)->findOrFail($id);

        $advertiserName = trim(($pixel->advertiser->profile->first_name ?? '') . ' ' . ($pixel->advertiser->profile->last_name ?? ''));
        if (!$advertiserName) {
            $advertiserName = $pixel->advertiser->email ?? 'N/A';
        }

        return response()->json([
            'id' => $pixel->id,
            'name' => $pixel->name,
            'type' => $pixel->type,
            'pixel_goal' => $pixel->pixel_goal,
            'category' => $pixel->category,
            'status' => $pixel->status ?? 'active',
            'append_code' => $pixel->append_code,
            'advertiser_id' => $pixel->advertiser_id,
            'advertiser_name' => $advertiserName,
            'pixel_code' => $pixel->pixel_code,
            'tracking_url' => $pixel->tracking_url,
        ]);
    }

    /**
     * Update pixel tracker
     */
    public function update(Request $request, $id)
    {
        $pixel = PixelTracker::where('is_deleted', false)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'advertiser_id' => 'required|exists:aq_users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:html_pixel,s2s_pixel,mobile_s2s',
            'pixel_goal' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'status' => 'required|in:active,paused,archived',
            'append_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $pixel->update([
            'advertiser_id' => $request->advertiser_id,
            'name' => $request->name,
            'type' => $request->type,
            'pixel_goal' => $request->pixel_goal,
            'category' => $request->category,
            'status' => $request->status,
            'is_active' => $request->status === 'active',
            'append_code' => $request->append_code,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Pixel tracker updated successfully.']);
        }

        return redirect()->route('admin.pixel-trackers')->with('success', 'Pixel tracker updated successfully.');
    }

    /**
     * Get pixel code and tracking details (AJAX)
     */
    public function getCode($id)
    {
        $pixel = PixelTracker::where('is_deleted', false)->findOrFail($id);

        $code = $pixel->pixel_code;
        $jsUrl = url("/track/pixel/{$code}/pixel.js");
        $gifUrl = url("/track/pixel/{$code}/pixel.gif");
        $postbackUrl = url("/track/pixel/{$code}/postback");

        $htmlCode = match ($pixel->type) {
            'html_pixel' => "<!-- AdsHqip Pixel — {$code} -->\n"
                . "<script type=\"text/javascript\">\n"
                . "  (function() {\n"
                . "    var aq = document.createElement('script');\n"
                . "    aq.type = 'text/javascript'; aq.async = true;\n"
                . "    aq.src = '{$jsUrl}';\n"
                . "    var s = document.getElementsByTagName('script')[0];\n"
                . "    s.parentNode.insertBefore(aq, s);\n"
                . "  })();\n"
                . "</script>\n"
                . "<noscript><img src=\"{$gifUrl}\" width=\"1\" height=\"1\" style=\"display:none\" /></noscript>"
                . ($pixel->append_code ? "\n" . $pixel->append_code : ''),
            's2s_pixel' => $postbackUrl . '?click_id={click_id}&payout={payout}',
            'mobile_s2s' => $postbackUrl . '?device_id={device_id}&click_id={click_id}&payout={payout}',
            default => $pixel->tracking_url,
        };

        // Use real local tracking URL
        $trackingUrl = match ($pixel->type) {
            'html_pixel' => $gifUrl,
            's2s_pixel', 'mobile_s2s' => $postbackUrl,
            default => $pixel->tracking_url,
        };

        return response()->json([
            'id' => $pixel->id,
            'name' => $pixel->name,
            'type' => $pixel->type,
            'pixel_code' => $pixel->pixel_code,
            'tracking_url' => $trackingUrl,
            'html_code' => $htmlCode,
            'append_code' => $pixel->append_code,
        ]);
    }

    /**
     * Get campaigns available for linking (AJAX)
     */
    public function getCampaigns($advertiserId)
    {
        $networkCampaigns = Campaign::where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name', 'pixel_tracker_id'])
            ->map(fn (Campaign $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => 'network',
                'label' => $c->name,
                'has_pixel' => !is_null($c->pixel_tracker_id),
            ]);

        $directCampaigns = DirectCampaign::where('advertiser_id', $advertiserId)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (DirectCampaign $c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => 'direct',
                'label' => $c->name . ' (Direct)',
                'has_pixel' => false,
            ]);

        return response()->json($networkCampaigns->concat($directCampaigns)->sortBy('name')->values());
    }

    /**
     * Link pixel tracker to a campaign
     */
    public function linkCampaign(Request $request, $id)
    {
        $pixel = PixelTracker::where('is_deleted', false)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'campaign_id' => 'required|integer',
            'campaign_type' => 'required|in:network,direct',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        if ($request->campaign_type === 'network') {
            $campaign = Campaign::where('id', $request->campaign_id)
                ->where('advertiser_id', $pixel->advertiser_id)
                ->where('is_deleted', false)
                ->firstOrFail();

            $campaign->update(['pixel_tracker_id' => $pixel->id]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pixel tracker linked to campaign successfully.',
        ]);
    }

    public function destroy($id)
    {
        $pixel = PixelTracker::findOrFail($id);
        $pixel->update(['is_deleted' => true]);

        return response()->json(['success' => true, 'message' => 'Pixel tracker deleted successfully.']);
    }

    // ─── PUBLIC PIXEL FIRE ENDPOINTS ──────────────────────

    /**
     * JavaScript pixel fire — serves a JS snippet that fires an image pixel
     * and detects meta keywords from the page for campaign targeting.
     * URL: /track/pixel/{code}/pixel.js
     */
    public function fireJs(string $code)
    {
        $pixel = PixelTracker::where('pixel_code', $code)
            ->where('is_deleted', false)
            ->first();

        if ($pixel && $pixel->is_active) {
            $pixel->increment('fire_count');
            $pixel->update(['last_fired_at' => now()]);

            // Also increment stats on any linked campaign
            $this->trackPixelOnCampaign($pixel);
        }

        $gifUrl = url("/track/pixel/{$code}/pixel.gif");
        $keywordEndpoint = url("/track/pixel/{$code}/keywords");

        // Enhanced JS that detects meta keywords and sends them to the server
        $js = <<<JS
/* AdsHqip Pixel — {$code} */
(function(){
    // Fire the tracking pixel
    var i=new Image();
    i.src="{$gifUrl}?cb="+Math.random();

    // Detect and send meta keywords for campaign targeting
    try {
        var metaKeywords = '';
        var metaTags = document.getElementsByTagName('meta');
        for (var j = 0; j < metaTags.length; j++) {
            var name = (metaTags[j].getAttribute('name') || '').toLowerCase();
            if (name === 'keywords') {
                metaKeywords = metaTags[j].getAttribute('content') || '';
                break;
            }
        }
        if (metaKeywords) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', '{$keywordEndpoint}', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.send(JSON.stringify({
                keywords: metaKeywords,
                url: window.location.href,
                title: document.title
            }));
        }
    } catch(e) {}
})();
JS;

        return response($js, 200)
            ->header('Content-Type', 'application/javascript')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Receive meta keywords from a page and match against campaigns.
     * URL: /track/pixel/{code}/keywords (POST)
     */
    public function receiveKeywords(string $code, Request $request)
    {
        $pixel = PixelTracker::where('pixel_code', $code)
            ->where('is_deleted', false)
            ->first();

        if (!$pixel || !$pixel->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Pixel not found or inactive.'], 404);
        }

        $keywordsString = $request->input('keywords', '');
        $pageUrl = $request->input('url', '');
        $pageTitle = $request->input('title', '');

        if (empty($keywordsString)) {
            return response()->json(['status' => 'ok', 'matched' => 0]);
        }

        // Parse keywords (comma-separated, trim whitespace)
        $pageKeywords = array_filter(
            array_map('trim', explode(',', strtolower($keywordsString)))
        );

        if (empty($pageKeywords)) {
            return response()->json(['status' => 'ok', 'matched' => 0]);
        }

        // Track keyword matches in our database
        $matchedKeywordCount = $this->trackKeywordMatches($pageKeywords);

        // Find campaigns that target any of these keywords
        $matchedCampaigns = $this->findCampaignsByKeywords($pageKeywords, $pixel);

        return response()->json([
            'status' => 'success',
            'pixel_code' => $code,
            'keywords_received' => count($pageKeywords),
            'keywords_matched' => $matchedKeywordCount,
            'campaigns_matched' => count($matchedCampaigns),
        ], 200);
    }

    /**
     * Track keyword matches in the aq_keywords table.
     */
    private function trackKeywordMatches(array $pageKeywords): int
    {
        if (!Schema::hasTable('aq_keywords')) {
            return 0;
        }

        $matchedCount = 0;

        // Get all active keywords from database
        $dbKeywords = Keyword::where('status', 'active')->get();

        foreach ($dbKeywords as $dbKeyword) {
            $keywordLower = strtolower($dbKeyword->keyword);

            // Check if any page keyword matches (exact or contains)
            foreach ($pageKeywords as $pageKw) {
                if ($keywordLower === $pageKw || stripos($pageKw, $keywordLower) !== false || stripos($keywordLower, $pageKw) !== false) {
                    $dbKeyword->increment('match_count');
                    $dbKeyword->update(['last_matched_at' => now()]);
                    $matchedCount++;
                    break; // Only count once per db keyword
                }
            }
        }

        return $matchedCount;
    }

    /**
     * Find campaigns that target any of the given keywords.
     */
    private function findCampaignsByKeywords(array $pageKeywords, PixelTracker $pixel): array
    {
        // Get active campaigns that have keyword targeting set
        $campaigns = Campaign::where('is_deleted', false)
            ->where('status', 'active')
            ->whereNotNull('targeting_keywords')
            ->get();

        $matchedCampaigns = [];

        foreach ($campaigns as $campaign) {
            $targetingKeywords = $campaign->targeting_keywords;

            if (!is_array($targetingKeywords) || empty($targetingKeywords)) {
                continue;
            }

            // Check if any campaign keyword matches any page keyword
            foreach ($targetingKeywords as $targetKw) {
                $targetKwLower = strtolower($targetKw);

                foreach ($pageKeywords as $pageKw) {
                    if ($targetKwLower === $pageKw || stripos($pageKw, $targetKwLower) !== false) {
                        $matchedCampaigns[] = [
                            'campaign_id' => $campaign->id,
                            'campaign_name' => $campaign->name,
                            'matched_keyword' => $targetKw,
                        ];

                        // Record this as a keyword-based impression/match
                        $this->trackKeywordMatchOnCampaign($campaign);
                        break 2; // Found a match, move to next campaign
                    }
                }
            }
        }

        return $matchedCampaigns;
    }

    /**
     * Record a keyword-based match on a campaign's stats.
     */
    private function trackKeywordMatchOnCampaign(Campaign $campaign): void
    {
        $today = now()->toDateString();

        $row = \App\Models\StatDaily::where('date', $today)
            ->where('campaign_id', $campaign->id)
            ->whereNull('ad_id')
            ->first();

        if (!$row) {
            \App\Models\StatDaily::create([
                'date' => $today,
                'campaign_id' => $campaign->id,
                'ad_id' => null,
                'impressions' => 1,
                'clicks' => 0,
                'conversions' => 0,
                'unique_impressions' => 1,
                'unique_clicks' => 0,
                'viewable_impressions' => 1,
                'revenue' => 0,
                'publisher_earnings' => 0,
            ]);
        } else {
            $row->increment('impressions');
            $row->increment('viewable_impressions');
        }
    }

    /**
     * Image pixel fire — serves a 1x1 transparent GIF.
     * URL: /track/pixel/{code}/pixel.gif
     */
    public function fireGif(string $code)
    {
        $pixel = PixelTracker::where('pixel_code', $code)
            ->where('is_deleted', false)
            ->first();

        // Only increment if not already counted by JS (check for cache-buster param)
        if ($pixel && $pixel->is_active && !request()->has('cb')) {
            $pixel->increment('fire_count');
            $pixel->update(['last_fired_at' => now()]);

            $this->trackPixelOnCampaign($pixel);
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * S2S / Mobile S2S postback fire.
     * URL: /track/pixel/{code}/postback
     */
    public function firePostback(string $code, Request $request)
    {
        $pixel = PixelTracker::where('pixel_code', $code)
            ->where('is_deleted', false)
            ->first();

        if (!$pixel) {
            return response()->json(['status' => 'error', 'message' => 'Pixel not found.'], 404);
        }

        if (!$pixel->is_active) {
            return response()->json(['status' => 'error', 'message' => 'Pixel is inactive.'], 403);
        }

        $pixel->increment('fire_count');
        $pixel->update(['last_fired_at' => now()]);

        $this->trackPixelOnCampaign($pixel);

        return response()->json([
            'status' => 'success',
            'message' => 'Pixel fire recorded.',
            'pixel_code' => $code,
            'fire_count' => $pixel->fresh()->fire_count,
        ], 200);
    }

    /**
     * When a pixel fires, find any campaign linked to it and record a conversion stat.
     */
    private function trackPixelOnCampaign(PixelTracker $pixel): void
    {
        $campaigns = Campaign::where('pixel_tracker_id', $pixel->id)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->get();

        foreach ($campaigns as $campaign) {
            $today = now()->toDateString();

            $row = \App\Models\StatDaily::where('date', $today)
                ->where('campaign_id', $campaign->id)
                ->whereNull('ad_id')
                ->first();

            if (!$row) {
                \App\Models\StatDaily::create([
                    'date' => $today,
                    'campaign_id' => $campaign->id,
                    'ad_id' => null,
                    'impressions' => 0,
                    'clicks' => 0,
                    'conversions' => 1,
                    'unique_impressions' => 0,
                    'unique_clicks' => 0,
                    'viewable_impressions' => 0,
                    'revenue' => 0,
                    'publisher_earnings' => 0,
                ]);
            } else {
                $row->increment('conversions');
            }
        }
    }

    public function export(Request $request)
    {
        $query = PixelTracker::with(['advertiser.profile'])->where('is_deleted', false);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('pixel_code', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('advertiser', function ($subQ) use ($search) {
                      $subQ->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        $pixels = $query->orderBy('created_at', 'desc')->get();

        $filename = 'pixel_trackers_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pixels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Type', 'Advertiser', 'Category', 'Pixel Code', 'Status', 'Created At']);

            foreach ($pixels as $pixel) {
                $advertiserName = trim(($pixel->advertiser->profile->first_name ?? '') . ' ' . ($pixel->advertiser->profile->last_name ?? ''));
                if (!$advertiserName) {
                    $advertiserName = $pixel->advertiser->email ?? 'N/A';
                }

                $typeLabels = [
                    'html_pixel' => 'HTML Pixel',
                    's2s_pixel' => 'S2S Pixel',
                    'mobile_s2s' => 'Mobile S2S',
                ];

                fputcsv($file, [
                    $pixel->id,
                    $pixel->name,
                    $typeLabels[$pixel->type] ?? $pixel->type,
                    $advertiserName,
                    $pixel->category ?? '-',
                    $pixel->pixel_code,
                    ucfirst($pixel->status ?? 'active'),
                    $pixel->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

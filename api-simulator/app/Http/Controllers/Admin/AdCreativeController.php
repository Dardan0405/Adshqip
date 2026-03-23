<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\StatDaily;
use Illuminate\Http\Request;

class AdCreativeController extends Controller
{
    /**
     * Display the ad formats / creatives listing page.
     */
    public function index(Request $request)
    {
        // Get all ads with their relationships
        $query = Ad::with(['campaign', 'primaryCreative'])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc');

        // Search filter
        $search = $request->query('search', '');
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('campaign', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Ad type filter
        $typeFilter = $request->query('type', 'all');
        if ($typeFilter !== 'all') {
            $query->where('ad_type', $typeFilter);
        }

        // Status filter
        $statusFilter = $request->query('status', 'all');
        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $allAds = $query->get();

        // Calculate earnings/stats (demo data for now)
        $today = now();
        $totalEarnings = $allAds->count() * 12.50; // Demo: avg $12.50 per creative
        $todayEarnings = $allAds->where('status', 'active')->count() * 3.25;
        $monthEarnings = $totalEarnings * 0.35;
        $yearEarnings = $totalEarnings;

        $stats = [
            'total_creatives' => $allAds->count(),
            'active' => $allAds->where('status', 'active')->count(),
            'paused' => $allAds->where('status', 'paused')->count(),
            'pending_review' => $allAds->where('status', 'pending_review')->count(),
            'rejected' => $allAds->where('status', 'rejected')->count(),
            'total_earnings' => $totalEarnings,
            'today_earnings' => $todayEarnings,
            'month_earnings' => $monthEarnings,
            'year_earnings' => $yearEarnings,
            'today_date' => $today->format('F j, Y'),
            'today_day' => $today->format('l'),
            'today_time' => $today->format('H:i'),
        ];

        // Transform ads for the view
        $ads = $allAds->map(function ($ad) {
            $creative = $ad->primaryCreative;
            return [
                'id' => $ad->id,
                'name' => $ad->name,
                'campaign_name' => $ad->campaign ? $ad->campaign->name : '—',
                'campaign_id' => $ad->campaign_id,
                'ad_type' => $ad->ad_type,
                'status' => $ad->status,
                'file_type' => $creative ? $creative->file_type : '—',
                'size' => $creative ? ($creative->width && $creative->height ? $creative->width . 'x' . $creative->height : '—') : '—',
                'file_size_kb' => $creative ? ($creative->file_size_bytes ? number_format($creative->file_size_bytes / 1024, 1) : '—') : '—',
                'weight' => $ad->weight ?? 5,
                'destination_url' => $ad->destination_url,
                'file_path' => $creative ? $creative->file_path : null,
                'headline' => $ad->headline,
                'body_text' => $ad->body_text,
                'call_to_action' => $ad->call_to_action,
                'display_url' => $ad->display_url,
                'brand_name' => $ad->brand_name,
                'created_at' => $ad->created_at?->format('Y-m-d H:i'),
            ];
        })->toArray();

        // Pagination
        $perPage = 25;
        $currentPage = (int) $request->query('page', 1);
        $totalAds = count($ads);
        $totalPages = max(1, ceil($totalAds / $perPage));
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        $paginatedAds = array_slice($ads, $offset, $perPage);

        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $totalAds,
            'total_pages' => $totalPages,
            'from' => $totalAds > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $totalAds),
        ];

        // Ad types for filter dropdown
        $adTypes = [
            'image' => 'Image',
            'html' => 'HTML',
            'video' => 'Video',
            'text' => 'Text',
            'rich_media' => 'Rich Media',
            'native' => 'Native',
            'vast' => 'VAST',
        ];

        return view('admin.adformats.index', [
            'ads' => $paginatedAds,
            'stats' => $stats,
            'search' => $search,
            'typeFilter' => $typeFilter,
            'statusFilter' => $statusFilter,
            'adTypes' => $adTypes,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Show the edit form for an ad creative.
     */
    public function edit(int $id)
    {
        $ad = Ad::with(['campaign', 'primaryCreative'])->find($id);

        if (!$ad || $ad->is_deleted) {
            return redirect()
                ->route('admin.adformats')
                ->with('error', 'Creative not found.');
        }

        $creative = $ad->primaryCreative;

        $adData = [
            'id' => $ad->id,
            'name' => $ad->name,
            'campaign_id' => $ad->campaign_id,
            'campaign_name' => $ad->campaign ? $ad->campaign->name : '—',
            'ad_type' => $ad->ad_type,
            'status' => $ad->status,
            'destination_url' => $ad->destination_url,
            'display_url' => $ad->display_url,
            'headline' => $ad->headline,
            'body_text' => $ad->body_text,
            'call_to_action' => $ad->call_to_action,
            'brand_name' => $ad->brand_name,
            'weight' => $ad->weight ?? 5,
            'file_type' => $creative ? $creative->file_type : null,
            'file_path' => $creative ? $creative->file_path : null,
            'width' => $creative ? $creative->width : null,
            'height' => $creative ? $creative->height : null,
            'alt_text' => $creative ? $creative->alt_text : null,
        ];

        $adTypes = [
            'image' => 'Image',
            'html' => 'HTML',
            'video' => 'Video',
            'text' => 'Text',
            'rich_media' => 'Rich Media',
            'native' => 'Native',
            'vast' => 'VAST',
        ];

        $statuses = [
            'active' => 'Active',
            'paused' => 'Paused',
            'pending_review' => 'Pending Review',
            'rejected' => 'Rejected',
            'archived' => 'Archived',
        ];

        $campaigns = \App\Models\Campaign::where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->toArray();

        return view('admin.adformats.edit', [
            'ad' => $adData,
            'adTypes' => $adTypes,
            'statuses' => $statuses,
            'campaigns' => $campaigns,
        ]);
    }

    /**
     * Update an ad creative.
     */
    public function update(Request $request, int $id)
    {
        $ad = Ad::with('primaryCreative')->find($id);

        if (!$ad || $ad->is_deleted) {
            return redirect()
                ->route('admin.adformats')
                ->with('error', 'Creative not found.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'campaign_id' => 'required|exists:aq_campaigns,id',
            'ad_type' => 'required|in:image,html,video,text,rich_media,native,vast',
            'status' => 'required|in:active,paused,pending_review,rejected,archived',
            'destination_url' => 'required|url|max:2000',
            'display_url' => 'nullable|string|max:500',
            'headline' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
            'call_to_action' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:100',
            'weight' => 'required|integer|min:1|max:10',
            'alt_text' => 'nullable|string|max:255',
            'width' => 'nullable|integer|min:0',
            'height' => 'nullable|integer|min:0',
        ]);

        $ad->update([
            'name' => $request->input('name'),
            'campaign_id' => $request->input('campaign_id'),
            'ad_type' => $request->input('ad_type'),
            'status' => $request->input('status'),
            'destination_url' => $request->input('destination_url'),
            'display_url' => $request->input('display_url'),
            'headline' => $request->input('headline'),
            'body_text' => $request->input('body_text'),
            'call_to_action' => $request->input('call_to_action'),
            'brand_name' => $request->input('brand_name'),
            'weight' => $request->input('weight'),
        ]);

        // Update or create primary creative for dimensions
        $creative = $ad->primaryCreative;
        if ($creative) {
            $creative->update([
                'alt_text' => $request->input('alt_text'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
            ]);
        } elseif ($request->input('width') || $request->input('height')) {
            $ad->creatives()->create([
                'file_path' => null,
                'file_type' => 'image',
                'is_primary' => true,
                'alt_text' => $request->input('alt_text'),
                'width' => $request->input('width'),
                'height' => $request->input('height'),
            ]);
        }

        return redirect()
            ->route('admin.adformats')
            ->with('success', "Creative \"{$ad->name}\" updated successfully.");
    }

    /**
     * Update the weight of an ad.
     */
    public function updateWeight(Request $request, int $id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return response()->json(['success' => false, 'message' => 'Creative not found'], 404);
        }

        $request->validate(['weight' => 'required|integer|min:1|max:10']);

        $ad->weight = $request->input('weight');
        $ad->save();

        return response()->json(['success' => true, 'weight' => $ad->weight]);
    }

    /**
     * Update the status of an ad.
     */
    public function updateStatus(Request $request, int $id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return redirect()
                ->route('admin.adformats')
                ->with('error', 'Creative not found');
        }

        $request->validate(['status' => 'required|in:active,paused,pending_review,rejected,archived']);

        $ad->status = $request->input('status');
        $ad->save();

        return redirect()
            ->route('admin.adformats')
            ->with('success', "Creative \"{$ad->name}\" status updated to {$ad->status}.");
    }

    /**
     * Soft delete an ad creative.
     */
    public function destroy(int $id)
    {
        $ad = Ad::find($id);

        if (!$ad) {
            return redirect()
                ->route('admin.adformats')
                ->with('error', 'Creative not found');
        }

        $ad->is_deleted = true;
        $ad->save();

        return redirect()
            ->route('admin.adformats')
            ->with('success', 'Creative deleted successfully!');
    }

    /**
     * Show a live demo page for the ad creative.
     */
    public function demo(int $id)
    {
        $ad = Ad::with(['campaign', 'primaryCreative'])->find($id);

        if (!$ad || $ad->is_deleted) {
            return redirect()
                ->route('admin.adformats')
                ->with('error', 'Creative not found.');
        }

        $creative = $ad->primaryCreative;

        $adData = [
            'id' => $ad->id,
            'name' => $ad->name,
            'campaign_name' => $ad->campaign ? $ad->campaign->name : '—',
            'ad_type' => $ad->ad_type,
            'status' => $ad->status,
            'destination_url' => $ad->destination_url,
            'display_url' => $ad->display_url,
            'headline' => $ad->headline,
            'body_text' => $ad->body_text,
            'call_to_action' => $ad->call_to_action,
            'brand_name' => $ad->brand_name,
            'weight' => $ad->weight ?? 5,
            'file_type' => $creative ? $creative->file_type : null,
            'file_path' => $creative ? $creative->file_path : null,
            'width' => $creative ? $creative->width : null,
            'height' => $creative ? $creative->height : null,
            'alt_text' => $creative ? $creative->alt_text : null,
            'file_size_kb' => $creative && $creative->file_size_bytes ? number_format($creative->file_size_bytes / 1024, 1) : null,
        ];

        return view('admin.adformats.demo', ['ad' => $adData]);
    }

    /**
     * Show reports for a specific ad creative.
     */
    public function reports(Request $request, int $id)
    {
        $ad = Ad::with(['campaign', 'primaryCreative'])->find($id);

        if (!$ad || $ad->is_deleted) {
            return redirect()->route('admin.adformats')->with('error', 'Creative not found.');
        }

        $query = StatDaily::where('ad_id', $id);

        // Date range filter
        $dateFrom = $request->query('date_from', now()->subDays(29)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));
        $query->whereBetween('date', [$dateFrom, $dateTo]);

        // Device filter
        $deviceFilter = $request->query('device', 'all');
        if ($deviceFilter !== 'all') {
            $query->where('device_type', $deviceFilter);
        }

        // Country filter
        $countryFilter = $request->query('country', 'all');
        if ($countryFilter !== 'all') {
            $query->where('country_code', $countryFilter);
        }

        $stats = $query->orderBy('date', 'desc')->get();

        // Aggregate totals
        $totals = [
            'impressions' => $stats->sum('impressions'),
            'unique_impressions' => $stats->sum('unique_impressions'),
            'clicks' => $stats->sum('clicks'),
            'unique_clicks' => $stats->sum('unique_clicks'),
            'conversions' => $stats->sum('conversions'),
            'spend' => $stats->sum('revenue'),
        ];
        $totals['ctr'] = $totals['impressions'] > 0 ? round(($totals['clicks'] / $totals['impressions']) * 100, 2) : 0;
        $totals['ecpm'] = $totals['impressions'] > 0 ? round(($totals['spend'] / $totals['impressions']) * 1000, 2) : 0;

        // Group by date for the table
        $dailyStats = $stats->groupBy(fn ($s) => $s->date->format('Y-m-d'))->map(function ($dayRows, $date) {
            $impressions = $dayRows->sum('impressions');
            $clicks = $dayRows->sum('clicks');
            $spend = $dayRows->sum('revenue');
            return [
                'date' => $date,
                'impressions' => $impressions,
                'unique_impressions' => $dayRows->sum('unique_impressions'),
                'clicks' => $clicks,
                'unique_clicks' => $dayRows->sum('unique_clicks'),
                'conversions' => $dayRows->sum('conversions'),
                'spend' => round($spend, 2),
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
                'ecpm' => $impressions > 0 ? round(($spend / $impressions) * 1000, 2) : 0,
            ];
        })->values()->toArray();

        // Pagination
        $perPage = 25;
        $currentPage = max(1, (int) $request->query('page', 1));
        $totalRows = count($dailyStats);
        $totalPages = max(1, ceil($totalRows / $perPage));
        $currentPage = min($currentPage, $totalPages);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedStats = array_slice($dailyStats, $offset, $perPage);

        $pagination = [
            'current_page' => $currentPage,
            'total' => $totalRows,
            'total_pages' => $totalPages,
            'from' => $totalRows > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $totalRows),
        ];

        // Available countries for filter
        $availableCountries = StatDaily::where('ad_id', $id)
            ->select('country_code')
            ->distinct()
            ->pluck('country_code')
            ->sort()
            ->toArray();

        $creative = $ad->primaryCreative;

        $adData = [
            'id' => $ad->id,
            'name' => $ad->name,
            'campaign_name' => $ad->campaign ? $ad->campaign->name : '—',
            'ad_type' => $ad->ad_type,
            'status' => $ad->status,
            'size' => $creative && $creative->width ? $creative->width . 'x' . $creative->height : '—',
        ];

        return view('admin.adformats.reports', [
            'ad' => $adData,
            'totals' => $totals,
            'dailyStats' => $paginatedStats,
            'pagination' => $pagination,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'deviceFilter' => $deviceFilter,
            'countryFilter' => $countryFilter,
            'availableCountries' => $availableCountries,
        ]);
    }

    /**
     * Export reports data for a specific ad creative.
     */
    public function exportReports(Request $request, int $id)
    {
        $ad = Ad::find($id);
        if (!$ad) {
            return redirect()->route('admin.adformats')->with('error', 'Creative not found.');
        }

        $query = StatDaily::where('ad_id', $id);

        $dateFrom = $request->query('date_from', now()->subDays(29)->format('Y-m-d'));
        $dateTo = $request->query('date_to', now()->format('Y-m-d'));
        $query->whereBetween('date', [$dateFrom, $dateTo]);

        $deviceFilter = $request->query('device', 'all');
        if ($deviceFilter !== 'all') {
            $query->where('device_type', $deviceFilter);
        }

        $countryFilter = $request->query('country', 'all');
        if ($countryFilter !== 'all') {
            $query->where('country_code', $countryFilter);
        }

        $stats = $query->orderBy('date', 'desc')->get();

        $dailyStats = $stats->groupBy(fn ($s) => $s->date->format('Y-m-d'))->map(function ($dayRows, $date) {
            $impressions = $dayRows->sum('impressions');
            $clicks = $dayRows->sum('clicks');
            $spend = $dayRows->sum('revenue');
            return [
                'date' => $date,
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $dayRows->sum('conversions'),
                'spend' => round($spend, 2),
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0,
                'ecpm' => $impressions > 0 ? round(($spend / $impressions) * 1000, 2) : 0,
            ];
        })->values();

        $format = $request->query('format', 'csv');

        if ($format === 'csv') {
            $csv = "Date,Impressions,Clicks,Conversions,Spend,CTR (%),eCPM\n";
            foreach ($dailyStats as $row) {
                $csv .= implode(',', [
                    $row['date'],
                    $row['impressions'],
                    $row['clicks'],
                    $row['conversions'],
                    '$' . number_format($row['spend'], 2),
                    $row['ctr'] . '%',
                    '$' . number_format($row['ecpm'], 2),
                ]) . "\n";
            }

            $filename = 'reports-' . str_replace(' ', '-', strtolower($ad->name)) . '-' . now()->format('Y-m-d') . '.csv';
            return response($csv, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ]);
        }

        return response()->json([
            'message' => "Export as {$format} will be implemented",
            'count' => $dailyStats->count(),
        ]);
    }

    /**
     * Export creatives data.
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');

        $ads = Ad::with(['campaign', 'primaryCreative'])
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'csv') {
            $csvData = "ID,Creative Name,Campaign Name,Creative Type,Image Type,Size,Destination URL,Status,Created At\n";
            foreach ($ads as $ad) {
                $creative = $ad->primaryCreative;
                $size = $creative && $creative->width ? $creative->width . 'x' . $creative->height : '—';
                $fileType = $creative ? $creative->file_type : '—';
                $csvData .= implode(',', [
                    $ad->id,
                    '"' . str_replace('"', '""', $ad->name) . '"',
                    '"' . str_replace('"', '""', $ad->campaign ? $ad->campaign->name : '—') . '"',
                    $ad->ad_type,
                    $fileType,
                    $size,
                    '"' . str_replace('"', '""', $ad->destination_url) . '"',
                    $ad->status,
                    $ad->created_at?->format('Y-m-d H:i'),
                ]) . "\n";
            }

            return response($csvData, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="creatives-export-' . now()->format('Y-m-d') . '.csv"',
            ]);
        }

        // For other formats, return JSON for now
        return response()->json([
            'message' => "Export as {$format} will be implemented",
            'count' => $ads->count(),
        ]);
    }

    /**
     * Record an impression or click in the daily stats table.
     */
    private function trackStat(Ad $ad, string $type = 'impression'): void
    {
        $today = now()->toDateString();
        $request = request();

        // Detect device type from User-Agent
        $ua = strtolower($request->userAgent() ?? '');
        $deviceType = 'desktop';
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
            $deviceType = str_contains($ua, 'tablet') || str_contains($ua, 'ipad') ? 'tablet' : 'mobile';
        } elseif (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            $deviceType = 'tablet';
        }

        // Detect country from IP
        $countryCode = $this->detectCountry($request);

        // Use session to detect unique vs repeat (graceful if no session)
        $sessionKey = "aq_tracked_{$type}_{$ad->id}_{$today}";
        try {
            $isUnique = !$request->session()->has($sessionKey);
        } catch (\Exception $e) {
            $isUnique = true;
        }

        // Upsert: find existing row or create new one (grouped by date + ad + device + country)
        $rowQuery = StatDaily::where('date', $today)
            ->where('ad_id', $ad->id)
            ->where('device_type', $deviceType);

        if ($countryCode) {
            $rowQuery->where('country_code', $countryCode);
        } else {
            $rowQuery->whereNull('country_code');
        }

        $row = $rowQuery->whereNull('zone_id')->first();

        if (!$row) {
            $row = StatDaily::create([
                'date' => $today,
                'ad_id' => $ad->id,
                'campaign_id' => $ad->campaign_id,
                'device_type' => $deviceType,
                'country_code' => $countryCode,
                'zone_id' => null,
                'impressions' => 0,
                'unique_impressions' => 0,
                'clicks' => 0,
                'unique_clicks' => 0,
                'conversions' => 0,
                'viewable_impressions' => 0,
                'revenue' => 0,
                'publisher_earnings' => 0,
            ]);
        }

        // Get campaign pricing info for revenue calculation
        $campaign = $ad->campaign;
        $bidAmount = (float) ($campaign->bid_amount ?? 0);
        $pricingModel = $campaign->campaign_type ?? 'cpm';

        // Country bid override: if country_bids has a custom bid for this country, use it
        if ($countryCode && !empty($campaign->country_bids) && is_array($campaign->country_bids)) {
            foreach ($campaign->country_bids as $cb) {
                $cbCode = $cb['code'] ?? $cb['country'] ?? null;
                if ($cbCode && strtoupper($cbCode) === strtoupper($countryCode) && isset($cb['bid'])) {
                    $bidAmount = (float) $cb['bid'];
                    break;
                }
            }
        }

        // Traffic source bid override: match Referer header against configured sources
        $trafficSources = $campaign->traffic_sources;
        if (!empty($trafficSources) && is_array($trafficSources)) {
            $referer = strtolower($request->header('Referer', ''));
            $sourceKeywords = [
                'google ads' => ['google', 'googleads', 'googlesyndication', 'doubleclick'],
                'facebook ads' => ['facebook', 'fb.com', 'fbcdn'],
                'taboola' => ['taboola'],
                'outbrain' => ['outbrain'],
                'direct traffic' => [], // matches when referer is empty
                'bing ads' => ['bing', 'msn'],
                'yahoo ads' => ['yahoo'],
                'tiktok ads' => ['tiktok', 'bytedance'],
                'twitter ads' => ['twitter', 'x.com', 't.co'],
                'linkedin ads' => ['linkedin'],
                'reddit ads' => ['reddit'],
                'pinterest ads' => ['pinterest'],
                'snapchat ads' => ['snapchat'],
                'amazon ads' => ['amazon'],
                'propellerads' => ['propellerads'],
                'adsterra' => ['adsterra'],
                'exoclick' => ['exoclick'],
                'trafficjunky' => ['trafficjunky'],
                'admaven' => ['admaven'],
                'clickadu' => ['clickadu'],
            ];

            foreach ($trafficSources as $ts) {
                if (($ts['status'] ?? 'active') !== 'active') {
                    continue;
                }
                $sourceName = strtolower($ts['name'] ?? '');
                $keywords = $sourceKeywords[$sourceName] ?? [str_replace(' ', '', $sourceName)];

                $matched = false;
                if ($sourceName === 'direct traffic') {
                    $matched = empty($referer);
                } else {
                    foreach ($keywords as $kw) {
                        if ($kw && str_contains($referer, $kw)) {
                            $matched = true;
                            break;
                        }
                    }
                }

                if ($matched && isset($ts['bid'])) {
                    $bidAmount = (float) $ts['bid'];
                    break;
                }
            }
        }

        if ($type === 'impression') {
            $row->increment('impressions');
            if ($isUnique) {
                $row->increment('unique_impressions');
            }
            // CPM: revenue per 1000 impressions
            if ($pricingModel === 'cpm' && $bidAmount > 0) {
                $row->increment('revenue', round($bidAmount / 1000, 4));
            }
        } elseif ($type === 'click') {
            $row->increment('clicks');
            if ($isUnique) {
                $row->increment('unique_clicks');
            }
            // CPC: revenue per click
            if ($pricingModel === 'cpc' && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        } elseif ($type === 'view') {
            $row->increment('viewable_impressions');
            // CPV / CPV_CTW: revenue per view
            if (in_array($pricingModel, ['cpv', 'cpv_ctw']) && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        } elseif ($type === 'adblock') {
            $row->increment('adblock_detected');
        } elseif ($type === 'conversion') {
            $row->increment('conversions');
            // CPA: revenue per conversion
            if ($pricingModel === 'cpa' && $bidAmount > 0) {
                $row->increment('revenue', $bidAmount);
            }
        }

        // Recalculate CTR and eCPM
        $row->refresh();
        if ($row->impressions > 0) {
            $row->ctr = round(($row->clicks / $row->impressions) * 100, 4);
            $row->ecpm = $row->revenue > 0 ? round(($row->revenue / $row->impressions) * 1000, 4) : 0;
            $row->save();
        }

        // Mark as seen in session
        if ($isUnique) {
            try {
                $request->session()->put($sessionKey, true);
            } catch (\Exception $e) {
                // No session available (e.g., API context)
            }
        }
    }

    /**
     * Generate tracking JavaScript for view and adblock detection.
     */
    private function trackingScript(int $adId, ?string $countryOverride = null): string
    {
        $countrySuffix = $countryOverride ? '&country=' . urlencode($countryOverride) : '';
        $viewUrl = route('ad.view', $adId);
        $adblockUrl = route('ad.adblock', $adId);

        return <<<SCRIPT
<script>
(function(){
  var vFired=false;
  var obs=new IntersectionObserver(function(entries){
    if(entries[0].isIntersecting&&!vFired){
      setTimeout(function(){
        if(!vFired){vFired=true;new Image().src='{$viewUrl}?_='+Date.now()+'{$countrySuffix}';}
      },1000);
    }
  },{threshold:0.5});
  obs.observe(document.body);
})();
setTimeout(function(){
  var el=document.querySelector('#aq-ad,img,a');
  if(!el||el.offsetHeight===0||getComputedStyle(el).display==='none'){
    new Image().src='{$adblockUrl}?_='+Date.now()+'{$countrySuffix}';
  }
},2000);
</script>
SCRIPT;
    }

    /**
     * Helper: Return HTML with headers that allow cross-origin iframe embedding.
     */
    private function adResponse(string $html, int $status = 200)
    {
        return response($html, $status)
            ->header('Content-Type', 'text/html')
            ->header('X-Frame-Options', 'ALLOWALL')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Content-Security-Policy', 'frame-ancestors *');
    }

    /**
     * Public: Serve an ad creative (HTML snippet for embedding).
     */
    public function serve(int $id)
    {
        $ad = Ad::with(['campaign', 'primaryCreative'])->find($id);

        $debug = request()->query('debug') === '1';

        if (!$ad || $ad->is_deleted || in_array($ad->status, ['paused', 'archived'])) {
            if ($debug) return $this->adResponse('<pre>BLOCKED: ad not available (missing, deleted, paused, or archived). Ad status: ' . ($ad->status ?? 'not found') . '</pre>');
            return $this->adResponse('<!-- ad not available -->', 204);
        }

        // ── Targeting enforcement ──
        $campaign = $ad->campaign;
        if ($campaign) {
            $request = request();

            // 1. Campaign status check
            if ($campaign->status !== 'active') {
                if ($debug) return $this->adResponse('<pre>BLOCKED: campaign not active. Campaign status: ' . $campaign->status . '</pre>');
                return $this->adResponse('<!-- campaign not active -->', 204);
            }

            // 2. Schedule / date range check
            if ($campaign->start_date && now()->lt($campaign->start_date)) {
                if ($debug) return $this->adResponse('<pre>BLOCKED: campaign not started yet. Start date: ' . $campaign->start_date . ', Now: ' . now() . '</pre>');
                return $this->adResponse('<!-- campaign not started -->', 204);
            }
            if ($campaign->end_date && now()->gt($campaign->end_date)) {
                if ($debug) return $this->adResponse('<pre>BLOCKED: campaign ended. End date: ' . $campaign->end_date . ', Now: ' . now() . '</pre>');
                return $this->adResponse('<!-- campaign ended -->', 204);
            }

            // 3. Budget check
            if ($campaign->total_budget > 0 && $campaign->remaining_budget <= 0) {
                if ($debug) return $this->adResponse('<pre>BLOCKED: budget exhausted. Total: ' . $campaign->total_budget . ', Remaining: ' . $campaign->remaining_budget . '</pre>');
                return $this->adResponse('<!-- budget exhausted -->', 204);
            }

            // 4. Device targeting
            $ua = strtolower($request->userAgent() ?? '');
            $visitorDevice = 'desktop';
            if (str_contains($ua, 'mobile') || str_contains($ua, 'android')) {
                $visitorDevice = (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) ? 'tablet' : 'mobile';
            } elseif (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
                $visitorDevice = 'tablet';
            }

            $targetDevices = $campaign->targeting_device;
            if (!empty($targetDevices) && is_array($targetDevices)) {
                // Check if any targeted device name matches the visitor device type
                $deviceMatched = false;
                foreach ($targetDevices as $dev) {
                    $devLower = strtolower($dev);
                    if ($visitorDevice === 'mobile' && (str_contains($devLower, 'iphone') || str_contains($devLower, 'samsung') || str_contains($devLower, 'pixel') || str_contains($devLower, 'huawei') || str_contains($devLower, 'xiaomi') || str_contains($devLower, 'oneplus') || str_contains($devLower, 'oppo') || str_contains($devLower, 'vivo') || str_contains($devLower, 'realme') || str_contains($devLower, 'nokia'))) {
                        $deviceMatched = true;
                        break;
                    }
                    if ($visitorDevice === 'desktop' && (str_contains($devLower, 'windows') || str_contains($devLower, 'macbook') || str_contains($devLower, 'imac') || str_contains($devLower, 'linux') || str_contains($devLower, 'chromebook'))) {
                        $deviceMatched = true;
                        break;
                    }
                    if ($visitorDevice === 'tablet' && (str_contains($devLower, 'ipad') || str_contains($devLower, 'tab') || str_contains($devLower, 'fire') || str_contains($devLower, 'surface'))) {
                        $deviceMatched = true;
                        break;
                    }
                }
                if (!$deviceMatched) {
                    if ($debug) return $this->adResponse('<pre>BLOCKED: device not targeted. Visitor: ' . $visitorDevice . ', Targeted devices: ' . json_encode($targetDevices) . '</pre>');
                    return $this->adResponse('<!-- device not targeted -->', 204);
                }
            }

            // 5. Geo / Country targeting (uses IP-based detection)
            $targetGeo = $campaign->targeting_geo;
            if (!empty($targetGeo) && is_array($targetGeo)) {
                $visitorCountry = $this->detectCountry($request);
                if ($visitorCountry && !in_array($visitorCountry, $targetGeo)) {
                    if ($debug) return $this->adResponse('<pre>BLOCKED: geo not targeted. Visitor country: ' . $visitorCountry . ', Targeted countries: ' . json_encode($targetGeo) . '</pre>');
                    return $this->adResponse('<!-- geo not targeted -->', 204);
                }
            }

            // 6. Region targeting
            $targetRegion = $campaign->targeting_region;
            if (!empty($targetRegion) && is_array($targetRegion) && !in_array('all', $targetRegion)) {
                $visitorCountry = $visitorCountry ?? $this->detectCountry($request);
                $visitorRegion = $this->countryToRegion($visitorCountry);
                if ($visitorRegion && !in_array($visitorRegion, $targetRegion)) {
                    if ($debug) return $this->adResponse('<pre>BLOCKED: region not targeted. Visitor country: ' . $visitorCountry . ', Region: ' . $visitorRegion . ', Targeted regions: ' . json_encode($targetRegion) . '</pre>');
                    return $this->adResponse('<!-- region not targeted -->', 204);
                }
            }

            // 7. Dayparting / weekly schedule
            $schedule = $campaign->targeting_schedule;
            if (!empty($schedule) && is_array($schedule)) {
                $currentDay = strtolower(now()->format('l')); // e.g. "monday"
                $currentHour = (string) now()->hour; // e.g. "14"
                if (isset($schedule[$currentDay]) && is_array($schedule[$currentDay])) {
                    if (!in_array($currentHour, $schedule[$currentDay])) {
                        if ($debug) return $this->adResponse('<pre>BLOCKED: outside schedule. Day: ' . $currentDay . ', Hour: ' . $currentHour . ', Allowed hours: ' . json_encode($schedule[$currentDay]) . '</pre>');
                        return $this->adResponse('<!-- outside schedule -->', 204);
                    }
                } else {
                    // Day not in schedule at all — blocked
                    if ($debug) return $this->adResponse('<pre>BLOCKED: outside schedule. Day: ' . $currentDay . ' not in schedule. Schedule days: ' . json_encode(array_keys($schedule)) . '</pre>');
                    return $this->adResponse('<!-- outside schedule -->', 204);
                }
            }

            // 8. Frequency cap (per user per day via session)
            $freqCap = $campaign->frequency_cap;
            if ($freqCap && $freqCap > 0) {
                $freqKey = "aq_freq_{$campaign->id}_{$ad->id}_" . now()->toDateString();
                try {
                    $count = (int) $request->session()->get($freqKey, 0);
                    if ($count >= $freqCap) {
                        if ($debug) return $this->adResponse('<pre>BLOCKED: frequency cap reached. Cap: ' . $freqCap . ', Current count: ' . $count . '</pre>');
                        return $this->adResponse('<!-- frequency cap reached -->', 204);
                    }
                    $request->session()->put($freqKey, $count + 1);
                } catch (\Exception $e) {
                    // No session — skip cap
                }
            }
        }

        // Track impression
        $this->trackStat($ad, 'impression');

        $creative = $ad->primaryCreative;
        // Carry ?country= override through to click and tracking URLs (for local testing)
        $countryParam = request()->query('country');
        $clickUrl = route('ad.click', $id) . ($countryParam ? '?country=' . urlencode($countryParam) : '');
        $width = $creative->width ?? null;
        $height = $creative->height ?? null;
        $tracking = $this->trackingScript($id, $countryParam);

        // Popunder / interstitial / direct link — URL-only ads
        if (in_array($ad->ad_type, ['html', 'rich_media']) && !$creative?->file_path) {
            $name = e($ad->name);
            $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title>
<style>*{margin:0;padding:0}body{cursor:pointer}</style>
</head><body>
<div id="aq-ad" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:sans-serif;font-size:13px;color:#666;">
    <span>{$name}</span>
</div>
<script>
document.body.addEventListener('click', function() {
    window.open('{$clickUrl}', '_blank');
});
</script>
{$tracking}
</body></html>
HTML;
            return $this->adResponse($html);
        }

        // Image ad
        if ($creative && ($creative->file_type === 'image' || $creative->file_type === 'gif')) {
            $imgUrl = asset($creative->file_path);
            $alt = e($ad->name);
            $w = $width ? "width=\"{$width}\"" : '';
            $h = $height ? "height=\"{$height}\"" : '';
            $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title><style>*{margin:0;padding:0}a{display:block;line-height:0}</style></head>
<body>
<a href="{$clickUrl}" target="_blank" rel="noopener"><img src="{$imgUrl}" alt="{$alt}" {$w} {$h} style="max-width:100%;border:0;"></a>
{$tracking}
</body></html>
HTML;
            return $this->adResponse($html);
        }

        // HTML5 creative with file
        if ($creative && $creative->file_path && $creative->file_type === 'html5') {
            return redirect(asset($creative->file_path));
        }

        // Fallback
        $name = e($ad->name);
        $html = <<<HTML
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Ad</title><style>*{margin:0;padding:0}</style></head>
<body>
<a href="{$clickUrl}" target="_blank" rel="noopener" style="display:block;width:100%;height:100%;text-decoration:none;font-family:sans-serif;font-size:13px;color:#666;display:flex;align-items:center;justify-content:center;">
    <span>{$name}</span>
</a>
{$tracking}
</body></html>
HTML;
        return $this->adResponse($html);
    }

    /**
     * Public: Track viewable impression (1x1 GIF beacon).
     */
    public function view(int $id)
    {
        $ad = Ad::find($id);

        if ($ad && !$ad->is_deleted) {
            $this->trackStat($ad, 'view');
        }

        return $this->pixelResponse();
    }

    /**
     * Public: Track adblock detection (1x1 GIF beacon).
     */
    public function adblock(int $id)
    {
        $ad = Ad::find($id);

        if ($ad && !$ad->is_deleted) {
            $this->trackStat($ad, 'adblock');
        }

        return $this->pixelResponse();
    }

    /**
     * Public: Track conversion (1x1 GIF beacon).
     */
    public function conversion(int $id)
    {
        $ad = Ad::find($id);

        if ($ad && !$ad->is_deleted) {
            $this->trackStat($ad, 'conversion');
        }

        return $this->pixelResponse();
    }

    /**
     * Return a 1x1 transparent GIF with no-cache headers.
     */
    private function pixelResponse()
    {
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Access-Control-Allow-Origin', '*');
    }

    /**
     * Public: Track click and redirect to destination URL.
     */
    public function click(int $id)
    {
        $ad = Ad::find($id);

        if (!$ad || $ad->is_deleted) {
            return redirect('/');
        }

        // Track click
        $this->trackStat($ad, 'click');

        return redirect($ad->destination_url);
    }

    /**
     * Detect the visitor's 2-letter country code from their IP address.
     * Uses ip-api.com free tier (no API key needed, 45 req/min limit).
     * Falls back to null if detection fails.
     */
    private function detectCountry(Request $request): ?string
    {
        // Allow manual override via query param for local testing: ?country=XK
        $override = $request->query('country');
        if ($override && preg_match('/^[A-Za-z]{2}$/', $override)) {
            return strtoupper($override);
        }

        $ip = $request->ip();

        // Local/private IPs can't be geolocated
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }

        // Cache the result in session to avoid repeated API calls
        $cacheKey = "aq_geo_{$ip}";
        try {
            $cached = $request->session()->get($cacheKey);
            if ($cached !== null) {
                return $cached ?: null; // empty string means "unknown"
            }
        } catch (\Exception $e) {
            // No session
        }

        try {
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode");
            if ($response) {
                $data = json_decode($response, true);
                $countryCode = $data['countryCode'] ?? null;

                // Cache in session
                try {
                    $request->session()->put($cacheKey, $countryCode ?? '');
                } catch (\Exception $e) {
                    // No session
                }

                return $countryCode;
            }
        } catch (\Exception $e) {
            // API call failed
        }

        return null;
    }

    /**
     * Map a 2-letter country code to a region/continent slug.
     * Returns the region slug (e.g. 'europe', 'asia', 'north_america') or null.
     */
    private function countryToRegion(?string $countryCode): ?string
    {
        if (!$countryCode) {
            return null;
        }

        $regionMap = [
            // Europe
            'europe' => ['AL','AD','AT','BY','BE','BA','BG','HR','CY','CZ','DK','EE','FI','FR','DE','GR','HU','IS','IE','IT','XK','LV','LI','LT','LU','MK','MT','MD','MC','ME','NL','NO','PL','PT','RO','RU','SM','RS','SK','SI','ES','SE','CH','UA','GB','VA'],
            // North America
            'north_america' => ['US','CA','MX','GT','BZ','HN','SV','NI','CR','PA','CU','JM','HT','DO','TT','BS','BB','AG','DM','GD','KN','LC','VC'],
            // South America
            'south_america' => ['AR','BO','BR','CL','CO','EC','GY','PY','PE','SR','UY','VE'],
            // Asia
            'asia' => ['AF','AM','AZ','BH','BD','BT','BN','KH','CN','GE','IN','ID','IR','IQ','IL','JP','JO','KZ','KW','KG','LA','LB','MY','MV','MN','MM','NP','KP','OM','PK','PS','PH','QA','SA','SG','KR','LK','SY','TW','TJ','TH','TL','TR','TM','AE','UZ','VN','YE'],
            // Africa
            'africa' => ['DZ','AO','BJ','BW','BF','BI','CV','CM','CF','TD','KM','CG','CD','CI','DJ','EG','GQ','ER','SZ','ET','GA','GM','GH','GN','GW','KE','LS','LR','LY','MG','MW','ML','MR','MU','MA','MZ','NA','NE','NG','RW','ST','SN','SC','SL','SO','ZA','SS','SD','TZ','TG','TN','UG','ZM','ZW'],
            // Oceania
            'oceania' => ['AU','NZ','FJ','PG','WS','SB','TO','VU','KI','MH','FM','NR','PW','TV'],
            // Middle East (overlaps with Asia — add if your region selector uses it separately)
            'middle_east' => ['BH','IR','IQ','IL','JO','KW','LB','OM','PS','QA','SA','SY','AE','YE','TR'],
        ];

        $code = strtoupper($countryCode);

        foreach ($regionMap as $region => $countries) {
            if (in_array($code, $countries)) {
                return $region;
            }
        }

        return null;
    }
}

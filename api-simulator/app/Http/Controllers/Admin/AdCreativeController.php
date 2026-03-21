<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
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

        $query = \App\Models\StatDaily::where('ad_id', $id);

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
        $availableCountries = \App\Models\StatDaily::where('ad_id', $id)
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

        $query = \App\Models\StatDaily::where('ad_id', $id);

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

        if (!$ad || $ad->is_deleted || in_array($ad->status, ['paused', 'archived'])) {
            return $this->adResponse('<!-- ad not available -->', 204);
        }

        $creative = $ad->primaryCreative;
        $clickUrl = route('ad.click', $id);
        $width = $creative->width ?? null;
        $height = $creative->height ?? null;

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
</body></html>
HTML;
        return $this->adResponse($html);
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

        // Here you could log the click to stats table
        // StatDaily::where('ad_id', $id)->where('date', today())->increment('clicks');

        return redirect($ad->destination_url);
    }
}

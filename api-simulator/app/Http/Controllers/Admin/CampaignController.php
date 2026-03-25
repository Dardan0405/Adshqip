<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\Campaign;
use App\Models\CampaignGroup;
use App\Models\PixelTracker;
use App\Models\StatDaily;
use App\Models\User;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display the campaigns listing page for admin.
     */
    public function index(Request $request)
    {
        // Get all campaigns with their relationships and aggregated stats
        $campaigns = Campaign::with(['group', 'advertiser'])
            ->withSum('stats', 'impressions')
            ->withSum('stats', 'clicks')
            ->withSum('stats', 'conversions')
            ->withSum('stats', 'revenue')
            ->withSum('stats', 'viewable_impressions')
            ->withSum('stats', 'adblock_detected')
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Transform campaigns to match the expected array format for the view
        $allCampaigns = $campaigns->map(function ($campaign) {
            $impressions = (int) ($campaign->stats_sum_impressions ?? 0);
            $clicks = (int) ($campaign->stats_sum_clicks ?? 0);
            $conversions = (int) ($campaign->stats_sum_conversions ?? 0);
            $spend = (float) ($campaign->stats_sum_revenue ?? ($campaign->total_budget - $campaign->remaining_budget));
            $views = (int) ($campaign->stats_sum_viewable_impressions ?? 0);
            $adblockDetected = (int) ($campaign->stats_sum_adblock_detected ?? 0);

            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'type' => $campaign->campaign_type,
                'start_date' => $campaign->start_date?->format('Y-m-d'),
                'end_date' => $campaign->end_date?->format('Y-m-d'),
                'model' => strtoupper($campaign->campaign_type),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00,
                'advertiser' => $campaign->advertiser->email ?? 'Unknown',
                'budget' => (float)$campaign->total_budget,
                'spend' => $spend,
                'views' => $views,
                'adblock_detected' => $adblockDetected,
                'group' => $campaign->group ? $campaign->group->name : null,
                'group_id' => $campaign->group_id,
            ];
        })->toArray();

        // Calculate statistics before filtering
        $stats = [
            'total' => count($allCampaigns),
            'active' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'active')),
            'paused' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'paused')),
            'draft' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'draft')),
            'completed' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'completed')),
            'pending_review' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'pending_review')),
            'rejected' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'rejected')),
            'total_budget' => array_sum(array_column($allCampaigns, 'budget')),
            'total_spend' => array_sum(array_column($allCampaigns, 'spend')),
        ];

        // Apply filters
        $statusFilter = $request->query('status', 'all');
        $searchQuery = $request->query('search', '');

        if ($statusFilter !== 'all') {
            $allCampaigns = array_filter($allCampaigns, function ($c) use ($statusFilter) {
                return $c['status'] === $statusFilter;
            });
        }

        if ($searchQuery !== '') {
            $allCampaigns = array_filter($allCampaigns, function ($c) use ($searchQuery) {
                return stripos($c['name'], $searchQuery) !== false ||
                       stripos($c['advertiser'], $searchQuery) !== false;
            });
        }

        // Re-index the array
        $allCampaigns = array_values($allCampaigns);

        // Pagination
        $perPage = 25;
        $currentPage = (int)$request->query('page', 1);
        $totalCampaigns = count($allCampaigns);
        $totalPages = max(1, ceil($totalCampaigns / $perPage));
        $currentPage = max(1, min($currentPage, $totalPages));
        $offset = ($currentPage - 1) * $perPage;
        $paginatedCampaigns = array_slice($allCampaigns, $offset, $perPage);

        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $totalCampaigns,
            'total_pages' => $totalPages,
            'from' => $totalCampaigns > 0 ? $offset + 1 : 0,
            'to' => min($offset + $perPage, $totalCampaigns),
        ];

        // Get campaign groups and pixel trackers
        $campaignGroups = CampaignGroup::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'status' => $group->status,
                    'campaign_count' => $group->campaigns()->where('is_deleted', false)->count(),
                ];
            })
            ->toArray();

        $pixelTrackers = PixelTracker::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'name' => $pixel->name,
                    'type' => $pixel->type,
                    'code' => $pixel->pixel_code,
                    'status' => $pixel->is_active ? 'active' : 'inactive',
                ];
            })
            ->toArray();

        return view('admin.campaigns.index', [
            'campaigns' => $paginatedCampaigns,
            'campaignGroups' => $campaignGroups,
            'groups' => $campaignGroups,
            'pixels' => $pixelTrackers,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
            'search' => $searchQuery,
            'pagination' => $pagination,
        ]);
    }

    /**
     * Show the form for creating a new campaign.
     */
    public function create()
    {
        $campaignGroups = CampaignGroup::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'status' => $group->status,
                ];
            })
            ->toArray();

        $pixelTrackers = PixelTracker::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'name' => $pixel->name,
                    'type' => $pixel->type,
                    'code' => $pixel->pixel_code,
                    'advertiser' => $pixel->advertiser->email ?? 'Unknown',
                ];
            })
            ->toArray();

        $campaignTypes = [
            'cpm' => 'CPM (Cost Per Mille)',
            'cpc' => 'CPC (Cost Per Click)',
            'cpa' => 'CPA (Cost Per Action)',
            'cpv' => 'CPV (Cost Per View)',
            'cpv_ctw' => 'CPV Click-to-Watch',
        ];

        $marketingObjectives = [
            'brand_awareness' => 'Brand Awareness',
            'reach' => 'Reach',
            'traffic' => 'Traffic',
            'engagement' => 'Engagement',
            'app_installs' => 'App Installs',
            'video_views' => 'Video Views',
            'lead_generation' => 'Lead Generation',
            'conversions' => 'Conversions',
            'catalog_sales' => 'Catalog Sales',
            'store_visits' => 'Store Visits',
        ];

        // Balkan countries
        $countries = [
            ['code' => 'AL', 'name' => 'Albania'],
            ['code' => 'BA', 'name' => 'Bosnia and Herzegovina'],
            ['code' => 'BG', 'name' => 'Bulgaria'],
            ['code' => 'HR', 'name' => 'Croatia'],
            ['code' => 'GR', 'name' => 'Greece'],
            ['code' => 'XK', 'name' => 'Kosovo'],
            ['code' => 'ME', 'name' => 'Montenegro'],
            ['code' => 'MK', 'name' => 'North Macedonia'],
            ['code' => 'RO', 'name' => 'Romania'],
            ['code' => 'RS', 'name' => 'Serbia'],
            ['code' => 'SI', 'name' => 'Slovenia'],
            ['code' => 'TR', 'name' => 'Turkey'],
        ];

        // Traffic sources (demo data)
        $trafficSources = [
            ['id' => 1, 'name' => 'Google Ads'],
            ['id' => 2, 'name' => 'Facebook Ads'],
            ['id' => 3, 'name' => 'Taboola'],
            ['id' => 4, 'name' => 'Outbrain'],
            ['id' => 5, 'name' => 'Direct Traffic'],
            ['id' => 6, 'name' => 'Organic Search'],
        ];

        $pricingModels = ['CPM', 'CPC', 'CPA', 'CPV'];

        // Advertisers (demo data)
        $advertisers = [
            ['id' => 1, 'name' => 'Demo Advertiser', 'email' => 'demo@adshqip.com'],
        ];

        // Categories (demo data)
        $categories = [
            ['id' => 1, 'name' => 'E-commerce'],
            ['id' => 2, 'name' => 'Lead Generation'],
            ['id' => 3, 'name' => 'Brand Awareness'],
            ['id' => 4, 'name' => 'App Install'],
        ];

        // Ad formats
        $adFormats = [
            'display_web' => [
                'label' => 'Display Web',
                'sizes' => [
                    '300x250' => '300x250 (Medium Rectangle)',
                    '728x90' => '728x90 (Leaderboard)',
                    '160x600' => '160x600 (Wide Skyscraper)',
                    '300x600' => '300x600 (Half Page)',
                    '320x50' => '320x50 (Mobile Banner)',
                    '970x250' => '970x250 (Billboard)',
                ],
            ],
            'special_web' => [
                'label' => 'Special Web',
                'sizes' => [
                    'text' => 'Text Ad',
                    'native' => 'Native Ad',
                    'interstitial' => 'Interstitial',
                    'popunder' => 'Popunder',
                    'direct_link' => 'Direct Link',
                    'in_page_push' => 'In-Page Push',
                    'social_bar' => 'Social Bar',
                ],
            ],
            'display_video' => [
                'label' => 'Display Video',
                'sizes' => [
                    'instream' => 'In-Stream Video',
                    'outstream' => 'Out-Stream Video',
                    'rewarded' => 'Rewarded Video',
                ],
            ],
        ];

        return view('admin.campaigns.create', [
            'campaignGroups' => $campaignGroups,
            'groups' => $campaignGroups,
            'pixelTrackers' => $pixelTrackers,
            'pixels' => $pixelTrackers,
            'campaignTypes' => $campaignTypes,
            'marketingObjectives' => $marketingObjectives,
            'countries' => $countries,
            'trafficSources' => $trafficSources,
            'pricingModels' => $pricingModels,
            'advertisers' => $advertisers,
            'categories' => $categories,
            'adFormats' => $adFormats,
        ]);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'campaign_type' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'group_id' => 'nullable|exists:aq_campaign_groups,id',
            'status' => 'required|in:draft,pending_review,active,paused',
            'bid_amount' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'frequency_cap' => 'nullable|integer|min:1',
            'targeting_geo' => 'nullable|json',
            'targeting_device' => 'nullable|json',
            'targeting_region' => 'nullable|array',
            'targeting_region.*' => 'string',
            'traffic_sources' => 'nullable|array',
            'country_bids' => 'nullable|array',
            'ad_formats' => 'nullable|array',
            'ad_formats.*.ad_name' => 'nullable|string',
            'ad_formats.*.ad_url' => 'nullable|string',
            'ad_formats.*.ad_type' => 'nullable|string',
            'ad_formats.*.format' => 'nullable|string',
            'ad_formats.*.format_label' => 'nullable|string',
            'ad_formats.*.content_type' => 'nullable|string',
            'ad_formats.*.filename' => 'nullable|string',
            'ad_formats.*.dimension' => 'nullable|string',
            'ad_formats.*.file_path' => 'nullable|string',
            'ad_formats.*.file_size' => 'nullable|integer',
            'ad_formats.*.video_url' => 'nullable|string',
            'ad_formats.*.text_title' => 'nullable|string',
            'ad_formats.*.text_description' => 'nullable|string',
            'ad_formats.*.text_body' => 'nullable|string',
            'ad_formats.*.text_cta' => 'nullable|string',
            'ad_formats.*.native_headline' => 'nullable|string',
            'ad_formats.*.native_brand' => 'nullable|string',
            'ad_formats.*.native_body' => 'nullable|string',
            'ad_formats.*.native_cta' => 'nullable|string',
            'ad_formats.*.interstitial_headline' => 'nullable|string',
            'ad_formats.*.interstitial_body' => 'nullable|string',
            'ad_formats.*.interstitial_cta' => 'nullable|string',
            'ad_formats.*.popunder_headline' => 'nullable|string',
            'ad_formats.*.popunder_body' => 'nullable|string',
            'ad_formats.*.ipp_headline' => 'nullable|string',
            'ad_formats.*.ipp_body' => 'nullable|string',
            'ad_formats.*.video_headline' => 'nullable|string',
            'ad_formats.*.video_cta' => 'nullable|string',
            'ad_formats.*.reward_amount' => 'nullable|string',
            'ad_formats.*.reward_type' => 'nullable|string',
            'pixel_tracker_id' => 'nullable|exists:aq_pixel_trackers,id',
        ]);

        // Set advertiser_id to logged-in user (or default to 1 for demo)
        $validated['advertiser_id'] = auth()->id() ?? 1;

        // Set remaining budget equal to total budget for new campaigns
        $validated['remaining_budget'] = $validated['total_budget'] ?? 0;

        // Parse JSON fields if they're strings
        if (isset($validated['targeting_geo']) && is_string($validated['targeting_geo'])) {
            $validated['targeting_geo'] = json_decode($validated['targeting_geo'], true);
        }
        if (isset($validated['targeting_device']) && is_string($validated['targeting_device'])) {
            $validated['targeting_device'] = json_decode($validated['targeting_device'], true);
        }

        // Convert traffic_sources indexed array to clean array
        if (isset($validated['traffic_sources'])) {
            $validated['traffic_sources'] = array_values($validated['traffic_sources']);
        }

        // Convert country_bids indexed array to clean array
        if (isset($validated['country_bids'])) {
            $validated['country_bids'] = array_values($validated['country_bids']);
        }

        // Keep original ad_formats keys for file upload matching (don't re-index yet)
        $originalAdFormats = $validated['ad_formats'] ?? null;

        // Convert ad_formats indexed array to clean array for campaign creation
        if (isset($validated['ad_formats'])) {
            $validated['ad_formats'] = array_values($validated['ad_formats']);
        }

        // Handle empty pixel_tracker_id (empty string from select)
        if (empty($validated['pixel_tracker_id'])) {
            $validated['pixel_tracker_id'] = null;
        }

        // Handle dayparting / targeting_schedule
        if ($request->input('weekly_mode') === 'custom' && $request->has('dayparting')) {
            $validated['targeting_schedule'] = $request->input('dayparting');
        } else {
            $validated['targeting_schedule'] = null;
        }

        $campaign = Campaign::create($validated);

        // Create Ad + AdCreative records from ad_formats so they appear on Ad Formats page
        if (!empty($originalAdFormats)) {
            $updatedAdFormats = $this->processAdFormats($campaign, $originalAdFormats, $request);
            $campaign->update(['ad_formats' => $updatedAdFormats]);
        }

        return redirect()
            ->route('admin.campaigns')
            ->with('success', 'Campaign created successfully!');
    }

    /**
     * Store a new campaign group.
     */
    public function storeGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|in:active,paused,archived',
            'color' => 'nullable|string|max:20',
        ]);

        // Set advertiser_id to logged-in user (or default to 1 for demo)
        $validated['advertiser_id'] = auth()->id() ?? 1;

        // Set default status if not provided (database also has default 'active')
        if (!isset($validated['status'])) {
            $validated['status'] = 'active';
        }

        $group = CampaignGroup::create($validated);

        return response()->json([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'status' => $group->status,
                'campaign_count' => 0,
            ],
        ]);
    }

    /**
     * Store a new pixel tracker.
     */
    public function storePixel(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:conversion,pageview,event,custom',
            'advertiser_id' => 'sometimes|integer',
            'status' => 'sometimes|in:active,paused',
        ]);

        // Set advertiser_id to logged-in user or from request (or default to 1 for demo)
        if (!isset($validated['advertiser_id'])) {
            $validated['advertiser_id'] = auth()->id() ?? 1;
        }

        // Generate unique pixel code
        $validated['pixel_code'] = 'PX' . strtoupper(substr(md5(uniqid()), 0, 10));
        $validated['tracking_url'] = 'https://track.adshqip.com/pixel/' . strtolower($validated['type']);

        // Handle is_active from status
        $validated['is_active'] = ($validated['status'] ?? 'active') === 'active';
        unset($validated['status']); // Remove status as it's not a database field

        $pixel = PixelTracker::create($validated);

        // Get advertiser name for display
        $advertiser = User::find($pixel->advertiser_id);
        $advertiserName = $advertiser ? $advertiser->name : 'Demo Advertiser';

        return response()->json([
            'success' => true,
            'pixel' => [
                'id' => $pixel->id,
                'name' => $pixel->name,
                'type' => $pixel->type,
                'code' => $pixel->pixel_code,
                'advertiser' => $advertiserName,
            ],
        ]);
    }

    /**
     * Duplicate a campaign.
     */
    public function duplicate(int $id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        // Create a duplicate
        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Copy)';
        $newCampaign->status = 'draft';
        $newCampaign->save();

        return redirect()
            ->route('admin.campaigns')
            ->with('success', 'Campaign duplicated successfully!');
    }

    /**
     * Move a campaign to a different group.
     */
    public function moveToGroup(Request $request, int $id)
    {
        $validated = $request->validate([
            'group_id' => 'required|exists:aq_campaign_groups,id',
        ]);

        $campaign = Campaign::find($id);

        if (!$campaign) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $campaign->group_id = $validated['group_id'];
        $campaign->save();

        $group = CampaignGroup::find($validated['group_id']);

        return redirect()
            ->route('admin.campaigns')
            ->with('success', 'Campaign moved to "' . $group->name . '" successfully!');
    }

    /**
     * Display the specified campaign.
     */
    public function show(int $id)
    {
        $campaign = Campaign::with(['group', 'advertiser'])
            ->withSum('stats', 'impressions')
            ->withSum('stats', 'clicks')
            ->withSum('stats', 'conversions')
            ->withSum('stats', 'revenue')
            ->find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $impressions = (int) ($campaign->stats_sum_impressions ?? 0);
        $clicks = (int) ($campaign->stats_sum_clicks ?? 0);
        $conversions = (int) ($campaign->stats_sum_conversions ?? 0);
        $spend = (float) ($campaign->stats_sum_revenue ?? ($campaign->total_budget - $campaign->remaining_budget));

        $campaignData = [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'description' => $campaign->description,
            'status' => $campaign->status,
            'type' => $campaign->campaign_type,
            'model' => strtoupper($campaign->campaign_type),
            'marketing_objective' => $campaign->marketing_objective,
            'start_date' => $campaign->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $campaign->end_date?->format('Y-m-d H:i:s'),
            'advertiser' => $campaign->advertiser->email ?? 'Unknown',
            'advertiser_id' => $campaign->advertiser_id,
            'bid_amount' => (float)$campaign->bid_amount,
            'daily_budget' => (float)$campaign->daily_budget,
            'total_budget' => (float)$campaign->total_budget,
            'remaining_budget' => (float)$campaign->remaining_budget,
            'budget' => (float)$campaign->total_budget,
            'currency' => $campaign->currency,
            'frequency_cap' => $campaign->frequency_cap,
            'frequency_cap_period' => $campaign->frequency_cap_period,
            'targeting_geo' => $campaign->targeting_geo,
            'targeting_device' => $campaign->targeting_device,
            'targeting_schedule' => $campaign->targeting_schedule,
            'weight' => $campaign->weight,
            'group' => $campaign->group ? $campaign->group->name : null,
            'group_name' => $campaign->group ? $campaign->group->name : null,
            'group_id' => $campaign->group_id,
            'targeting_region' => $campaign->targeting_region,
            'traffic_sources' => $campaign->traffic_sources,
            'country_bids' => $campaign->country_bids,
            'ad_formats' => $campaign->ad_formats,
            'impressions' => $impressions,
            'clicks' => $clicks,
            'conversions' => $conversions,
            'spend' => $spend,
            'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00,
        ];

        return view('admin.campaigns.show', ['campaign' => $campaignData]);
    }

    /**
     * Show the form for editing the specified campaign.
     */
    public function edit(int $id)
    {
        $campaign = Campaign::with(['group'])->find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $campaignGroups = CampaignGroup::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($group) {
                return [
                    'id' => $group->id,
                    'name' => $group->name,
                    'status' => $group->status,
                ];
            })
            ->toArray();

        $campaignTypes = [
            'cpm' => 'CPM (Cost Per Mille)',
            'cpc' => 'CPC (Cost Per Click)',
            'cpa' => 'CPA (Cost Per Action)',
            'cpv' => 'CPV (Cost Per View)',
            'cpv_ctw' => 'CPV Click-to-Watch',
        ];

        $marketingObjectives = [
            'brand_awareness' => 'Brand Awareness',
            'reach' => 'Reach',
            'traffic' => 'Traffic',
            'engagement' => 'Engagement',
            'app_installs' => 'App Installs',
            'video_views' => 'Video Views',
            'lead_generation' => 'Lead Generation',
            'conversions' => 'Conversions',
            'catalog_sales' => 'Catalog Sales',
            'store_visits' => 'Store Visits',
        ];

        $spend = (float)($campaign->total_budget - $campaign->remaining_budget);

        $campaignData = [
            'id' => $campaign->id,
            'name' => $campaign->name,
            'description' => $campaign->description,
            'status' => $campaign->status,
            'type' => $campaign->campaign_type,
            'marketing_objective' => $campaign->marketing_objective,
            'start_date' => $campaign->start_date?->format('Y-m-d\TH:i'),
            'end_date' => $campaign->end_date?->format('Y-m-d\TH:i'),
            'bid_amount' => (float)$campaign->bid_amount,
            'daily_budget' => (float)$campaign->daily_budget,
            'total_budget' => (float)$campaign->total_budget,
            'remaining_budget' => (float)$campaign->remaining_budget,
            'budget' => (float)$campaign->total_budget,
            'currency' => $campaign->currency,
            'frequency_cap' => $campaign->frequency_cap,
            'targeting_schedule' => $campaign->targeting_schedule,
            'targeting_region' => $campaign->targeting_region,
            'targeting_geo' => $campaign->targeting_geo,
            'targeting_device' => $campaign->targeting_device,
            'traffic_sources' => $campaign->traffic_sources,
            'country_bids' => $campaign->country_bids,
            'ad_formats' => $campaign->ad_formats,
            'weight' => $campaign->weight,
            'group_id' => $campaign->group_id,
            'pixel_tracker_id' => $campaign->pixel_tracker_id,
            'spend' => $spend,
        ];

        // Balkan countries
        $countries = [
            ['code' => 'AL', 'name' => 'Albania'],
            ['code' => 'BA', 'name' => 'Bosnia and Herzegovina'],
            ['code' => 'BG', 'name' => 'Bulgaria'],
            ['code' => 'HR', 'name' => 'Croatia'],
            ['code' => 'GR', 'name' => 'Greece'],
            ['code' => 'XK', 'name' => 'Kosovo'],
            ['code' => 'ME', 'name' => 'Montenegro'],
            ['code' => 'MK', 'name' => 'North Macedonia'],
            ['code' => 'RO', 'name' => 'Romania'],
            ['code' => 'RS', 'name' => 'Serbia'],
            ['code' => 'SI', 'name' => 'Slovenia'],
            ['code' => 'TR', 'name' => 'Turkey'],
        ];

        $trafficSources = [
            ['id' => 1, 'name' => 'Google Ads'],
            ['id' => 2, 'name' => 'Facebook Ads'],
            ['id' => 3, 'name' => 'Taboola'],
            ['id' => 4, 'name' => 'Outbrain'],
            ['id' => 5, 'name' => 'Direct Traffic'],
            ['id' => 6, 'name' => 'Organic Search'],
        ];

        $pricingModels = ['CPM', 'CPC', 'CPA', 'CPV'];

        $pixelTrackers = PixelTracker::where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'name' => $pixel->name,
                    'type' => $pixel->type,
                    'code' => $pixel->pixel_code,
                    'advertiser' => $pixel->advertiser->email ?? 'Unknown',
                ];
            })
            ->toArray();

        $advertisers = [
            ['id' => 1, 'name' => 'Demo Advertiser', 'email' => 'demo@adshqip.com'],
        ];

        $categories = [
            ['id' => 1, 'name' => 'E-commerce'],
            ['id' => 2, 'name' => 'Lead Generation'],
            ['id' => 3, 'name' => 'Brand Awareness'],
            ['id' => 4, 'name' => 'App Install'],
        ];

        // Ad formats
        $adFormats = [
            'display_web' => [
                'label' => 'Display Web',
                'sizes' => [
                    '300x250' => '300x250 (Medium Rectangle)',
                    '728x90' => '728x90 (Leaderboard)',
                    '160x600' => '160x600 (Wide Skyscraper)',
                    '300x600' => '300x600 (Half Page)',
                    '320x50' => '320x50 (Mobile Banner)',
                    '970x250' => '970x250 (Billboard)',
                ],
            ],
            'special_web' => [
                'label' => 'Special Web',
                'sizes' => [
                    'text' => 'Text Ad',
                    'native' => 'Native Ad',
                    'interstitial' => 'Interstitial',
                    'popunder' => 'Popunder',
                    'direct_link' => 'Direct Link',
                    'in_page_push' => 'In-Page Push',
                    'social_bar' => 'Social Bar',
                ],
            ],
            'display_video' => [
                'label' => 'Display Video',
                'sizes' => [
                    'instream' => 'In-Stream Video',
                    'outstream' => 'Out-Stream Video',
                    'rewarded' => 'Rewarded Video',
                ],
            ],
        ];

        return view('admin.campaigns.edit', [
            'campaign' => $campaignData,
            'groups' => $campaignGroups,
            'campaignGroups' => $campaignGroups,
            'campaignTypes' => $campaignTypes,
            'marketingObjectives' => $marketingObjectives,
            'countries' => $countries,
            'trafficSources' => $trafficSources,
            'pricingModels' => $pricingModels,
            'pixelTrackers' => $pixelTrackers,
            'advertisers' => $advertisers,
            'categories' => $categories,
            'adFormats' => $adFormats,
        ]);
    }

    /**
     * Update the specified campaign.
     */
    public function update(Request $request, int $id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'campaign_type' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'group_id' => 'nullable|exists:aq_campaign_groups,id',
            'status' => 'required|in:draft,pending_review,active,paused,completed,rejected',
            'bid_amount' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'frequency_cap' => 'nullable|integer|min:1',
            'weight' => 'nullable|integer|min:1|max:10',
            'targeting_geo' => 'nullable|json',
            'targeting_device' => 'nullable|json',
            'targeting_region' => 'nullable|array',
            'targeting_region.*' => 'string',
            'traffic_sources' => 'nullable|array',
            'country_bids' => 'nullable|array',
            'ad_formats' => 'nullable|array',
            'ad_formats.*.ad_name' => 'nullable|string',
            'ad_formats.*.ad_url' => 'nullable|string',
            'ad_formats.*.ad_type' => 'nullable|string',
            'ad_formats.*.format' => 'nullable|string',
            'ad_formats.*.format_label' => 'nullable|string',
            'ad_formats.*.content_type' => 'nullable|string',
            'ad_formats.*.filename' => 'nullable|string',
            'ad_formats.*.dimension' => 'nullable|string',
            'ad_formats.*.file_path' => 'nullable|string',
            'ad_formats.*.file_size' => 'nullable|integer',
            'ad_formats.*.video_url' => 'nullable|string',
            'ad_formats.*.text_title' => 'nullable|string',
            'ad_formats.*.text_description' => 'nullable|string',
            'ad_formats.*.text_body' => 'nullable|string',
            'ad_formats.*.text_cta' => 'nullable|string',
            'ad_formats.*.native_headline' => 'nullable|string',
            'ad_formats.*.native_brand' => 'nullable|string',
            'ad_formats.*.native_body' => 'nullable|string',
            'ad_formats.*.native_cta' => 'nullable|string',
            'ad_formats.*.interstitial_headline' => 'nullable|string',
            'ad_formats.*.interstitial_body' => 'nullable|string',
            'ad_formats.*.interstitial_cta' => 'nullable|string',
            'ad_formats.*.popunder_headline' => 'nullable|string',
            'ad_formats.*.popunder_body' => 'nullable|string',
            'ad_formats.*.ipp_headline' => 'nullable|string',
            'ad_formats.*.ipp_body' => 'nullable|string',
            'ad_formats.*.video_headline' => 'nullable|string',
            'ad_formats.*.video_cta' => 'nullable|string',
            'ad_formats.*.reward_amount' => 'nullable|string',
            'ad_formats.*.reward_type' => 'nullable|string',
            'pixel_tracker_id' => 'nullable|exists:aq_pixel_trackers,id',
        ]);

        // Convert traffic_sources indexed array to clean array
        if (isset($validated['traffic_sources'])) {
            $validated['traffic_sources'] = array_values($validated['traffic_sources']);
        }

        // Convert country_bids indexed array to clean array
        if (isset($validated['country_bids'])) {
            $validated['country_bids'] = array_values($validated['country_bids']);
        }

        // Keep original ad_formats keys for file upload matching (don't re-index yet)
        $originalAdFormats = $validated['ad_formats'] ?? null;

        // Convert ad_formats indexed array to clean array
        if (isset($validated['ad_formats'])) {
            $validated['ad_formats'] = array_values($validated['ad_formats']);
        }

        // Parse JSON fields if they're strings
        if (isset($validated['targeting_geo']) && is_string($validated['targeting_geo'])) {
            $validated['targeting_geo'] = json_decode($validated['targeting_geo'], true);
        }
        if (isset($validated['targeting_device']) && is_string($validated['targeting_device'])) {
            $validated['targeting_device'] = json_decode($validated['targeting_device'], true);
        }

        // If region/formats/geo/device not sent, set to null (unchecked all)
        if (!$request->has('targeting_region')) {
            $validated['targeting_region'] = null;
        }
        if (!$request->has('targeting_geo')) {
            $validated['targeting_geo'] = null;
        }
        if (!$request->has('targeting_device')) {
            $validated['targeting_device'] = null;
        }
        if (!$request->has('ad_formats')) {
            $validated['ad_formats'] = null;
            $originalAdFormats = null;
        }
        if (!$request->has('traffic_sources')) {
            $validated['traffic_sources'] = null;
        }
        if (!$request->has('country_bids')) {
            $validated['country_bids'] = null;
        }

        // Handle empty pixel_tracker_id (empty string from select)
        if (empty($validated['pixel_tracker_id'])) {
            $validated['pixel_tracker_id'] = null;
        }

        // Handle dayparting / targeting_schedule
        if ($request->input('weekly_mode') === 'custom' && $request->has('dayparting')) {
            $validated['targeting_schedule'] = $request->input('dayparting');
        } else {
            $validated['targeting_schedule'] = null;
        }

        $campaign->update($validated);

        // Sync Ad + AdCreative records from ad_formats
        // Soft-delete existing ads for this campaign, then recreate from ad_formats
        Ad::where('campaign_id', $campaign->id)->update(['is_deleted' => true]);

        if (!empty($originalAdFormats)) {
            $updatedAdFormats = $this->processAdFormats($campaign, $originalAdFormats, $request);
            $campaign->update(['ad_formats' => $updatedAdFormats]);
        }

        return redirect()
            ->route('admin.campaigns.show', $id)
            ->with('success', 'Campaign updated successfully!');
    }

    /**
     * Update campaign status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending_review,active,paused,completed,rejected',
        ]);

        $campaign = Campaign::find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $campaign->status = $validated['status'];
        $campaign->save();

        return redirect()
            ->route('admin.campaigns')
            ->with('success', 'Campaign status updated successfully!');
    }

    /**
     * Soft delete the specified campaign.
     */
    public function destroy(int $id)
    {
        $campaign = Campaign::find($id);

        if (!$campaign) {
            return redirect()
                ->route('admin.campaigns')
                ->with('error', 'Campaign not found');
        }

        $campaign->is_deleted = true;
        $campaign->save();

        return redirect()
            ->route('admin.campaigns')
            ->with('success', 'Campaign deleted successfully!');
    }

    /**
     * Export campaigns.
     */
    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        $statusFilter = $request->query('status', 'all');

        // Get campaigns based on status filter
        $query = Campaign::with(['group', 'advertiser'])
            ->where('is_deleted', false);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->get();

        // For now, just return a simple response
        // In production, you would generate actual Excel/CSV files here
        return response()->json([
            'message' => 'Export functionality will be implemented',
            'format' => $format,
            'count' => $campaigns->count(),
        ]);
    }

    /**
     * Map form ad_type to database enum value.
     */
    private function mapAdType(string $formAdType, string $contentType, string $format = ''): string
    {
        // Check format-specific types first
        if (in_array($format, ['text', 'text_banner', 'social_bar'])) {
            return 'text';
        }
        if ($format === 'native') {
            return 'native';
        }
        if (in_array($format, ['instream', 'outstream', 'rewarded'])) {
            return 'video';
        }
        if ($format === 'vast') {
            return 'vast';
        }

        // Map the form's ad_type (display_web, special_web, display_video) to aq_ads enum
        return match ($formAdType) {
            'display_video' => 'video',
            'special_web' => match ($contentType) {
                'video' => 'video',
                'html', 'html5' => 'html',
                'text' => 'text',
                default => 'rich_media',
            },
            default => match ($contentType) {
                'video' => 'video',
                'html', 'html5' => 'html',
                'text' => 'text',
                'url' => 'rich_media',
                default => 'image',
            },
        };
    }

    /**
     * Process ad_formats array: create Ad + AdCreative records, handle file uploads.
     * Returns the enriched ad_formats array with file paths.
     */
    private function processAdFormats(Campaign $campaign, array $originalAdFormats, Request $request): array
    {
        $uploadedFiles = $request->file('ad_files', []);
        $uploadedThumbs = $request->file('ad_thumbs', []);
        $updatedAdFormats = [];

        foreach ($originalAdFormats as $index => $af) {
            $format = $af['format'] ?? '';
            $contentType = $af['content_type'] ?? 'image';
            $adType = $this->mapAdType($af['ad_type'] ?? '', $contentType, $format);

            // Build Ad record data
            $adData = [
                'campaign_id' => $campaign->id,
                'name' => $af['ad_name'] ?? 'Untitled Creative',
                'ad_type' => $adType,
                'status' => 'pending_review',
                'destination_url' => $af['ad_url'] ?? '',
                'display_url' => parse_url($af['ad_url'] ?? '', PHP_URL_HOST) ?: null,
                'admin_approved' => false,
                'is_deleted' => false,
            ];

            // Text ad fields → save to Ad model
            if ($format === 'social_bar') {
                $adData['headline'] = $af['text_title'] ?? null;
                $adData['body_text'] = $af['text_description'] ?? null;
                $adData['call_to_action'] = $af['text_body'] ?? 'Learn More';
            } elseif ($contentType === 'text' || in_array($format, ['text', 'text_banner'])) {
                $adData['headline'] = $af['text_title'] ?? null;
                $desc = $af['text_description'] ?? '';
                $body = $af['text_body'] ?? '';
                if ($desc && $body) {
                    $adData['body_text'] = $desc . "\n" . $body;
                } else {
                    $adData['body_text'] = $body ?: ($desc ?: null);
                }
                $adData['call_to_action'] = !empty($af['text_cta']) ? $af['text_cta'] : null;
            } elseif ($format === 'native') {
                $adData['headline'] = $af['native_headline'] ?? null;
                $adData['body_text'] = $af['native_body'] ?? null;
                $adData['brand_name'] = $af['native_brand'] ?? null;
                $adData['call_to_action'] = !empty($af['native_cta']) ? $af['native_cta'] : null;
            } elseif ($format === 'interstitial') {
                $adData['headline'] = $af['interstitial_headline'] ?? null;
                $adData['body_text'] = $af['interstitial_body'] ?? null;
                $adData['call_to_action'] = !empty($af['interstitial_cta']) ? $af['interstitial_cta'] : null;
            } elseif ($format === 'popunder') {
                $adData['headline'] = $af['popunder_headline'] ?? null;
                $adData['body_text'] = $af['popunder_body'] ?? null;
            } elseif ($format === 'in_page_push') {
                $adData['headline'] = $af['ipp_headline'] ?? null;
                $adData['body_text'] = $af['ipp_body'] ?? null;
            } elseif (in_array($format, ['instream', 'outstream', 'rewarded'])) {
                $adData['headline'] = $af['video_headline'] ?? null;
                $adData['call_to_action'] = !empty($af['video_cta']) ? $af['video_cta'] : null;
                if ($format === 'rewarded') {
                    $adData['body_text'] = $af['reward_amount'] ?? null;
                    $adData['sponsored_label'] = $af['reward_type'] ?? 'Coins';
                }
            }

            $ad = Ad::create($adData);

            // Parse dimension (e.g. "300x250")
            $width = null;
            $height = null;
            $dimension = $af['dimension'] ?? '';
            if (str_contains($dimension, 'x')) {
                [$width, $height] = array_map('intval', explode('x', $dimension));
            }

            // Map form content_type to valid DB enum: image, video, html5, gif
            $fileType = match ($contentType) {
                'html', 'flash' => 'html5',
                'video' => 'video',
                'gif' => 'gif',
                'text', 'url', 'interstitial', 'popunder', 'in_page_push' => 'image', // text/url/interstitial/popunder/in_page_push ads may not have a file, default to image
                default => 'image',
            };
            $mimeTypes = ['image' => 'image/png', 'video' => 'video/mp4', 'html5' => 'text/html', 'gif' => 'image/gif'];

            // Handle file upload
            $filePath = null;
            $fileSizeBytes = 0;
            $mimeType = $mimeTypes[$fileType] ?? 'image/png';

            $uploadedFile = $uploadedFiles[$index] ?? null;
            if ($uploadedFile && $uploadedFile->isValid()) {
                $uploadDir = public_path('uploads' . DIRECTORY_SEPARATOR . 'creatives');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $mimeType = $uploadedFile->getClientMimeType() ?: ($mimeTypes[$fileType] ?? 'image/png');
                $extension = $uploadedFile->getClientOriginalExtension() ?: match ($fileType) {
                    'video' => 'mp4',
                    'html5' => 'html',
                    'gif' => 'gif',
                    default => 'png',
                };
                $fileName = time() . '_' . $ad->id . '_' . preg_replace('/[^a-z0-9\-]/', '', strtolower(str_replace(' ', '-', $af['ad_name'] ?? 'creative'))) . '.' . $extension;

                $uploadedFile->move($uploadDir, $fileName);

                $filePath = '/uploads/creatives/' . $fileName;
                $fullPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
                $fileSizeBytes = file_exists($fullPath) ? filesize($fullPath) : 0;

                // Update file type based on actual upload
                if (str_contains($mimeType, 'video')) {
                    $fileType = 'video';
                }
            } else {
                $filePath = !empty($af['file_path']) ? $af['file_path'] : null;
                $fileSizeBytes = (int)($af['file_size'] ?? 0);
            }

            // Handle video thumbnail upload
            $thumbnailPath = null;
            $uploadedThumb = $uploadedThumbs[$index] ?? null;
            if ($uploadedThumb && $uploadedThumb->isValid()) {
                $uploadDir = public_path('uploads' . DIRECTORY_SEPARATOR . 'creatives');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $thumbName = time() . '_' . $ad->id . '_thumb.' . ($uploadedThumb->getClientOriginalExtension() ?: 'jpg');
                $uploadedThumb->move($uploadDir, $thumbName);
                $thumbnailPath = '/uploads/creatives/' . $thumbName;
            }

            // Video URL from form
            $videoUrl = $af['video_url'] ?? null;

            AdCreative::create([
                'ad_id' => $ad->id,
                'file_path' => $filePath,
                'video_url' => $videoUrl,
                'file_type' => $fileType,
                'mime_type' => $mimeType,
                'file_size_bytes' => $fileSizeBytes,
                'width' => $width,
                'height' => $height,
                'alt_text' => $af['ad_name'] ?? '',
                'thumbnail_path' => $thumbnailPath,
                'is_primary' => true,
            ]);

            // Store enriched data back
            $af['file_path'] = $filePath;
            $af['file_size'] = $fileSizeBytes;
            if ($videoUrl) {
                $af['video_url'] = $videoUrl;
            }
            if ($thumbnailPath) {
                $af['thumbnail_path'] = $thumbnailPath;
            }
            $updatedAdFormats[] = $af;
        }

        return $updatedAdFormats;
    }
}

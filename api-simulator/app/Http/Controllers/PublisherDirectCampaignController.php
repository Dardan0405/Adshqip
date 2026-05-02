<?php

namespace App\Http\Controllers;

use App\Models\DirectCampaign;
use App\Models\DirectCampaignStat;
use App\Models\PlatformSetting;
use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PublisherDirectCampaignController extends Controller
{
    private const VISIBLE_STATUSES = ['active', 'paused', 'pending_review', 'completed'];

    public function index(Request $request): View
    {
        $publisherId = (int) $request->user()->id;
        $publisherZones = $this->publisherZones($publisherId);
        $publisherZoneIds = $publisherZones->pluck('id')->all();

        $statsByCampaign = $this->statsByCampaign($publisherZoneIds);

        $query = DirectCampaign::query()
            ->with(['advertiser:id,email', 'zones.zone.site:id,name,domain', 'zones.zone.mobileApp:id,app_name'])
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereHas('zones', function ($zoneLink) use ($publisherZoneIds) {
                $zoneLink->where('is_active', true)->whereIn('zone_id', $publisherZoneIds ?: [0]);
            });

        $statusFilter = $request->query('status', 'all');
        $search = trim((string) $request->query('search', ''));

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('brand_name', 'like', '%' . $search . '%')
                    ->orWhere('headline', 'like', '%' . $search . '%');
            });
        }

        $campaigns = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $campaigns->getCollection()->transform(function (DirectCampaign $campaign) use ($statsByCampaign) {
            $stat = $statsByCampaign[$campaign->id] ?? [
                'impressions' => 0,
                'viewable_impressions' => 0,
                'clicks' => 0,
                'unique_clicks' => 0,
                'conversions' => 0,
                'revenue' => 0.0,
                'publisher_earnings' => 0.0,
            ];

            $impressions = (int) $stat['impressions'];
            $clicks = (int) $stat['clicks'];
            $publisherRevenue = (float) ($stat['publisher_earnings'] > 0 ? $stat['publisher_earnings'] : $stat['revenue']);

            $campaign->publisher_stats = [
                'impressions' => $impressions,
                'viewable_impressions' => (int) $stat['viewable_impressions'],
                'clicks' => $clicks,
                'unique_clicks' => (int) $stat['unique_clicks'],
                'conversions' => (int) $stat['conversions'],
                'revenue' => (float) $stat['revenue'],
                'publisher_earnings' => (float) $stat['publisher_earnings'],
                'publisher_revenue' => $publisherRevenue,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
            ];

            return $campaign;
        });

        $allCampaigns = DirectCampaign::query()
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereHas('zones', function ($zoneLink) use ($publisherZoneIds) {
                $zoneLink->where('is_active', true)->whereIn('zone_id', $publisherZoneIds ?: [0]);
            })
            ->get(['id', 'status']);

        $totals = [
            'total' => $allCampaigns->count(),
            'active' => $allCampaigns->where('status', 'active')->count(),
            'paused' => $allCampaigns->where('status', 'paused')->count(),
            'pending_review' => $allCampaigns->where('status', 'pending_review')->count(),
            'completed' => $allCampaigns->where('status', 'completed')->count(),
            'impressions' => array_sum(array_column($statsByCampaign, 'impressions')),
            'clicks' => array_sum(array_column($statsByCampaign, 'clicks')),
            'conversions' => array_sum(array_column($statsByCampaign, 'conversions')),
            'publisher_revenue' => array_sum(array_map(
                fn ($row) => (float) (($row['publisher_earnings'] ?? 0) > 0 ? $row['publisher_earnings'] : ($row['revenue'] ?? 0)),
                $statsByCampaign
            )),
        ];

        return view('publisher.direct-campaigns.index', [
            'campaigns' => $campaigns,
            'publisherZones' => $publisherZones,
            'statusFilter' => $statusFilter,
            'search' => $search,
            'totals' => $totals,
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $publisherZoneIds = $this->publisherZones((int) $request->user()->id)->pluck('id')->all();
        $campaign = $this->publisherCampaignQuery($id, $publisherZoneIds)
            ->with(['advertiser:id,email', 'targeting', 'creatives', 'zones.zone.site:id,name,domain', 'zones.zone.mobileApp:id,app_name'])
            ->firstOrFail();

        $stats = $this->statsByCampaign($publisherZoneIds, $campaign->id)[$campaign->id] ?? [];

        return response()->json([
            'success' => true,
            'campaign' => [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'brand_name' => $campaign->brand_name,
                'headline' => $campaign->headline,
                'body_text' => $campaign->body_text,
                'call_to_action' => $campaign->call_to_action,
                'pricing_model' => $campaign->pricing_model,
                'bid_amount' => (float) ($campaign->bid_amount ?? 0),
                'destination_url' => $campaign->destination_url,
                'advertiser' => $campaign->advertiser?->email,
                'stats' => $stats,
                'zones' => $campaign->zones->where('is_active', true)->map(fn ($link) => [
                    'id' => $link->zone?->id,
                    'name' => $link->zone?->name,
                    'property' => $link->zone?->site?->name ?? $link->zone?->mobileApp?->app_name,
                ])->values(),
            ],
        ]);
    }

    public function generateTag(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'zone_id' => 'required|integer',
        ]);

        $publisherZoneIds = $this->publisherZones((int) $request->user()->id)->pluck('id')->all();
        $zoneId = (int) $validated['zone_id'];

        if (! in_array($zoneId, $publisherZoneIds, true)) {
            return response()->json(['success' => false, 'message' => 'Selected AdBlock does not belong to your publisher account.'], 403);
        }

        $campaign = $this->publisherCampaignQuery($id, $publisherZoneIds)->firstOrFail();

        $isAssignedToZone = $campaign->zones()
            ->where('is_active', true)
            ->where('zone_id', $zoneId)
            ->exists();

        if (! $isAssignedToZone) {
            return response()->json(['success' => false, 'message' => 'This direct campaign is not assigned to the selected AdBlock.'], 422);
        }

        return response()->json([
            'success' => true,
            'campaign_name' => $campaign->name,
            'codes' => $this->tagCodes($campaign, $zoneId),
        ]);
    }

    private function publisherCampaignQuery(int $id, array $publisherZoneIds)
    {
        return DirectCampaign::query()
            ->where('id', $id)
            ->where('is_deleted', false)
            ->whereIn('status', self::VISIBLE_STATUSES)
            ->whereHas('zones', function ($zoneLink) use ($publisherZoneIds) {
                $zoneLink->where('is_active', true)->whereIn('zone_id', $publisherZoneIds ?: [0]);
            });
    }

    private function publisherZones(int $publisherId): Collection
    {
        return Zone::query()
            ->with(['site:id,name,domain,publisher_id,is_deleted', 'mobileApp:id,app_name,user_id,is_deleted'])
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->where(function ($builder) use ($publisherId) {
                $builder->whereHas('site', function ($site) use ($publisherId) {
                    $site->where('publisher_id', $publisherId)->where('is_deleted', false);
                })->orWhereHas('mobileApp', function ($app) use ($publisherId) {
                    $app->where('user_id', $publisherId)->where('is_deleted', false);
                });
            })
            ->orderBy('name')
            ->get();
    }

    private function statsByCampaign(array $publisherZoneIds, ?int $campaignId = null): array
    {
        if ($publisherZoneIds === []) {
            return [];
        }

        $query = DirectCampaignStat::query()
            ->selectRaw('campaign_id, SUM(impressions) as impressions, SUM(viewable_impressions) as viewable_impressions, SUM(clicks) as clicks, SUM(unique_clicks) as unique_clicks, SUM(conversions) as conversions, SUM(revenue) as revenue, SUM(publisher_earnings) as publisher_earnings')
            ->whereIn('zone_id', $publisherZoneIds)
            ->groupBy('campaign_id');

        if ($campaignId !== null) {
            $query->where('campaign_id', $campaignId);
        }

        return $query->get()
            ->mapWithKeys(fn ($row) => [
                (int) $row->campaign_id => [
                    'impressions' => (int) $row->impressions,
                    'viewable_impressions' => (int) $row->viewable_impressions,
                    'clicks' => (int) $row->clicks,
                    'unique_clicks' => (int) $row->unique_clicks,
                    'conversions' => (int) $row->conversions,
                    'revenue' => (float) $row->revenue,
                    'publisher_earnings' => (float) $row->publisher_earnings,
                ],
            ])
            ->all();
    }

    private function tagCodes(DirectCampaign $campaign, int $zoneId): array
    {
        $serveUrl = route('direct.serve', $campaign->id) . '?' . http_build_query(['zone_id' => $zoneId]);
        $debugUrl = $serveUrl . '&debug=1';
        $postbackUrl = route('direct.postback', $campaign->id) . '?click_id={click_id}&payout={payout}&tx_id={transaction_id}';
        $conversionPixel = '<img src="' . e(route('direct.conversion', $campaign->id) . '?' . http_build_query(['zone_id' => $zoneId])) . '" width="1" height="1" style="display:none" alt="">';
        $iframe = '<iframe src="' . e($serveUrl) . '" loading="lazy" frameborder="0" scrolling="no" style="width:100%;min-height:250px;border:0;"></iframe>';

        $js = '<div id="adshqip-direct-' . $campaign->id . '-' . $zoneId . '"></div>' . "\n"
            . '<script>(function(){var s=' . json_encode($serveUrl) . ';var f=document.createElement("iframe");f.src=s;f.loading="lazy";f.frameBorder="0";f.scrolling="no";f.style.cssText="width:100%;min-height:250px;border:0;";document.getElementById("adshqip-direct-' . $campaign->id . '-' . $zoneId . '").appendChild(f);})();</script>';

        return [
            'iframe' => $iframe,
            'js' => $js,
            'debug_url' => $debugUrl,
            'conversion_pixel' => $conversionPixel,
            'postback_url' => $postbackUrl,
            'serve_domain' => PlatformSetting::getServeDomain(),
        ];
    }
}

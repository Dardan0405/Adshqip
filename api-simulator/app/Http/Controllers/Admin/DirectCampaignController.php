<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectCampaign;
use App\Models\DirectCampaignCreative;
use App\Models\DirectCampaignTargeting;
use App\Models\DirectCampaignZone;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class DirectCampaignController extends Controller
{
    private function getBalkanCities(): array
    {
        return [
            'AL' => ['Tirana', 'Durrës', 'Vlorë', 'Elbasan', 'Shkodër', 'Korçë', 'Fier', 'Berat', 'Lushnjë', 'Pogradec', 'Kavajë', 'Gjirokastër', 'Sarandë', 'Lezhë', 'Kukës', 'Peshkopi'],
            'BA' => ['Sarajevo', 'Banja Luka', 'Tuzla', 'Zenica', 'Mostar', 'Bijeljina', 'Brčko', 'Prijedor', 'Doboj', 'Cazin', 'Trebinje', 'Livno'],
            'BG' => ['Sofia', 'Plovdiv', 'Varna', 'Burgas', 'Ruse', 'Stara Zagora', 'Pleven', 'Sliven', 'Dobrich', 'Shumen', 'Pernik', 'Blagoevgrad', 'Veliko Tarnovo', 'Gabrovo'],
            'HR' => ['Zagreb', 'Split', 'Rijeka', 'Osijek', 'Zadar', 'Pula', 'Slavonski Brod', 'Karlovac', 'Varaždin', 'Dubrovnik', 'Šibenik', 'Sisak'],
            'GR' => ['Athens', 'Thessaloniki', 'Patras', 'Heraklion', 'Larissa', 'Volos', 'Ioannina', 'Kavala', 'Chania', 'Rhodes', 'Alexandroupoli', 'Serres', 'Katerini', 'Corfu'],
            'XK' => ['Prishtina', 'Prizren', 'Peja', 'Mitrovica', 'Gjilan', 'Ferizaj', 'Gjakova', 'Podujeva', 'Vushtrri', 'Suhareka', 'Rahovec', 'Drenas', 'Lipjan', 'Malisheva', 'Kamenica', 'Deçan', 'Istog', 'Skenderaj', 'Klinë', 'Kaçanik', 'Shtime', 'Fushë Kosovë', 'Obiliq', 'Viti'],
            'ME' => ['Podgorica', 'Nikšić', 'Bijelo Polje', 'Herceg Novi', 'Budva', 'Bar', 'Cetinje', 'Kotor', 'Tivat', 'Ulcinj', 'Berane', 'Pljevlja'],
            'MK' => ['Skopje', 'Bitola', 'Kumanovo', 'Prilep', 'Tetovo', 'Ohrid', 'Veles', 'Štip', 'Strumica', 'Gostivar', 'Kavadarci', 'Kočani'],
            'RO' => ['Bucharest', 'Cluj-Napoca', 'Timișoara', 'Iași', 'Constanța', 'Craiova', 'Brașov', 'Galați', 'Ploiești', 'Oradea', 'Brăila', 'Arad', 'Pitești', 'Sibiu', 'Bacău', 'Târgu Mureș'],
            'RS' => ['Belgrade', 'Novi Sad', 'Niš', 'Kragujevac', 'Subotica', 'Zrenjanin', 'Pančevo', 'Čačak', 'Novi Pazar', 'Kraljevo', 'Smederevo', 'Leskovac', 'Užice', 'Vranje'],
            'SI' => ['Ljubljana', 'Maribor', 'Celje', 'Kranj', 'Koper', 'Velenje', 'Novo Mesto', 'Ptuj', 'Trbovlje', 'Kamnik', 'Nova Gorica', 'Murska Sobota'],
            'TR' => ['Istanbul', 'Ankara', 'Izmir', 'Bursa', 'Antalya', 'Adana', 'Konya', 'Gaziantep', 'Mersin', 'Diyarbakır', 'Kayseri', 'Eskişehir', 'Trabzon', 'Samsun', 'Denizli', 'Malatya'],
        ];
    }

    private function getOperatingSystems(): array
    {
        return [
            'Windows' => ['Windows 11', 'Windows 10', 'Windows 8.1', 'Windows 8', 'Windows 7'],
            'macOS' => ['macOS 15 Sequoia', 'macOS 14 Sonoma', 'macOS 13 Ventura', 'macOS 12 Monterey', 'macOS 11 Big Sur'],
            'Android' => ['Android 15', 'Android 14', 'Android 13', 'Android 12', 'Android 11', 'Android 10', 'Android 9'],
            'iOS' => ['iOS 18', 'iOS 17', 'iOS 16', 'iOS 15', 'iOS 14'],
            'iPadOS' => ['iPadOS 18', 'iPadOS 17', 'iPadOS 16', 'iPadOS 15'],
            'Linux' => ['Ubuntu', 'Fedora', 'Debian', 'Arch', 'CentOS'],
            'Chrome OS' => ['Chrome OS 130', 'Chrome OS 125', 'Chrome OS 120'],
        ];
    }

    private function getBrowsers(): array
    {
        return [
            'Chrome' => ['Chrome 131', 'Chrome 130', 'Chrome 129', 'Chrome 128', 'Chrome 125', 'Chrome 120', 'Chrome 115'],
            'Firefox' => ['Firefox 133', 'Firefox 132', 'Firefox 131', 'Firefox 130', 'Firefox 125', 'Firefox 120', 'Firefox 115'],
            'Safari' => ['Safari 18', 'Safari 17', 'Safari 16', 'Safari 15'],
            'Edge' => ['Edge 131', 'Edge 130', 'Edge 129', 'Edge 125', 'Edge 120'],
            'Opera' => ['Opera 114', 'Opera 113', 'Opera 110', 'Opera 105', 'Opera 100'],
            'Samsung Internet' => ['Samsung Internet 26', 'Samsung Internet 25', 'Samsung Internet 24', 'Samsung Internet 23'],
            'Brave' => ['Brave 1.73', 'Brave 1.72', 'Brave 1.70', 'Brave 1.65'],
            'UC Browser' => ['UC Browser 15', 'UC Browser 14', 'UC Browser 13'],
            'Vivaldi' => ['Vivaldi 7.0', 'Vivaldi 6.9', 'Vivaldi 6.8'],
        ];
    }

    private function getConnectionTypes(): array
    {
        return ['Wi-Fi', '3G', '4G/LTE', '5G'];
    }

    private function getMobileCarriers(): array
    {
        return [
            'AL' => ['Vodafone Albania', 'One Telecommunications (ONE)', 'ALBtelecom'],
            'BA' => ['BH Telecom', 'HT Eronet', 'm:tel (Telekom Srpske)'],
            'BG' => ['A1 Bulgaria', 'Yettel Bulgaria', 'Vivacom'],
            'HR' => ['Hrvatski Telekom', 'A1 Croatia', 'Telemach Croatia'],
            'GR' => ['Cosmote', 'Vodafone Greece', 'WIND Hellas (Nova)'],
            'XK' => ['IPKO', 'Vala (Kosovo Telecom)', 'Z Mobile'],
            'ME' => ['Crnogorski Telekom', 'One Montenegro', 'Telenor Montenegro'],
            'MK' => ['Makedonski Telekom', 'A1 Macedonia', 'Lycamobile MK'],
            'RO' => ['Orange Romania', 'Vodafone Romania', 'Digi Mobil (RCS & RDS)', 'Telekom Romania'],
            'RS' => ['Telekom Srbija (MTS)', 'Telenor Serbia (Yettel)', 'A1 Serbia'],
            'SI' => ['Telekom Slovenije', 'A1 Slovenia', 'Telemach Slovenia'],
            'TR' => ['Turkcell', 'Vodafone Turkey', 'Türk Telekom (TT Mobil)'],
        ];
    }

    private function getLanguages(): array
    {
        return [
            'sq' => 'Albanian', 'bs' => 'Bosnian', 'bg' => 'Bulgarian', 'hr' => 'Croatian',
            'el' => 'Greek', 'mk' => 'Macedonian', 'me' => 'Montenegrin', 'ro' => 'Romanian',
            'sr' => 'Serbian', 'sl' => 'Slovenian', 'tr' => 'Turkish', 'en' => 'English',
            'de' => 'German', 'fr' => 'French', 'it' => 'Italian', 'es' => 'Spanish',
            'pt' => 'Portuguese', 'ru' => 'Russian', 'ar' => 'Arabic', 'zh' => 'Chinese',
            'ja' => 'Japanese', 'ko' => 'Korean', 'hi' => 'Hindi', 'nl' => 'Dutch',
            'pl' => 'Polish', 'sv' => 'Swedish', 'da' => 'Danish', 'fi' => 'Finnish',
            'no' => 'Norwegian', 'cs' => 'Czech', 'hu' => 'Hungarian', 'uk' => 'Ukrainian',
        ];
    }

    private function getCountries(): array
    {
        return [
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
    }

    private function getPricingModels(): array
    {
        return [
            'cpm' => 'CPM (Cost Per Mille)',
            'cpc' => 'CPC (Cost Per Click)',
            'cpa' => 'CPA (Cost Per Action)',
            'cpv' => 'CPV (Cost Per View)',
            'cpv_ctw' => 'CPV Click-to-Watch',
            'flat_rate' => 'Flat Rate',
        ];
    }

    private function getMarketingObjectives(): array
    {
        return [
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
    }

    private function getCommonViewData(): array
    {
        $zones = Zone::with('site')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn (Zone $zone) => [
                'id' => $zone->id,
                'name' => $zone->name,
                'site_name' => $zone->site->name ?? 'Unknown Site',
                'format_key' => $zone->format_key,
                'size_key' => $zone->size_key,
            ])
            ->values()
            ->all();

        return [
            'pricingModels' => $this->getPricingModels(),
            'marketingObjectives' => $this->getMarketingObjectives(),
            'countries' => $this->getCountries(),
            'cities' => $this->getBalkanCities(),
            'operatingSystems' => $this->getOperatingSystems(),
            'browsers' => $this->getBrowsers(),
            'connectionTypes' => $this->getConnectionTypes(),
            'mobileCarriers' => $this->getMobileCarriers(),
            'languages' => $this->getLanguages(),
            'zones' => $zones,
        ];
    }

    private function syncZoneLink(DirectCampaign $campaign, ?int $zoneId): void
    {
        DirectCampaignZone::where('campaign_id', $campaign->id)->delete();

        if (! $zoneId) {
            return;
        }

        DirectCampaignZone::create([
            'campaign_id' => $campaign->id,
            'zone_id' => $zoneId,
            'priority' => max(1, (int) ($campaign->priority ?? 1)),
            'is_active' => true,
        ]);
    }

    public function index(Request $request)
    {
        $campaigns = DirectCampaign::with(['advertiser', 'zones.zone'])
            ->withSum('stats', 'impressions')
            ->withSum('stats', 'clicks')
            ->withSum('stats', 'conversions')
            ->withSum('stats', 'revenue')
            ->withSum('stats', 'viewable_impressions')
            ->withSum('stats', 'adblock_detected')
            ->where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->get();

        $allCampaigns = $campaigns->map(function ($campaign) {
            $impressions = (int) ($campaign->stats_sum_impressions ?? 0);
            $clicks = (int) ($campaign->stats_sum_clicks ?? 0);
            $conversions = (int) ($campaign->stats_sum_conversions ?? 0);
            $spend = (float) ($campaign->stats_sum_revenue ?? (($campaign->total_budget ?? 0) - ($campaign->remaining_budget ?? 0)));
            $views = (int) ($campaign->stats_sum_viewable_impressions ?? 0);
            $linkedZone = $campaign->zones->where('is_active', true)->sortByDesc('priority')->first()?->zone;

            return [
                'id' => $campaign->id,
                'name' => $campaign->name,
                'status' => $campaign->status,
                'pricing_model' => $campaign->pricing_model,
                'start_date' => $campaign->start_date?->format('Y-m-d'),
                'end_date' => $campaign->end_date?->format('Y-m-d'),
                'impressions' => $impressions,
                'clicks' => $clicks,
                'conversions' => $conversions,
                'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00,
                'advertiser' => $campaign->advertiser->email ?? 'Unknown',
                'brand_name' => $campaign->brand_name,
                'budget' => (float) ($campaign->total_budget ?? 0),
                'spend' => $spend,
                'views' => $views,
                'adblock_detected' => (int) ($campaign->stats_sum_adblock_detected ?? 0),
                'destination_url' => $campaign->destination_url,
                'priority' => $campaign->priority,
                'delivery_mode' => $campaign->delivery_mode,
                'campaign_group_name' => $campaign->campaign_group_name,
                'zone_id' => $linkedZone?->id,
                'zone_name' => $linkedZone?->name,
                // Ad creative fields for preview
                'headline' => $campaign->headline,
                'body_text' => $campaign->body_text,
                'call_to_action' => $campaign->call_to_action,
                'sponsored_label' => $campaign->sponsored_label,
                'display_url' => $campaign->display_url,
                'brand_logo_url' => $campaign->brand_logo_url,
                'brand_tagline' => $campaign->brand_tagline,
                'brand_color_primary' => $campaign->brand_color_primary,
                'brand_color_secondary' => $campaign->brand_color_secondary,
                // CTW Video
                'ctw_enabled' => (bool) $campaign->ctw_enabled,
                'ctw_video_url' => $campaign->ctw_video_url,
                'ctw_thumbnail_url' => $campaign->ctw_thumbnail_url,
                'ctw_video_title' => $campaign->ctw_video_title,
                'ctw_video_description' => $campaign->ctw_video_description,
                // End Card
                'end_card_enabled' => (bool) $campaign->end_card_enabled,
                'end_card_headline' => $campaign->end_card_headline,
                'end_card_description' => $campaign->end_card_description,
                'end_card_cta_text' => $campaign->end_card_cta_text,
                'end_card_image_url' => $campaign->end_card_image_url,
                // Clip Ad
                'clip_enabled' => (bool) $campaign->clip_enabled,
                'clip_video_url' => $campaign->clip_video_url,
                'clip_thumbnail_url' => $campaign->clip_thumbnail_url,
                'clip_caption' => $campaign->clip_caption,
                'clip_cta_text' => $campaign->clip_cta_text,
            ];
        })->toArray();

        $stats = [
            'total' => count($allCampaigns),
            'active' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'active')),
            'paused' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'paused')),
            'draft' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'draft')),
            'completed' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'completed')),
            'pending_review' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'pending_review')),
            'rejected' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'rejected')),
            'archived' => count(array_filter($allCampaigns, fn($c) => $c['status'] === 'archived')),
            'total_budget' => array_sum(array_column($allCampaigns, 'budget')),
            'total_spend' => array_sum(array_column($allCampaigns, 'spend')),
        ];

        $statusFilter = $request->query('status', 'all');
        $searchQuery = $request->query('search', '');

        if ($statusFilter !== 'all') {
            $allCampaigns = array_filter($allCampaigns, fn($c) => $c['status'] === $statusFilter);
        }

        if ($searchQuery !== '') {
            $allCampaigns = array_filter($allCampaigns, function ($c) use ($searchQuery) {
                return stripos($c['name'], $searchQuery) !== false ||
                       stripos($c['advertiser'], $searchQuery) !== false ||
                       stripos($c['brand_name'] ?? '', $searchQuery) !== false;
            });
        }

        $allCampaigns = array_values($allCampaigns);

        $perPage = 25;
        $currentPage = (int) $request->query('page', 1);
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

        return view('admin.direct-campaigns.index', [
            'campaigns' => $paginatedCampaigns,
            'stats' => $stats,
            'statusFilter' => $statusFilter,
            'search' => $searchQuery,
            'pagination' => $pagination,
        ]);
    }

    public function create()
    {
        return view('admin.direct-campaigns.create', $this->getCommonViewData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pricing_model' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw,flat_rate',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'status' => 'required|in:draft,pending_review,active,paused',
            'destination_url' => 'required|string|max:2000',
            'display_url' => 'nullable|string|max:2000',
            'tracking_url' => 'nullable|string|max:2000',
            'click_tracking_url' => 'nullable|string|max:2000',
            'headline' => 'nullable|string|max:255',
            'headline_dki' => 'nullable|string|max:255',
            'headline_dki_default' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
            'body_text_dki' => 'nullable|string',
            'call_to_action' => 'nullable|string|max:50',
            'sponsored_label' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:100',
            'brand_logo_url' => 'nullable|string',
            'brand_tagline' => 'nullable|string|max:255',
            'brand_color_primary' => 'nullable|string|max:7',
            'brand_color_secondary' => 'nullable|string|max:7',
            'bid_amount' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'schedule_timezone' => 'nullable|string|max:50',
            'delivery_mode' => 'nullable|in:standard,accelerated',
            'priority' => 'nullable|integer|min:1|max:10',
            'weight' => 'nullable|integer|min:1|max:10',
            'zone_id' => 'nullable|exists:aq_zones,id',
            'frequency_cap' => 'nullable|integer|min:1',
            'frequency_cap_period' => 'nullable|in:hour,day,week,month,lifetime',
            'ctw_enabled' => 'nullable|boolean',
            'ctw_thumbnail_url' => 'nullable|string',
            'ctw_min_watch_seconds' => 'nullable|integer|min:1',
            'ctw_skip_after_seconds' => 'nullable|integer|min:1',
            'ctw_autoplay' => 'nullable|boolean',
            'ctw_muted_autoplay' => 'nullable|boolean',
            'end_card_enabled' => 'nullable|boolean',
            'end_card_type' => 'nullable|in:static_image,html,cta_button,product_feed,custom',
            'end_card_image_url' => 'nullable|string',
            'end_card_html' => 'nullable|string',
            'end_card_headline' => 'nullable|string|max:255',
            'end_card_body' => 'nullable|string',
            'end_card_cta_text' => 'nullable|string|max:50',
            'end_card_cta_url' => 'nullable|string',
            'end_card_cta_color' => 'nullable|string|max:7',
            'end_card_display_seconds' => 'nullable|integer|min:1',
            'end_card_logo_url' => 'nullable|string',
            'clip_enabled' => 'nullable|boolean',
            'clip_video_url' => 'nullable|string',
            'clip_thumbnail_url' => 'nullable|string',
            'clip_duration_seconds' => 'nullable|integer|min:1',
            'clip_aspect_ratio' => 'nullable|in:9:16,4:5,1:1',
            'clip_sound_default' => 'nullable|in:on,off',
            'clip_autoplay' => 'nullable|boolean',
            'clip_loop' => 'nullable|boolean',
            'clip_swipe_up_enabled' => 'nullable|boolean',
            'clip_swipe_up_text' => 'nullable|string|max:100',
            'clip_swipe_up_url' => 'nullable|string',
            'clip_caption' => 'nullable|string',
            'campaign_group_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $validated['advertiser_id'] = auth()->id() ?? 1;
        $validated['remaining_budget'] = $validated['total_budget'] ?? 0;

        // Handle boolean checkboxes
        foreach (['ctw_enabled', 'ctw_autoplay', 'ctw_muted_autoplay', 'end_card_enabled', 'clip_enabled', 'clip_autoplay', 'clip_loop', 'clip_swipe_up_enabled'] as $field) {
            $validated[$field] = $request->has($field) ? true : false;
        }

        $campaign = DirectCampaign::create($validated);

        // Handle targeting from form
        $this->syncTargeting($campaign, $request);
        $this->syncZoneLink($campaign, $request->integer('zone_id') ?: null);

        return redirect()
            ->route('admin.direct-campaigns')
            ->with('success', 'Direct campaign created successfully!');
    }

    public function show(int $id)
    {
        $campaign = DirectCampaign::with(['advertiser', 'creatives', 'targeting', 'zones.zone'])
            ->withSum('stats', 'impressions')
            ->withSum('stats', 'clicks')
            ->withSum('stats', 'conversions')
            ->withSum('stats', 'revenue')
            ->withSum('stats', 'adblock_detected')
            ->find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $impressions = (int) ($campaign->stats_sum_impressions ?? 0);
        $clicks = (int) ($campaign->stats_sum_clicks ?? 0);
        $conversions = (int) ($campaign->stats_sum_conversions ?? 0);
        $spend = (float) ($campaign->stats_sum_revenue ?? (($campaign->total_budget ?? 0) - ($campaign->remaining_budget ?? 0)));

        // Group targeting by type
        $targetingByType = [];
        foreach ($campaign->targeting as $t) {
            $targetingByType[$t->targeting_type] = $t->target_values;
        }

        $linkedZone = $campaign->zones->where('is_active', true)->sortByDesc('priority')->first()?->zone;

        $campaignData = $campaign->toArray();
        $campaignData['impressions'] = $impressions;
        $campaignData['clicks'] = $clicks;
        $campaignData['conversions'] = $conversions;
        $campaignData['spend'] = $spend;
        $campaignData['budget'] = (float) ($campaign->total_budget ?? 0);
        $campaignData['ctr'] = $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.00;
        $campaignData['adblock_detected'] = (int) ($campaign->stats_sum_adblock_detected ?? 0);
        $campaignData['advertiser_email'] = $campaign->advertiser->email ?? 'Unknown';
        $campaignData['targeting_by_type'] = $targetingByType;
        $campaignData['creatives'] = $campaign->creatives->toArray();
        $campaignData['zone_id'] = $linkedZone?->id;
        $campaignData['zone_name'] = $linkedZone?->name;
        $campaignData['zone_site_name'] = $linkedZone?->site?->name;

        return view('admin.direct-campaigns.show', ['campaign' => $campaignData]);
    }

    public function edit(int $id)
    {
        $campaign = DirectCampaign::with(['targeting', 'zones.zone'])->find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $spend = (float) (($campaign->total_budget ?? 0) - ($campaign->remaining_budget ?? 0));

        // Group targeting by type for pre-populating form
        $targetingByType = [];
        foreach ($campaign->targeting as $t) {
            $targetingByType[$t->targeting_type] = $t->target_values;
        }

        $linkedZone = $campaign->zones->where('is_active', true)->sortByDesc('priority')->first()?->zone;

        $campaignData = $campaign->toArray();
        $campaignData['start_date'] = $campaign->start_date?->format('Y-m-d\TH:i');
        $campaignData['end_date'] = $campaign->end_date?->format('Y-m-d\TH:i');
        $campaignData['spend'] = $spend;
        $campaignData['targeting_by_type'] = $targetingByType;
        $campaignData['zone_id'] = $linkedZone?->id;

        $viewData = $this->getCommonViewData();
        $viewData['campaign'] = $campaignData;

        return view('admin.direct-campaigns.edit', $viewData);
    }

    public function update(Request $request, int $id)
    {
        $campaign = DirectCampaign::find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'pricing_model' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw,flat_rate',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'status' => 'required|in:draft,pending_review,active,paused,completed,rejected,archived',
            'destination_url' => 'required|string|max:2000',
            'display_url' => 'nullable|string|max:2000',
            'tracking_url' => 'nullable|string|max:2000',
            'click_tracking_url' => 'nullable|string|max:2000',
            'headline' => 'nullable|string|max:255',
            'headline_dki' => 'nullable|string|max:255',
            'headline_dki_default' => 'nullable|string|max:255',
            'body_text' => 'nullable|string',
            'body_text_dki' => 'nullable|string',
            'call_to_action' => 'nullable|string|max:50',
            'sponsored_label' => 'nullable|string|max:50',
            'brand_name' => 'nullable|string|max:100',
            'brand_logo_url' => 'nullable|string',
            'brand_tagline' => 'nullable|string|max:255',
            'brand_color_primary' => 'nullable|string|max:7',
            'brand_color_secondary' => 'nullable|string|max:7',
            'bid_amount' => 'required|numeric|min:0',
            'daily_budget' => 'nullable|numeric|min:0',
            'total_budget' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'schedule_timezone' => 'nullable|string|max:50',
            'delivery_mode' => 'nullable|in:standard,accelerated',
            'priority' => 'nullable|integer|min:1|max:10',
            'weight' => 'nullable|integer|min:1|max:10',
            'zone_id' => 'nullable|exists:aq_zones,id',
            'frequency_cap' => 'nullable|integer|min:1',
            'frequency_cap_period' => 'nullable|in:hour,day,week,month,lifetime',
            'ctw_enabled' => 'nullable|boolean',
            'ctw_thumbnail_url' => 'nullable|string',
            'ctw_min_watch_seconds' => 'nullable|integer|min:1',
            'ctw_skip_after_seconds' => 'nullable|integer|min:1',
            'ctw_autoplay' => 'nullable|boolean',
            'ctw_muted_autoplay' => 'nullable|boolean',
            'end_card_enabled' => 'nullable|boolean',
            'end_card_type' => 'nullable|in:static_image,html,cta_button,product_feed,custom',
            'end_card_image_url' => 'nullable|string',
            'end_card_html' => 'nullable|string',
            'end_card_headline' => 'nullable|string|max:255',
            'end_card_body' => 'nullable|string',
            'end_card_cta_text' => 'nullable|string|max:50',
            'end_card_cta_url' => 'nullable|string',
            'end_card_cta_color' => 'nullable|string|max:7',
            'end_card_display_seconds' => 'nullable|integer|min:1',
            'end_card_logo_url' => 'nullable|string',
            'clip_enabled' => 'nullable|boolean',
            'clip_video_url' => 'nullable|string',
            'clip_thumbnail_url' => 'nullable|string',
            'clip_duration_seconds' => 'nullable|integer|min:1',
            'clip_aspect_ratio' => 'nullable|in:9:16,4:5,1:1',
            'clip_sound_default' => 'nullable|in:on,off',
            'clip_autoplay' => 'nullable|boolean',
            'clip_loop' => 'nullable|boolean',
            'clip_swipe_up_enabled' => 'nullable|boolean',
            'clip_swipe_up_text' => 'nullable|string|max:100',
            'clip_swipe_up_url' => 'nullable|string',
            'clip_caption' => 'nullable|string',
            'campaign_group_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        // Handle boolean checkboxes
        foreach (['ctw_enabled', 'ctw_autoplay', 'ctw_muted_autoplay', 'end_card_enabled', 'clip_enabled', 'clip_autoplay', 'clip_loop', 'clip_swipe_up_enabled'] as $field) {
            $validated[$field] = $request->has($field) ? true : false;
        }

        $campaign->update($validated);

        // Sync targeting
        $this->syncTargeting($campaign, $request);
        $this->syncZoneLink($campaign, $request->integer('zone_id') ?: null);

        return redirect()
            ->route('admin.direct-campaigns.show', $id)
            ->with('success', 'Direct campaign updated successfully!');
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,pending_review,active,paused,completed,rejected,archived',
        ]);

        $campaign = DirectCampaign::find($id);

        if (!$campaign || $campaign->is_deleted) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $campaign->status = $validated['status'];
        $campaign->save();

        return redirect()
            ->route('admin.direct-campaigns')
            ->with('success', 'Direct campaign status updated successfully!');
    }

    public function destroy(int $id)
    {
        $campaign = DirectCampaign::find($id);

        if (!$campaign) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $campaign->is_deleted = true;
        $campaign->save();

        return redirect()
            ->route('admin.direct-campaigns')
            ->with('success', 'Direct campaign deleted successfully!');
    }

    public function duplicate(int $id)
    {
        $campaign = DirectCampaign::find($id);

        if (!$campaign) {
            return redirect()
                ->route('admin.direct-campaigns')
                ->with('error', 'Direct campaign not found');
        }

        $newCampaign = $campaign->replicate();
        $newCampaign->name = $campaign->name . ' (Copy)';
        $newCampaign->status = 'draft';
        $newCampaign->save();

        // Duplicate targeting rules
        foreach ($campaign->targeting as $targeting) {
            $newTargeting = $targeting->replicate();
            $newTargeting->campaign_id = $newCampaign->id;
            $newTargeting->save();
        }

        foreach ($campaign->zones as $zoneLink) {
            $newZoneLink = $zoneLink->replicate();
            $newZoneLink->campaign_id = $newCampaign->id;
            $newZoneLink->save();
        }

        return redirect()
            ->route('admin.direct-campaigns')
            ->with('success', 'Direct campaign duplicated successfully!');
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'csv');
        $statusFilter = $request->query('status', 'all');

        $query = DirectCampaign::with(['advertiser'])
            ->where('is_deleted', false);

        if ($statusFilter !== 'all') {
            $query->where('status', $statusFilter);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Export functionality will be implemented',
            'format' => $format,
            'count' => $campaigns->count(),
        ]);
    }

    private function syncTargeting(DirectCampaign $campaign, Request $request): void
    {
        // Delete existing targeting rules
        DirectCampaignTargeting::where('campaign_id', $campaign->id)->delete();

        // Geo targeting
        $geoCountries = $request->input('targeting_geo');
        if ($geoCountries) {
            if (is_string($geoCountries)) {
                $geoCountries = json_decode($geoCountries, true);
            }
            if (!empty($geoCountries)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'geo_country',
                    'match_mode' => 'include',
                    'target_values' => $geoCountries,
                ]);
            }
        }

        // Device targeting
        $devices = $request->input('targeting_device');
        if ($devices) {
            if (is_string($devices)) {
                $devices = json_decode($devices, true);
            }
            if (!empty($devices)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'device',
                    'match_mode' => 'include',
                    'target_values' => $devices,
                ]);
            }
        }

        // OS targeting
        $os = $request->input('targeting_os');
        if ($os) {
            if (is_string($os)) {
                $os = json_decode($os, true);
            }
            if (!empty($os)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'os',
                    'match_mode' => 'include',
                    'target_values' => $os,
                ]);
            }
        }

        // Browser targeting
        $browsers = $request->input('targeting_browser');
        if ($browsers) {
            if (is_string($browsers)) {
                $browsers = json_decode($browsers, true);
            }
            if (!empty($browsers)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'browser',
                    'match_mode' => 'include',
                    'target_values' => $browsers,
                ]);
            }
        }

        // Language targeting
        $languages = $request->input('targeting_language');
        if ($languages) {
            if (is_string($languages)) {
                $languages = json_decode($languages, true);
            }
            if (!empty($languages)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'language',
                    'match_mode' => 'include',
                    'target_values' => $languages,
                ]);
            }
        }

        // Connection type targeting
        $connTypes = $request->input('targeting_connection_type');
        if ($connTypes) {
            if (is_string($connTypes)) {
                $connTypes = json_decode($connTypes, true);
            }
            if (!empty($connTypes)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'connection_type',
                    'match_mode' => 'include',
                    'target_values' => $connTypes,
                ]);
            }
        }

        // Carrier targeting
        $carriers = $request->input('targeting_carrier');
        if ($carriers) {
            if (is_string($carriers)) {
                $carriers = json_decode($carriers, true);
            }
            if (!empty($carriers)) {
                DirectCampaignTargeting::create([
                    'campaign_id' => $campaign->id,
                    'targeting_type' => 'carrier',
                    'match_mode' => 'include',
                    'target_values' => $carriers,
                ]);
            }
        }
    }
}

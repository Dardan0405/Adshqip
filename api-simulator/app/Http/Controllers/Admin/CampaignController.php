<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\Campaign;
use App\Models\CampaignGroup;
use App\Models\Browser;
use App\Models\BrowserLanguage;
use App\Models\CarrierIspConnection;
use App\Models\ConnectionType;
use App\Models\Device;
use App\Models\DisplayScreen;
use App\Models\MobileManufacturer;
use App\Models\OperatingSystem;
use App\Models\PixelTracker;
use App\Models\PlatformSetting;
use App\Models\StatDaily;
use App\Models\User;
use App\Models\Zone;
use App\Models\Category;
use App\Models\Keyword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CampaignController extends Controller
{
    private function getActiveDisplayScreens(): array
    {
        return DisplayScreen::query()
            ->where('status', 'active')
            ->orderBy('screen_name')
            ->get()
            ->map(function (DisplayScreen $screen) {
                return [
                    'id' => $screen->id,
                    'screen_name' => $screen->screen_name,
                    'value' => $screen->value,
                    'width' => $screen->width,
                    'height' => $screen->height,
                    'dimension' => $screen->width . 'x' . $screen->height,
                    'label' => $screen->screen_name . ' (' . $screen->width . 'x' . $screen->height . ')',
                ];
            })
            ->toArray();
    }

    private function campaignSettings(): array
    {
        return [
            'minimum_budget' => PlatformSetting::getCampaignMinimumBudget(),
            'minimum_bid_rate' => PlatformSetting::getCampaignMinimumBidRate(),
            'creative_type' => PlatformSetting::getCampaignCreativeType(),
        ];
    }

    private function campaignCreativeDefaultGroup(): string
    {
        return match (PlatformSetting::getCampaignCreativeType()) {
            PlatformSetting::CAMPAIGN_CREATIVE_TYPE_VIDEO,
            PlatformSetting::CAMPAIGN_CREATIVE_TYPE_VAST => 'display_video',
            PlatformSetting::CAMPAIGN_CREATIVE_TYPE_TEXT,
            PlatformSetting::CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA,
            PlatformSetting::CAMPAIGN_CREATIVE_TYPE_NATIVE => 'special_web',
            default => 'display_web',
        };
    }

    private function validateCampaignPayload(Request $request, array $rules): array
    {
        $minimumBudget = PlatformSetting::getCampaignMinimumBudget();
        $minimumBidRate = PlatformSetting::getCampaignMinimumBidRate();

        $validator = Validator::make($request->all(), $rules);

        $validator->after(function ($validator) use ($request, $minimumBudget, $minimumBidRate) {
            $totalBudget = $request->input('total_budget');
            if ($totalBudget !== null && $totalBudget !== '' && is_numeric($totalBudget) && (float) $totalBudget < $minimumBudget) {
                $validator->errors()->add('total_budget', 'The total budget must be at least ' . number_format($minimumBudget, 2) . '.');
            }

            $bidAmount = $request->input('bid_amount');
            if ($bidAmount !== null && $bidAmount !== '' && is_numeric($bidAmount) && (float) $bidAmount < $minimumBidRate) {
                $validator->errors()->add('bid_amount', 'The bid amount must be at least ' . number_format($minimumBidRate, 4) . '.');
            }
        });

        return $validator->validate();
    }

    private function getAvailableZones(): array
    {
        return Zone::with('site')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'site_name' => $zone->site->name ?? 'Unknown Site',
                    'placement' => $zone->placement,
                    'format_key' => $zone->format_key,
                ];
            })
            ->toArray();
    }

    private function getDeviceTargetingOptions(): array
    {
        if (! Schema::hasTable('aq_devices') && ! Schema::hasTable('aq_mobile_manufacturers')) {
            return [
                'deviceTypes' => [],
                'devicesByType' => [],
            ];
        }

        $devices = collect();
        if (Schema::hasTable('aq_devices')) {
            $devices = $devices->merge(
                Device::query()
                    ->where('status', 'active')
                    ->orderBy('device_value')
                    ->orderBy('device_name')
                    ->get()
                    ->map(function (Device $device) {
                        return [
                            'type' => $device->device_value,
                            'name' => $device->device_name,
                        ];
                    })
            );
        }

        if (Schema::hasTable('aq_mobile_manufacturers')) {
            $devices = $devices->merge(
                MobileManufacturer::query()
                    ->where('status', 'active')
                    ->orderBy('manufacturer_value')
                    ->orderBy('manufacturer_name')
                    ->get()
                    ->map(function (MobileManufacturer $manufacturer) {
                        return [
                            'type' => $manufacturer->manufacturer_value,
                            'name' => $manufacturer->manufacturer_name,
                        ];
                    })
            );
        }

        $deviceTypes = $devices->pluck('type')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $devicesByType = $devices->groupBy('type')
            ->map(function ($items) {
                return $items->map(function (array $device) {
                    return [
                        'label' => $device['name'],
                        'value' => strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '_', trim($device['name']))),
                    ];
                })->unique('value')->values()->toArray();
            })
            ->toArray();

        return [
            'deviceTypes' => $deviceTypes,
            'devicesByType' => $devicesByType,
        ];
    }

    /**
     * Balkan cities grouped by country code.
     */
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

    /**
     * Operating systems with their versions.
     */
    private function getOperatingSystems(): array
    {
        if (! Schema::hasTable('aq_operating_systems')) {
            return [];
        }

        return OperatingSystem::query()
            ->where('status', 'active')
            ->orderBy('os_name')
            ->orderBy('os_value')
            ->get()
            ->groupBy('os_name')
            ->map(function ($items) {
                return $items->pluck('os_value')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            })
            ->toArray();
    }

    /**
     * Browsers with their versions.
     */
    private function getBrowsers(): array
    {
        if (! Schema::hasTable('aq_browsers')) {
            return [];
        }

        return Browser::query()
            ->where('status', 'active')
            ->orderBy('browser_name')
            ->orderBy('browser_code')
            ->get()
            ->mapWithKeys(function (Browser $browser) {
                return [$browser->browser_name => array_values(array_filter([$browser->browser_code]))];
            })
            ->toArray();
    }

    /**
     * Connection types available for targeting.
     */
    private function getConnectionTypes(): array
    {
        if (! Schema::hasTable('aq_connection_types')) {
            return [];
        }

        return ConnectionType::query()
            ->where('status', 'active')
            ->orderBy('connection_name')
            ->orderBy('connection_value')
            ->pluck('connection_name')
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Mobile carriers grouped by country code (Balkan region).
     */
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

    /**
     * Languages available for targeting (Balkan region + major web languages).
     */
    private function getLanguages(): array
    {
        if (! Schema::hasTable('aq_browser_languages')) {
            return [];
        }

        return BrowserLanguage::query()
            ->where('status', 'active')
            ->orderBy('language_name')
            ->orderBy('language_value')
            ->get()
            ->mapWithKeys(function (BrowserLanguage $language) {
                return [$language->language_value => $language->language_name];
            })
            ->toArray();
    }

    private function getCarrierIspConnections(): array
    {
        if (! Schema::hasTable('aq_carrier_isp_connections')) {
            return [];
        }

        return CarrierIspConnection::query()
            ->where('status', 'active')
            ->orderBy('country')
            ->orderBy('carrier_name')
            ->get()
            ->groupBy('country')
            ->map(function ($items) {
                return $items->pluck('carrier_name')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
            })
            ->toArray();
    }

    /**
     * Get active keywords for meta keyword targeting.
     */
    private function getKeywords(): array
    {
        if (! Schema::hasTable('aq_keywords')) {
            return [];
        }

        return Keyword::query()
            ->where('status', 'active')
            ->orderBy('category')
            ->orderBy('keyword')
            ->get()
            ->map(function (Keyword $kw) {
                return [
                    'id' => $kw->id,
                    'keyword' => $kw->keyword,
                    'category' => $kw->category,
                    'description' => $kw->description,
                ];
            })
            ->toArray();
    }

    /**
     * Display the campaigns listing page for admin.
     */
    public function index(Request $request)
    {
        // Get all campaigns with their relationships and aggregated stats
        $campaigns = Campaign::with(['group', 'advertiser', 'zone.site'])
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
                'zone_id' => $campaign->zone_id,
                'zone_name' => $campaign->zone?->name,
                'zone_site_name' => $campaign->zone?->site?->name,
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
        $deviceTargeting = $this->getDeviceTargetingOptions();

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

        $pixelTrackers = PixelTracker::with('advertiser')
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'name' => $pixel->name,
                    'type' => $pixel->type,
                    'code' => $pixel->pixel_code,
                    'advertiser' => $pixel->advertiser?->email ?? 'Unknown',
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

        // Advertisers
        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get()
            ->map(function ($adv) {
                $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                if (!$name) $name = $adv->email;
                return ['id' => $adv->id, 'name' => $name, 'email' => $adv->email];
            })
            ->toArray();

        // Categories from database
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])
            ->toArray();

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
            'zones' => $this->getAvailableZones(),
            'pixelTrackers' => $pixelTrackers,
            'pixels' => $pixelTrackers,
            'campaignTypes' => $campaignTypes,
            'marketingObjectives' => $marketingObjectives,
            'countries' => $countries,
            'cities' => $this->getBalkanCities(),
            'operatingSystems' => $this->getOperatingSystems(),
            'browsers' => $this->getBrowsers(),
            'connectionTypes' => $this->getConnectionTypes(),
            'mobileCarriers' => $this->getCarrierIspConnections(),
            'languages' => $this->getLanguages(),
            'trafficSources' => $trafficSources,
            'pricingModels' => $pricingModels,
            'advertisers' => $advertisers,
            'categories' => $categories,
            'adFormats' => $adFormats,
            'deviceTypes' => $deviceTargeting['deviceTypes'],
            'devicesByType' => $deviceTargeting['devicesByType'],
            'keywords' => $this->getKeywords(),
            'campaignSettings' => $this->campaignSettings(),
            'defaultAdTypeGroup' => $this->campaignCreativeDefaultGroup(),
            'displayScreens' => $this->getActiveDisplayScreens(),
        ]);
    }

    /**
     * Store a newly created campaign.
     */
    public function store(Request $request)
    {
        $validated = $this->validateCampaignPayload($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'campaign_type' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'group_id' => 'nullable|exists:aq_campaign_groups,id',
            'zone_id' => 'nullable|exists:aq_zones,id',
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
            'targeting_city' => 'nullable|json',
            'targeting_os' => 'nullable|json',
            'targeting_os_version' => 'nullable|json',
            'targeting_browser' => 'nullable|json',
            'targeting_browser_version' => 'nullable|json',
            'targeting_connection_type' => 'nullable|json',
            'targeting_carrier' => 'nullable|json',
            'targeting_language' => 'nullable|json',
            'targeting_traffic_type' => 'nullable|in:all,mainstream,non-mainstream',
            'targeting_ip_include' => 'nullable|string',
            'targeting_ip_exclude' => 'nullable|string',
            'targeting_keywords' => 'nullable|array',
            'targeting_keywords.*' => 'string',
            's2s_enabled' => 'nullable|boolean',
            's2s_postback_url' => 'nullable|string|max:2000',
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
            'ad_formats.*.display_screen_id' => 'nullable|exists:aq_display_screens,id',
            'ad_formats.*.display_screen_name' => 'nullable|string',
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

        // Handle S2S checkbox (unchecked = not sent)
        $validated['s2s_enabled'] = $request->has('s2s_enabled') ? true : false;
        if (!$validated['s2s_enabled']) {
            $validated['s2s_postback_url'] = null;
        }

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
        if (isset($validated['targeting_city']) && is_string($validated['targeting_city'])) {
            $validated['targeting_city'] = json_decode($validated['targeting_city'], true);
        }
        foreach (['targeting_os', 'targeting_os_version', 'targeting_browser', 'targeting_browser_version', 'targeting_connection_type', 'targeting_carrier', 'targeting_language'] as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = json_decode($validated[$field], true);
            }
        }

        // Parse IP include/exclude textarea (one per line) into JSON arrays
        foreach (['targeting_ip_include', 'targeting_ip_exclude'] as $ipField) {
            if (!empty($validated[$ipField]) && is_string($validated[$ipField])) {
                $validated[$ipField] = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated[$ipField]))));
            }
            if (empty($validated[$ipField])) {
                $validated[$ipField] = null;
            }
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
        if (empty($validated['zone_id'])) {
            $validated['zone_id'] = null;
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
        $advertiserLabel = $advertiser?->email ?? 'Unknown';

        return response()->json([
            'success' => true,
            'pixel' => [
                'id' => $pixel->id,
                'name' => $pixel->name,
                'type' => $pixel->type,
                'code' => $pixel->pixel_code,
                'advertiser' => $advertiserLabel,
                'advertiser_email' => $advertiserLabel,
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
        $campaign = Campaign::with(['group', 'advertiser', 'zone.site', 'pixelTracker.advertiser'])
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
            'advertiser_email' => $campaign->advertiser->email ?? 'Unknown',
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
            'targeting_os' => $campaign->targeting_os,
            'targeting_os_version' => $campaign->targeting_os_version,
            'targeting_browser' => $campaign->targeting_browser,
            'targeting_browser_version' => $campaign->targeting_browser_version,
            'targeting_schedule' => $campaign->targeting_schedule,
            'weight' => $campaign->weight,
            'group' => $campaign->group ? $campaign->group->name : null,
            'group_name' => $campaign->group ? $campaign->group->name : null,
            'group_id' => $campaign->group_id,
            'zone_id' => $campaign->zone_id,
            'zone_name' => $campaign->zone?->name,
            'zone_site_name' => $campaign->zone?->site?->name,
            'targeting_region' => $campaign->targeting_region,
            'targeting_city' => $campaign->targeting_city,
            'targeting_connection_type' => $campaign->targeting_connection_type,
            'targeting_carrier' => $campaign->targeting_carrier,
            'targeting_language' => $campaign->targeting_language,
            'targeting_traffic_type' => $campaign->targeting_traffic_type,
            'targeting_ip_include' => $campaign->targeting_ip_include,
            'targeting_ip_exclude' => $campaign->targeting_ip_exclude,
            'targeting_keywords' => $campaign->targeting_keywords,
            's2s_enabled' => (bool) $campaign->s2s_enabled,
            's2s_postback_url' => $campaign->s2s_postback_url,
            'pixel_tracker' => $campaign->pixelTracker ? [
                'id' => $campaign->pixelTracker->id,
                'name' => $campaign->pixelTracker->name,
                'type' => $campaign->pixelTracker->type,
                'advertiser_email' => $campaign->pixelTracker->advertiser?->email ?? 'Unknown',
                'pixel_code' => $campaign->pixelTracker->pixel_code,
                'pixel_goal' => $campaign->pixelTracker->pixel_goal,
                'category' => $campaign->pixelTracker->category,
                'tracking_url' => $campaign->pixelTracker->tracking_url,
                'status' => $campaign->pixelTracker->status ?? 'active',
                'is_active' => (bool) $campaign->pixelTracker->is_active,
                'fire_count' => (int) ($campaign->pixelTracker->fire_count ?? 0),
                'last_fired_at' => $campaign->pixelTracker->last_fired_at?->format('M d, Y H:i:s'),
            ] : null,
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
        $deviceTargeting = $this->getDeviceTargetingOptions();

        $campaign = Campaign::with(['group', 'zone.site'])->find($id);

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
            'targeting_city' => $campaign->targeting_city,
            'targeting_connection_type' => $campaign->targeting_connection_type,
            'targeting_carrier' => $campaign->targeting_carrier,
            'targeting_language' => $campaign->targeting_language,
            'targeting_traffic_type' => $campaign->targeting_traffic_type,
            'targeting_ip_include' => $campaign->targeting_ip_include,
            'targeting_ip_exclude' => $campaign->targeting_ip_exclude,
            'targeting_keywords' => $campaign->targeting_keywords,
            's2s_enabled' => (bool) $campaign->s2s_enabled,
            's2s_postback_url' => $campaign->s2s_postback_url,
            'targeting_os' => $campaign->targeting_os,
            'targeting_os_version' => $campaign->targeting_os_version,
            'targeting_browser' => $campaign->targeting_browser,
            'targeting_browser_version' => $campaign->targeting_browser_version,
            'traffic_sources' => $campaign->traffic_sources,
            'country_bids' => $campaign->country_bids,
            'ad_formats' => $campaign->ad_formats,
            'weight' => $campaign->weight,
            'group_id' => $campaign->group_id,
            'zone_id' => $campaign->zone_id,
            'zone_name' => $campaign->zone?->name,
            'zone_site_name' => $campaign->zone?->site?->name,
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

        $pixelTrackers = PixelTracker::with('advertiser')
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get()
            ->map(function ($pixel) {
                return [
                    'id' => $pixel->id,
                    'name' => $pixel->name,
                    'type' => $pixel->type,
                    'code' => $pixel->pixel_code,
                    'advertiser' => $pixel->advertiser?->email ?? 'Unknown',
                ];
            })
            ->toArray();

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get()
            ->map(function ($adv) {
                $name = trim(($adv->profile->first_name ?? '') . ' ' . ($adv->profile->last_name ?? ''));
                if (!$name) $name = $adv->email;
                return ['id' => $adv->id, 'name' => $name, 'email' => $adv->email];
            })
            ->toArray();

        // Categories from database
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get()
            ->map(fn($cat) => ['id' => $cat->id, 'name' => $cat->name])
            ->toArray();

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
            'zones' => $this->getAvailableZones(),
            'campaignTypes' => $campaignTypes,
            'marketingObjectives' => $marketingObjectives,
            'countries' => $countries,
            'cities' => $this->getBalkanCities(),
            'operatingSystems' => $this->getOperatingSystems(),
            'browsers' => $this->getBrowsers(),
            'connectionTypes' => $this->getConnectionTypes(),
            'mobileCarriers' => $this->getCarrierIspConnections(),
            'languages' => $this->getLanguages(),
            'trafficSources' => $trafficSources,
            'pricingModels' => $pricingModels,
            'pixelTrackers' => $pixelTrackers,
            'advertisers' => $advertisers,
            'categories' => $categories,
            'adFormats' => $adFormats,
            'deviceTypes' => $deviceTargeting['deviceTypes'],
            'devicesByType' => $deviceTargeting['devicesByType'],
            'keywords' => $this->getKeywords(),
            'campaignSettings' => $this->campaignSettings(),
            'defaultAdTypeGroup' => $this->campaignCreativeDefaultGroup(),
            'displayScreens' => $this->getActiveDisplayScreens(),
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

        $validated = $this->validateCampaignPayload($request, [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'campaign_type' => 'required|in:cpm,cpc,cpa,cpv,cpv_ctw',
            'marketing_objective' => 'required|in:brand_awareness,reach,traffic,engagement,app_installs,video_views,lead_generation,conversions,catalog_sales,store_visits',
            'group_id' => 'nullable|exists:aq_campaign_groups,id',
            'zone_id' => 'nullable|exists:aq_zones,id',
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
            'targeting_city' => 'nullable|json',
            'targeting_os' => 'nullable|json',
            'targeting_os_version' => 'nullable|json',
            'targeting_browser' => 'nullable|json',
            'targeting_browser_version' => 'nullable|json',
            'targeting_connection_type' => 'nullable|json',
            'targeting_carrier' => 'nullable|json',
            'targeting_language' => 'nullable|json',
            'targeting_traffic_type' => 'nullable|in:all,mainstream,non-mainstream',
            'targeting_ip_include' => 'nullable|string',
            'targeting_ip_exclude' => 'nullable|string',
            'targeting_keywords' => 'nullable|array',
            'targeting_keywords.*' => 'string',
            's2s_enabled' => 'nullable|boolean',
            's2s_postback_url' => 'nullable|string|max:2000',
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
            'ad_formats.*.display_screen_id' => 'nullable|exists:aq_display_screens,id',
            'ad_formats.*.display_screen_name' => 'nullable|string',
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

        // Handle S2S checkbox (unchecked = not sent)
        $validated['s2s_enabled'] = $request->has('s2s_enabled') ? true : false;
        if (!$validated['s2s_enabled']) {
            $validated['s2s_postback_url'] = null;
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
        if (isset($validated['targeting_city']) && is_string($validated['targeting_city'])) {
            $validated['targeting_city'] = json_decode($validated['targeting_city'], true);
        }
        foreach (['targeting_os', 'targeting_os_version', 'targeting_browser', 'targeting_browser_version', 'targeting_connection_type', 'targeting_carrier', 'targeting_language'] as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = json_decode($validated[$field], true);
            }
        }

        // Parse IP include/exclude textarea (one per line) into JSON arrays
        foreach (['targeting_ip_include', 'targeting_ip_exclude'] as $ipField) {
            if (!empty($validated[$ipField]) && is_string($validated[$ipField])) {
                $validated[$ipField] = array_values(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $validated[$ipField]))));
            }
            if (empty($validated[$ipField])) {
                $validated[$ipField] = null;
            }
        }

        // If region/formats/geo/device/city/os/browser/connection/carrier not sent, set to null (unchecked all)
        if (!$request->has('targeting_region')) {
            $validated['targeting_region'] = null;
        }
        if (!$request->has('targeting_geo')) {
            $validated['targeting_geo'] = null;
        }
        if (!$request->has('targeting_device')) {
            $validated['targeting_device'] = null;
        }
        if (!$request->has('targeting_city')) {
            $validated['targeting_city'] = null;
        }
        foreach (['targeting_os', 'targeting_os_version', 'targeting_browser', 'targeting_browser_version', 'targeting_connection_type', 'targeting_carrier', 'targeting_language'] as $field) {
            if (!$request->has($field)) {
                $validated[$field] = null;
            }
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
        if (!$request->has('targeting_keywords')) {
            $validated['targeting_keywords'] = null;
        }

        // Handle empty pixel_tracker_id (empty string from select)
        if (empty($validated['pixel_tracker_id'])) {
            $validated['pixel_tracker_id'] = null;
        }
        if (empty($validated['zone_id'])) {
            $validated['zone_id'] = null;
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

        $initialCreativeStatus = PlatformSetting::getCreativeInitialStatus();

        foreach ($originalAdFormats as $index => $af) {
            $format = $af['format'] ?? '';
            $contentType = $af['content_type'] ?? 'image';
            $adType = $this->mapAdType($af['ad_type'] ?? '', $contentType, $format);
            $displayScreenId = !empty($af['display_screen_id']) ? (int) $af['display_screen_id'] : null;
            $displayScreen = null;
            if ($displayScreenId) {
                $displayScreen = DisplayScreen::query()
                    ->where('status', 'active')
                    ->find($displayScreenId);
            }

            // Build Ad record data
            $adData = [
                'campaign_id' => $campaign->id,
                'name' => $af['ad_name'] ?? 'Untitled Creative',
                'ad_type' => $adType,
                'status' => $initialCreativeStatus,
                'destination_url' => $af['ad_url'] ?? '',
                'display_url' => parse_url($af['ad_url'] ?? '', PHP_URL_HOST) ?: null,
                'admin_approved' => $initialCreativeStatus === 'active',
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
            if ($displayScreen) {
                $width = $displayScreen->width;
                $height = $displayScreen->height;
                $dimension = $width . 'x' . $height;
            } elseif (str_contains($dimension, 'x')) {
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
                'display_screen_id' => $displayScreen?->id,
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
            $af['dimension'] = $dimension;
            $af['display_screen_id'] = $displayScreen?->id;
            $af['display_screen_name'] = $displayScreen?->screen_name;
            if ($displayScreen) {
                $af['format'] = 'display_web';
                $af['format_label'] = $displayScreen->screen_name;
            }
            $updatedAdFormats[] = $af;
        }

        return $updatedAdFormats;
    }
}

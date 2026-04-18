<?php

namespace App\Support;

use App\Models\PlatformSetting;
use Illuminate\Http\Request;

class ActivityLogSettings
{
    public static function sections(): array
    {
        return [
            'campaign_settings' => [
                'title' => 'Campaign Settings',
                'items' => [
                    'campaign_approved' => 'Campaign Approved',
                    'campaign_rejected' => 'Campaign Rejected',
                    'campaign_create' => 'Campaign Create',
                    'campaign_edit' => 'Campaign Edit',
                    'campaign_delete' => 'Campaign Delete',
                    'campaign_activated' => 'Campaign Activated',
                    'campaign_deactivated' => 'Campaign Deactivated',
                    'campaign_moved' => 'Campaign Moved',
                    'campaign_duplicated' => 'Campaign Duplicated',
                ],
            ],
            'creative_settings' => [
                'title' => 'Creative Settings',
                'items' => [
                    'creative_approved' => 'Creative Approved',
                    'creative_rejected' => 'Creative Rejected',
                    'creative_added' => 'Creative Added',
                    'creative_edited' => 'Creative Edited',
                    'creative_deleted' => 'Creative Deleted',
                ],
            ],
            'payment_settings' => [
                'title' => 'Payment Settings',
                'items' => [
                    'payment_approved' => 'Payment Approved',
                    'payment_rejected' => 'Payment Rejected',
                    'payment_add' => 'Payment Add',
                    'payment_requested' => 'Payment Requested',
                ],
            ],
            'network_settings' => [
                'title' => 'Network Settings',
                'items' => [
                    'pixel_tracker_added' => 'Pixel Tracker Added',
                    'pixel_tracker_edited' => 'Pixel Tracker Edited',
                    'pixel_tracker_deleted' => 'Pixel Tracker Deleted',
                    'traffic_sources_create' => 'Create Traffic Sources',
                    'traffic_sources_remove' => 'Remove Traffic Sources',
                    'zonelimitation_edit' => 'Zonelimitation Edit',
                    'zonelimitation_delete' => 'Zonelimitation Delete',
                    'country_bidding_add' => 'Country Bidding Add',
                    'country_bidding_edit' => 'Country Bidding Edit',
                    'country_bidding_delete' => 'Country Bidding Delete',
                ],
            ],
            'settings_information' => [
                'title' => 'Settings Information',
                'items' => [
                    'login' => 'Login',
                    'forget_password' => 'Forget Password',
                    'personal_information' => 'Personal Information',
                    'company_information' => 'Company Information',
                    'settings_payment_settings' => 'Payment Settings',
                    'account_settings' => 'Account Settings',
                    'notification_settings' => 'Notification Settings',
                    'security' => 'Security',
                    'user_block' => 'User Block',
                    'user_unblock' => 'User Unblock',
                ],
            ],
            'site_settings' => [
                'title' => 'Site Settings',
                'items' => [
                    'site_add' => 'Site Add',
                    'site_edit' => 'Site Edit',
                    'site_delete' => 'Site Delete',
                ],
            ],
            'app_settings' => [
                'title' => 'App Settings',
                'items' => [
                    'app_add' => 'App Add',
                    'app_edit' => 'App Edit',
                    'app_delete' => 'App Delete',
                ],
            ],
            'adblock_settings' => [
                'title' => 'Adblock Settings',
                'items' => [
                    'adblock_add_update' => 'Adblock Add/Update',
                    'adblock_update' => 'Adblcok Update',
                    'adblock_delete' => 'Adblcok Delete',
                ],
            ],
            'network_users' => [
                'title' => 'Network Users',
                'items' => [
                    'manager_add' => 'Manager Add',
                    'manager_edit' => 'Manager Edit',
                    'manager_block' => 'Manager Block',
                    'manager_unblock' => 'Manager Unblock',
                    'advertiser_add' => 'Advertiser Add',
                    'advertiser_edit' => 'Advertiser Edit',
                    'advertiser_delete' => 'Advertiser Delete',
                    'publisher_add' => 'Publisher Add',
                    'publisher_edit' => 'Publisher Edit',
                    'publisher_delete' => 'Publisher Delete',
                    'rtb_add' => 'RTB Add',
                    'rtb_edit' => 'RTB Edit',
                    'rtb_block' => 'RTB Block',
                    'rtb_unblock' => 'RTB Unblock',
                ],
            ],
            'approvals' => [
                'title' => 'Approvals',
                'items' => [
                    'advertiser_approval' => 'Advertiser Approval',
                    'advertiser_reject' => 'Advertiser Reject',
                    'publisher_approval' => 'Publisher Approval',
                    'publisher_reject' => 'Publisher Reject',
                    'publisher_payment_approval' => 'Publisher Payment Approval',
                    'direct_campaign_payment_approval' => 'Direct Campaign Payment Approval',
                    'direct_campaign_approval' => 'Direct Campaign Approval',
                    'direct_campaign_reject' => 'Direct Campaign Reject',
                    'mobile_application_approval' => 'Mobile Application Approval',
                    'mobile_application_reject' => 'Mobile Application Reject',
                ],
            ],
            'category_targeting' => [
                'title' => 'Category Targeting',
                'items' => [
                    'sub_category_add' => 'Sub Category Add',
                    'sub_category_edit' => 'Sub Category Edit',
                    'sub_category_delete' => 'Sub Category Delete',
                    'sub_category_block' => 'Sub Category Block',
                    'sub_category_unblock' => 'Sub Category Unblock',
                    'main_category_add' => 'Main Category Add',
                    'main_category_edit' => 'Main Category Edit',
                    'main_category_delete' => 'Main Category Delete',
                    'main_category_block' => 'Main Category Block',
                    'main_category_unblock' => 'Main Category Unblock',
                ],
            ],
            'devices_and_operating_systems' => [
                'title' => 'Devices and Operating Systems',
                'items' => [
                    'devices_add' => 'Devices Add',
                    'devices_delete' => 'Devices Delete',
                    'devices_block' => 'Devices Block',
                    'devices_unblock' => 'Devices Unblock',
                    'os_add' => 'OS Add',
                    'os_edit' => 'OS Edit',
                    'os_delete' => 'OS Delete',
                    'os_block' => 'OS Block',
                    'os_unblock' => 'OS Unblock',
                ],
            ],
            'browser_and_languages' => [
                'title' => 'Browser & Languages',
                'items' => [
                    'browser_add' => 'Browser Add',
                    'browser_delete' => 'Browser Delete',
                    'browser_block' => 'Browser Block',
                    'browser_unblock' => 'Browser Unblock',
                    'browser_language_add' => 'Browser Language Add',
                    'browser_language_edit' => 'Browser Language Edit',
                    'browser_language_delete' => 'Browser Language Delete',
                    'browser_language_block' => 'Browser Language Block',
                    'browser_language_unblock' => 'Browser Language Unblock',
                ],
            ],
            'manufactures_and_capabilities' => [
                'title' => 'Manufactures & Capabilities',
                'items' => [
                    'manufacture_add' => 'Manufacture Add',
                    'manufacture_edit' => 'Manufacture Edit',
                    'manufacture_delete' => 'Manufacture Delete',
                    'manufacture_block' => 'Manufacture Block',
                    'manufacture_unblock' => 'Manufacture Unblock',
                    'capability_add' => 'Capablity Add',
                    'capability_edit' => 'Capablity Edit',
                    'capability_delete' => 'Capablity Delete',
                    'capability_block' => 'Capablity Block',
                    'capability_unblock' => 'Capablity Unblock',
                ],
            ],
            'connection_and_isp' => [
                'title' => 'Connection & ISP/Carrier',
                'items' => [
                    'connection_add' => 'Connection Add',
                    'connection_edit' => 'Connection Edit',
                    'connection_delete' => 'Connection Delete',
                    'connection_block' => 'Connection Block',
                    'connection_unblock' => 'Connection Unblock',
                    'isp_carrier_add' => 'ISP/Carrier Add',
                    'isp_carrier_edit' => 'ISP/Carrier Edit',
                    'isp_carrier_delete' => 'ISP/Carrier Delete',
                    'isp_carrier_block' => 'ISP/Carrier Block',
                    'isp_carrier_unblock' => 'ISP/Carrier Unblock',
                ],
            ],
            'contextual_targeting' => [
                'title' => 'Contextual Targeting',
                'items' => [
                    'keyword_add' => 'Keyword Add',
                    'keyword_edit' => 'Keyword Edit',
                    'keyword_delete' => 'Keyword Delete',
                    'keyword_block' => 'Keyword Block',
                    'keyword_unblock' => 'Keyword Unblock',
                ],
            ],
            'admin_settings' => [
                'title' => 'Admin Settings',
                'items' => [
                    'billing_information' => 'Billing Information',
                    'user_roles' => 'User Roles',
                    'admin_payment_settings' => 'Payment Settings',
                    'geo_cpm_add' => 'Geo CPM Add',
                    'geo_cpm_edit' => 'Geo CPM Edit',
                    'geo_cpm_delete' => 'Geo CPM Delete',
                    'geo_cpm_block' => 'Geo CPM Block',
                    'geo_cpm_unblock' => 'Geo CPM Unblock',
                    'display_screen_add' => 'Display Screen Add',
                    'display_screen_edit' => 'Display Screen Edit',
                    'display_screen_block' => 'Display Screen Block',
                    'display_screen_unblock' => 'Display Screen Unblock',
                ],
            ],
        ];
    }

    public static function allKeys(): array
    {
        $keys = [];

        foreach (self::sections() as $section) {
            foreach ($section['items'] as $key => $label) {
                $keys[$key] = $label;
            }
        }

        return $keys;
    }

    public static function defaults(): array
    {
        return array_fill_keys(array_keys(self::allKeys()), true);
    }

    public static function getSettings(): array
    {
        $stored = PlatformSetting::getValue('activity_log_settings', []);
        $stored = is_array($stored) ? $stored : [];

        return array_merge(
            self::defaults(),
            array_intersect_key($stored, self::defaults()),
        );
    }

    public static function store(array $settings, ?int $updatedBy = null): void
    {
        PlatformSetting::setValue(
            'activity_log_settings',
            array_replace(self::defaults(), $settings),
            'json',
            'activity_log',
            'Enable or disable individual admin activity log events',
            $updatedBy,
        );
    }

    public static function isEnabled(Request $request, array $overrides = []): bool
    {
        $key = self::resolveKey($request, $overrides);

        if ($key === null) {
            return true;
        }

        $settings = self::getSettings();

        return (bool) ($settings[$key] ?? true);
    }

    public static function resolveKey(Request $request, array $overrides = []): ?string
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        $action = (string) ($overrides['action'] ?? '');

        if ($action === 'auth_login') {
            return 'login';
        }

        if ($action === 'auth_logout') {
            return 'security';
        }

        if ($action === 'auth_two_factor_verified') {
            return 'security';
        }

        return match (true) {
            $routeName === 'admin.campaign-approvals.approve' => 'campaign_approved',
            $routeName === 'admin.campaign-approvals.reject' => 'campaign_rejected',
            $routeName === 'admin.campaigns.store' => 'campaign_create',
            $routeName === 'admin.campaigns.update' => 'campaign_edit',
            $routeName === 'admin.campaigns.destroy' => 'campaign_delete',
            $routeName === 'admin.campaigns.updateStatus' => self::campaignStatusKey($request),
            $routeName === 'admin.campaigns.moveToGroup' => 'campaign_moved',
            $routeName === 'admin.campaigns.duplicate' => 'campaign_duplicated',
            $routeName === 'admin.creative-approvals.approve' => 'creative_approved',
            $routeName === 'admin.creative-approvals.reject' => 'creative_rejected',
            $routeName === 'admin.adformats.store' => 'creative_added',
            $routeName === 'admin.adformats.update' => 'creative_edited',
            $routeName === 'admin.adformats.destroy' => 'creative_deleted',
            in_array($routeName, [
                'admin.advertiser-payment-approvals.approve',
                'admin.publisher-payment-approvals.approve',
                'admin.payouts.approve',
                'admin.publisher-invoices.approve',
            ], true) => 'payment_approved',
            in_array($routeName, [
                'admin.advertiser-payment-approvals.reject',
                'admin.payouts.reject',
                'admin.direct-campaign-approvals.reject',
            ], true) => 'payment_rejected',
            $routeName === 'admin.advertiser-deposits.store' => 'payment_add',
            $routeName === 'admin.payment-settings.update' => 'settings_payment_settings',
            in_array($routeName, ['admin.pixel-trackers.store', 'admin.pixel-trackers.update', 'admin.pixel-trackers.destroy'], true) => self::pixelTrackerKey($routeName),
            in_array($routeName, ['admin.traffic-sources.store', 'admin.traffic-sources.destroy'], true) => $routeName === 'admin.traffic-sources.store' ? 'traffic_sources_create' : 'traffic_sources_remove',
            in_array($routeName, ['admin.zone-limitations.update', 'admin.zone-limitations.destroy'], true) => $routeName === 'admin.zone-limitations.update' ? 'zonelimitation_edit' : 'zonelimitation_delete',
            in_array($routeName, ['admin.country-wise-bidding.store', 'admin.country-wise-bidding.update', 'admin.country-wise-bidding.destroy'], true) => self::countryBiddingKey($routeName),
            $routeName === 'admin.personal-information.update' => 'personal_information',
            $routeName === 'admin.company-information.update' => 'company_information',
            $routeName === 'admin.billing-information.update' => 'billing_information',
            $routeName === 'admin.account-settings.update' => self::accountSettingsKey($request),
            in_array($routeName, ['admin.display-screens.store', 'admin.display-screens.update', 'admin.display-screens.block', 'admin.display-screens.unblock'], true) => self::displayScreenKey($routeName),
            str_starts_with($routeName, 'admin.activity-log-settings') => 'notification_settings',
            in_array($routeName, ['admin.sites.store', 'admin.sites.update', 'admin.sites.destroy'], true) => self::siteKey($routeName),
            in_array($routeName, ['admin.mobile-applications.store', 'admin.mobile-applications.update', 'admin.mobile-applications.destroy'], true) => self::appKey($routeName),
            in_array($routeName, ['admin.adblocks.store', 'admin.adblocks.update', 'admin.adblocks.destroy'], true) => self::adblockKey($routeName),
            in_array($routeName, ['admin.managers.store', 'admin.managers.update', 'admin.managers.block', 'admin.managers.unblock'], true) => self::managerKey($routeName),
            in_array($routeName, ['admin.advertisers.store', 'admin.advertisers.update', 'admin.advertisers.destroy', 'admin.advertisers.block', 'admin.advertisers.unblock'], true) => self::advertiserKey($routeName),
            in_array($routeName, ['admin.publishers.store', 'admin.publishers.update', 'admin.publishers.destroy', 'admin.publishers.block', 'admin.publishers.unblock'], true) => self::publisherKey($routeName),
            in_array($routeName, ['admin.rtb.store', 'admin.rtb.update', 'admin.rtb.block', 'admin.rtb.unblock'], true) => self::rtbKey($routeName),
            in_array($routeName, ['admin.advertiser-approvals.approve', 'admin.advertiser-approvals.reject'], true) => $routeName === 'admin.advertiser-approvals.approve' ? 'advertiser_approval' : 'advertiser_reject',
            in_array($routeName, ['admin.publisher-approvals.approve', 'admin.publisher-approvals.reject'], true) => $routeName === 'admin.publisher-approvals.approve' ? 'publisher_approval' : 'publisher_reject',
            $routeName === 'admin.publisher-payment-approvals.approve' => 'publisher_payment_approval',
            $routeName === 'admin.direct-campaign-approvals.approve' => 'direct_campaign_approval',
            in_array($routeName, ['admin.mobile-application-approvals.approve', 'admin.mobile-application-approvals.reject'], true) => $routeName === 'admin.mobile-application-approvals.approve' ? 'mobile_application_approval' : 'mobile_application_reject',
            in_array($routeName, ['admin.parent-categories.store', 'admin.parent-categories.update', 'admin.parent-categories.destroy', 'admin.parent-categories.block', 'admin.parent-categories.unblock'], true) => self::parentCategoryKey($routeName),
            in_array($routeName, ['admin.categories.store', 'admin.categories.update', 'admin.categories.destroy', 'admin.categories.block', 'admin.categories.unblock'], true) => self::categoryKey($routeName),
            in_array($routeName, ['admin.devices.store', 'admin.devices.destroy', 'admin.devices.block', 'admin.devices.unblock'], true) => self::deviceKey($routeName),
            in_array($routeName, ['admin.operating-systems.store', 'admin.operating-systems.update', 'admin.operating-systems.destroy', 'admin.operating-systems.block', 'admin.operating-systems.unblock'], true) => self::osKey($routeName),
            in_array($routeName, ['admin.browsers.store', 'admin.browsers.destroy', 'admin.browsers.block', 'admin.browsers.unblock'], true) => self::browserKey($routeName),
            in_array($routeName, ['admin.browser-languages.store', 'admin.browser-languages.update', 'admin.browser-languages.destroy', 'admin.browser-languages.block', 'admin.browser-languages.unblock'], true) => self::browserLanguageKey($routeName),
            in_array($routeName, ['admin.mobile-manufacturers.store', 'admin.mobile-manufacturers.update', 'admin.mobile-manufacturers.destroy', 'admin.mobile-manufacturers.block', 'admin.mobile-manufacturers.unblock'], true) => self::manufacturerKey($routeName),
            in_array($routeName, ['admin.mobile-capabilities.store', 'admin.mobile-capabilities.update', 'admin.mobile-capabilities.destroy', 'admin.mobile-capabilities.block', 'admin.mobile-capabilities.unblock'], true) => self::capabilityKey($routeName),
            in_array($routeName, ['admin.connection-types.store', 'admin.connection-types.update', 'admin.connection-types.destroy', 'admin.connection-types.block', 'admin.connection-types.unblock'], true) => self::connectionKey($routeName),
            in_array($routeName, ['admin.carrier-isp-connections.store', 'admin.carrier-isp-connections.update', 'admin.carrier-isp-connections.destroy', 'admin.carrier-isp-connections.block', 'admin.carrier-isp-connections.unblock'], true) => self::carrierKey($routeName),
            in_array($routeName, ['admin.keywords.store', 'admin.keywords.update', 'admin.keywords.destroy', 'admin.keywords.block', 'admin.keywords.unblock'], true) => self::keywordKey($routeName),
            default => null,
        };
    }

    private static function campaignStatusKey(Request $request): string
    {
        $status = strtolower((string) ($request->input('status') ?? $request->input('campaign_status') ?? ''));

        return in_array($status, ['paused', 'inactive', 'disabled', 'deactivated'], true)
            ? 'campaign_deactivated'
            : 'campaign_activated';
    }

    private static function pixelTrackerKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.pixel-trackers.store' => 'pixel_tracker_added',
            'admin.pixel-trackers.update' => 'pixel_tracker_edited',
            default => 'pixel_tracker_deleted',
        };
    }

    private static function countryBiddingKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.country-wise-bidding.store' => 'country_bidding_add',
            'admin.country-wise-bidding.update' => 'country_bidding_edit',
            default => 'country_bidding_delete',
        };
    }

    private static function accountSettingsKey(Request $request): string
    {
        return $request->hasAny(['current_password', 'new_password', 'password', 'two_factor_enabled'])
            ? 'security'
            : 'account_settings';
    }

    private static function displayScreenKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.display-screens.store' => 'display_screen_add',
            'admin.display-screens.update' => 'display_screen_edit',
            'admin.display-screens.block' => 'display_screen_block',
            default => 'display_screen_unblock',
        };
    }

    private static function siteKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.sites.store' => 'site_add',
            'admin.sites.update' => 'site_edit',
            default => 'site_delete',
        };
    }

    private static function appKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.mobile-applications.store' => 'app_add',
            'admin.mobile-applications.update' => 'app_edit',
            default => 'app_delete',
        };
    }

    private static function adblockKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.adblocks.store' => 'adblock_add_update',
            'admin.adblocks.update' => 'adblock_update',
            default => 'adblock_delete',
        };
    }

    private static function managerKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.managers.store' => 'manager_add',
            'admin.managers.update' => 'manager_edit',
            'admin.managers.block' => 'manager_block',
            default => 'manager_unblock',
        };
    }

    private static function advertiserKey(string $routeName): ?string
    {
        return match ($routeName) {
            'admin.advertisers.store' => 'advertiser_add',
            'admin.advertisers.update' => 'advertiser_edit',
            'admin.advertisers.destroy' => 'advertiser_delete',
            'admin.advertisers.block' => 'user_block',
            'admin.advertisers.unblock' => 'user_unblock',
            default => null,
        };
    }

    private static function publisherKey(string $routeName): ?string
    {
        return match ($routeName) {
            'admin.publishers.store' => 'publisher_add',
            'admin.publishers.update' => 'publisher_edit',
            'admin.publishers.destroy' => 'publisher_delete',
            'admin.publishers.block' => 'user_block',
            'admin.publishers.unblock' => 'user_unblock',
            default => null,
        };
    }

    private static function rtbKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.rtb.store' => 'rtb_add',
            'admin.rtb.update' => 'rtb_edit',
            'admin.rtb.block' => 'rtb_block',
            default => 'rtb_unblock',
        };
    }

    private static function parentCategoryKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.parent-categories.store' => 'main_category_add',
            'admin.parent-categories.update' => 'main_category_edit',
            'admin.parent-categories.destroy' => 'main_category_delete',
            'admin.parent-categories.block' => 'main_category_block',
            default => 'main_category_unblock',
        };
    }

    private static function categoryKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.categories.store' => 'sub_category_add',
            'admin.categories.update' => 'sub_category_edit',
            'admin.categories.destroy' => 'sub_category_delete',
            'admin.categories.block' => 'sub_category_block',
            default => 'sub_category_unblock',
        };
    }

    private static function deviceKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.devices.store' => 'devices_add',
            'admin.devices.destroy' => 'devices_delete',
            'admin.devices.block' => 'devices_block',
            default => 'devices_unblock',
        };
    }

    private static function osKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.operating-systems.store' => 'os_add',
            'admin.operating-systems.update' => 'os_edit',
            'admin.operating-systems.destroy' => 'os_delete',
            'admin.operating-systems.block' => 'os_block',
            default => 'os_unblock',
        };
    }

    private static function browserKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.browsers.store' => 'browser_add',
            'admin.browsers.destroy' => 'browser_delete',
            'admin.browsers.block' => 'browser_block',
            default => 'browser_unblock',
        };
    }

    private static function browserLanguageKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.browser-languages.store' => 'browser_language_add',
            'admin.browser-languages.update' => 'browser_language_edit',
            'admin.browser-languages.destroy' => 'browser_language_delete',
            'admin.browser-languages.block' => 'browser_language_block',
            default => 'browser_language_unblock',
        };
    }

    private static function manufacturerKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.mobile-manufacturers.store' => 'manufacture_add',
            'admin.mobile-manufacturers.update' => 'manufacture_edit',
            'admin.mobile-manufacturers.destroy' => 'manufacture_delete',
            'admin.mobile-manufacturers.block' => 'manufacture_block',
            default => 'manufacture_unblock',
        };
    }

    private static function capabilityKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.mobile-capabilities.store' => 'capability_add',
            'admin.mobile-capabilities.update' => 'capability_edit',
            'admin.mobile-capabilities.destroy' => 'capability_delete',
            'admin.mobile-capabilities.block' => 'capability_block',
            default => 'capability_unblock',
        };
    }

    private static function connectionKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.connection-types.store' => 'connection_add',
            'admin.connection-types.update' => 'connection_edit',
            'admin.connection-types.destroy' => 'connection_delete',
            'admin.connection-types.block' => 'connection_block',
            default => 'connection_unblock',
        };
    }

    private static function carrierKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.carrier-isp-connections.store' => 'isp_carrier_add',
            'admin.carrier-isp-connections.update' => 'isp_carrier_edit',
            'admin.carrier-isp-connections.destroy' => 'isp_carrier_delete',
            'admin.carrier-isp-connections.block' => 'isp_carrier_block',
            default => 'isp_carrier_unblock',
        };
    }

    private static function keywordKey(string $routeName): string
    {
        return match ($routeName) {
            'admin.keywords.store' => 'keyword_add',
            'admin.keywords.update' => 'keyword_edit',
            'admin.keywords.destroy' => 'keyword_delete',
            'admin.keywords.block' => 'keyword_block',
            default => 'keyword_unblock',
        };
    }
}

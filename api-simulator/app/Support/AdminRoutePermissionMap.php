<?php

namespace App\Support;

use Illuminate\Http\Request;

class AdminRoutePermissionMap
{
    public static function resolve(Request $request): ?string
    {
        $routeName = (string) ($request->route()?->getName() ?? '');

        return match (true) {
            $routeName === 'admin.dashboard' => null,

            str_starts_with($routeName, 'admin.manage-admarket-campaigns') => self::inventoryAction($routeName, 'inventory.ads_campaigns'),
            str_starts_with($routeName, 'admin.campaigns') => self::inventoryAction($routeName, 'inventory.campaigns'),
            str_starts_with($routeName, 'admin.direct-campaigns') => self::inventoryAction($routeName, 'inventory.ads_campaigns'),

            str_starts_with($routeName, 'admin.advertisers') => self::advertiserAction($routeName),
            str_starts_with($routeName, 'admin.publishers') => self::publisherAction($routeName),
            str_starts_with($routeName, 'admin.adblocks') => self::adblockAction($routeName),
            str_starts_with($routeName, 'admin.sites') => self::inventoryAction($routeName, 'inventory.sites'),
            str_starts_with($routeName, 'admin.adformats') => self::creativeAction($routeName),
            str_starts_with($routeName, 'admin.creative-approvals') => 'approvals.permissions.creative',

            str_starts_with($routeName, 'admin.advertiser-approvals') => 'approvals.permissions.advertiser',
            str_starts_with($routeName, 'admin.publisher-approvals') => 'approvals.permissions.publisher',
            str_starts_with($routeName, 'admin.campaign-approvals') => 'approvals.permissions.creative',
            str_starts_with($routeName, 'admin.direct-campaign-approvals') => 'approvals.permissions.creative',
            str_starts_with($routeName, 'admin.advertiser-payment-approvals') => 'approvals.permissions.advertiser_payment',
            str_starts_with($routeName, 'admin.publisher-payment-approvals') => 'approvals.permissions.publisher_payment',
            str_starts_with($routeName, 'admin.mobile-application-approvals') => 'approvals.permissions.publisher',
            str_starts_with($routeName, 'admin.requests') => 'approvals.permissions.creative',
            str_starts_with($routeName, 'admin.direct-campaign-request-approvals') => 'approvals.permissions.creative',

            str_starts_with($routeName, 'admin.advertiser-payment-history') => 'payments.permissions.advertiser_payment_history',
            str_starts_with($routeName, 'admin.publisher-payment-history') => 'payments.permissions.publisher_payment_history',
            str_starts_with($routeName, 'admin.publisher-invoices') => 'payments.permissions.publisher_invoice',
            str_starts_with($routeName, 'admin.balance-sheet') => 'payments.permissions.your_balance_sheet',
            str_starts_with($routeName, 'admin.advertiser-deposits') => 'payments.permissions.advertiser_deposits',

            str_starts_with($routeName, 'admin.reports.environment') => 'reports.permissions.view_enviroment_report',
            str_starts_with($routeName, 'admin.reports.graphical') => 'reports.permissions.view_geographical_report',
            str_starts_with($routeName, 'admin.reports.advertiser') => 'reports.permissions.advertiser_report',
            str_starts_with($routeName, 'admin.reports.publisher') => 'reports.permissions.publisher_report',
            str_starts_with($routeName, 'admin.reports.site') => 'reports.permissions.publisher_report',
            str_starts_with($routeName, 'admin.reports.adblock') => 'reports.permissions.publisher_report',
            str_starts_with($routeName, 'admin.reports.creative') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.reports.campaign') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.reports.platform') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.reports.requests') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.reports.dsp') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.reports.ssp') => 'reports.permissions.your_revneue_related_statistics',
            str_starts_with($routeName, 'admin.network-kit') => 'reports.permissions.your_revneue_related_statistics',

            str_starts_with($routeName, 'admin.anti-fraud') => 'analystics.permissions.view_ant_fraud_clicks',

            str_starts_with($routeName, 'admin.parent-categories') => 'targetting.permissions.category',
            str_starts_with($routeName, 'admin.categories') => 'targetting.permissions.category',
            str_starts_with($routeName, 'admin.operating-systems') => 'targetting.permissions.operating_system',
            str_starts_with($routeName, 'admin.browsers') => 'targetting.permissions.browsers',
            str_starts_with($routeName, 'admin.browser-languages') => 'targetting.permissions.browsers',
            str_starts_with($routeName, 'admin.devices') => 'targetting.permissions.mobile.mobile_operating_system',
            str_starts_with($routeName, 'admin.mobile-manufacturers') => 'targetting.permissions.mobile.manufactures',
            str_starts_with($routeName, 'admin.mobile-capabilities') => 'targetting.permissions.mobile.capability',
            str_starts_with($routeName, 'admin.connection-types') => 'targetting.permissions.mobile.capability',
            str_starts_with($routeName, 'admin.carrier-isp-connections') => 'targetting.permissions.mobile.capability',
            str_starts_with($routeName, 'admin.keywords') => 'targetting.permissions.contextual.keyword',

            default => null,
        };
    }

    private static function inventoryAction(string $routeName, string $base): string
    {
        return match (true) {
            str_ends_with($routeName, '.destroy') => $base . '.delete',
            str_contains($routeName, '.block'),
            str_contains($routeName, '.unblock') => $base . '.blcok_unblock',
            str_ends_with($routeName, '.store'),
            str_ends_with($routeName, '.create'),
            str_ends_with($routeName, '.edit'),
            str_ends_with($routeName, '.update'),
            str_contains($routeName, '.status'),
            str_contains($routeName, '.duplicate'),
            str_contains($routeName, '.group'),
            str_contains($routeName, '.pixels'),
            str_contains($routeName, '.disallow') => $base . '.add_edit',
            default => $base . '.read_list',
        };
    }

    private static function advertiserAction(string $routeName): string
    {
        return match (true) {
            str_ends_with($routeName, '.destroy') => 'inventory.advertisers.delete',
            str_contains($routeName, '.block'),
            str_contains($routeName, '.unblock') => 'inventory.advertisers.blcok_unblock',
            str_ends_with($routeName, '.store'),
            str_ends_with($routeName, '.update'),
            str_ends_with($routeName, '.notify'),
            str_ends_with($routeName, '.loginAs') => 'inventory.advertisers.add_edit',
            default => 'inventory.advertisers.read_list',
        };
    }

    private static function publisherAction(string $routeName): string
    {
        return match (true) {
            str_ends_with($routeName, '.destroy') => 'inventory.publisher.delete',
            str_contains($routeName, '.block'),
            str_contains($routeName, '.unblock') => 'inventory.publisher.blcok_unblock',
            str_ends_with($routeName, '.store'),
            str_ends_with($routeName, '.update'),
            str_ends_with($routeName, '.notify'),
            str_ends_with($routeName, '.loginAs') => 'inventory.publisher.add_edit',
            default => 'inventory.publisher.read_list',
        };
    }

    private static function creativeAction(string $routeName): string
    {
        return match (true) {
            str_ends_with($routeName, '.destroy') => 'inventory.creatives.web_mobile.delete',
            str_ends_with($routeName, '.store'),
            str_ends_with($routeName, '.create'),
            str_ends_with($routeName, '.edit'),
            str_ends_with($routeName, '.update') => 'inventory.creatives.web_mobile.add_edit',
            default => 'inventory.creatives.web_mobile.read_preview',
        };
    }

    private static function adblockAction(string $routeName): string
    {
        return match (true) {
            str_ends_with($routeName, '.destroy') => 'inventory.adblocks.delete',
            str_ends_with($routeName, '.store'),
            str_ends_with($routeName, '.update') => 'inventory.adblocks.add_edit',
            str_contains($routeName, '.sdk'),
            str_contains($routeName, '.script'),
            str_contains($routeName, '.tag') => 'inventory.adblocks.mobile_sdk',
            default => 'inventory.adblocks.read_list',
        };
    }
}

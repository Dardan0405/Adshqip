<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppConfigurationController extends Controller
{
    public function show(Request $request)
    {
        return view('admin.app-configurations', [
            'user' => $request->user()->load('profile'),
            'creativeApprovalTypes' => [
                PlatformSetting::CREATIVE_APPROVAL_MANUAL => 'Manual Approve',
                PlatformSetting::CREATIVE_APPROVAL_AUTO => 'Auto Approve',
            ],
            'advertiserApprovalTypes' => [
                PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION => 'Email Verification',
                PlatformSetting::ADVERTISER_APPROVAL_ADMIN => 'Approve By Admin',
            ],
            'publisherApprovalTypes' => [
                PlatformSetting::PUBLISHER_APPROVAL_EMAIL_VERIFICATION => 'Email Verification',
                PlatformSetting::PUBLISHER_APPROVAL_ADMIN => 'Approve By Admin',
            ],
            'messageDeliveryModes' => [
                PlatformSetting::MESSAGE_DELIVERY_DEFAULT => 'Default',
                PlatformSetting::MESSAGE_DELIVERY_SEND_MESSAGE => 'Send Message',
                PlatformSetting::MESSAGE_DELIVERY_SEND_EMAIL => 'Send Email',
            ],
            'advertiserPaymentTypes' => PlatformSetting::getAdvertiserPaymentTypes(),
            'campaignCreativeTypes' => PlatformSetting::getCampaignCreativeTypes(),
            'settings' => [
                'creative_minimum_impressions_per_day' => PlatformSetting::getCreativeMinimumImpressionsPerDay(),
                'creative_approval_type' => PlatformSetting::getCreativeApprovalType(),
                'anti_fraud_clicks_enabled' => PlatformSetting::getAntiFraudClicksEnabled(),
                'anti_fraud_reset_counter_seconds' => PlatformSetting::getAntiFraudResetCounterSeconds(),
                'anti_fraud_threshold_clicks' => PlatformSetting::getAntiFraudThresholdClicks(),
                'terawurl_mobile_tracking_url' => PlatformSetting::getTerawurlMobileTrackingUrl(),
                'jw_player_license_key' => PlatformSetting::getJwPlayerLicenseKey(),
                'advertiser_approval_type' => PlatformSetting::getAdvertiserApprovalType(),
                'advertiser_payment_type' => PlatformSetting::getAdvertiserPaymentType(),
                'campaign_minimum_budget' => PlatformSetting::getCampaignMinimumBudget(),
                'campaign_minimum_bid_rate' => PlatformSetting::getCampaignMinimumBidRate(),
                'campaign_creative_type' => PlatformSetting::getCampaignCreativeType(),
                'publisher_approval_type' => PlatformSetting::getPublisherApprovalType(),
                'message_delivery_mode' => PlatformSetting::getMessageDeliveryMode(),
                'advertiser_messages_enabled' => PlatformSetting::getAdvertiserMessagesEnabled(),
                'publisher_messages_enabled' => PlatformSetting::getPublisherMessagesEnabled(),
                'publisher_floor_price_enabled' => PlatformSetting::getPublisherFloorPriceEnabled(),
                'publisher_minimum_floor_price_percent' => PlatformSetting::getPublisherMinimumFloorPricePercent(),
                'publisher_minimum_amount_for_invoice_generation' => PlatformSetting::getPublisherMinimumAmountForInvoiceGeneration(),
                'publisher_auto_invoice_enabled' => PlatformSetting::getPublisherAutoInvoiceEnabled(),
                'publisher_minimum_payment_amount' => PlatformSetting::getPublisherMinimumPaymentAmount(),
                'video_ads_enabled' => PlatformSetting::getVideoAdsEnabled(),
                'mobile_ads_enabled' => PlatformSetting::getMobileAdsEnabled(),
                'banner_autoload_enabled' => PlatformSetting::getBannerAutoloadEnabled(),
                'revive_memcache_enabled' => PlatformSetting::getReviveMemcacheEnabled(),
                'adshqip_memcache_enabled' => PlatformSetting::getAdshqipMemcacheEnabled(),
                'hide_url_enabled' => PlatformSetting::getHideUrlEnabled(),
                'encryption_enabled' => PlatformSetting::getEncryptionEnabled(),
                'url_encoding_enabled' => PlatformSetting::getUrlEncodingEnabled(),
                'report_url_ads_enabled' => PlatformSetting::getReportUrlAdsEnabled(),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'creative_minimum_impressions_per_day' => 'required|integer|min:0|max:100000000',
            'creative_approval_type' => [
                'required',
                Rule::in([
                    PlatformSetting::CREATIVE_APPROVAL_MANUAL,
                    PlatformSetting::CREATIVE_APPROVAL_AUTO,
                ]),
            ],
            'anti_fraud_clicks_enabled' => 'nullable|boolean',
            'anti_fraud_reset_counter_seconds' => 'required|integer|min:1|max:86400',
            'anti_fraud_threshold_clicks' => 'required|integer|min:1|max:100000',
            'terawurl_mobile_tracking_url' => ['nullable', 'string', 'max:255'],
            'jw_player_license_key' => 'nullable|string|max:255',
            'advertiser_approval_type' => [
                'required',
                Rule::in([
                    PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION,
                    PlatformSetting::ADVERTISER_APPROVAL_ADMIN,
                ]),
            ],
            'advertiser_payment_type' => [
                'required',
                Rule::in(array_keys(PlatformSetting::getAdvertiserPaymentTypes())),
            ],
            'campaign_minimum_budget' => 'required|numeric|min:0|max:100000000',
            'campaign_minimum_bid_rate' => 'required|numeric|min:0|max:100000',
            'campaign_creative_type' => [
                'required',
                Rule::in([
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_IMAGE,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_HTML,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_VIDEO,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_TEXT,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_RICH_MEDIA,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_NATIVE,
                    PlatformSetting::CAMPAIGN_CREATIVE_TYPE_VAST,
                ]),
            ],
            'publisher_approval_type' => [
                'required',
                Rule::in([
                    PlatformSetting::PUBLISHER_APPROVAL_EMAIL_VERIFICATION,
                    PlatformSetting::PUBLISHER_APPROVAL_ADMIN,
                ]),
            ],
            'message_delivery_mode' => [
                'required',
                Rule::in([
                    PlatformSetting::MESSAGE_DELIVERY_DEFAULT,
                    PlatformSetting::MESSAGE_DELIVERY_SEND_MESSAGE,
                    PlatformSetting::MESSAGE_DELIVERY_SEND_EMAIL,
                ]),
            ],
            'advertiser_messages_enabled' => 'nullable|boolean',
            'publisher_messages_enabled' => 'nullable|boolean',
            'publisher_floor_price_enabled' => 'nullable|boolean',
            'publisher_minimum_floor_price_percent' => 'required|numeric|min:0|max:100000',
            'publisher_minimum_amount_for_invoice_generation' => 'required|numeric|min:0|max:100000000',
            'publisher_auto_invoice_enabled' => 'nullable|boolean',
            'publisher_minimum_payment_amount' => 'required|numeric|min:0|max:100000000',
            'video_ads_enabled' => 'nullable|boolean',
            'mobile_ads_enabled' => 'nullable|boolean',
            'banner_autoload_enabled' => 'nullable|boolean',
            'revive_memcache_enabled' => 'nullable|boolean',
            'adshqip_memcache_enabled' => 'nullable|boolean',
            'hide_url_enabled' => 'nullable|boolean',
            'encryption_enabled' => 'nullable|boolean',
            'url_encoding_enabled' => 'nullable|boolean',
            'report_url_ads_enabled' => 'nullable|boolean',
        ]);

        PlatformSetting::setValue(
            'creative_minimum_impressions_per_day',
            $validated['creative_minimum_impressions_per_day'],
            'integer',
            'creative',
            'Minimum daily impressions target for creatives',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'creative_approval_type',
            $validated['creative_approval_type'],
            'string',
            'creative',
            'Creative approval flow for newly created creatives',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'anti_fraud_clicks_enabled',
            $request->boolean('anti_fraud_clicks_enabled'),
            'boolean',
            'anti_fraud',
            'Enable or disable anti-fraud click protection',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'anti_fraud_reset_counter_seconds',
            $validated['anti_fraud_reset_counter_seconds'],
            'integer',
            'anti_fraud',
            'Rolling time window used for anti-fraud click counting',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'anti_fraud_threshold_clicks',
            $validated['anti_fraud_threshold_clicks'],
            'integer',
            'anti_fraud',
            'Maximum allowed clicks inside the anti-fraud reset window before blocking',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'terawurl_mobile_tracking_url',
            PlatformSetting::normalizeServeUrl($validated['terawurl_mobile_tracking_url'] ?? null),
            'string',
            'tracking',
            'Custom Terawurl base link used for mobile and tablet tracking URLs',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'jw_player_license_key',
            $validated['jw_player_license_key'] ?? null,
            'string',
            'video',
            'JW Player licence key used by video ad renderers when jwplayer is available',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'advertiser_approval_type',
            $validated['advertiser_approval_type'],
            'string',
            'account',
            'Advertiser onboarding approval flow',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'advertiser_payment_type',
            $validated['advertiser_payment_type'],
            'string',
            'advertiser_payments',
            'Default payment method for advertiser deposits',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'campaign_minimum_budget',
            $validated['campaign_minimum_budget'],
            'decimal',
            'campaign',
            'Minimum budget required for new campaigns',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'campaign_minimum_bid_rate',
            $validated['campaign_minimum_bid_rate'],
            'decimal',
            'campaign',
            'Minimum bid rate (CPM/CPC) for campaigns',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'campaign_creative_type',
            $validated['campaign_creative_type'],
            'string',
            'campaign',
            'Default creative type for new campaigns',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_approval_type',
            $validated['publisher_approval_type'],
            'string',
            'account',
            'Publisher onboarding approval flow',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'message_delivery_mode',
            $validated['message_delivery_mode'],
            'string',
            'account',
            'Choose whether registration follow-up uses the default flash flow, in-app messages, or email delivery',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'advertiser_messages_enabled',
            $request->boolean('advertiser_messages_enabled'),
            'boolean',
            'account',
            'Enable or disable in-app messages sent to advertisers',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_messages_enabled',
            $request->boolean('publisher_messages_enabled'),
            'boolean',
            'account',
            'Enable or disable in-app messages sent to publishers',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_floor_price_enabled',
            $request->boolean('publisher_floor_price_enabled'),
            'boolean',
            'publisher_payments',
            'Enable minimum floor price enforcement for publisher ad blocks',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_minimum_floor_price_percent',
            $validated['publisher_minimum_floor_price_percent'],
            'decimal',
            'publisher_payments',
            'Minimum allowed floor price represented as a percent value',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_minimum_amount_for_invoice_generation',
            $validated['publisher_minimum_amount_for_invoice_generation'],
            'decimal',
            'publisher_payments',
            'Minimum outstanding publisher earnings needed before an invoice is generated',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_auto_invoice_enabled',
            $request->boolean('publisher_auto_invoice_enabled'),
            'boolean',
            'publisher_payments',
            'Enable automatic publisher invoice generation',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'publisher_minimum_payment_amount',
            $validated['publisher_minimum_payment_amount'],
            'decimal',
            'publisher_payments',
            'Minimum paid invoice amount required before publisher payout generation',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'video_ads_enabled',
            $request->boolean('video_ads_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable video ad delivery across public serving endpoints',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'mobile_ads_enabled',
            $request->boolean('mobile_ads_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable mobile and tablet ad delivery',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'banner_autoload_enabled',
            $request->boolean('banner_autoload_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable automatic zone banner injection when zone JavaScript loads',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'revive_memcache_enabled',
            $request->boolean('revive_memcache_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable cached zone serving payloads',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'adshqip_memcache_enabled',
            $request->boolean('adshqip_memcache_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable internal AdShqip report caching',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'hide_url_enabled',
            $request->boolean('hide_url_enabled'),
            'boolean',
            'ad_delivery',
            'Hide visible destination URLs inside served ads',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'encryption_enabled',
            $request->boolean('encryption_enabled'),
            'boolean',
            'ad_delivery',
            'Enable or disable encrypted values in public ad delivery flows',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'url_encoding_enabled',
            $request->boolean('url_encoding_enabled'),
            'boolean',
            'ad_delivery',
            'Encrypt destination URLs passed through click tracking links',
            $request->user()?->id,
        );

        PlatformSetting::setValue(
            'report_url_ads_enabled',
            $request->boolean('report_url_ads_enabled'),
            'boolean',
            'ad_delivery',
            'Record URL-based ad serve and click activity into URL ad reports',
            $request->user()?->id,
        );

        \Illuminate\Support\Facades\DB::table('aq_antifraud_rules')->updateOrInsert(
            ['rule_type' => 'click_cap'],
            [
                'rule_name' => 'Platform click cap',
                'threshold_value' => $validated['anti_fraud_threshold_clicks'],
                'reset_period_seconds' => $validated['anti_fraud_reset_counter_seconds'],
                'action' => 'block',
                'is_active' => $request->boolean('anti_fraud_clicks_enabled'),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()
            ->route('admin.app-configurations')
            ->with('success', 'App configurations updated successfully.');
    }
}

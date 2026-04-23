<?php

namespace Tests\Feature;

use App\Mail\AdvertiserEventMail;
use App\Models\Campaign;
use App\Models\Ad;
use App\Models\AdCreative;
use App\Models\User;
use App\Models\StatDaily;
use App\Models\Transaction;
use App\Models\PlatformSetting;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdvertiserCampaignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('aq_stats_daily');
        Schema::dropIfExists('aq_traffic_source_bidding');
        Schema::dropIfExists('aq_traffic_sources');
        Schema::dropIfExists('zone_limitations');
        Schema::dropIfExists('country_wise_bidding');
        Schema::dropIfExists('aq_url_ad_reports');
        Schema::dropIfExists('aq_video_tracking');
        Schema::dropIfExists('aq_vast_events');
        Schema::dropIfExists('aq_transactions');
        Schema::dropIfExists('aq_invoices');
        Schema::dropIfExists('aq_payouts');
        Schema::dropIfExists('aq_ad_creatives');
        Schema::dropIfExists('aq_ads');
        Schema::dropIfExists('aq_zones');
        Schema::dropIfExists('aq_sites');
        Schema::dropIfExists('aq_ad_sizes');
        Schema::dropIfExists('aq_platform_settings');
        Schema::dropIfExists('aq_pixel_trackers');
        Schema::dropIfExists('aq_campaign_groups');
        Schema::dropIfExists('aq_campaigns');
        Schema::dropIfExists('aq_push_subscriptions');
        Schema::dropIfExists('aq_admin_messages');
        Schema::dropIfExists('aq_notifications');
        Schema::dropIfExists('aq_two_factor_backup_codes');
        Schema::dropIfExists('aq_user_profiles');
        Schema::dropIfExists('aq_user_roles');
        Schema::dropIfExists('aq_activity_log');
        Schema::dropIfExists('aq_users');

        Schema::create('aq_user_roles', function (Blueprint $table) {
            $table->id();
            $table->string('role_key')->unique();
            $table->string('role_name');
            $table->string('status')->default('active');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('role')->default('advertiser');
            $table->string('status')->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->text('two_factor_secret')->nullable();
            $table->json('two_factor_verification_options')->nullable();
            $table->json('two_factor_token_types')->nullable();
            $table->string('two_factor_phone')->nullable();
            $table->string('two_factor_email')->nullable();
            $table->string('two_factor_backup_question')->nullable();
            $table->string('two_factor_backup_answer_hash')->nullable();
            $table->string('two_factor_trusted_ip')->nullable();
            $table->string('two_factor_trusted_subnet')->nullable();
            $table->string('two_factor_trusted_browser')->nullable();
            $table->string('two_factor_trusted_os')->nullable();
            $table->string('two_factor_trusted_user_agent_hash')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        DB::table('aq_user_roles')->insert([
            'role_key' => 'admin',
            'role_name' => 'Admin',
            'status' => 'active',
            'permissions' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('aq_activity_log', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action');
            $table->string('entity_type')->nullable();
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('os')->nullable();
            $table->json('metadata')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type')->default('info');
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_admin_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->string('subject');
            $table->text('message');
            $table->string('priority')->default('normal');
            $table->boolean('is_read')->default(false);
            $table->dateTime('read_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->text('endpoint');
            $table->string('endpoint_hash')->unique();
            $table->text('p256dh_key');
            $table->text('auth_token');
            $table->text('user_agent')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('aq_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('balance_before', 12, 4)->default(0);
            $table->decimal('balance_after', 12, 4)->default(0);
            $table->unsignedBigInteger('payment_method_id')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('gateway_txn_id')->nullable();
            $table->string('gateway_status')->nullable();
            $table->text('gateway_response')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->string('description')->nullable();
            $table->string('admin_note')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('completed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('invoice_number')->unique();
            $table->string('type');
            $table->decimal('amount', 12, 4);
            $table->decimal('tax_amount', 12, 4)->default(0);
            $table->decimal('total_amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->string('status')->default('draft');
            $table->date('due_date')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('pdf_url')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_method');
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('pending');
            $table->date('period_start')->nullable();
            $table->date('period_end')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('mobile_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('skype_address')->nullable();
            $table->string('icq_address')->nullable();
            $table->string('jabber_address')->nullable();
            $table->string('alternative_email')->nullable();
            $table->string('company_name')->nullable();
            $table->string('company_address_line1')->nullable();
            $table->string('company_address_line2')->nullable();
            $table->string('company_city')->nullable();
            $table->string('company_state_region')->nullable();
            $table->string('company_country_code')->nullable();
            $table->string('city')->nullable();
            $table->string('state_region')->nullable();
            $table->string('country_code')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('website_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->json('notification_settings')->nullable();
            $table->decimal('balance', 12, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('aq_two_factor_backup_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('code_hash');
            $table->boolean('used')->default(false);
            $table->dateTime('used_at')->nullable();
            $table->string('used_ip')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_campaign_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('advertiser_id')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_pixel_trackers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('advertiser_id')->nullable();
            $table->string('type')->default('conversion');
            $table->string('pixel_goal')->nullable();
            $table->string('category')->nullable();
            $table->string('pixel_code')->nullable();
            $table->text('tracking_url')->nullable();
            $table->text('append_code')->nullable();
            $table->string('status')->default('active');
            $table->integer('fire_count')->default(0);
            $table->dateTime('last_fired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('pixel_tracker_id')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('marketing_objective')->default('traffic');
            $table->string('campaign_type')->default('cpm');
            $table->string('status')->default('draft');
            $table->boolean('admin_approved')->default(false);
            $table->decimal('bid_amount', 10, 4)->default(0);
            $table->decimal('daily_budget', 12, 4)->nullable();
            $table->decimal('total_budget', 12, 4)->nullable();
            $table->decimal('remaining_budget', 12, 4)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_ads', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('campaign_id');
            $table->string('name');
            $table->string('ad_type')->default('image');
            $table->string('status')->default('pending_review');
            $table->string('destination_url')->nullable();
            $table->string('display_url')->nullable();
            $table->string('headline')->nullable();
            $table->text('body_text')->nullable();
            $table->string('call_to_action')->nullable();
            $table->string('sponsored_label')->nullable();
            $table->string('brand_name')->nullable();
            $table->boolean('admin_approved')->default(false);
            $table->integer('weight')->default(5);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->string('name');
            $table->string('domain');
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_ad_sizes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('status')->default('active');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('aq_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedInteger('size_id')->nullable();
            $table->string('name');
            $table->string('format_key')->nullable();
            $table->string('size_key')->nullable();
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('country_wise_bidding', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->string('campaign_type')->nullable();
            $table->string('type');
            $table->string('country_code', 2);
            $table->decimal('bid_value', 10, 2);
            $table->timestamps();
        });

        Schema::create('aq_traffic_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('active');
        });

        Schema::create('aq_traffic_source_bidding', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('traffic_source_id');
            $table->unsignedBigInteger('campaign_id');
            $table->string('campaign_type')->default('network');
            $table->decimal('bid_rate', 10, 2);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('zone_limitations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('advertiser_id');
            $table->string('name');
            $table->string('type');
            $table->json('zone_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_ad_creatives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->unsignedBigInteger('display_screen_id')->nullable();
            $table->string('file_path')->nullable();
            $table->string('video_url')->nullable();
            $table->string('file_type')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size_bytes')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('thumbnail_path')->nullable();
            $table->boolean('is_primary')->default(true);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('aq_platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('setting_key')->unique();
            $table->text('setting_value')->nullable();
            $table->string('setting_type')->default('string');
            $table->string('category')->default('general');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
        });

        Schema::create('aq_vast_events', function (Blueprint $table) {
            $table->increments('id');
            $table->string('event_name', 50)->unique();
            $table->string('description')->nullable();
            $table->boolean('is_trackable')->default(true);
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_video_tracking', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id');
            $table->unsignedBigInteger('impression_id')->nullable();
            $table->unsignedInteger('event_id');
            $table->string('viewer_id', 64);
            $table->unsignedTinyInteger('progress_percent')->nullable();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_url_ad_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->string('event_type');
            $table->text('request_url')->nullable();
            $table->text('referrer_url')->nullable();
            $table->text('tracking_url')->nullable();
            $table->text('destination_url')->nullable();
            $table->string('device_type')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->boolean('url_hidden')->default(false);
            $table->boolean('url_encoded')->default(false);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('aq_stats_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('ad_id')->nullable();
            $table->unsignedBigInteger('campaign_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('advertiser_id')->nullable();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->string('country_code')->nullable();
            $table->string('device_type')->nullable();
            $table->integer('impressions')->default(0);
            $table->integer('unique_impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('unique_clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('publisher_earnings', 12, 4)->default(0);
            $table->decimal('ecpm', 8, 4)->nullable();
            $table->decimal('ctr', 8, 4)->nullable();
            $table->decimal('fill_rate', 5, 2)->nullable();
            $table->integer('viewable_impressions')->default(0);
            $table->integer('adblock_detected')->default(0);
            $table->timestamps();
        });
    }

    public function test_advertiser_campaign_index_is_scoped_to_current_user(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Visible Campaign',
            'campaign_type' => 'cpc',
            'status' => 'draft',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Hidden Campaign',
            'campaign_type' => 'cpc',
            'status' => 'draft',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.campaigns'))
            ->assertOk()
            ->assertSee('Visible Campaign')
            ->assertDontSee('Hidden Campaign');
    }

    public function test_advertiser_cannot_open_another_advertisers_campaign(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        $campaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Other Account Campaign',
            'campaign_type' => 'cpm',
            'status' => 'draft',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.campaigns.show', $campaign->id))
            ->assertRedirect(route('advertiser.campaigns'))
            ->assertSessionHas('error', 'Campaign not found');
    }

    public function test_advertiser_ad_formats_index_is_scoped_and_hides_admin_action_routes(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        $visibleCampaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Visible Campaign',
            'campaign_type' => 'cpc',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $hiddenCampaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Hidden Campaign',
            'campaign_type' => 'cpc',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $visibleAd = Ad::create([
            'campaign_id' => $visibleCampaign->id,
            'name' => 'Visible Creative',
            'ad_type' => 'image',
            'status' => 'pending_review',
            'destination_url' => 'https://example.com/visible',
            'weight' => 7,
        ]);

        Ad::create([
            'campaign_id' => $hiddenCampaign->id,
            'name' => 'Hidden Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://example.com/hidden',
            'weight' => 9,
        ]);

        AdCreative::create([
            'ad_id' => $visibleAd->id,
            'file_type' => 'image',
            'width' => 300,
            'height' => 250,
            'is_primary' => true,
            'created_at' => now(),
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.adformats'))
            ->assertOk()
            ->assertSee('Visible Creative')
            ->assertDontSee('Hidden Creative')
            ->assertDontSee('advertiser.adformats.updateStatus')
            ->assertDontSee('advertiser.adformats.updateWeight')
            ->assertDontSee('advertiser.adformats.destroy');
    }

    public function test_advertiser_overview_report_is_scoped_to_current_user(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        StatDaily::create([
            'date' => '2026-04-21',
            'advertiser_id' => $advertiser->id,
            'impressions' => 1000,
            'clicks' => 50,
            'conversions' => 5,
            'revenue' => 12.50,
        ]);

        StatDaily::create([
            'date' => '2026-04-21',
            'advertiser_id' => $otherAdvertiser->id,
            'impressions' => 9000,
            'clicks' => 900,
            'conversions' => 90,
            'revenue' => 99.00,
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.reports.overview'))
            ->assertOk()
            ->assertSee('Overview Report')
            ->assertSee('Date')
            ->assertSee('Impressions')
            ->assertSee('Clicks')
            ->assertSee('Conversions')
            ->assertSee('Spend')
            ->assertSee('CTR')
            ->assertSee('ECPM')
            ->assertSee('1,000')
            ->assertSee('50')
            ->assertSee('5')
            ->assertSee('12.50')
            ->assertDontSee('9,000')
            ->assertDontSee('99.00');
    }

    public function test_advertiser_video_creative_report_is_scoped_and_shows_requested_columns(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Video Campaign',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $otherCampaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Other Video Campaign',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $ad = Ad::create([
            'campaign_id' => $campaign->id,
            'name' => 'Visible Video Creative',
            'ad_type' => 'video',
            'status' => 'active',
            'destination_url' => 'https://example.com/video',
        ]);

        $otherAd = Ad::create([
            'campaign_id' => $otherCampaign->id,
            'name' => 'Hidden Video Creative',
            'ad_type' => 'video',
            'status' => 'active',
            'destination_url' => 'https://example.com/other-video',
        ]);

        $eventIds = [];
        foreach (['start', 'firstQuartile', 'midpoint', 'thirdQuartile', 'complete', 'pause', 'resume', 'fullscreen', 'unmute', 'mute'] as $eventName) {
            $eventIds[$eventName] = DB::table('aq_vast_events')->insertGetId([
                'event_name' => $eventName,
                'created_at' => now(),
            ]);
        }

        foreach ($eventIds as $eventName => $eventId) {
            DB::table('aq_video_tracking')->insert([
                'ad_id' => $ad->id,
                'impression_id' => 1001,
                'event_id' => $eventId,
                'viewer_id' => 'viewer-1',
                'progress_percent' => $eventName === 'complete' ? 100 : null,
                'created_at' => now(),
            ]);
        }

        DB::table('aq_video_tracking')->insert([
            'ad_id' => $otherAd->id,
            'impression_id' => 9001,
            'event_id' => $eventIds['complete'],
            'viewer_id' => 'viewer-2',
            'progress_percent' => 100,
            'created_at' => now(),
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.reports.video-creative'))
            ->assertOk()
            ->assertSee('Video Creative Report')
            ->assertSee('Creative')
            ->assertSee('Impressions')
            ->assertSee('View 25%')
            ->assertSee('View 50%')
            ->assertSee('View 75%')
            ->assertSee('Complete')
            ->assertSee('Pause')
            ->assertSee('Resume')
            ->assertSee('Full Screen')
            ->assertSee('Unmute')
            ->assertSee('Mute')
            ->assertSee('Visible Video Creative')
            ->assertDontSee('Hidden Video Creative');
    }

    public function test_advertiser_site_url_report_is_scoped_and_shows_requested_columns(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');
        $publisher = User::create([
            'email' => 'publisher@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'publisher',
            'status' => 'active',
            'email_verified_at' => now(),
            'is_deleted' => false,
        ]);

        $siteId = DB::table('aq_sites')->insertGetId([
            'publisher_id' => $publisher->id,
            'name' => 'News Site',
            'domain' => 'news.example.com',
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $zoneId = DB::table('aq_zones')->insertGetId([
            'site_id' => $siteId,
            'name' => 'Top Banner',
            'format_key' => 'display_web',
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Visible Campaign',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $hiddenCampaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Hidden Campaign',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $ad = Ad::create([
            'campaign_id' => $campaign->id,
            'name' => 'Visible Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://advertiser.example/landing',
        ]);

        $hiddenAd = Ad::create([
            'campaign_id' => $hiddenCampaign->id,
            'name' => 'Hidden Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://hidden.example/landing',
        ]);

        foreach (['serve', 'click', 'conversion'] as $eventType) {
            DB::table('aq_url_ad_reports')->insert([
                'ad_id' => $ad->id,
                'campaign_id' => $campaign->id,
                'zone_id' => $zoneId,
                'event_type' => $eventType,
                'request_url' => 'https://app.example/serve/ad/' . $ad->id,
                'referrer_url' => 'https://news.example.com/article',
                'created_at' => now(),
            ]);
        }

        DB::table('aq_url_ad_reports')->insert([
            'ad_id' => $hiddenAd->id,
            'campaign_id' => $hiddenCampaign->id,
            'zone_id' => $zoneId,
            'event_type' => 'serve',
            'request_url' => 'https://app.example/serve/ad/' . $hiddenAd->id,
            'referrer_url' => 'https://news.example.com/hidden',
            'created_at' => now(),
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.reports.site-url'))
            ->assertOk()
            ->assertSee('Site URL Report')
            ->assertSee('Site URL')
            ->assertSee('Domain')
            ->assertSee('Referrer')
            ->assertSee('Impressions')
            ->assertSee('Clicks')
            ->assertSee('Conversions')
            ->assertSee('CTR')
            ->assertSee('https://news.example.com')
            ->assertSee('https://news.example.com/article')
            ->assertDontSee('https://news.example.com/hidden');
    }

    public function test_advertiser_group_settings_report_uses_filters_dimensions_and_metrics(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        $sizeId = DB::table('aq_ad_sizes')->insertGetId([
            'name' => 'Medium Rectangle',
            'width' => 300,
            'height' => 250,
            'status' => 'active',
            'created_at' => now(),
        ]);

        $siteId = DB::table('aq_sites')->insertGetId([
            'publisher_id' => null,
            'name' => 'News Site',
            'domain' => 'news.example.com',
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $zoneId = DB::table('aq_zones')->insertGetId([
            'site_id' => $siteId,
            'size_id' => $sizeId,
            'name' => 'Medium Rectangle Zone',
            'format_key' => 'display_web',
            'size_key' => '300x250',
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Grouped Campaign',
            'campaign_type' => 'cpm',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $otherCampaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Hidden Grouped Campaign',
            'campaign_type' => 'cpc',
            'status' => 'active',
            'bid_amount' => 1,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
        ]);

        $ad = Ad::create([
            'campaign_id' => $campaign->id,
            'name' => 'Grouped Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://example.com/grouped',
        ]);

        $hiddenAd = Ad::create([
            'campaign_id' => $otherCampaign->id,
            'name' => 'Hidden Grouped Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://example.com/hidden',
        ]);

        StatDaily::create([
            'date' => '2026-04-21',
            'ad_id' => $ad->id,
            'campaign_id' => $campaign->id,
            'zone_id' => $zoneId,
            'site_id' => $siteId,
            'advertiser_id' => $advertiser->id,
            'country_code' => 'US',
            'device_type' => 'mobile',
            'impressions' => 1200,
            'clicks' => 60,
            'conversions' => 6,
            'revenue' => 24.00,
            'fill_rate' => 88.50,
        ]);

        StatDaily::create([
            'date' => '2026-04-21',
            'ad_id' => $hiddenAd->id,
            'campaign_id' => $otherCampaign->id,
            'zone_id' => $zoneId,
            'site_id' => $siteId,
            'advertiser_id' => $otherAdvertiser->id,
            'country_code' => 'US',
            'device_type' => 'mobile',
            'impressions' => 9000,
            'clicks' => 900,
            'conversions' => 90,
            'revenue' => 99.00,
            'fill_rate' => 90,
        ]);

        DB::table('aq_url_ad_reports')->insert([
            'ad_id' => $ad->id,
            'campaign_id' => $campaign->id,
            'zone_id' => $zoneId,
            'event_type' => 'serve',
            'referrer_url' => 'https://news.example.com/article',
            'created_at' => '2026-04-21 10:00:00',
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.reports.group-settings', [
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
                'device_type' => 'mobile',
                'metrics' => ['requests', 'impressions', 'clicks', 'conversions', 'spend', 'ctr', 'ecpm', 'fill_rate'],
                'group_by' => ['campaign', 'creative', 'country', 'ad_size', 'site_url', 'domain', 'referrer', 'revenue_type', 'date'],
                'display_by' => 'cumulative',
            ]))
            ->assertOk()
            ->assertSee('Group Settings')
            ->assertSee('Basic Settings')
            ->assertSee('Request')
            ->assertSee('Impression')
            ->assertSee('Fill Rate')
            ->assertSee('Grouped Campaign')
            ->assertSee('Grouped Creative')
            ->assertSee('US')
            ->assertSee('300x250')
            ->assertSee('https://news.example.com')
            ->assertSee('https://news.example.com/article')
            ->assertSee('CPM')
            ->assertSee('1,200')
            ->assertDontSee('Hidden Grouped Campaign')
            ->assertDontSee('9,000');
    }

    public function test_advertiser_network_pages_are_scoped_and_show_requested_columns(): void
    {
        $advertiser = $this->advertiser('network-one@example.com');
        $otherAdvertiser = $this->advertiser('network-two@example.com');

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Network Visible Campaign',
            'status' => 'active',
            'campaign_type' => 'cpm',
            'bid_amount' => 1.5,
            'is_deleted' => false,
        ]);

        $otherCampaign = Campaign::create([
            'advertiser_id' => $otherAdvertiser->id,
            'name' => 'Network Hidden Campaign',
            'status' => 'active',
            'campaign_type' => 'cpc',
            'bid_amount' => 1.5,
            'is_deleted' => false,
        ]);

        $sizeId = DB::table('aq_ad_sizes')->insertGetId([
            'name' => '300x250',
            'width' => 300,
            'height' => 250,
            'status' => 'active',
            'created_at' => now(),
        ]);

        $zoneId = DB::table('aq_zones')->insertGetId([
            'site_id' => null,
            'size_id' => $sizeId,
            'name' => 'Visible Zone',
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('country_wise_bidding')->insert([
            [
                'advertiser_id' => $advertiser->id,
                'campaign_id' => $campaign->id,
                'campaign_type' => 'network',
                'type' => 'CPC',
                'country_code' => 'US',
                'bid_value' => 2.75,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'advertiser_id' => $otherAdvertiser->id,
                'campaign_id' => $otherCampaign->id,
                'campaign_type' => 'network',
                'type' => 'CPM',
                'country_code' => 'GB',
                'bid_value' => 9.99,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $trafficSourceId = DB::table('aq_traffic_sources')->insertGetId([
            'name' => 'Hidden Source Name',
            'slug' => 'hidden-source-name',
            'status' => 'active',
        ]);

        DB::table('aq_traffic_source_bidding')->insert([
            'traffic_source_id' => $trafficSourceId,
            'campaign_id' => $campaign->id,
            'campaign_type' => 'network',
            'bid_rate' => 1.25,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('zone_limitations')->insert([
            'advertiser_id' => $advertiser->id,
            'name' => 'Visible Zone Limit',
            'type' => 'adblock_whitelist',
            'zone_ids' => json_encode([$zoneId]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('aq_pixel_trackers')->insert([
            'advertiser_id' => $advertiser->id,
            'name' => 'Visible Pixel',
            'type' => 'html_pixel',
            'category' => 'Signup',
            'pixel_code' => 'PXVISIBLE',
            'tracking_url' => 'https://example.com/pixel',
            'status' => 'active',
            'is_active' => true,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        StatDaily::create([
            'date' => '2026-04-23',
            'advertiser_id' => $advertiser->id,
            'campaign_id' => $campaign->id,
            'zone_id' => $zoneId,
            'country_code' => 'US',
            'device_type' => 'mobile',
            'impressions' => 1000,
            'clicks' => 25,
            'conversions' => 7,
            'revenue' => 4.50,
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.network.country-wise-bidding'))
            ->assertOk()
            ->assertSee('Country Wise Bidding')
            ->assertSee('Network Visible Campaign')
            ->assertSee('Bid Value')
            ->assertSee('2.75')
            ->assertDontSee('Network Hidden Campaign');

        $this->actingAs($advertiser)
            ->get(route('advertiser.network.traffic-sources'))
            ->assertOk()
            ->assertSee('Traffic Source')
            ->assertSee('Bid Rate')
            ->assertSee('Network Visible Campaign');

        $this->actingAs($advertiser)
            ->get(route('advertiser.network.zone-limitations'))
            ->assertOk()
            ->assertSee('Zone Limitation')
            ->assertSee('Visible Zone Limit')
            ->assertSee('Edit')
            ->assertSee('Delete');

        $this->actingAs($advertiser)
            ->get(route('advertiser.network.pixel-trackers'))
            ->assertOk()
            ->assertSee('Pixel Tracker')
            ->assertSee('Visible Pixel')
            ->assertSee('Category');

        $this->actingAs($advertiser)
            ->get(route('advertiser.network.network-kit', [
                'start_date' => '2026-04-01',
                'end_date' => '2026-04-30',
            ]))
            ->assertOk()
            ->assertSee('Network Kit')
            ->assertSee('Ad Size')
            ->assertSee('Type')
            ->assertSee('ECPM')
            ->assertSee('300x250')
            ->assertSee('CPM')
            ->assertDontSee('Conversions');
    }

    public function test_advertiser_payment_history_groups_months_and_daily_details(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        Transaction::create([
            'user_id' => $advertiser->id,
            'type' => 'deposit',
            'amount' => 500.00,
            'currency' => 'EUR',
            'balance_before' => 0,
            'balance_after' => 500,
            'payment_gateway' => 'stripe',
            'gateway_status' => 'confirmed',
            'description' => 'Visible deposit',
            'status' => 'completed',
            'completed_at' => '2026-04-03 10:00:00',
        ]);

        Transaction::create([
            'user_id' => $advertiser->id,
            'type' => 'deposit',
            'amount' => 250.00,
            'currency' => 'EUR',
            'balance_before' => 500,
            'balance_after' => 750,
            'payment_gateway' => 'paypal',
            'gateway_status' => 'confirmed',
            'description' => 'Visible second deposit',
            'status' => 'completed',
            'completed_at' => '2026-04-18 10:00:00',
        ]);

        Transaction::create([
            'user_id' => $otherAdvertiser->id,
            'type' => 'deposit',
            'amount' => 9000.00,
            'currency' => 'EUR',
            'balance_before' => 0,
            'balance_after' => 9000,
            'payment_gateway' => 'stripe',
            'gateway_status' => 'confirmed',
            'description' => 'Hidden deposit',
            'status' => 'completed',
            'completed_at' => '2026-04-03 10:00:00',
        ]);

        StatDaily::create([
            'date' => '2026-04-03',
            'advertiser_id' => $advertiser->id,
            'impressions' => 1000,
            'clicks' => 50,
            'conversions' => 4,
            'revenue' => 33.25,
        ]);

        StatDaily::create([
            'date' => '2026-04-18',
            'advertiser_id' => $advertiser->id,
            'impressions' => 2000,
            'clicks' => 80,
            'conversions' => 6,
            'revenue' => 44.75,
        ]);

        StatDaily::create([
            'date' => '2026-04-03',
            'advertiser_id' => $otherAdvertiser->id,
            'impressions' => 9000,
            'clicks' => 900,
            'conversions' => 90,
            'revenue' => 999.00,
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.history', [
                'start_month' => '2026-04',
                'end_month' => '2026-04',
            ]))
            ->assertOk()
            ->assertSee('Payment History')
            ->assertSee('Total Deposits')
            ->assertSee('Spend Amount')
            ->assertSee('April 2026')
            ->assertSee('Apr 03, 2026')
            ->assertSee('Apr 18, 2026')
            ->assertSee('750.00')
            ->assertSee('78.00')
            ->assertSee('33.25')
            ->assertDontSee('9,000.00')
            ->assertDontSee('999.00');

        $admin = $this->admin('admin@example.com');

        $this->actingAs($admin)
            ->get(route('admin.advertiser-payment-history', [
                'start_month' => '2026-04',
                'end_month' => '2026-04',
            ]))
            ->assertOk()
            ->assertSee('Advertiser Payment History')
            ->assertSee('PayPal')
            ->assertSee('Bitcoin')
            ->assertSee('Bank Wire')
            ->assertSee('Stripe')
            ->assertSee('Authorize.net');
    }

    public function test_advertiser_deposit_history_lists_own_deposits(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');
        $otherAdvertiser = $this->advertiser('advertiser-two@example.com');

        DB::table('aq_user_profiles')->insert([
            'user_id' => $advertiser->id,
            'first_name' => 'Ava',
            'last_name' => 'Stone',
            'currency' => 'EUR',
            'balance' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $completed = Transaction::create([
            'user_id' => $advertiser->id,
            'type' => 'deposit',
            'amount' => 123.45,
            'currency' => 'EUR',
            'balance_before' => 0,
            'balance_after' => 123.45,
            'payment_gateway' => 'wire_transfer',
            'gateway_status' => 'confirmed',
            'description' => 'Visible bank wire deposit',
            'status' => 'completed',
            'completed_at' => '2026-04-23 14:09:20',
        ]);

        $pending = Transaction::create([
            'user_id' => $advertiser->id,
            'type' => 'deposit',
            'amount' => 50.00,
            'currency' => 'EUR',
            'balance_before' => 123.45,
            'balance_after' => 123.45,
            'payment_gateway' => 'paypal',
            'gateway_status' => 'pending',
            'description' => 'Visible pending deposit',
            'status' => 'pending',
        ]);

        Transaction::create([
            'user_id' => $otherAdvertiser->id,
            'type' => 'deposit',
            'amount' => 9999.00,
            'currency' => 'EUR',
            'balance_before' => 0,
            'balance_after' => 9999,
            'payment_gateway' => 'stripe',
            'gateway_status' => 'confirmed',
            'description' => 'Hidden deposit',
            'status' => 'completed',
            'completed_at' => '2026-04-23 14:09:20',
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.deposit-history'))
            ->assertOk()
            ->assertSee('Deposit History')
            ->assertSee('Id')
            ->assertSee('Status')
            ->assertSee('Paid Date')
            ->assertSee('Name')
            ->assertSee('Email')
            ->assertSee('Amount')
            ->assertSee('Payment Type')
            ->assertSee('#' . $completed->id)
            ->assertSee('#' . $pending->id)
            ->assertSee('Ava Stone')
            ->assertSee('advertiser-one@example.com')
            ->assertSee('Bank Wire')
            ->assertSee('PayPal')
            ->assertDontSee('9999.00')
            ->assertDontSee('advertiser-two@example.com');
    }

    public function test_advertiser_add_funds_creates_pending_deposit_and_shows_paypal_confirmation(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');

        PlatformSetting::setValue(
            'advertiser_payment_type',
            PlatformSetting::ADVERTISER_PAYMENT_PAYPAL,
            'string',
            'advertiser_payments'
        );

        PlatformSetting::setValue(
            'admin_payment_settings_details',
            [
                PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => [
                    'mode' => 'sandbox',
                    'client_id' => 'paypal-client',
                    'secret' => 'paypal-secret',
                    'paypal_email' => 'payments@example.com',
                    'merchant_id' => 'MERCHANT-123',
                    'instructions' => 'Use your deposit reference in PayPal notes.',
                ],
            ],
            'json',
            'advertiser_payments'
        );

        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds'))
            ->assertOk()
            ->assertSee('Add Funds')
            ->assertSee('Select Payment Type')
            ->assertSee('Enter the Amount')
            ->assertSee('PayPal');

        $response = $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.store'), [
                'payment_type' => PlatformSetting::ADVERTISER_PAYMENT_PAYPAL,
                'amount' => '125.50',
            ]);

        $transaction = Transaction::where('user_id', $advertiser->id)
            ->where('type', 'deposit')
            ->where('payment_gateway', 'paypal')
            ->firstOrFail();

        $response->assertRedirect(route('advertiser.payments.add-funds.confirm', $transaction));

        $this->assertSame('pending', $transaction->status);
        $this->assertSame('pending', $transaction->gateway_status);
        $this->assertEquals(125.50, (float) $transaction->amount);
        $this->assertNotNull($transaction->invoice_id);

        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds.confirm', $transaction))
            ->assertOk()
            ->assertSee('Confirm Payment')
            ->assertSee('PayPal Payment Page')
            ->assertSee('Continue to PayPal')
            ->assertSee('payments@example.com')
            ->assertSee('MERCHANT-123')
            ->assertSee('Use your deposit reference in PayPal notes.')
            ->assertSee('125.50');

        Http::fake([
            'https://api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response(['access_token' => 'paypal-token'], 200),
            'https://api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'ORDER-123',
                'status' => 'CREATED',
                'links' => [
                    ['rel' => 'approve', 'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123'],
                ],
            ], 200),
        ]);

        $paypalResponse = $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $transaction));

        $paypalResponse->assertRedirect('https://www.sandbox.paypal.com/checkoutnow?token=ORDER-123');

        $transaction->refresh();
        $this->assertSame('ORDER-123', $transaction->gateway_txn_id);
        $this->assertSame('processing', $transaction->gateway_status);
    }

    public function test_paypal_redirects_to_classic_checkout_when_api_credentials_are_missing(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');

        PlatformSetting::setValue(
            'admin_payment_settings_details',
            [
                PlatformSetting::ADVERTISER_PAYMENT_PAYPAL => [
                    'mode' => 'sandbox',
                    'paypal_email' => 'payments@example.com',
                    'merchant_id' => 'MERCHANT-123',
                    'instructions' => 'Use your deposit reference in PayPal notes.',
                ],
            ],
            'json',
            'advertiser_payments'
        );

        $transaction = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_PAYPAL);

        $response = $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $transaction));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringStartsWith('https://www.sandbox.paypal.com/cgi-bin/webscr?', $location);
        $this->assertStringContainsString('business=payments%40example.com', $location);
        $this->assertStringContainsString('amount=77.25', $location);
        $this->assertStringContainsString('invoice=' . $transaction->id, $location);

        $transaction->refresh();
        $this->assertSame('processing', $transaction->gateway_status);
        $this->assertSame('paypal_classic', $transaction->gateway_response['provider']);
    }

    public function test_advertiser_add_funds_starts_real_checkout_for_supported_payment_types(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');

        PlatformSetting::setValue(
            'admin_payment_settings_details',
            [
                PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER => [
                    'account_holder' => 'Adshqip LLC',
                    'bank_name' => 'Production Bank',
                    'account_number' => 'IBAN-123',
                ],
                PlatformSetting::ADVERTISER_PAYMENT_BITCOIN => [
                    'mode' => 'test',
                    'bitpay_api_token' => 'bitpay-api-token',
                    'bitpay_webhook_secret' => 'bitpay-webhook-secret',
                    'network' => 'Bitcoin',
                ],
                PlatformSetting::ADVERTISER_PAYMENT_STRIPE => [
                    'secret_key' => 'sk_test_123',
                    'webhook_secret' => 'whsec_test',
                ],
                PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE => [
                    'mode' => 'sandbox',
                    'login_id' => 'authorize-login',
                    'transaction_key' => 'authorize-key',
                ],
            ],
            'json',
            'advertiser_payments'
        );

        Http::fake([
            'https://api.stripe.com/v1/checkout/sessions' => Http::response([
                'id' => 'cs_test_123',
                'url' => 'https://checkout.stripe.com/c/pay/cs_test_123',
            ], 200),
            'https://test.bitpay.com/invoices' => Http::response([
                'id' => 'bitpay-invoice-123',
                'url' => 'https://test.bitpay.com/invoice?id=bitpay-invoice-123',
            ], 200),
            'https://apitest.authorize.net/xml/v1/request.api' => Http::response([
                'messages' => ['resultCode' => 'Ok'],
                'token' => 'authorize-hosted-token',
            ], 200),
        ]);

        $wire = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER);

        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds.confirm', $wire))
            ->assertOk()
            ->assertSee('Confirm Bank Wire')
            ->assertSee('77.25');

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $wire))
            ->assertRedirect(route('advertiser.payments.add-funds.confirm', $wire))
            ->assertSessionHas('success');

        $wire->refresh();
        $this->assertSame('pending', $wire->status);
        $this->assertSame('processing', $wire->gateway_status);
        $this->assertStringContainsString('Advertiser confirmed', (string) $wire->admin_note);

        $stripe = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_STRIPE);
        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds.confirm', $stripe))
            ->assertOk()
            ->assertSee('Stripe Payment Page')
            ->assertSee('Continue to Stripe Checkout')
            ->assertSee('77.25');

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $stripe))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        $stripe->refresh();
        $this->assertSame('cs_test_123', $stripe->gateway_txn_id);
        $this->assertSame('processing', $stripe->gateway_status);

        $bitcoin = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_BITCOIN);
        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds.confirm', $bitcoin))
            ->assertOk()
            ->assertSee('Bitcoin Payment Page')
            ->assertSee('Continue to Bitcoin Checkout')
            ->assertSee('77.25');

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $bitcoin))
            ->assertRedirect('https://test.bitpay.com/invoice?id=bitpay-invoice-123');

        $bitcoin->refresh();
        $this->assertSame('bitpay-invoice-123', $bitcoin->gateway_txn_id);
        $this->assertSame('processing', $bitcoin->gateway_status);

        $authorize = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_AUTHORIZE);
        $this->actingAs($advertiser)
            ->get(route('advertiser.payments.add-funds.confirm', $authorize))
            ->assertOk()
            ->assertSee('Authorize.net Payment Page')
            ->assertSee('Continue to Authorize.net')
            ->assertSee('77.25');

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $authorize))
            ->assertRedirect(route('advertiser.payments.add-funds.authorize-hosted', $authorize));

        $authorize->refresh();
        $this->assertSame('authorize-hosted-token', $authorize->gateway_txn_id);
        $this->assertSame('processing', $authorize->gateway_status);
    }

    public function test_stripe_webhook_completes_deposit_and_updates_advertiser_balance(): void
    {
        $advertiser = $this->advertiser('advertiser-one@example.com');

        PlatformSetting::setValue(
            'admin_payment_settings_details',
            [
                PlatformSetting::ADVERTISER_PAYMENT_STRIPE => [
                    'webhook_secret' => 'whsec_test',
                ],
            ],
            'json',
            'advertiser_payments'
        );

        $transaction = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_STRIPE);
        $payload = json_encode([
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_webhook',
                    'metadata' => ['transaction_id' => (string) $transaction->id],
                ],
            ],
        ]);
        $timestamp = time();
        $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, 'whsec_test');

        $this->postJson('/api/payments/stripe/webhook', json_decode($payload, true), [
            'Stripe-Signature' => $signature,
        ])->assertOk();

        $transaction->refresh();
        $advertiser->refresh();

        $this->assertSame('completed', $transaction->status);
        $this->assertSame('confirmed', $transaction->gateway_status);
        $this->assertEquals(77.25, (float) $advertiser->profile->balance);
    }

    public function test_admin_completes_bank_wire_deposit_and_updates_advertiser_balance(): void
    {
        $admin = $this->admin('admin@example.com');
        $advertiser = $this->advertiser('advertiser-one@example.com');

        $wire = $this->createAddFundsDeposit($advertiser, PlatformSetting::ADVERTISER_PAYMENT_WIRE_TRANSFER);

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.pay', $wire))
            ->assertRedirect(route('advertiser.payments.add-funds.confirm', $wire));

        $wire->refresh();
        $this->assertSame('pending', $wire->status);
        $this->assertSame('processing', $wire->gateway_status);

        $this->actingAs($admin)
            ->get(route('admin.advertiser-deposits', ['status' => 'pending']))
            ->assertOk()
            ->assertSee('#' . $wire->id)
            ->assertSee('Complete');

        $this->actingAs($admin)
            ->get(route('admin.advertiser-payment-approvals'))
            ->assertOk()
            ->assertSee('Pending Add Funds Deposits')
            ->assertSee('#' . $wire->id)
            ->assertSee('Approve')
            ->assertSee('Reject');

        $this->actingAs($admin)
            ->patch(route('admin.advertiser-deposits.complete', $wire), ['redirect_to' => 'approvals'])
            ->assertRedirect(route('admin.advertiser-payment-approvals'))
            ->assertSessionHas('success');

        $wire->refresh();
        $advertiser->refresh();

        $this->assertSame('completed', $wire->status);
        $this->assertSame('confirmed', $wire->gateway_status);
        $this->assertEquals(77.25, (float) $wire->balance_after);
        $this->assertEquals(77.25, (float) $advertiser->profile->balance);
    }

    public function test_advertiser_settings_pages_match_admin_flows_and_save_requested_fields(): void
    {
        $advertiser = $this->advertiser('advertiser-settings@example.com');

        $this->actingAs($advertiser)
            ->get(route('advertiser.personal-information'))
            ->assertOk()
            ->assertSee('Skype Address')
            ->assertSee('ICQ Address')
            ->assertSee('European VAT')
            ->assertSee('Jabber Address');

        $this->actingAs($advertiser)
            ->put(route('advertiser.personal-information.update'), [
                'first_name' => 'Ada',
                'last_name' => 'Advertiser',
                'gender' => 'female',
                'date_of_birth' => '1995-04-12',
                'mobile_number' => '+355690000000',
                'skype_address' => 'ada.skype',
                'icq_address' => '123456',
                'vat_number' => 'EU-VAT-44',
                'jabber_address' => 'ada@jabber.test',
                'city' => 'Tirane',
                'state_region' => 'Tirane',
                'country_code' => 'AL',
            ])
            ->assertRedirect(route('advertiser.personal-information'))
            ->assertSessionHas('success');

        $this->actingAs($advertiser)
            ->get(route('advertiser.company-information'))
            ->assertOk()
            ->assertSee('Website');

        $this->actingAs($advertiser)
            ->put(route('advertiser.company-information.update'), [
                'company_name' => 'Adshqip Media',
                'company_address_line1' => 'Rruga Example 10',
                'company_address_line2' => 'Suite 4',
                'company_city' => 'Tirane',
                'company_state_region' => 'Tirane',
                'company_country_code' => 'AL',
                'website_url' => 'https://advertiser.example.test',
            ])
            ->assertRedirect(route('advertiser.company-information'))
            ->assertSessionHas('success');

        $this->actingAs($advertiser)
            ->get(route('advertiser.account-settings'))
            ->assertOk()
            ->assertSee('Save Account Settings')
            ->assertSee('Company Information');

        $this->actingAs($advertiser)
            ->put(route('advertiser.account-settings.update'), [
                'email' => 'advertiser-settings@example.com',
                'alternative_email' => 'backup@example.com',
                'website_url' => 'https://advertiser.example.test',
                'currency' => 'USD',
                'two_factor_enabled' => '1',
            ])
            ->assertRedirect(route('advertiser.account-settings'))
            ->assertSessionHas('success');

        $this->actingAs($advertiser)
            ->get(route('advertiser.two-factor-authentication'))
            ->assertOk()
            ->assertSee('Verification Options')
            ->assertSee('Token Types');

        $this->actingAs($advertiser)
            ->put(route('advertiser.two-factor-authentication.update'), [
                'verification_options' => ['unknown_ips', 'unknown_browser'],
                'token_types' => ['email_otp', 'backup_code'],
                'two_factor_email' => 'security@example.com',
                'two_factor_backup_question' => 'Favorite color?',
                'two_factor_backup_answer' => 'Blue',
                'regenerate_backup_codes' => '1',
            ])
            ->assertRedirect(route('advertiser.two-factor-authentication'))
            ->assertSessionHas('success');

        $this->actingAs($advertiser)
            ->get(route('advertiser.audit-logs'))
            ->assertOk()
            ->assertSee('Audit Log');

        $advertiser->refresh();
        $profile = $advertiser->profile()->firstOrFail();

        $this->assertSame('ada.skype', $profile->skype_address);
        $this->assertSame('123456', $profile->icq_address);
        $this->assertSame('EU-VAT-44', $profile->vat_number);
        $this->assertSame('ada@jabber.test', $profile->jabber_address);
        $this->assertSame('https://advertiser.example.test', $profile->website_url);
        $this->assertSame('backup@example.com', $profile->alternative_email);
        $this->assertSame('USD', $profile->currency);
        $this->assertTrue((bool) $advertiser->two_factor_enabled);
        $this->assertSame(['unknown_ips', 'unknown_browser'], $advertiser->two_factor_verification_options);
        $this->assertSame(['email_otp', 'backup_code'], $advertiser->two_factor_token_types);
        $this->assertSame('security@example.com', $advertiser->two_factor_email);
    }

    public function test_advertiser_notification_settings_page_saves_grouped_preferences(): void
    {
        $advertiser = $this->advertiser('advertiser-notify@example.com');

        $this->actingAs($advertiser)
            ->get(route('advertiser.notification-settings'))
            ->assertOk()
            ->assertSee('Notification Settings')
            ->assertSee('Campaign Approved')
            ->assertSee('Payment Requested')
            ->assertSee('Blocked User');

        $this->actingAs($advertiser)
            ->put(route('advertiser.notification-settings.update'), [
                'receive_newsletter' => '1',
                'delivery_channels' => ['email', 'platform_message'],
                'enabled_events' => [
                    'campaign_approved',
                    'creative_added',
                    'payment_requested',
                    'personal_information',
                    'blocked_user',
                ],
            ])
            ->assertRedirect(route('advertiser.notification-settings'))
            ->assertSessionHas('success');

        $advertiser->refresh();
        $profile = $advertiser->profile()->firstOrFail();

        $this->assertSame([
            'receive_newsletter' => true,
            'delivery_channels' => ['email', 'platform_message'],
            'enabled_events' => [
                'campaign_approved',
                'creative_added',
                'payment_requested',
                'personal_information',
                'blocked_user',
            ],
        ], $profile->notification_settings);

        $this->assertDatabaseHas('aq_activity_log', [
            'user_id' => $advertiser->id,
            'action' => 'notification_settings',
        ]);
    }

    public function test_advertiser_header_uses_profile_avatar_and_exposes_notifications_messages_and_push_endpoints(): void
    {
        $admin = $this->admin('admin-header@example.com');
        $advertiser = $this->advertiser('advertiser-header@example.com');

        $advertiser->profile()->create([
            'first_name' => 'Header',
            'last_name' => 'User',
            'avatar_url' => 'https://cdn.example.com/avatar-header.png',
            'currency' => 'EUR',
        ]);

        DB::table('aq_notifications')->insert([
            'user_id' => $advertiser->id,
            'type' => 'info',
            'title' => 'Budget Alert',
            'message' => 'Your balance is low.',
            'action_url' => '/advertisers/payments/add-funds',
            'is_read' => false,
            'created_at' => now(),
        ]);

        DB::table('aq_admin_messages')->insert([
            'sender_id' => $admin->id,
            'recipient_id' => $advertiser->id,
            'subject' => 'Welcome',
            'message' => 'Your account manager sent a message.',
            'priority' => 'high',
            'is_read' => false,
            'is_archived' => false,
            'created_at' => now(),
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.account-settings'))
            ->assertOk()
            ->assertSee('https://cdn.example.com/avatar-header.png', false);

        $this->actingAs($advertiser)
            ->get(route('advertiser.notifications'))
            ->assertOk()
            ->assertJsonFragment(['title' => 'Budget Alert']);

        $messagesResponse = $this->actingAs($advertiser)
            ->get(route('advertiser.messages.unread'))
            ->assertOk()
            ->assertJsonFragment(['subject' => 'Welcome']);

        $messageId = $messagesResponse->json('messages.0.id');

        $this->actingAs($advertiser)
            ->post(route('advertiser.messages.read', $messageId))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_admin_messages', [
            'id' => $messageId,
            'is_read' => true,
        ]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.push.status'))
            ->assertOk()
            ->assertJson([
                'subscribed' => false,
                'subscription_count' => 0,
            ]);

        $this->actingAs($advertiser)
            ->postJson(route('advertiser.push.subscribe'), [
                'endpoint' => 'https://push.example.test/subscriptions/123',
                'keys' => [
                    'p256dh' => 'demo-p256dh',
                    'auth' => 'demo-auth',
                ],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->actingAs($advertiser)
            ->get(route('advertiser.push.status'))
            ->assertOk()
            ->assertJson([
                'subscribed' => true,
                'subscription_count' => 1,
            ]);

        $this->actingAs($advertiser)
            ->post(route('advertiser.push.test'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_notifications', [
            'user_id' => $advertiser->id,
            'type' => 'push',
            'title' => 'Test Notification',
        ]);
    }

    public function test_campaign_approval_delivers_advertiser_message_and_email_when_notification_settings_allow_it(): void
    {
        Mail::fake();

        $admin = $this->admin('admin-approval@example.com');
        $advertiser = $this->advertiser('advertiser-approval@example.com');

        $advertiser->profile()->create([
            'first_name' => 'Approve',
            'last_name' => 'User',
            'currency' => 'EUR',
            'notification_settings' => [
                'receive_newsletter' => false,
                'delivery_channels' => ['email', 'platform_message'],
                'enabled_events' => ['campaign_approved'],
            ],
        ]);

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Approval Test Campaign',
            'marketing_objective' => 'traffic',
            'campaign_type' => 'cpm',
            'status' => 'pending_review',
            'bid_amount' => 1.50,
            'total_budget' => 100,
            'remaining_budget' => 100,
            'currency' => 'EUR',
            'is_deleted' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.campaign-approvals.approve', $campaign->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_admin_messages', [
            'sender_id' => $admin->id,
            'recipient_id' => $advertiser->id,
            'subject' => 'Campaign Approved',
        ]);

        Mail::assertSent(AdvertiserEventMail::class, function (AdvertiserEventMail $mail) use ($advertiser) {
            return $mail->hasTo($advertiser->email)
                && $mail->mailSubject === 'Campaign Approved';
        });
    }

    public function test_request_driven_notification_settings_events_are_delivered(): void
    {
        $advertiser = $this->advertiser('advertiser-request-events@example.com');

        $advertiser->profile()->create([
            'first_name' => 'Request',
            'last_name' => 'Events',
            'currency' => 'EUR',
            'notification_settings' => [
                'receive_newsletter' => false,
                'delivery_channels' => ['platform_message'],
                'enabled_events' => [
                    'personal_information',
                    'payment_requested',
                    'pixel_tracker_added',
                ],
            ],
        ]);

        DB::table('aq_platform_settings')->insert([
            'setting_key' => 'advertiser_payment_type',
            'setting_value' => json_encode(['wire_transfer']),
            'setting_type' => 'json',
            'category' => 'payments',
            'description' => 'Advertiser payment types',
            'updated_by' => $advertiser->id,
        ]);

        $this->actingAs($advertiser)
            ->put(route('advertiser.personal-information.update'), [
                'first_name' => 'Updated',
                'last_name' => 'Profile',
                'country_code' => 'AL',
            ])
            ->assertRedirect(route('advertiser.personal-information'));

        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.store'), [
                'payment_type' => 'wire_transfer',
                'amount' => '55.00',
            ])
            ->assertRedirect();

        $this->actingAs($advertiser)
            ->post(route('advertiser.network.pixel-trackers.store'), [
                'name' => 'Notify Pixel',
                'type' => 'html_pixel',
                'status' => 'active',
            ])
            ->assertRedirect(route('advertiser.network.pixel-trackers'));

        $this->assertDatabaseHas('aq_notifications', [
            'user_id' => $advertiser->id,
            'title' => 'Personal Information Updated',
        ]);

        $this->assertDatabaseHas('aq_notifications', [
            'user_id' => $advertiser->id,
            'title' => 'Payment Requested',
        ]);

        $this->assertDatabaseHas('aq_notifications', [
            'user_id' => $advertiser->id,
            'title' => 'Pixel Tracker Added',
        ]);

        $this->assertDatabaseHas('aq_admin_messages', [
            'recipient_id' => $advertiser->id,
            'subject' => 'Payment Requested',
        ]);
    }

    public function test_login_and_tracking_notification_events_are_delivered(): void
    {
        $advertiser = $this->advertiser('advertiser-live-events@example.com');

        $advertiser->profile()->create([
            'first_name' => 'Live',
            'last_name' => 'Events',
            'currency' => 'EUR',
            'balance' => 0,
            'notification_settings' => [
                'receive_newsletter' => false,
                'delivery_channels' => ['platform_message'],
                'enabled_events' => [
                    'password_incorrect',
                    'impression',
                    'view_threshold',
                    'conversion',
                    'conversion_tracking_threshold',
                    'budget_completed',
                    'campaign_stopped_total_budget_limit',
                    'campaign_completed',
                    'balance_zero',
                ],
            ],
        ]);

        $campaign = Campaign::create([
            'advertiser_id' => $advertiser->id,
            'name' => 'Tracking Notifications Campaign',
            'marketing_objective' => 'traffic',
            'campaign_type' => 'cpa',
            'status' => 'active',
            'admin_approved' => true,
            'bid_amount' => 1.00,
            'daily_budget' => 1.00,
            'total_budget' => 1.00,
            'remaining_budget' => 1.00,
            'currency' => 'EUR',
            'is_deleted' => false,
        ]);

        $ad = Ad::create([
            'campaign_id' => $campaign->id,
            'name' => 'Tracking Creative',
            'ad_type' => 'image',
            'status' => 'active',
            'destination_url' => 'https://example.test/landing',
            'display_url' => 'example.test',
            'admin_approved' => true,
            'is_deleted' => false,
        ]);

        AdCreative::create([
            'ad_id' => $ad->id,
            'file_path' => 'creatives/banner.png',
            'file_type' => 'image',
            'width' => 300,
            'height' => 250,
            'is_primary' => true,
            'created_at' => now(),
        ]);

        $this->postJson(route('web.login'), [
            'email' => $advertiser->email,
            'password' => 'wrong-password',
        ])->assertStatus(401);

        $this->get(route('ad.serve', $ad->id))->assertOk();
        $this->get(route('ad.view', $ad->id))->assertOk();
        $this->get(route('ad.conversion', $ad->id))->assertOk();

        $campaign->refresh();

        $this->assertSame('completed', $campaign->status);
        $this->assertSame('0.0000', number_format((float) $campaign->remaining_budget, 4, '.', ''));

        foreach ([
            'Password Incorrect',
            'Impression Recorded',
            'View Threshold Reached',
            'Conversion Recorded',
            'Conversion Threshold Reached',
            'Budget Completed',
            'Campaign Stopped',
            'Campaign Completed',
            'Balance Is Zero',
        ] as $title) {
            $this->assertDatabaseHas('aq_notifications', [
                'user_id' => $advertiser->id,
                'title' => $title,
            ]);
        }
    }

    private function createAddFundsDeposit(User $advertiser, string $paymentType): Transaction
    {
        $this->actingAs($advertiser)
            ->post(route('advertiser.payments.add-funds.store'), [
                'payment_type' => $paymentType,
                'amount' => '77.25',
            ])
            ->assertRedirect();

        return Transaction::where('user_id', $advertiser->id)
            ->where('type', 'deposit')
            ->latest('id')
            ->firstOrFail();
    }

    private function advertiser(string $email): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => 'advertiser',
            'status' => 'active',
            'email_verified_at' => now(),
            'is_deleted' => false,
        ]);
    }

    private function admin(string $email): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(),
            'is_deleted' => false,
        ]);
    }
}

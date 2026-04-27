<?php

namespace Tests\Feature;

use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublisherSiteManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('aq_telegram_mini_app_events');
        Schema::dropIfExists('aq_stats_daily');
        Schema::dropIfExists('aq_zones');
        Schema::dropIfExists('aq_telegram_mini_apps');
        Schema::dropIfExists('aq_referral_payouts');
        Schema::dropIfExists('aq_referral_conversions');
        Schema::dropIfExists('aq_referral_links');
        Schema::dropIfExists('aq_transactions');
        Schema::dropIfExists('aq_user_profiles');
        Schema::dropIfExists('aq_platform_settings');
        Schema::dropIfExists('aq_site_categories');
        Schema::dropIfExists('aq_categories');
        Schema::dropIfExists('aq_sites');
        Schema::dropIfExists('aq_notifications');
        Schema::dropIfExists('aq_users');

        Schema::create('aq_users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->string('role')->default('publisher');
            $table->string('status')->default('active');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('referral_code')->nullable()->unique();
            $table->unsignedBigInteger('referred_by')->nullable();
            $table->timestamp('referred_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_user_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->timestamps();
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

        Schema::create('aq_sites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->string('name');
            $table->string('domain');
            $table->string('category', 100)->nullable();
            $table->string('language', 5)->default('sq');
            $table->string('status')->default('active');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
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

        Schema::create('aq_telegram_mini_apps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('application_type')->default('web');
            $table->string('app_name');
            $table->string('app_short_name')->nullable();
            $table->string('bot_username')->nullable();
            $table->string('bot_token_hash')->nullable();
            $table->string('app_url');
            $table->string('category')->nullable();
            $table->string('status')->default('active');
            $table->boolean('admin_approved')->default(true);
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_telegram_mini_app_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mini_app_id');
            $table->string('event_type');
            $table->decimal('revenue', 12, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('aq_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('choose_type')->default('web');
            $table->unsignedBigInteger('mobile_app_id')->nullable();
            $table->string('name');
            $table->unsignedInteger('format_id')->nullable();
            $table->string('format_key')->nullable();
            $table->unsignedInteger('size_id')->nullable();
            $table->string('size_key')->nullable();
            $table->string('zone_type')->nullable();
            $table->string('placement')->default('content');
            $table->decimal('floor_price', 10, 4)->nullable();
            $table->text('passback')->nullable();
            $table->unsignedInteger('image_width')->nullable();
            $table->unsignedInteger('image_height')->nullable();
            $table->longText('html_template')->nullable();
            $table->longText('custom_css')->nullable();
            $table->string('bg_color')->nullable();
            $table->string('sponsored_prefix')->nullable();
            $table->string('css_path')->nullable();
            $table->boolean('inline_video')->default(false);
            $table->string('status')->default('active');
            $table->text('ad_code')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->integer('target_age_min')->nullable();
            $table->integer('target_age_max')->nullable();
            $table->string('target_gender')->nullable();
            $table->string('target_color')->nullable();
            $table->integer('target_height_min')->nullable();
            $table->integer('target_height_max')->nullable();
            $table->integer('target_weight_min')->nullable();
            $table->integer('target_weight_max')->nullable();
            $table->integer('frequency_views')->nullable();
            $table->boolean('auto_reload')->default(false);
            $table->integer('reload_time')->nullable();
            $table->integer('content_width_px')->nullable();
            $table->integer('content_height_px')->nullable();
            $table->integer('top_offset_px')->nullable();
            $table->integer('right_offset_px')->nullable();
            $table->integer('z_index_value')->nullable();
            $table->boolean('is_fixed')->default(false);
            $table->string('hide_side')->default('none');
            $table->json('target_countries')->nullable();
            $table->json('target_devices')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_categories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('iab_code', 20)->nullable();
            $table->string('status')->default('active');
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('aq_site_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('site_id');
            $table->unsignedInteger('category_id');
            $table->primary(['site_id', 'category_id']);
        });

        Schema::create('aq_stats_daily', function (Blueprint $table) {
            $table->id();
            $table->date('date')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('zone_id')->nullable();
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->decimal('revenue', 12, 4)->default(0);
            $table->decimal('publisher_earnings', 12, 4)->default(0);
            $table->timestamps();
        });

        Schema::create('aq_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->decimal('amount', 12, 4)->default(0);
            $table->string('currency', 3)->default('EUR');
            $table->decimal('balance_before', 12, 4)->default(0);
            $table->decimal('balance_after', 12, 4)->default(0);
            $table->string('description')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_referral_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->string('code')->unique();
            $table->string('slug')->nullable();
            $table->enum('target_role', ['advertiser', 'publisher', 'any'])->default('any');
            $table->string('campaign_name')->nullable();
            $table->string('landing_url')->nullable();
            $table->enum('commission_type', ['percentage', 'flat'])->default('percentage');
            $table->decimal('commission_rate', 8, 4)->default(5);
            $table->unsignedInteger('commission_duration_days')->default(365)->nullable();
            $table->decimal('max_commission_per_referral', 12, 4)->nullable();
            $table->unsignedBigInteger('total_clicks')->default(0);
            $table->unsignedInteger('total_signups')->default(0);
            $table->unsignedInteger('total_qualified')->default(0);
            $table->decimal('total_earned', 12, 4)->default(0);
            $table->enum('status', ['active', 'paused', 'expired', 'revoked'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('aq_referral_conversions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('link_id');
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id')->unique();
            $table->enum('referred_role', ['advertiser', 'publisher']);
            $table->string('click_ip')->nullable();
            $table->string('click_user_agent')->nullable();
            $table->string('click_referer')->nullable();
            $table->string('signup_ip')->nullable();
            $table->string('cookie_id')->nullable();
            $table->boolean('is_qualified')->default(false);
            $table->timestamp('qualified_at')->nullable();
            $table->decimal('qualification_threshold', 12, 4)->nullable();
            $table->decimal('commission_earned', 12, 4)->default(0);
            $table->string('commission_currency', 3)->default('EUR');
            $table->timestamp('commission_ends_at')->nullable();
            $table->enum('status', ['pending', 'active', 'qualified', 'expired', 'fraudulent'])->default('pending');
            $table->text('fraud_flags')->nullable();
            $table->timestamps();
        });

        Schema::create('aq_referral_payouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->decimal('amount', 12, 4);
            $table->string('currency', 3)->default('EUR');
            $table->string('payment_method')->default('balance_credit');
            $table->string('payment_reference')->nullable();
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('conversions_count')->default(0);
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function test_publisher_sites_page_is_scoped_and_shows_real_metrics(): void
    {
        $publisher = $this->publisher('publisher-sites@example.com');
        $otherPublisher = $this->publisher('other-publisher@example.com');
        $technologyId = DB::table('aq_categories')->insertGetId([
            'name' => 'Technology',
            'slug' => 'technology',
            'status' => 'active',
            'created_at' => now(),
        ]);
        $newsId = DB::table('aq_categories')->insertGetId([
            'name' => 'News',
            'slug' => 'news',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $visibleSite = Site::create([
            'publisher_id' => $publisher->id,
            'name' => 'Tech Daily',
            'domain' => 'techdaily.test',
            'category' => 'News, Technology',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $hiddenSite = Site::create([
            'publisher_id' => $otherPublisher->id,
            'name' => 'Hidden Site',
            'domain' => 'hidden.test',
            'category' => 'News',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        DB::table('aq_site_categories')->insert([
            ['site_id' => $visibleSite->id, 'category_id' => $technologyId],
            ['site_id' => $visibleSite->id, 'category_id' => $newsId],
        ]);

        DB::table('aq_stats_daily')->insert([
            [
                'date' => now()->toDateString(),
                'site_id' => $visibleSite->id,
                'impressions' => 1200,
                'clicks' => 36,
                'revenue' => 18.5000,
                'publisher_earnings' => 12.7500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => now()->subDay()->toDateString(),
                'site_id' => $visibleSite->id,
                'impressions' => 800,
                'clicks' => 24,
                'revenue' => 11.5000,
                'publisher_earnings' => 7.2500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => now()->toDateString(),
                'site_id' => $hiddenSite->id,
                'impressions' => 5000,
                'clicks' => 200,
                'revenue' => 99.0000,
                'publisher_earnings' => 50.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.sites'))
            ->assertOk()
            ->assertSee('Websites')
            ->assertSee('Tech Daily')
            ->assertDontSee('Hidden Site')
            ->assertSee('Technology')
            ->assertSee('News')
            ->assertSee('2,000', false)
            ->assertSee('60', false)
            ->assertSee('3.00%', false)
            ->assertSee('&euro;10.00', false)
            ->assertSee('&euro;20.00', false);

        $this->actingAs($publisher)
            ->get(route('publisher.sites.show', $visibleSite->id))
            ->assertOk()
            ->assertJson([
                'name' => 'Tech Daily',
                'impressions' => 2000,
                'clicks' => 60,
                'ctr' => 3.0,
                'ecp' => 10.0,
                'revenue' => 20.0,
            ]);
    }

    public function test_publisher_can_create_update_and_delete_own_site(): void
    {
        $publisher = $this->publisher('publisher-crud@example.com');
        $sportsId = DB::table('aq_categories')->insertGetId([
            'name' => 'Sports',
            'slug' => 'sports',
            'status' => 'active',
            'created_at' => now(),
        ]);
        $financeId = DB::table('aq_categories')->insertGetId([
            'name' => 'Finance',
            'slug' => 'finance',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $this->actingAs($publisher)
            ->post(route('publisher.sites.store'), [
                'name' => 'My Site',
                'url' => 'https://mysite.example/path',
                'category_ids' => [$sportsId, $financeId],
            ])
            ->assertRedirect(route('publisher.sites'));

        $site = Site::query()->where('publisher_id', $publisher->id)->firstOrFail();

        $this->assertSame('My Site', $site->name);
        $this->assertSame('mysite.example', $site->domain);
        $this->assertSame('Finance, Sports', $site->category);
        $this->assertDatabaseHas('aq_site_categories', [
            'site_id' => $site->id,
            'category_id' => $sportsId,
        ]);
        $this->assertDatabaseHas('aq_site_categories', [
            'site_id' => $site->id,
            'category_id' => $financeId,
        ]);

        $newsId = DB::table('aq_categories')->insertGetId([
            'name' => 'News',
            'slug' => 'news-crud',
            'status' => 'active',
            'created_at' => now(),
        ]);

        $this->actingAs($publisher)
            ->put(route('publisher.sites.update', $site->id), [
                'name' => 'My Site Updated',
                'url' => 'updated.example',
                'category_ids' => [$newsId],
            ])
            ->assertRedirect(route('publisher.sites'));

        $site->refresh();

        $this->assertSame('My Site Updated', $site->name);
        $this->assertSame('updated.example', $site->domain);
        $this->assertSame('News', $site->category);
        $this->assertDatabaseHas('aq_site_categories', [
            'site_id' => $site->id,
            'category_id' => $newsId,
        ]);

        $this->actingAs($publisher)
            ->delete(route('publisher.sites.destroy', $site->id))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_sites', [
            'id' => $site->id,
            'is_deleted' => true,
        ]);
    }

    public function test_publisher_sites_page_exposes_add_adblock_and_creates_publisher_scoped_zone(): void
    {
        $publisher = $this->publisher('publisher-adblock@example.com');
        $otherPublisher = $this->publisher('other-adblock@example.com');

        $site = Site::create([
            'publisher_id' => $publisher->id,
            'name' => 'Publisher Site',
            'domain' => 'publisher-site.test',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $otherSite = Site::create([
            'publisher_id' => $otherPublisher->id,
            'name' => 'Other Site',
            'domain' => 'other-site.test',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $appId = DB::table('aq_telegram_mini_apps')->insertGetId([
            'user_id' => $publisher->id,
            'application_type' => 'web',
            'app_name' => 'Publisher App',
            'app_short_name' => 'publisher_app_test',
            'bot_username' => 'publisherapptestbot',
            'bot_token_hash' => hash('sha256', 'publisher-app'),
            'app_url' => 'https://app.publisher.test',
            'category' => 'custom',
            'status' => 'active',
            'admin_approved' => true,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.sites'))
            ->assertOk()
            ->assertSee('Add Adblock')
            ->assertSee('choose_type', false)
            ->assertSee('site_list_id', false)
            ->assertSee('mobileapp_list', false)
            ->assertSee('adblock_name', false)
            ->assertSee('html_template', false)
            ->assertSee('tab-js', false);

        $response = $this->actingAs($publisher)
            ->postJson(route('publisher.adblocks.store'), [
                'choose_type' => 'web',
                'site_id' => $site->id,
                'name' => 'Publisher Banner Block',
                'format_id' => 'display_web',
                'size_id' => '300x250',
                'zone_type' => 'banner',
                'placement' => 'content',
                'floor_price' => '0.7500',
                'passback' => '<script>publisher-passback</script>',
                'image_width' => 300,
                'image_height' => 250,
                'html_template' => '<div class="slot">{{ad}}</div>',
                'custom_css' => '.slot{padding:8px;}',
                'bg_color' => '#f8fafc',
                'sponsored_prefix' => 'Sponsored',
                'css_path' => 'https://cdn.publisher.test/ad.css',
                'inline_video' => false,
                'status' => 'active',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $zoneId = $response->json('zone_id');

        $this->assertDatabaseHas('aq_zones', [
            'id' => $zoneId,
            'site_id' => $site->id,
            'name' => 'Publisher Banner Block',
            'choose_type' => 'web',
            'zone_type' => 'banner',
            'format_key' => 'display_web',
            'size_key' => '300x250',
            'sponsored_prefix' => 'Sponsored',
        ]);

        $this->assertDatabaseMissing('aq_zones', [
            'site_id' => $otherSite->id,
            'name' => 'Publisher Banner Block',
        ]);

        $tagResponse = $this->actingAs($publisher)
            ->get(route('publisher.adblocks.tag', $zoneId))
            ->assertOk();

        $this->assertArrayHasKey('codes', $tagResponse->json());
        $this->assertArrayHasKey('js', $tagResponse->json('codes'));
        $this->assertArrayHasKey('iframe', $tagResponse->json('codes'));
        $this->assertArrayHasKey('overlay', $tagResponse->json('codes'));
        $this->assertArrayHasKey('curl', $tagResponse->json('codes'));

        $this->actingAs($otherPublisher)
            ->postJson(route('publisher.adblocks.store'), [
                'choose_type' => 'web',
                'site_id' => $site->id,
                'name' => 'Blocked Scope Test',
                'format_id' => 'display_web',
                'size_id' => '300x250',
                'zone_type' => 'banner',
                'placement' => 'content',
            ])
            ->assertStatus(422);

        $this->assertNotNull($appId);
    }

    public function test_publisher_apps_page_is_scoped_and_supports_crud_metrics_and_adblock_creation(): void
    {
        $publisher = $this->publisher('publisher-apps@example.com');
        $otherPublisher = $this->publisher('other-publisher-apps@example.com');

        $visibleAppId = DB::table('aq_telegram_mini_apps')->insertGetId([
            'user_id' => $publisher->id,
            'application_type' => 'android',
            'app_name' => 'Publisher Weather App',
            'app_short_name' => 'publisher_weather',
            'bot_username' => 'publisherweatherbot',
            'bot_token_hash' => hash('sha256', 'publisher-weather'),
            'app_url' => 'https://weather.publisher.test',
            'category' => 'analytics',
            'status' => 'active',
            'admin_approved' => true,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('aq_telegram_mini_apps')->insert([
            'user_id' => $otherPublisher->id,
            'application_type' => 'ios',
            'app_name' => 'Hidden App',
            'app_short_name' => 'hidden_app',
            'bot_username' => 'hiddenappbot',
            'bot_token_hash' => hash('sha256', 'hidden-app'),
            'app_url' => 'https://hidden.publisher.test',
            'category' => 'custom',
            'status' => 'active',
            'admin_approved' => true,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('aq_telegram_mini_app_events')->insert([
            [
                'mini_app_id' => $visibleAppId,
                'event_type' => 'ad_impression',
                'revenue' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mini_app_id' => $visibleAppId,
                'event_type' => 'ad_impression',
                'revenue' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'mini_app_id' => $visibleAppId,
                'event_type' => 'ad_click',
                'revenue' => 1.5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.apps'))
            ->assertOk()
            ->assertSee('Apps')
            ->assertSee('Publisher Weather App')
            ->assertDontSee('Hidden App')
            ->assertSee('Android App')
            ->assertSee('https://weather.publisher.test')
            ->assertSee('2', false)
            ->assertSee('1', false)
            ->assertSee('50.00%', false)
            ->assertSee('&euro;750.00', false)
            ->assertSee('&euro;1.50', false)
            ->assertSee('Add App')
            ->assertSee('Adblock');

        $this->actingAs($publisher)
            ->post(route('publisher.apps.store'), [
                'application_type' => 'telegram',
                'app_url' => 'mini.publisher.test',
                'app_name' => 'Publisher Mini',
                'category' => 'custom',
            ])
            ->assertRedirect(route('publisher.apps'));

        $createdApp = DB::table('aq_telegram_mini_apps')
            ->where('user_id', $publisher->id)
            ->where('app_name', 'Publisher Mini')
            ->first();

        $this->assertNotNull($createdApp);
        $this->assertSame('https://mini.publisher.test', $createdApp->app_url);
        $this->assertSame('telegram', $createdApp->application_type);
        $this->assertSame('pending_review', $createdApp->status);

        $this->actingAs($publisher)
            ->get(route('publisher.apps.show', $visibleAppId))
            ->assertOk()
            ->assertJson([
                'id' => $visibleAppId,
                'application_type' => 'android',
                'app_name' => 'Publisher Weather App',
                'category' => 'analytics',
            ]);

        $this->actingAs($publisher)
            ->put(route('publisher.apps.update', $visibleAppId), [
                'application_type' => 'ios',
                'app_url' => 'updated.publisher.test',
                'app_name' => 'Publisher Weather App Updated',
                'category' => 'campaign_manager',
            ])
            ->assertRedirect(route('publisher.apps'));

        $this->assertDatabaseHas('aq_telegram_mini_apps', [
            'id' => $visibleAppId,
            'application_type' => 'ios',
            'app_url' => 'https://updated.publisher.test',
            'app_name' => 'Publisher Weather App Updated',
            'category' => 'campaign_manager',
        ]);

        $adblockResponse = $this->actingAs($publisher)
            ->postJson(route('publisher.apps.adblocks.store', $visibleAppId), [
                'name' => 'Publisher App Outstream',
                'format_id' => 'display_video',
                'size_id' => 'outstream',
                'zone_type' => 'video',
                'placement' => 'content',
                'floor_price' => '1.25',
                'passback' => '<div>fallback</div>',
                'image_width' => 320,
                'image_height' => 180,
                'html_template' => '<div>{{ad}}</div>',
                'custom_css' => '.ad{display:block;}',
                'bg_color' => '#ffffff',
                'sponsored_prefix' => 'Sponsored',
                'css_path' => 'https://cdn.publisher.test/app.css',
                'inline_video' => true,
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $zoneId = $adblockResponse->json('zone_id');

        $this->assertDatabaseHas('aq_zones', [
            'id' => $zoneId,
            'choose_type' => 'app',
            'mobile_app_id' => $visibleAppId,
            'name' => 'Publisher App Outstream',
            'format_key' => 'display_video',
            'size_key' => 'outstream',
            'zone_type' => 'video',
            'inline_video' => true,
        ]);

        $codes = $adblockResponse->json('codes');
        $this->assertArrayHasKey('js', $codes);
        $this->assertArrayHasKey('iframe', $codes);
        $this->assertArrayHasKey('overlay', $codes);
        $this->assertArrayHasKey('curl', $codes);

        $this->actingAs($publisher)
            ->delete(route('publisher.apps.destroy', $visibleAppId))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_telegram_mini_apps', [
            'id' => $visibleAppId,
            'is_deleted' => true,
        ]);
    }

    public function test_publisher_adblocks_page_lists_combined_inventory_and_supports_crud_and_tags(): void
    {
        $publisher = $this->publisher('publisher-zones@example.com');
        $otherPublisher = $this->publisher('other-zones@example.com');

        $site = Site::create([
            'publisher_id' => $publisher->id,
            'name' => 'Zone Site',
            'domain' => 'zone-site.test',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        $appId = DB::table('aq_telegram_mini_apps')->insertGetId([
            'user_id' => $publisher->id,
            'application_type' => 'telegram',
            'app_name' => 'Zone Mini App',
            'app_short_name' => 'zone_mini_app',
            'bot_username' => 'zoneminiappbot',
            'bot_token_hash' => hash('sha256', 'zone-mini-app'),
            'app_url' => 'https://zone-app.test',
            'category' => 'custom',
            'status' => 'active',
            'admin_approved' => true,
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $webZoneId = DB::table('aq_zones')->insertGetId([
            'site_id' => $site->id,
            'choose_type' => 'web',
            'mobile_app_id' => null,
            'name' => 'Homepage Banner',
            'format_key' => 'display_web',
            'size_key' => '300x250',
            'zone_type' => 'banner',
            'placement' => 'content',
            'floor_price' => 1.2500,
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $appZoneId = DB::table('aq_zones')->insertGetId([
            'site_id' => null,
            'choose_type' => 'app',
            'mobile_app_id' => $appId,
            'name' => 'Rewarded App Slot',
            'format_key' => 'display_video',
            'size_key' => 'rewarded',
            'zone_type' => 'video',
            'placement' => 'content',
            'floor_price' => 2.0000,
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherSite = Site::create([
            'publisher_id' => $otherPublisher->id,
            'name' => 'Hidden Zone Site',
            'domain' => 'hidden-zone.test',
            'language' => 'en',
            'status' => 'active',
            'is_deleted' => false,
        ]);

        DB::table('aq_zones')->insert([
            'site_id' => $otherSite->id,
            'choose_type' => 'web',
            'mobile_app_id' => null,
            'name' => 'Hidden Zone',
            'format_key' => 'display_web',
            'size_key' => '728x90',
            'zone_type' => 'banner',
            'placement' => 'content',
            'floor_price' => 0.5000,
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('aq_stats_daily')->insert([
            [
                'date' => now()->toDateString(),
                'site_id' => $site->id,
                'zone_id' => $webZoneId,
                'impressions' => 1200,
                'clicks' => 36,
                'revenue' => 15.0000,
                'publisher_earnings' => 12.0000,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'date' => now()->toDateString(),
                'site_id' => null,
                'zone_id' => $appZoneId,
                'impressions' => 500,
                'clicks' => 10,
                'revenue' => 4.5000,
                'publisher_earnings' => 3.7500,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.adblocks'))
            ->assertOk()
            ->assertSee('Adblocks')
            ->assertSee('Homepage Banner')
            ->assertSee('Rewarded App Slot')
            ->assertDontSee('Hidden Zone')
            ->assertSee('Zone Site')
            ->assertSee('Zone Mini App')
            ->assertSee('https://zone-site.test')
            ->assertSee('https://zone-app.test')
            ->assertSee('300x250')
            ->assertSee('Display Video')
            ->assertSee('Rewarded Video')
            ->assertSee('1,200', false)
            ->assertSee('36', false)
            ->assertSee('3.00%', false)
            ->assertSee('Get Tag')
            ->assertSee('Edit')
            ->assertSee('Delete');

        $createdResponse = $this->actingAs($publisher)
            ->postJson(route('publisher.adblocks.store'), [
                'choose_type' => 'app',
                'mobile_app_id' => $appId,
                'name' => 'In-App Native Slot',
                'format_id' => 'special_web',
                'size_id' => 'native',
                'zone_type' => 'native',
                'placement' => 'content',
                'floor_price' => '1.50',
                'passback' => '<div>fallback</div>',
                'bg_color' => '#ffffff',
                'status' => 'active',
                'target_age_min' => 20,
                'target_age_max' => 35,
                'target_gender' => 'female',
                'target_color' => 'blue',
                'target_height_min' => 160,
                'target_height_max' => 185,
                'target_weight_min' => 50,
                'target_weight_max' => 80,
                'frequency_views' => 3,
                'auto_reload' => true,
                'reload_time' => 30,
                'content_width_px' => 320,
                'content_height_px' => 100,
                'top_offset_px' => 24,
                'right_offset_px' => 16,
                'z_index_value' => 9999,
                'is_fixed' => true,
                'hide_side' => 'left',
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $createdZoneId = $createdResponse->json('zone_id');

        $this->assertDatabaseHas('aq_zones', [
            'id' => $createdZoneId,
            'mobile_app_id' => $appId,
            'choose_type' => 'app',
            'name' => 'In-App Native Slot',
            'format_key' => 'special_web',
            'size_key' => 'native',
            'target_age_min' => 20,
            'target_age_max' => 35,
            'target_gender' => 'female',
            'target_color' => 'blue',
            'frequency_views' => 3,
            'auto_reload' => true,
            'reload_time' => 30,
            'content_width_px' => 320,
            'content_height_px' => 100,
            'top_offset_px' => 24,
            'right_offset_px' => 16,
            'z_index_value' => 9999,
            'is_fixed' => true,
            'hide_side' => 'left',
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.adblocks.show', $webZoneId))
            ->assertOk()
            ->assertJson([
                'id' => $webZoneId,
                'choose_type' => 'web',
                'name' => 'Homepage Banner',
                'format_key' => 'display_web',
            ]);

        $this->actingAs($publisher)
            ->put(route('publisher.adblocks.update', $webZoneId), [
                'choose_type' => 'web',
                'site_id' => $site->id,
                'name' => 'Homepage Billboard',
                'format_id' => 'display_web',
                'size_id' => '970x250',
                'zone_type' => 'banner',
                'placement' => 'content',
                'floor_price' => '2.75',
                'passback' => '<div>updated</div>',
                'bg_color' => '#ffffff',
                'status' => 'paused',
            ])
            ->assertRedirect(route('publisher.adblocks'));

        $this->assertDatabaseHas('aq_zones', [
            'id' => $webZoneId,
            'name' => 'Homepage Billboard',
            'size_key' => '970x250',
            'status' => 'paused',
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.adblocks.tag', $webZoneId))
            ->assertOk()
            ->assertJsonStructure([
                'name',
                'ad_code',
                'codes' => ['js', 'iframe', 'overlay', 'curl'],
            ]);

        $tagPayload = $this->actingAs($publisher)
            ->get(route('publisher.adblocks.tag', $createdZoneId))
            ->assertOk()
            ->json();

        $this->assertStringContainsString('data-target-age-min="20"', $tagPayload['codes']['js']);
        $this->assertStringContainsString('data-target-gender="female"', $tagPayload['codes']['js']);
        $this->assertStringContainsString('data-content-width-px="320"', $tagPayload['codes']['js']);
        $this->assertStringContainsString('window.adshqipZoneSettings', $tagPayload['ad_code']);
        $this->assertStringContainsString('"hideSide":"left"', $tagPayload['ad_code']);
        $this->assertSame('20 - 35', $tagPayload['targeting']['age']);
        $this->assertSame('female', $tagPayload['targeting']['gender']);
        $this->assertSame('Yes', $tagPayload['settings']['autoload']);
        $this->assertSame('320 px', $tagPayload['settings']['content_width']);
        $this->assertSame('Left', $tagPayload['settings']['hide']);

        $updatedTagPayload = $this->actingAs($publisher)
            ->putJson(route('publisher.adblocks.update', $createdZoneId), [
                'choose_type' => 'app',
                'mobile_app_id' => $appId,
                'name' => 'In-App Native Slot',
                'format_id' => 'special_web',
                'size_id' => 'native',
                'zone_type' => 'native',
                'placement' => 'content',
                'floor_price' => '1.50',
                'passback' => '<div>fallback</div>',
                'bg_color' => '#ffffff',
                'status' => 'active',
                'target_age_min' => 46,
                'target_age_max' => null,
                'target_gender' => 'male',
                'target_color' => 'green',
                'target_height_min' => 170,
                'target_height_max' => 195,
                'target_weight_min' => 60,
                'target_weight_max' => 95,
                'frequency_views' => 5,
                'auto_reload' => false,
                'reload_time' => 45,
                'content_width_px' => 480,
                'content_height_px' => 180,
                'top_offset_px' => 12,
                'right_offset_px' => 22,
                'z_index_value' => 12000,
                'is_fixed' => false,
                'hide_side' => 'right',
            ])
            ->assertOk()
            ->json();

        $this->assertSame('More than 45', $updatedTagPayload['targeting']['age']);
        $this->assertSame('male', $updatedTagPayload['targeting']['gender']);
        $this->assertSame('No', $updatedTagPayload['settings']['autoload']);
        $this->assertSame('Right', $updatedTagPayload['settings']['hide']);
        $this->assertStringContainsString('data-target-gender="male"', $updatedTagPayload['codes']['js']);
        $this->assertStringContainsString('data-hide-side="right"', $updatedTagPayload['codes']['js']);

        $this->actingAs($publisher)
            ->delete(route('publisher.adblocks.destroy', $appZoneId))
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('aq_zones', [
            'id' => $appZoneId,
            'is_deleted' => true,
        ]);
    }

    public function test_publisher_referrals_page_shows_advertiser_referral_link_and_rows(): void
    {
        $publisher = $this->publisher('publisher-referrals@example.com');

        DB::table('aq_user_profiles')->insert([
            'user_id' => $publisher->id,
            'first_name' => 'Pia',
            'last_name' => 'Publisher',
            'country_code' => 'AL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $publisher->update(['referral_code' => 'PUBREF01']);

        $linkId = DB::table('aq_referral_links')->insertGetId([
            'referrer_id' => $publisher->id,
            'code' => 'PUBREF01',
            'target_role' => 'advertiser',
            'campaign_name' => 'Publisher Advertiser Referral',
            'landing_url' => 'http://localhost/register?role=advertiser&ref=PUBREF01',
            'commission_type' => 'percentage',
            'commission_rate' => 5,
            'commission_duration_days' => 365,
            'total_clicks' => 12,
            'total_signups' => 1,
            'total_qualified' => 1,
            'total_earned' => 45.5000,
            'status' => 'active',
            'is_deleted' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $advertiser = User::create([
            'email' => 'referred-advertiser@example.com',
            'password_hash' => Hash::make('password'),
            'role' => 'advertiser',
            'status' => 'active',
            'email_verified_at' => now(),
            'referral_code' => 'ADVREF01',
            'referred_by' => $publisher->id,
            'referred_at' => now()->subDays(8),
            'is_deleted' => false,
        ]);

        DB::table('aq_user_profiles')->insert([
            'user_id' => $advertiser->id,
            'first_name' => 'Rina',
            'last_name' => 'Advertiser',
            'country_code' => 'AL',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('aq_referral_conversions')->insert([
            'link_id' => $linkId,
            'referrer_id' => $publisher->id,
            'referred_user_id' => $advertiser->id,
            'referred_role' => 'advertiser',
            'is_qualified' => true,
            'qualified_at' => now()->subDays(7),
            'qualification_threshold' => 100,
            'commission_earned' => 45.5000,
            'commission_currency' => 'EUR',
            'status' => 'qualified',
            'created_at' => now()->subDays(8),
            'updated_at' => now(),
        ]);

        DB::table('aq_transactions')->insert([
            'user_id' => $advertiser->id,
            'type' => 'ad_spend',
            'amount' => 910.2500,
            'currency' => 'EUR',
            'balance_before' => 0,
            'balance_after' => 0,
            'description' => 'Referral spend',
            'status' => 'completed',
            'completed_at' => now()->subDays(3),
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        $this->actingAs($publisher)
            ->get(route('publisher.referrals'))
            ->assertOk()
            ->assertSee('Referrals')
            ->assertSee('register?role=advertiser&amp;ref=PUBREF01', false)
            ->assertSee('Rina Advertiser')
            ->assertSee('referred-advertiser@example.com')
            ->assertSee('EUR 910.25')
            ->assertSee('EUR 45.50');
    }

    private function publisher(string $email): User
    {
        return User::create([
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => 'publisher',
            'status' => 'active',
            'email_verified_at' => now(),
            'is_deleted' => false,
        ]);
    }
}

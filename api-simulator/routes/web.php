<?php

use App\Http\Controllers\AdvertiserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Sign-in page (Blade)
Route::get('/signin', function () {
    if (Auth::check()) {
        return redirect(match (Auth::user()->role) {
            'admin', 'manager' => '/admin',
            'publisher' => '/publisher',
            'advertiser' => '/advertisers',
            default => '/',
        });
    }
    return view('auth.signin');
})->name('signin');

// Register page (Blade)
Route::get('/register', [RegisterController::class, 'showForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register'])->name('register.submit');

// Redirect Laravel's default /login to /signin
Route::get('/login', function () {
    return redirect()->route('signin');
})->name('login');

// Auto-login bridge (for static signin.html → Laravel session)
Route::get('/auto-login', function (\Illuminate\Http\Request $request) {
    $email = $request->query('email');
    $password = $request->query('password');

    if (!$email || !$password) {
        return redirect()->route('signin');
    }

    $user = \App\Models\User::where('email', $email)->where('is_deleted', false)->first();

    if (!$user || !\Illuminate\Support\Facades\Hash::check($password, $user->password_hash)) {
        return redirect()->route('signin');
    }

    Auth::login($user);
    $request->session()->regenerate();
    $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);

    return redirect(match ($user->role) {
        'admin', 'manager' => '/admin',
        'publisher' => '/publisher',
        'advertiser' => '/advertisers',
        default => '/',
    });
});

// Web-based login (POST from signin.html or form)
Route::post('/web-login', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    $user = \App\Models\User::where('email', $request->email)
        ->where('is_deleted', false)
        ->first();

    if (! $user || ! \Illuminate\Support\Facades\Hash::check($request->password, $user->password_hash)) {
        return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
    }

    if ($user->status !== 'active') {
        return response()->json(['success' => false, 'message' => 'Account not active.'], 403);
    }

    Auth::login($user, $request->boolean('remember'));

    $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);

    $redirect = match ($user->role) {
        'admin'      => '/admin',
        'publisher'  => '/publisher',
        'advertiser' => '/advertisers',
        'manager'    => '/admin',
        default      => '/',
    };

    return response()->json([
        'success' => true,
        'message' => 'Login successful.',
        'redirect' => $redirect,
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,
        ],
    ]);
})->name('web.login');

// Logout
Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ─── Public Ad Serving (no auth, no FrameGuard, no CSRF) ───
Route::withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class])
    ->group(function () {
        Route::get('/serve/ad/{id}', [\App\Http\Controllers\Admin\AdCreativeController::class, 'serve'])->name('ad.serve');
        Route::get('/serve/ad/{id}/click', [\App\Http\Controllers\Admin\AdCreativeController::class, 'click'])->name('ad.click');
        Route::get('/serve/ad/{id}/view', [\App\Http\Controllers\Admin\AdCreativeController::class, 'view'])->name('ad.view');
        Route::get('/serve/ad/{id}/adblock', [\App\Http\Controllers\Admin\AdCreativeController::class, 'adblock'])->name('ad.adblock');
        Route::get('/serve/ad/{id}/conversion', [\App\Http\Controllers\Admin\AdCreativeController::class, 'conversion'])->name('ad.conversion');

        // S2S (Server-to-Server) Postback endpoint — accepts GET or POST
        Route::match(['get', 'post'], '/track/campaign/{id}/postback', [\App\Http\Controllers\Admin\AdCreativeController::class, 'postback'])->name('track.postback');

        // ─── Direct Campaign Serve & Tracking ───
        Route::get('/serve/direct/{id}', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'serve'])->name('direct.serve');
        Route::get('/serve/direct/{id}/click', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'click'])->name('direct.click');
        Route::get('/serve/direct/{id}/view', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'view'])->name('direct.view');
        Route::get('/serve/direct/{id}/adblock', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'adblock'])->name('direct.adblock');
        Route::get('/serve/direct/{id}/conversion', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'conversion'])->name('direct.conversion');

        // Direct Campaign S2S Postback
        Route::match(['get', 'post'], '/track/direct/{id}/postback', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'postback'])->name('direct.postback');

        // ─── Pixel Tracker Fire Endpoints ───
        Route::get('/track/pixel/{code}/pixel.js', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'fireJs'])->name('pixel.fire.js');
        Route::get('/track/pixel/{code}/pixel.gif', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'fireGif'])->name('pixel.fire.gif');
        Route::match(['get', 'post'], '/track/pixel/{code}/postback', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'firePostback'])->name('pixel.fire.postback');

        // ─── Zone Ad Serving (obfuscated path, token-based) ───
        Route::get('/d/{token}.js', [\App\Http\Controllers\ZoneServeController::class, 'serve'])->name('zone.serve');
    });

// Protected dashboards (role-restricted)
Route::middleware('auth')->group(function () {
    Route::get('/advertisers', [AdvertiserController::class, 'dashboard'])->middleware('role:advertiser')->name('advertiser.dashboard');
    Route::get('/advertisers/notifications', [AdvertiserController::class, 'notifications'])->middleware('role:advertiser')->name('advertiser.notifications');
    Route::post('/advertisers/notifications/{id}/read', [AdvertiserController::class, 'markNotificationRead'])->middleware('role:advertiser')->name('advertiser.notifications.read');
    Route::post('/advertisers/notifications/read-all', [AdvertiserController::class, 'markAllNotificationsRead'])->middleware('role:advertiser')->name('advertiser.notifications.readAll');
    
    Route::get('/publisher', [\App\Http\Controllers\PublisherController::class, 'dashboard'])->middleware('role:publisher')->name('publisher.dashboard');
    Route::get('/publisher/earnings', [\App\Http\Controllers\PublisherEarningsController::class, 'index'])->middleware('role:publisher')->name('publisher.earnings');
    Route::get('/publisher/notifications', [\App\Http\Controllers\PublisherController::class, 'notifications'])->middleware('role:publisher')->name('publisher.notifications');
    Route::post('/publisher/notifications/{id}/read', [\App\Http\Controllers\PublisherController::class, 'markNotificationRead'])->middleware('role:publisher')->name('publisher.notifications.read');
    Route::post('/publisher/notifications/read-all', [\App\Http\Controllers\PublisherController::class, 'markAllNotificationsRead'])->middleware('role:publisher')->name('publisher.notifications.readAll');
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->middleware('role:admin,manager')->name('admin.dashboard');

    // Admin sub-pages
    Route::middleware('role:admin,manager')->prefix('admin')->group(function () {
        Route::get('/campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'index'])->name('admin.campaigns');
        Route::get('/campaigns/create', [\App\Http\Controllers\Admin\CampaignController::class, 'create'])->name('admin.campaigns.create');
        Route::post('/campaigns', [\App\Http\Controllers\Admin\CampaignController::class, 'store'])->name('admin.campaigns.store');
        Route::patch('/campaigns/{id}/status', [\App\Http\Controllers\Admin\CampaignController::class, 'updateStatus'])->name('admin.campaigns.updateStatus');
        Route::delete('/campaigns/{id}', [\App\Http\Controllers\Admin\CampaignController::class, 'destroy'])->name('admin.campaigns.destroy');
        Route::patch('/campaigns/{id}/group', [\App\Http\Controllers\Admin\CampaignController::class, 'moveToGroup'])->name('admin.campaigns.moveToGroup');
        Route::post('/campaigns/{id}/duplicate', [\App\Http\Controllers\Admin\CampaignController::class, 'duplicate'])->name('admin.campaigns.duplicate');
        Route::get('/campaigns/export', [\App\Http\Controllers\Admin\CampaignController::class, 'export'])->name('admin.campaigns.export');
        Route::get('/campaigns/{id}', [\App\Http\Controllers\Admin\CampaignController::class, 'show'])->name('admin.campaigns.show');
        Route::get('/campaigns/{id}/edit', [\App\Http\Controllers\Admin\CampaignController::class, 'edit'])->name('admin.campaigns.edit');
        Route::put('/campaigns/{id}', [\App\Http\Controllers\Admin\CampaignController::class, 'update'])->name('admin.campaigns.update');
        Route::post('/campaigns/groups', [\App\Http\Controllers\Admin\CampaignController::class, 'storeGroup'])->name('admin.campaigns.groups.store');
        Route::post('/campaigns/pixels', [\App\Http\Controllers\Admin\CampaignController::class, 'storePixel'])->name('admin.campaigns.pixels.store');

        // Campaign Approvals
        Route::get('/campaign-approvals', [\App\Http\Controllers\Admin\CampaignApprovalController::class, 'index'])->name('admin.campaign-approvals');
        Route::patch('/campaign-approvals/{id}/approve', [\App\Http\Controllers\Admin\CampaignApprovalController::class, 'approve'])->name('admin.campaign-approvals.approve');
        Route::patch('/campaign-approvals/{id}/reject', [\App\Http\Controllers\Admin\CampaignApprovalController::class, 'reject'])->name('admin.campaign-approvals.reject');

        // Manage AdMarket Campaigns
        Route::get('/manage-admarket-campaigns', [\App\Http\Controllers\Admin\ManageAdMarketCampaignController::class, 'index'])->name('admin.manage-admarket-campaigns');
        Route::patch('/manage-admarket-campaigns/{id}/disallow-advertiser', [\App\Http\Controllers\Admin\ManageAdMarketCampaignController::class, 'disallowAdvertiser'])->name('admin.manage-admarket-campaigns.disallow-advertiser');
        Route::patch('/manage-admarket-campaigns/{id}/disallow-campaign', [\App\Http\Controllers\Admin\ManageAdMarketCampaignController::class, 'disallowCampaign'])->name('admin.manage-admarket-campaigns.disallow-campaign');

        // Direct Campaigns
        Route::get('/direct-campaigns', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'index'])->name('admin.direct-campaigns');
        Route::get('/direct-campaigns/create', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'create'])->name('admin.direct-campaigns.create');
        Route::post('/direct-campaigns', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'store'])->name('admin.direct-campaigns.store');
        Route::patch('/direct-campaigns/{id}/status', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'updateStatus'])->name('admin.direct-campaigns.updateStatus');
        Route::delete('/direct-campaigns/{id}', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'destroy'])->name('admin.direct-campaigns.destroy');
        Route::post('/direct-campaigns/{id}/duplicate', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'duplicate'])->name('admin.direct-campaigns.duplicate');
        Route::get('/direct-campaigns/export', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'export'])->name('admin.direct-campaigns.export');
        Route::get('/direct-campaigns/{id}', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'show'])->name('admin.direct-campaigns.show');
        Route::get('/direct-campaigns/{id}/edit', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'edit'])->name('admin.direct-campaigns.edit');
        Route::put('/direct-campaigns/{id}', [\App\Http\Controllers\Admin\DirectCampaignController::class, 'update'])->name('admin.direct-campaigns.update');

        // Direct Campaign Approvals
        Route::get('/direct-campaign-approvals', [\App\Http\Controllers\Admin\DirectCampaignApprovalController::class, 'index'])->name('admin.direct-campaign-approvals');
        Route::get('/direct-campaign-approvals/export', [\App\Http\Controllers\Admin\DirectCampaignApprovalController::class, 'export'])->name('admin.direct-campaign-approvals.export');
        Route::patch('/direct-campaign-approvals/{id}/approve', [\App\Http\Controllers\Admin\DirectCampaignApprovalController::class, 'approve'])->name('admin.direct-campaign-approvals.approve');
        Route::patch('/direct-campaign-approvals/{id}/reject', [\App\Http\Controllers\Admin\DirectCampaignApprovalController::class, 'reject'])->name('admin.direct-campaign-approvals.reject');

        // Direct Campaign Request Approvals
        Route::get('/direct-campaign-request-approvals', [\App\Http\Controllers\Admin\DirectCampaignRequestApprovalController::class, 'index'])->name('admin.direct-campaign-request-approvals');
        Route::patch('/direct-campaign-request-approvals/{id}/approve', [\App\Http\Controllers\Admin\DirectCampaignRequestApprovalController::class, 'approve'])->name('admin.direct-campaign-request-approvals.approve');
        Route::patch('/direct-campaign-request-approvals/{id}/reject', [\App\Http\Controllers\Admin\DirectCampaignRequestApprovalController::class, 'reject'])->name('admin.direct-campaign-request-approvals.reject');

        // Advertisers Management
        Route::get('/advertisers-manage', [\App\Http\Controllers\Admin\AdvertiserController::class, 'index'])->name('admin.advertisers');
        Route::post('/advertisers-manage', [\App\Http\Controllers\Admin\AdvertiserController::class, 'store'])->name('admin.advertisers.store');
        Route::get('/advertisers-manage/export', [\App\Http\Controllers\Admin\AdvertiserController::class, 'export'])->name('admin.advertisers.export');
        Route::get('/advertisers-manage/{id}', [\App\Http\Controllers\Admin\AdvertiserController::class, 'show'])->name('admin.advertisers.show');
        Route::put('/advertisers-manage/{id}', [\App\Http\Controllers\Admin\AdvertiserController::class, 'update'])->name('admin.advertisers.update');
        Route::delete('/advertisers-manage/{id}', [\App\Http\Controllers\Admin\AdvertiserController::class, 'destroy'])->name('admin.advertisers.destroy');
        Route::patch('/advertisers-manage/{id}/block', [\App\Http\Controllers\Admin\AdvertiserController::class, 'block'])->name('admin.advertisers.block');
        Route::patch('/advertisers-manage/{id}/unblock', [\App\Http\Controllers\Admin\AdvertiserController::class, 'unblock'])->name('admin.advertisers.unblock');
        Route::get('/advertisers-manage/{id}/login-as', [\App\Http\Controllers\Admin\AdvertiserController::class, 'loginAs'])->name('admin.advertisers.loginAs');
        Route::post('/advertisers-manage/{id}/notify', [\App\Http\Controllers\Admin\AdvertiserController::class, 'sendNotification'])->name('admin.advertisers.notify');

        // Publishers Management
        Route::get('/publishers-manage', [\App\Http\Controllers\Admin\PublisherController::class, 'index'])->name('admin.publishers');
        Route::post('/publishers-manage', [\App\Http\Controllers\Admin\PublisherController::class, 'store'])->name('admin.publishers.store');
        Route::get('/publishers-manage/export', [\App\Http\Controllers\Admin\PublisherController::class, 'export'])->name('admin.publishers.export');
        Route::get('/publishers-manage/{id}', [\App\Http\Controllers\Admin\PublisherController::class, 'show'])->name('admin.publishers.show');
        Route::put('/publishers-manage/{id}', [\App\Http\Controllers\Admin\PublisherController::class, 'update'])->name('admin.publishers.update');
        Route::delete('/publishers-manage/{id}', [\App\Http\Controllers\Admin\PublisherController::class, 'destroy'])->name('admin.publishers.destroy');
        Route::patch('/publishers-manage/{id}/block', [\App\Http\Controllers\Admin\PublisherController::class, 'block'])->name('admin.publishers.block');
        Route::patch('/publishers-manage/{id}/unblock', [\App\Http\Controllers\Admin\PublisherController::class, 'unblock'])->name('admin.publishers.unblock');
        Route::get('/publishers-manage/{id}/login-as', [\App\Http\Controllers\Admin\PublisherController::class, 'loginAs'])->name('admin.publishers.loginAs');
        Route::post('/publishers-manage/{id}/notify', [\App\Http\Controllers\Admin\PublisherController::class, 'sendNotification'])->name('admin.publishers.notify');

        // Sites Management
        Route::get('/sites', [\App\Http\Controllers\Admin\SitesController::class, 'index'])->name('admin.sites');
        Route::post('/sites', [\App\Http\Controllers\Admin\SitesController::class, 'store'])->name('admin.sites.store');
        Route::get('/sites/{id}/reports', [\App\Http\Controllers\Admin\SitesController::class, 'reports'])->name('admin.sites.reports');
        Route::get('/sites/{id}/reports/export', [\App\Http\Controllers\Admin\SitesController::class, 'exportReports'])->name('admin.sites.reports.export');
        Route::get('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'show'])->name('admin.sites.show');
        Route::put('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'update'])->name('admin.sites.update');
        Route::delete('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'destroy'])->name('admin.sites.destroy');
        Route::patch('/sites/{id}/status', [\App\Http\Controllers\Admin\SitesController::class, 'updateStatus'])->name('admin.sites.updateStatus');

        // AdBlocks Management (Zones)
        Route::get('/adblocks', [\App\Http\Controllers\Admin\AdBlocksController::class, 'index'])->name('admin.adblocks');
        Route::post('/adblocks/serve-settings', [\App\Http\Controllers\Admin\AdBlocksController::class, 'updateServeSettings'])->name('admin.adblocks.serveSettings');
        Route::post('/adblocks', [\App\Http\Controllers\Admin\AdBlocksController::class, 'store'])->name('admin.adblocks.store');
        Route::get('/adblocks/sizes-by-format/{formatKey}', [\App\Http\Controllers\Admin\AdBlocksController::class, 'getSizesByFormat'])->name('admin.adblocks.sizes');
        Route::get('/adblocks/{id}/reports', [\App\Http\Controllers\Admin\AdBlocksController::class, 'reports'])->name('admin.adblocks.reports');
        Route::get('/adblocks/{id}/reports/export', [\App\Http\Controllers\Admin\AdBlocksController::class, 'exportReports'])->name('admin.adblocks.reports.export');
        Route::get('/adblocks/{id}/preview', [\App\Http\Controllers\Admin\AdBlocksController::class, 'preview'])->name('admin.adblocks.preview');
        Route::get('/adblocks/{id}', [\App\Http\Controllers\Admin\AdBlocksController::class, 'show'])->name('admin.adblocks.show');
        Route::put('/adblocks/{id}', [\App\Http\Controllers\Admin\AdBlocksController::class, 'update'])->name('admin.adblocks.update');
        Route::delete('/adblocks/{id}', [\App\Http\Controllers\Admin\AdBlocksController::class, 'destroy'])->name('admin.adblocks.destroy');
        Route::patch('/adblocks/{id}/status', [\App\Http\Controllers\Admin\AdBlocksController::class, 'updateStatus'])->name('admin.adblocks.updateStatus');
        Route::put('/adblocks/{id}/targeting', [\App\Http\Controllers\Admin\AdBlocksController::class, 'updateTargeting'])->name('admin.adblocks.updateTargeting');
        Route::get('/adblocks/{id}/tag', [\App\Http\Controllers\Admin\AdBlocksController::class, 'getTag'])->name('admin.adblocks.tag');
        Route::post('/adblocks/{id}/regenerate-code', [\App\Http\Controllers\Admin\AdBlocksController::class, 'regenerateCode'])->name('admin.adblocks.regenerateCode');

        // Ad Formats / Creatives
        Route::get('/ad-formats', [\App\Http\Controllers\Admin\AdCreativeController::class, 'index'])->name('admin.adformats');
        Route::get('/ad-formats/export', [\App\Http\Controllers\Admin\AdCreativeController::class, 'export'])->name('admin.adformats.export');
        Route::patch('/ad-formats/{id}/weight', [\App\Http\Controllers\Admin\AdCreativeController::class, 'updateWeight'])->name('admin.adformats.updateWeight');
        Route::patch('/ad-formats/{id}/status', [\App\Http\Controllers\Admin\AdCreativeController::class, 'updateStatus'])->name('admin.adformats.updateStatus');
        Route::get('/ad-formats/{id}/edit', [\App\Http\Controllers\Admin\AdCreativeController::class, 'edit'])->name('admin.adformats.edit');
        Route::put('/ad-formats/{id}', [\App\Http\Controllers\Admin\AdCreativeController::class, 'update'])->name('admin.adformats.update');
        Route::get('/ad-formats/{id}/demo', [\App\Http\Controllers\Admin\AdCreativeController::class, 'demo'])->name('admin.adformats.demo');
        Route::get('/ad-formats/{id}/reports', [\App\Http\Controllers\Admin\AdCreativeController::class, 'reports'])->name('admin.adformats.reports');
        Route::get('/ad-formats/{id}/reports/export', [\App\Http\Controllers\Admin\AdCreativeController::class, 'exportReports'])->name('admin.adformats.reports.export');
        Route::delete('/ad-formats/{id}', [\App\Http\Controllers\Admin\AdCreativeController::class, 'destroy'])->name('admin.adformats.destroy');

        // Creative Approvals
        Route::get('/creative-approvals', [\App\Http\Controllers\Admin\CreativeApprovalController::class, 'index'])->name('admin.creative-approvals');
        Route::patch('/creative-approvals/{id}/approve', [\App\Http\Controllers\Admin\CreativeApprovalController::class, 'approve'])->name('admin.creative-approvals.approve');
        Route::patch('/creative-approvals/{id}/reject', [\App\Http\Controllers\Admin\CreativeApprovalController::class, 'reject'])->name('admin.creative-approvals.reject');

        // Mobile Application Approvals
        Route::get('/mobile-application-approvals', [\App\Http\Controllers\Admin\MobileApplicationApprovalController::class, 'index'])->name('admin.mobile-application-approvals');
        Route::patch('/mobile-application-approvals/{id}/approve', [\App\Http\Controllers\Admin\MobileApplicationApprovalController::class, 'approve'])->name('admin.mobile-application-approvals.approve');
        Route::patch('/mobile-application-approvals/{id}/reject', [\App\Http\Controllers\Admin\MobileApplicationApprovalController::class, 'reject'])->name('admin.mobile-application-approvals.reject');

        // Country-wise Bidding
        Route::get('/country-wise-bidding', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'index'])->name('admin.country-wise-bidding');
        Route::post('/country-wise-bidding', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'store'])->name('admin.country-wise-bidding.store');
        Route::get('/country-wise-bidding/export', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'export'])->name('admin.country-wise-bidding.export');
        Route::get('/country-wise-bidding/campaigns/{advertiserId}', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'getCampaigns'])->name('admin.country-wise-bidding.campaigns');
        Route::get('/country-wise-bidding/{id}', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'show'])->name('admin.country-wise-bidding.show');
        Route::put('/country-wise-bidding/{id}', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'update'])->name('admin.country-wise-bidding.update');
        Route::delete('/country-wise-bidding/{id}', [\App\Http\Controllers\Admin\CountryWiseBiddingController::class, 'destroy'])->name('admin.country-wise-bidding.destroy');

        // Zone Limitations
        Route::get('/zone-limitations', [\App\Http\Controllers\Admin\ZoneLimitationController::class, 'index'])->name('admin.zone-limitations');
        Route::post('/zone-limitations', [\App\Http\Controllers\Admin\ZoneLimitationController::class, 'store'])->name('admin.zone-limitations.store');
        Route::get('/zone-limitations/export', [\App\Http\Controllers\Admin\ZoneLimitationController::class, 'export'])->name('admin.zone-limitations.export');
        Route::get('/zone-limitations/zones', [\App\Http\Controllers\Admin\ZoneLimitationController::class, 'getZones'])->name('admin.zone-limitations.zones');
        Route::delete('/zone-limitations/{id}', [\App\Http\Controllers\Admin\ZoneLimitationController::class, 'destroy'])->name('admin.zone-limitations.destroy');

        // Pixel Trackers
        Route::get('/pixel-trackers', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'index'])->name('admin.pixel-trackers');
        Route::post('/pixel-trackers', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'store'])->name('admin.pixel-trackers.store');
        Route::get('/pixel-trackers/export', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'export'])->name('admin.pixel-trackers.export');
        Route::get('/pixel-trackers/campaigns/{advertiserId}', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'getCampaigns'])->name('admin.pixel-trackers.campaigns');
        Route::get('/pixel-trackers/{id}', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'show'])->name('admin.pixel-trackers.show');
        Route::put('/pixel-trackers/{id}', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'update'])->name('admin.pixel-trackers.update');
        Route::get('/pixel-trackers/{id}/code', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'getCode'])->name('admin.pixel-trackers.code');
        Route::post('/pixel-trackers/{id}/link', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'linkCampaign'])->name('admin.pixel-trackers.link');
        Route::delete('/pixel-trackers/{id}', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'destroy'])->name('admin.pixel-trackers.destroy');

        // Traffic Sources
        Route::get('/traffic-sources', [\App\Http\Controllers\Admin\TrafficSourceController::class, 'index'])->name('admin.traffic-sources');
        Route::post('/traffic-sources', [\App\Http\Controllers\Admin\TrafficSourceController::class, 'store'])->name('admin.traffic-sources.store');
        Route::get('/traffic-sources/export', [\App\Http\Controllers\Admin\TrafficSourceController::class, 'export'])->name('admin.traffic-sources.export');
        Route::delete('/traffic-sources/{id}', [\App\Http\Controllers\Admin\TrafficSourceController::class, 'destroy'])->name('admin.traffic-sources.destroy');

        // RTB - Real-Time Bidding
        Route::get('/rtb', [\App\Http\Controllers\Admin\RtbController::class, 'index'])->name('admin.rtb');
        Route::post('/rtb', [\App\Http\Controllers\Admin\RtbController::class, 'store'])->name('admin.rtb.store');
        Route::get('/rtb/export', [\App\Http\Controllers\Admin\RtbController::class, 'export'])->name('admin.rtb.export');
        Route::get('/rtb/{id}', [\App\Http\Controllers\Admin\RtbController::class, 'show'])->name('admin.rtb.show');
        Route::put('/rtb/{id}', [\App\Http\Controllers\Admin\RtbController::class, 'update'])->name('admin.rtb.update');
        Route::patch('/rtb/{id}/block', [\App\Http\Controllers\Admin\RtbController::class, 'block'])->name('admin.rtb.block');
        Route::patch('/rtb/{id}/unblock', [\App\Http\Controllers\Admin\RtbController::class, 'unblock'])->name('admin.rtb.unblock');
        Route::delete('/rtb/{id}', [\App\Http\Controllers\Admin\RtbController::class, 'destroy'])->name('admin.rtb.destroy');

        // Advertiser Approvals
        Route::get('/advertiser-approvals', [\App\Http\Controllers\Admin\AdvertiserApprovalController::class, 'index'])->name('admin.advertiser-approvals');
        Route::post('/advertiser-approvals', [\App\Http\Controllers\Admin\AdvertiserApprovalController::class, 'store'])->name('admin.advertiser-approvals.store');
        Route::get('/advertiser-approvals/export', [\App\Http\Controllers\Admin\AdvertiserApprovalController::class, 'export'])->name('admin.advertiser-approvals.export');
        Route::patch('/advertiser-approvals/{id}/approve', [\App\Http\Controllers\Admin\AdvertiserApprovalController::class, 'approve'])->name('admin.advertiser-approvals.approve');
        Route::patch('/advertiser-approvals/{id}/reject', [\App\Http\Controllers\Admin\AdvertiserApprovalController::class, 'reject'])->name('admin.advertiser-approvals.reject');

        // Publisher Approvals
        Route::get('/publisher-approvals', [\App\Http\Controllers\Admin\PublisherApprovalController::class, 'index'])->name('admin.publisher-approvals');
        Route::post('/publisher-approvals', [\App\Http\Controllers\Admin\PublisherApprovalController::class, 'store'])->name('admin.publisher-approvals.store');
        Route::get('/publisher-approvals/export', [\App\Http\Controllers\Admin\PublisherApprovalController::class, 'export'])->name('admin.publisher-approvals.export');
        Route::patch('/publisher-approvals/{id}/approve', [\App\Http\Controllers\Admin\PublisherApprovalController::class, 'approve'])->name('admin.publisher-approvals.approve');
        Route::patch('/publisher-approvals/{id}/reject', [\App\Http\Controllers\Admin\PublisherApprovalController::class, 'reject'])->name('admin.publisher-approvals.reject');

        // Advertiser Reports
        Route::get('/reports/advertiser', [\App\Http\Controllers\Admin\AdvertiserReportController::class, 'index'])->name('admin.reports.advertiser');
        Route::get('/reports/advertiser/export', [\App\Http\Controllers\Admin\AdvertiserReportController::class, 'export'])->name('admin.reports.advertiser.export');

        // Campaign Reports
        Route::get('/reports/campaign', [\App\Http\Controllers\Admin\CampaignReportController::class, 'index'])->name('admin.reports.campaign');
        Route::get('/reports/campaign/export', [\App\Http\Controllers\Admin\CampaignReportController::class, 'export'])->name('admin.reports.campaign.export');

        // Creative Reports
        Route::get('/reports/creative', [\App\Http\Controllers\Admin\CreativeReportController::class, 'index'])->name('admin.reports.creative');
        Route::get('/reports/creative/export', [\App\Http\Controllers\Admin\CreativeReportController::class, 'export'])->name('admin.reports.creative.export');

        // Publisher Reports
        Route::get('/reports/publisher', [\App\Http\Controllers\Admin\PublisherReportController::class, 'index'])->name('admin.reports.publisher');
        Route::get('/reports/publisher/export', [\App\Http\Controllers\Admin\PublisherReportController::class, 'export'])->name('admin.reports.publisher.export');

        // Site Reports
        Route::get('/reports/site', [\App\Http\Controllers\Admin\SiteReportController::class, 'index'])->name('admin.reports.site');
        Route::get('/reports/site/export', [\App\Http\Controllers\Admin\SiteReportController::class, 'export'])->name('admin.reports.site.export');

        // AdBlock Reports
        Route::get('/reports/adblock', [\App\Http\Controllers\Admin\AdblockReportController::class, 'index'])->name('admin.reports.adblock');
        Route::get('/reports/adblock/export', [\App\Http\Controllers\Admin\AdblockReportController::class, 'export'])->name('admin.reports.adblock.export');

        // Advertiser Payment History
        Route::get('/advertiser-payment-history', [\App\Http\Controllers\Admin\PaymentHistoryController::class, 'index'])->name('admin.advertiser-payment-history');
        Route::get('/advertiser-payment-history/export', [\App\Http\Controllers\Admin\PaymentHistoryController::class, 'export'])->name('admin.advertiser-payment-history.export');

        // Advertiser Payment Approvals
        Route::get('/advertiser-payment-approvals', [\App\Http\Controllers\Admin\AdvertiserPaymentApprovalController::class, 'index'])->name('admin.advertiser-payment-approvals');
        Route::get('/advertiser-payment-approvals/export', [\App\Http\Controllers\Admin\AdvertiserPaymentApprovalController::class, 'export'])->name('admin.advertiser-payment-approvals.export');
        Route::get('/advertiser-payment-approvals/{id}', [\App\Http\Controllers\Admin\AdvertiserPaymentApprovalController::class, 'show'])->name('admin.advertiser-payment-approvals.show');
        Route::patch('/advertiser-payment-approvals/{id}/approve', [\App\Http\Controllers\Admin\AdvertiserPaymentApprovalController::class, 'approve'])->name('admin.advertiser-payment-approvals.approve');
        Route::patch('/advertiser-payment-approvals/{id}/reject', [\App\Http\Controllers\Admin\AdvertiserPaymentApprovalController::class, 'reject'])->name('admin.advertiser-payment-approvals.reject');

        // Advertiser Deposits
        Route::get('/advertiser-deposits', [\App\Http\Controllers\Admin\AdvertiserDepositController::class, 'index'])->name('admin.advertiser-deposits');
        Route::get('/advertiser-deposits/export', [\App\Http\Controllers\Admin\AdvertiserDepositController::class, 'export'])->name('admin.advertiser-deposits.export');

        // Publisher Payment History
        Route::get('/publisher-payment-history', [\App\Http\Controllers\Admin\PublisherPaymentHistoryController::class, 'index'])->name('admin.publisher-payment-history');
        Route::get('/publisher-payment-history/export', [\App\Http\Controllers\Admin\PublisherPaymentHistoryController::class, 'export'])->name('admin.publisher-payment-history.export');

        // Publisher Payment Approvals
        Route::get('/publisher-payment-approvals', [\App\Http\Controllers\Admin\PublisherPaymentApprovalController::class, 'index'])->name('admin.publisher-payment-approvals');
        Route::get('/publisher-payment-approvals/export', [\App\Http\Controllers\Admin\PublisherPaymentApprovalController::class, 'export'])->name('admin.publisher-payment-approvals.export');
        Route::get('/publisher-payment-approvals/{id}', [\App\Http\Controllers\Admin\PublisherPaymentApprovalController::class, 'show'])->name('admin.publisher-payment-approvals.show');
        Route::patch('/publisher-payment-approvals/{id}/approve', [\App\Http\Controllers\Admin\PublisherPaymentApprovalController::class, 'approve'])->name('admin.publisher-payment-approvals.approve');

        // Publisher Invoices
        Route::get('/publisher-invoices', [\App\Http\Controllers\Admin\PublisherInvoiceController::class, 'index'])->name('admin.publisher-invoices');
        Route::get('/publisher-invoices/export', [\App\Http\Controllers\Admin\PublisherInvoiceController::class, 'export'])->name('admin.publisher-invoices.export');
        Route::get('/publisher-invoices/{id}', [\App\Http\Controllers\Admin\PublisherInvoiceController::class, 'show'])->name('admin.publisher-invoices.show');
        Route::patch('/publisher-invoices/{id}/approve', [\App\Http\Controllers\Admin\PublisherInvoiceController::class, 'approve'])->name('admin.publisher-invoices.approve');
        Route::get('/publisher-invoices/{id}/download', [\App\Http\Controllers\Admin\PublisherInvoiceController::class, 'download'])->name('admin.publisher-invoices.download');

        // Balance Sheet
        Route::get('/balance-sheet', [\App\Http\Controllers\Admin\BalanceSheetController::class, 'index'])->name('admin.balance-sheet');
        Route::get('/balance-sheet/export', [\App\Http\Controllers\Admin\BalanceSheetController::class, 'export'])->name('admin.balance-sheet.export');

        // Payouts
        Route::get('/payouts', [\App\Http\Controllers\Admin\PayoutController::class, 'index'])->name('admin.payouts');
        Route::get('/payouts/export', [\App\Http\Controllers\Admin\PayoutController::class, 'export'])->name('admin.payouts.export');
        Route::get('/payouts/{id}', [\App\Http\Controllers\Admin\PayoutController::class, 'show'])->name('admin.payouts.show');
        Route::patch('/payouts/{id}/approve', [\App\Http\Controllers\Admin\PayoutController::class, 'approve'])->name('admin.payouts.approve');
        Route::patch('/payouts/{id}/reject', [\App\Http\Controllers\Admin\PayoutController::class, 'reject'])->name('admin.payouts.reject');

        // Referral Invoices
        Route::get('/referral-invoices', [\App\Http\Controllers\Admin\ReferralInvoiceController::class, 'index'])->name('admin.referral-invoices');
        Route::get('/referral-invoices/export', [\App\Http\Controllers\Admin\ReferralInvoiceController::class, 'export'])->name('admin.referral-invoices.export');
        Route::get('/referral-invoices/{id}/download', [\App\Http\Controllers\Admin\ReferralInvoiceController::class, 'download'])->name('admin.referral-invoices.download');
        Route::patch('/referral-invoices/{id}/status', [\App\Http\Controllers\Admin\ReferralInvoiceController::class, 'updateStatus'])->name('admin.referral-invoices.update-status');
    });
});

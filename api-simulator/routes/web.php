<?php

use App\Http\Controllers\AdvertiserController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RegisterController;
use App\Support\ActivityLogger;
use App\Support\TwoFactorAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Sign-in page (Blade)
Route::get('/signin', function () {
    if (Auth::check()) {
        return redirect(match (Auth::user()->role) {
            'admin', 'manager', 'operational' => '/admin',
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
Route::get('/verify-email/{id}/{hash}', [RegisterController::class, 'verifyEmail'])->name('account.email.verify');

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

    if ($user->status !== 'active') {
        return redirect()->route('signin')->withErrors([
            'email' => WebLoginController::inactiveAccountMessageFor($user),
        ]);
    }

    if ($user->two_factor_enabled) {
        $twoFactorAuth = app(TwoFactorAuth::class);
        $availableMethods = $twoFactorAuth->availableMethods($user);

        if ($availableMethods === []) {
            return redirect()->route('signin')->withErrors([
                'email' => '2 Factor Authentication is enabled but not configured correctly. Please update your account settings.',
            ]);
        }

        if ($twoFactorAuth->shouldRequireChallenge($user, $request)) {
            $issuedCodes = [];

            foreach ([TwoFactorAuth::METHOD_EMAIL, TwoFactorAuth::METHOD_SMS] as $method) {
                if (! in_array($method, $availableMethods, true)) {
                    continue;
                }

                $code = $twoFactorAuth->generateOtpCode();
                $twoFactorAuth->deliverChannelCode($user, $method, $code);
                $issuedCodes[$method] = $twoFactorAuth->issueOtpPayload($code);
            }

            $request->session()->put('two_factor_login', [
                'user_id' => $user->id,
                'remember' => false,
                'started_at' => now()->toDateTimeString(),
                'available_methods' => $availableMethods,
                'current_method' => in_array(TwoFactorAuth::METHOD_EMAIL, $availableMethods, true)
                    ? TwoFactorAuth::METHOD_EMAIL
                    : (in_array(TwoFactorAuth::METHOD_SMS, $availableMethods, true)
                        ? TwoFactorAuth::METHOD_SMS
                        : ($availableMethods[0] ?? TwoFactorAuth::METHOD_TOTP)),
                'issued_codes' => $issuedCodes,
            ]);

            return redirect()->route('two-factor.challenge');
        }
    }

    Auth::login($user);
    $request->session()->regenerate();
    $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
    if ($user->two_factor_enabled) {
        $twoFactorAuth->rememberTrustedContext($user, $request);
    }

    return redirect(match ($user->role) {
        'admin', 'manager', 'operational' => '/admin',
        'publisher' => '/publisher',
        'advertiser' => '/advertisers',
        default => '/',
    });
});

// Web-based login (POST from signin.html or form)
Route::post('/web-login', [WebLoginController::class, 'login'])->name('web.login');
Route::get('/two-factor-challenge', [WebLoginController::class, 'showChallenge'])->name('two-factor.challenge');
Route::post('/two-factor-challenge', [WebLoginController::class, 'verifyChallenge'])->name('two-factor.verify');
Route::post('/two-factor-challenge/cancel', [WebLoginController::class, 'cancelChallenge'])->name('two-factor.cancel');

// Logout
Route::post('/logout', function () {
    if (Auth::check()) {
        app(ActivityLogger::class)->logRequest(request(), 200, [
            'action' => 'auth_logout',
            'description' => 'Auth logout',
            'entity_type' => 'user',
            'entity_id' => Auth::id(),
        ]);
    }

    Auth::logout();
    request()->session()->forget('two_factor_login');
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
        Route::get('/t/{trackingPath}/ad/{id}/click', [\App\Http\Controllers\Admin\AdCreativeController::class, 'mobileClick'])->name('ad.mobile.click');
        Route::get('/t/{trackingPath}/ad/{id}/view', [\App\Http\Controllers\Admin\AdCreativeController::class, 'mobileView'])->name('ad.mobile.view');
        Route::get('/t/{trackingPath}/ad/{id}/adblock', [\App\Http\Controllers\Admin\AdCreativeController::class, 'mobileAdblock'])->name('ad.mobile.adblock');
        Route::get('/t/{trackingPath}/ad/{id}/conversion', [\App\Http\Controllers\Admin\AdCreativeController::class, 'mobileConversion'])->name('ad.mobile.conversion');

        // S2S (Server-to-Server) Postback endpoint — accepts GET or POST
        Route::match(['get', 'post'], '/track/campaign/{id}/postback', [\App\Http\Controllers\Admin\AdCreativeController::class, 'postback'])->name('track.postback');

        // ─── Direct Campaign Serve & Tracking ───
        Route::get('/serve/direct/{id}', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'serve'])->name('direct.serve');
        Route::get('/serve/direct/{id}/click', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'click'])->name('direct.click');
        Route::get('/serve/direct/{id}/view', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'view'])->name('direct.view');
        Route::get('/serve/direct/{id}/adblock', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'adblock'])->name('direct.adblock');
        Route::get('/serve/direct/{id}/conversion', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'conversion'])->name('direct.conversion');
        Route::get('/t/{trackingPath}/direct/{id}/click', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'mobileClick'])->name('direct.mobile.click');
        Route::get('/t/{trackingPath}/direct/{id}/view', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'mobileView'])->name('direct.mobile.view');
        Route::get('/t/{trackingPath}/direct/{id}/adblock', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'mobileAdblock'])->name('direct.mobile.adblock');
        Route::get('/t/{trackingPath}/direct/{id}/conversion', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'mobileConversion'])->name('direct.mobile.conversion');

        // Direct Campaign S2S Postback
        Route::match(['get', 'post'], '/track/direct/{id}/postback', [\App\Http\Controllers\Admin\DirectCampaignServeController::class, 'postback'])->name('direct.postback');

        // ─── Pixel Tracker Fire Endpoints ───
        Route::get('/track/pixel/{code}/pixel.js', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'fireJs'])->name('pixel.fire.js');
        Route::get('/track/pixel/{code}/pixel.gif', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'fireGif'])->name('pixel.fire.gif');
        Route::match(['get', 'post'], '/track/pixel/{code}/postback', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'firePostback'])->name('pixel.fire.postback');
        Route::post('/track/pixel/{code}/keywords', [\App\Http\Controllers\Admin\PixelTrackerController::class, 'receiveKeywords'])->name('pixel.fire.keywords');

        // ─── Zone Ad Serving (obfuscated path, token-based) ───
        Route::get('/d/{token}.js', [\App\Http\Controllers\ZoneServeController::class, 'serve'])->name('zone.serve');

        // ─── Direct Link Serve ───
        Route::get('/dl/{code}', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'serve'])->name('direct-link.serve');
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
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->middleware('role:admin,manager,operational')->name('admin.dashboard');

    // Admin sub-pages
    Route::middleware(['role:admin,manager,operational', 'admin.permission', 'audit.admin'])->prefix('admin')->group(function () {
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

        // Account Settings
        Route::get('/account-settings', [\App\Http\Controllers\Admin\AccountSettingsController::class, 'show'])->name('admin.account-settings');
        Route::put('/account-settings', [\App\Http\Controllers\Admin\AccountSettingsController::class, 'update'])->name('admin.account-settings.update');
        Route::get('/personal-information', [\App\Http\Controllers\Admin\PersonalInformationController::class, 'show'])->name('admin.personal-information');
        Route::put('/personal-information', [\App\Http\Controllers\Admin\PersonalInformationController::class, 'update'])->name('admin.personal-information.update');
        Route::get('/company-information', [\App\Http\Controllers\Admin\CompanyInformationController::class, 'show'])->name('admin.company-information');
        Route::put('/company-information', [\App\Http\Controllers\Admin\CompanyInformationController::class, 'update'])->name('admin.company-information.update');
        Route::get('/billing-information', [\App\Http\Controllers\Admin\BillingInformationController::class, 'show'])->name('admin.billing-information');
        Route::put('/billing-information', [\App\Http\Controllers\Admin\BillingInformationController::class, 'update'])->name('admin.billing-information.update');
        Route::get('/app-configurations', [\App\Http\Controllers\Admin\AppConfigurationController::class, 'show'])->name('admin.app-configurations');
        Route::put('/app-configurations', [\App\Http\Controllers\Admin\AppConfigurationController::class, 'update'])->name('admin.app-configurations.update');
        Route::get('/audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('admin.audit-logs');
        Route::delete('/audit-logs/{auditLog}', [\App\Http\Controllers\Admin\AuditLogController::class, 'destroy'])->name('admin.audit-logs.destroy');
        Route::get('/activity-log-settings', [\App\Http\Controllers\Admin\ActivityLogSettingsController::class, 'show'])->name('admin.activity-log-settings');
        Route::put('/activity-log-settings', [\App\Http\Controllers\Admin\ActivityLogSettingsController::class, 'update'])->name('admin.activity-log-settings.update');
        Route::get('/two-factor-authentication', [\App\Http\Controllers\Admin\TwoFactorAuthenticationController::class, 'show'])->name('admin.two-factor-authentication');
        Route::put('/two-factor-authentication', [\App\Http\Controllers\Admin\TwoFactorAuthenticationController::class, 'update'])->name('admin.two-factor-authentication.update');
        Route::get('/user-roles', [\App\Http\Controllers\Admin\UserRoleController::class, 'index'])->name('admin.user-roles');
        Route::get('/user-roles/{userRole}/edit', [\App\Http\Controllers\Admin\UserRoleController::class, 'edit'])->name('admin.user-roles.edit');
        Route::put('/user-roles/{userRole}', [\App\Http\Controllers\Admin\UserRoleController::class, 'update'])->name('admin.user-roles.update');
        Route::get('/payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'show'])->name('admin.payment-settings');
        Route::put('/payment-settings', [\App\Http\Controllers\Admin\PaymentSettingsController::class, 'update'])->name('admin.payment-settings.update');
        Route::get('/cpm-geo-settings', [\App\Http\Controllers\Admin\CpmGeoSettingController::class, 'index'])->name('admin.cpm-geo-settings');
        Route::post('/cpm-geo-settings', [\App\Http\Controllers\Admin\CpmGeoSettingController::class, 'store'])->name('admin.cpm-geo-settings.store');
        Route::delete('/cpm-geo-settings/{cpmGeoSetting}', [\App\Http\Controllers\Admin\CpmGeoSettingController::class, 'destroy'])->name('admin.cpm-geo-settings.destroy');
        Route::get('/display-screens', [\App\Http\Controllers\Admin\DisplayScreenController::class, 'index'])->name('admin.display-screens');
        Route::post('/display-screens', [\App\Http\Controllers\Admin\DisplayScreenController::class, 'store'])->name('admin.display-screens.store');
        Route::put('/display-screens/{displayScreen}', [\App\Http\Controllers\Admin\DisplayScreenController::class, 'update'])->name('admin.display-screens.update');
        Route::patch('/display-screens/{displayScreen}/block', [\App\Http\Controllers\Admin\DisplayScreenController::class, 'block'])->name('admin.display-screens.block');
        Route::patch('/display-screens/{displayScreen}/unblock', [\App\Http\Controllers\Admin\DisplayScreenController::class, 'unblock'])->name('admin.display-screens.unblock');
        Route::get('/kyc-verifications', [\App\Http\Controllers\Admin\KycVerificationController::class, 'index'])->name('admin.kyc-verifications');
        Route::post('/kyc-verifications', [\App\Http\Controllers\Admin\KycVerificationController::class, 'store'])->name('admin.kyc-verifications.store');
        Route::get('/kyc-verifications/{kycVerification}/edit', [\App\Http\Controllers\Admin\KycVerificationController::class, 'edit'])->name('admin.kyc-verifications.edit');
        Route::put('/kyc-verifications/{kycVerification}', [\App\Http\Controllers\Admin\KycVerificationController::class, 'update'])->name('admin.kyc-verifications.update');
        Route::patch('/kyc-verifications/{kycVerification}/approve', [\App\Http\Controllers\Admin\KycVerificationController::class, 'approve'])->name('admin.kyc-verifications.approve');
        Route::patch('/kyc-verifications/{kycVerification}/reject', [\App\Http\Controllers\Admin\KycVerificationController::class, 'reject'])->name('admin.kyc-verifications.reject');

        // Mass Email
        Route::get('/mass-email', [\App\Http\Controllers\Admin\MassEmailController::class, 'index'])->name('admin.mass-email');
        Route::post('/mass-email/send', [\App\Http\Controllers\Admin\MassEmailController::class, 'send'])->name('admin.mass-email.send');
        Route::get('/mass-email/history', [\App\Http\Controllers\Admin\MassEmailController::class, 'history'])->name('admin.mass-email.history');

        // Add Direct Link
        Route::get('/add-direct-link', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'index'])->name('admin.add-direct-link');
        Route::post('/add-direct-link', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'store'])->name('admin.add-direct-link.store');
        Route::get('/add-direct-link/adblocks', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'getAdblocksByPublisher'])->name('admin.add-direct-link.adblocks');
        Route::patch('/add-direct-link/{directLink}/status', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'updateStatus'])->name('admin.add-direct-link.status');
        Route::delete('/add-direct-link/{directLink}', [\App\Http\Controllers\Admin\AddDirectLinkController::class, 'destroy'])->name('admin.add-direct-link.destroy');

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

        // Requests
        Route::get('/requests', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('admin.requests');
        Route::patch('/requests/{id}/approve', [\App\Http\Controllers\Admin\RequestController::class, 'approve'])->name('admin.requests.approve');
        Route::patch('/requests/{id}/reject', [\App\Http\Controllers\Admin\RequestController::class, 'reject'])->name('admin.requests.reject');

        // Legacy direct campaign request approvals
        Route::get('/direct-campaign-request-approvals', [\App\Http\Controllers\Admin\RequestController::class, 'index'])->name('admin.direct-campaign-request-approvals');
        Route::patch('/direct-campaign-request-approvals/{id}/approve', [\App\Http\Controllers\Admin\RequestController::class, 'approve'])->name('admin.direct-campaign-request-approvals.approve');
        Route::patch('/direct-campaign-request-approvals/{id}/reject', [\App\Http\Controllers\Admin\RequestController::class, 'reject'])->name('admin.direct-campaign-request-approvals.reject');

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

        // Platform Reports
        Route::get('/reports/platform', [\App\Http\Controllers\Admin\PlatformReportController::class, 'index'])->name('admin.reports.platform');
        Route::get('/reports/platform/export', [\App\Http\Controllers\Admin\PlatformReportController::class, 'export'])->name('admin.reports.platform.export');

        // Request Reports
        Route::get('/reports/requests', [\App\Http\Controllers\Admin\RequestReportController::class, 'index'])->name('admin.reports.requests');
        Route::post('/reports/requests', [\App\Http\Controllers\Admin\RequestReportController::class, 'store'])->name('admin.reports.requests.store');
        Route::get('/reports/requests/export', [\App\Http\Controllers\Admin\RequestReportController::class, 'export'])->name('admin.reports.requests.export');
        Route::get('/reports/requests/{requestReport}/download', [\App\Http\Controllers\Admin\RequestReportController::class, 'download'])->name('admin.reports.requests.download');
        Route::patch('/reports/requests/{requestReport}/status', [\App\Http\Controllers\Admin\RequestReportController::class, 'updateStatus'])->name('admin.reports.requests.status');
        Route::delete('/reports/requests/{requestReport}', [\App\Http\Controllers\Admin\RequestReportController::class, 'destroy'])->name('admin.reports.requests.destroy');

        // Graphical Reports (Geographic)
        Route::get('/reports/graphical', [\App\Http\Controllers\Admin\GraphicalReportController::class, 'index'])->name('admin.reports.graphical');
        Route::get('/reports/graphical/export', [\App\Http\Controllers\Admin\GraphicalReportController::class, 'export'])->name('admin.reports.graphical.export');

        // Environment Performance
        Route::get('/reports/environment', [\App\Http\Controllers\Admin\EnvironmentPerformanceController::class, 'index'])->name('admin.reports.environment');
        Route::get('/reports/environment/export', [\App\Http\Controllers\Admin\EnvironmentPerformanceController::class, 'export'])->name('admin.reports.environment.export');

        // Network Kit
        Route::get('/network-kit', [\App\Http\Controllers\Admin\NetworkKitController::class, 'index'])->name('admin.network-kit');
        Route::get('/network-kit/export', [\App\Http\Controllers\Admin\NetworkKitController::class, 'export'])->name('admin.network-kit.export');

        // DSP Report
        Route::get('/reports/dsp', [\App\Http\Controllers\Admin\DspReportController::class, 'index'])->name('admin.reports.dsp');
        Route::get('/reports/dsp/export', [\App\Http\Controllers\Admin\DspReportController::class, 'export'])->name('admin.reports.dsp.export');

        // SSP Report
        Route::get('/reports/ssp', [\App\Http\Controllers\Admin\SspReportController::class, 'index'])->name('admin.reports.ssp');
        Route::get('/reports/ssp/export', [\App\Http\Controllers\Admin\SspReportController::class, 'export'])->name('admin.reports.ssp.export');

        // Anti-fraud Clicks
        Route::get('/anti-fraud', [\App\Http\Controllers\Admin\AntiFraudController::class, 'index'])->name('admin.anti-fraud');
        Route::get('/anti-fraud/export', [\App\Http\Controllers\Admin\AntiFraudController::class, 'export'])->name('admin.anti-fraud.export');

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

        // Parent Categories (Targeting)
        Route::get('/parent-categories', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'index'])->name('admin.parent-categories');
        Route::post('/parent-categories', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'store'])->name('admin.parent-categories.store');
        Route::put('/parent-categories/{id}', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'update'])->name('admin.parent-categories.update');
        Route::delete('/parent-categories/{id}', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'destroy'])->name('admin.parent-categories.destroy');
        Route::patch('/parent-categories/{id}/block', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'block'])->name('admin.parent-categories.block');
        Route::patch('/parent-categories/{id}/unblock', [\App\Http\Controllers\Admin\ParentCategoryController::class, 'unblock'])->name('admin.parent-categories.unblock');

        // Categories (Targeting)
        Route::get('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('admin.categories');
        Route::post('/categories', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('admin.categories.store');
        Route::get('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('admin.categories.show');
        Route::put('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('admin.categories.update');
        Route::delete('/categories/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('admin.categories.destroy');
        Route::patch('/categories/{id}/block', [\App\Http\Controllers\Admin\CategoryController::class, 'block'])->name('admin.categories.block');
        Route::patch('/categories/{id}/unblock', [\App\Http\Controllers\Admin\CategoryController::class, 'unblock'])->name('admin.categories.unblock');

        Route::get('/operating-systems', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'index'])->name('admin.operating-systems');
        Route::post('/operating-systems', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'store'])->name('admin.operating-systems.store');
        Route::put('/operating-systems/{id}', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'update'])->name('admin.operating-systems.update');
        Route::patch('/operating-systems/{id}/block', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'block'])->name('admin.operating-systems.block');
        Route::patch('/operating-systems/{id}/unblock', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'unblock'])->name('admin.operating-systems.unblock');
        Route::delete('/operating-systems/{id}', [\App\Http\Controllers\Admin\OperatingSystemController::class, 'destroy'])->name('admin.operating-systems.destroy');

        Route::get('/browsers', [\App\Http\Controllers\Admin\BrowserController::class, 'index'])->name('admin.browsers');
        Route::post('/browsers', [\App\Http\Controllers\Admin\BrowserController::class, 'store'])->name('admin.browsers.store');
        Route::put('/browsers/{id}', [\App\Http\Controllers\Admin\BrowserController::class, 'update'])->name('admin.browsers.update');
        Route::patch('/browsers/{id}/block', [\App\Http\Controllers\Admin\BrowserController::class, 'block'])->name('admin.browsers.block');
        Route::patch('/browsers/{id}/unblock', [\App\Http\Controllers\Admin\BrowserController::class, 'unblock'])->name('admin.browsers.unblock');
        Route::delete('/browsers/{id}', [\App\Http\Controllers\Admin\BrowserController::class, 'destroy'])->name('admin.browsers.destroy');

        Route::get('/browser-languages', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'index'])->name('admin.browser-languages');
        Route::post('/browser-languages', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'store'])->name('admin.browser-languages.store');
        Route::put('/browser-languages/{id}', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'update'])->name('admin.browser-languages.update');
        Route::patch('/browser-languages/{id}/block', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'block'])->name('admin.browser-languages.block');
        Route::patch('/browser-languages/{id}/unblock', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'unblock'])->name('admin.browser-languages.unblock');
        Route::delete('/browser-languages/{id}', [\App\Http\Controllers\Admin\BrowserLanguageController::class, 'destroy'])->name('admin.browser-languages.destroy');

        Route::get('/devices', [\App\Http\Controllers\Admin\DeviceController::class, 'index'])->name('admin.devices');
        Route::post('/devices', [\App\Http\Controllers\Admin\DeviceController::class, 'store'])->name('admin.devices.store');
        Route::put('/devices/{id}', [\App\Http\Controllers\Admin\DeviceController::class, 'update'])->name('admin.devices.update');
        Route::patch('/devices/{id}/block', [\App\Http\Controllers\Admin\DeviceController::class, 'block'])->name('admin.devices.block');
        Route::patch('/devices/{id}/unblock', [\App\Http\Controllers\Admin\DeviceController::class, 'unblock'])->name('admin.devices.unblock');
        Route::delete('/devices/{id}', [\App\Http\Controllers\Admin\DeviceController::class, 'destroy'])->name('admin.devices.destroy');

        Route::get('/mobile-manufacturers', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'index'])->name('admin.mobile-manufacturers');
        Route::post('/mobile-manufacturers', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'store'])->name('admin.mobile-manufacturers.store');
        Route::put('/mobile-manufacturers/{id}', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'update'])->name('admin.mobile-manufacturers.update');
        Route::patch('/mobile-manufacturers/{id}/block', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'block'])->name('admin.mobile-manufacturers.block');
        Route::patch('/mobile-manufacturers/{id}/unblock', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'unblock'])->name('admin.mobile-manufacturers.unblock');
        Route::delete('/mobile-manufacturers/{id}', [\App\Http\Controllers\Admin\MobileManufacturerController::class, 'destroy'])->name('admin.mobile-manufacturers.destroy');

        Route::get('/mobile-capabilities', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'index'])->name('admin.mobile-capabilities');
        Route::post('/mobile-capabilities', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'store'])->name('admin.mobile-capabilities.store');
        Route::put('/mobile-capabilities/{id}', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'update'])->name('admin.mobile-capabilities.update');
        Route::patch('/mobile-capabilities/{id}/block', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'block'])->name('admin.mobile-capabilities.block');
        Route::patch('/mobile-capabilities/{id}/unblock', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'unblock'])->name('admin.mobile-capabilities.unblock');
        Route::delete('/mobile-capabilities/{id}', [\App\Http\Controllers\Admin\MobileCapabilityController::class, 'destroy'])->name('admin.mobile-capabilities.destroy');

        Route::get('/connection-types', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'index'])->name('admin.connection-types');
        Route::post('/connection-types', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'store'])->name('admin.connection-types.store');
        Route::put('/connection-types/{id}', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'update'])->name('admin.connection-types.update');
        Route::patch('/connection-types/{id}/block', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'block'])->name('admin.connection-types.block');
        Route::patch('/connection-types/{id}/unblock', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'unblock'])->name('admin.connection-types.unblock');
        Route::delete('/connection-types/{id}', [\App\Http\Controllers\Admin\ConnectionTypeController::class, 'destroy'])->name('admin.connection-types.destroy');

        Route::get('/carrier-isp-connections', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'index'])->name('admin.carrier-isp-connections');
        Route::post('/carrier-isp-connections', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'store'])->name('admin.carrier-isp-connections.store');
        Route::put('/carrier-isp-connections/{id}', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'update'])->name('admin.carrier-isp-connections.update');
        Route::patch('/carrier-isp-connections/{id}/block', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'block'])->name('admin.carrier-isp-connections.block');
        Route::patch('/carrier-isp-connections/{id}/unblock', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'unblock'])->name('admin.carrier-isp-connections.unblock');
        Route::delete('/carrier-isp-connections/{id}', [\App\Http\Controllers\Admin\CarrierIspConnectionController::class, 'destroy'])->name('admin.carrier-isp-connections.destroy');

        Route::get('/keywords', [\App\Http\Controllers\Admin\KeywordController::class, 'index'])->name('admin.keywords');
        Route::get('/keywords/list-active', [\App\Http\Controllers\Admin\KeywordController::class, 'listActive'])->name('admin.keywords.list-active');
        Route::get('/keywords/export', [\App\Http\Controllers\Admin\KeywordController::class, 'export'])->name('admin.keywords.export');
        Route::post('/keywords', [\App\Http\Controllers\Admin\KeywordController::class, 'store'])->name('admin.keywords.store');
        Route::post('/keywords/bulk-import', [\App\Http\Controllers\Admin\KeywordController::class, 'bulkImport'])->name('admin.keywords.bulk-import');
        Route::get('/keywords/{id}', [\App\Http\Controllers\Admin\KeywordController::class, 'show'])->name('admin.keywords.show');
        Route::put('/keywords/{id}', [\App\Http\Controllers\Admin\KeywordController::class, 'update'])->name('admin.keywords.update');
        Route::patch('/keywords/{id}/block', [\App\Http\Controllers\Admin\KeywordController::class, 'block'])->name('admin.keywords.block');
        Route::patch('/keywords/{id}/unblock', [\App\Http\Controllers\Admin\KeywordController::class, 'unblock'])->name('admin.keywords.unblock');
        Route::delete('/keywords/{id}', [\App\Http\Controllers\Admin\KeywordController::class, 'destroy'])->name('admin.keywords.destroy');
    });
});

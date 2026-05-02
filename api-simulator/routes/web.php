<?php

use App\Http\Controllers\AdvertiserController;
use App\Http\Controllers\Auth\WebLoginController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\RegisterController;
use App\Support\ActivityLogger;
use App\Support\SessionTracker;
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
Route::get('/ref/{code}', \App\Http\Controllers\ReferralRedirectController::class)->name('referrals.redirect');
Route::get('/team-invitations/{token}', [\App\Http\Controllers\Advertiser\TeamInvitationController::class, 'accept'])->name('advertiser.team-invitations.accept');
Route::get('/pricing-plans-feed', [\App\Http\Controllers\PricingPlanFeedController::class, 'index'])->name('pricing-plans.feed');
Route::options('/api-docs-feed', [\App\Http\Controllers\ApiDocFeedController::class, 'options'])->name('api-docs.feed.options');
Route::get('/api-docs-feed', [\App\Http\Controllers\ApiDocFeedController::class, 'index'])->name('api-docs.feed');
Route::options('/case-studies-feed', [\App\Http\Controllers\CaseStudyFeedController::class, 'options'])->name('case-studies.feed.options');
Route::get('/case-studies-feed', [\App\Http\Controllers\CaseStudyFeedController::class, 'index'])->name('case-studies.feed');
Route::options('/case-studies-feed/{slug}', [\App\Http\Controllers\CaseStudyFeedController::class, 'options'])->name('case-studies.feed.show.options');
Route::get('/case-studies-feed/{slug}', [\App\Http\Controllers\CaseStudyFeedController::class, 'show'])->name('case-studies.feed.show');
Route::options('/faq-feed', [\App\Http\Controllers\FaqFeedController::class, 'options'])->name('faq.feed.options');
Route::get('/faq-feed', [\App\Http\Controllers\FaqFeedController::class, 'index'])->name('faq.feed');
Route::options('/testimonials-feed', [\App\Http\Controllers\TestimonialFeedController::class, 'options'])->name('testimonials.feed.options');
Route::get('/testimonials-feed', [\App\Http\Controllers\TestimonialFeedController::class, 'index'])->name('testimonials.feed');
Route::options('/newsletter/subscribe', [\App\Http\Controllers\NewsletterSubscriptionController::class, 'options'])->name('newsletter.subscribe.options');
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterSubscriptionController::class, 'store'])->name('newsletter.subscribe');

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
    app(SessionTracker::class)->trackLogin($request, $user);
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
Route::post('/web-login-security-question', [WebLoginController::class, 'verifySecurityQuestion'])->name('web.login.security-question');
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

    app(SessionTracker::class)->revokeCurrent(request());

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
        Route::match(['get', 'post'], '/serve/ad/{id}/video-event', [\App\Http\Controllers\Admin\AdCreativeController::class, 'videoEvent'])->name('ad.video-event');
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
    Route::middleware(['role:advertiser', 'audit.advertiser'])->prefix('advertisers')->group(function () {
        Route::get('/campaigns', [\App\Http\Controllers\Advertiser\CampaignController::class, 'index'])->name('advertiser.campaigns');
        Route::get('/campaigns/create', [\App\Http\Controllers\Advertiser\CampaignController::class, 'create'])->name('advertiser.campaigns.create');
        Route::post('/campaigns', [\App\Http\Controllers\Advertiser\CampaignController::class, 'store'])->name('advertiser.campaigns.store');
        Route::patch('/campaigns/{id}/status', [\App\Http\Controllers\Advertiser\CampaignController::class, 'updateStatus'])->name('advertiser.campaigns.updateStatus');
        Route::delete('/campaigns/{id}', [\App\Http\Controllers\Advertiser\CampaignController::class, 'destroy'])->name('advertiser.campaigns.destroy');
        Route::patch('/campaigns/{id}/group', [\App\Http\Controllers\Advertiser\CampaignController::class, 'moveToGroup'])->name('advertiser.campaigns.moveToGroup');
        Route::post('/campaigns/{id}/duplicate', [\App\Http\Controllers\Advertiser\CampaignController::class, 'duplicate'])->name('advertiser.campaigns.duplicate');
        Route::get('/campaigns/export', [\App\Http\Controllers\Advertiser\CampaignController::class, 'export'])->name('advertiser.campaigns.export');
        Route::get('/campaigns/{id}', [\App\Http\Controllers\Advertiser\CampaignController::class, 'show'])->name('advertiser.campaigns.show');
        Route::get('/campaigns/{id}/edit', [\App\Http\Controllers\Advertiser\CampaignController::class, 'edit'])->name('advertiser.campaigns.edit');
        Route::put('/campaigns/{id}', [\App\Http\Controllers\Advertiser\CampaignController::class, 'update'])->name('advertiser.campaigns.update');
        Route::post('/campaigns/groups', [\App\Http\Controllers\Advertiser\CampaignController::class, 'storeGroup'])->name('advertiser.campaigns.groups.store');
        Route::post('/campaigns/pixels', [\App\Http\Controllers\Advertiser\CampaignController::class, 'storePixel'])->name('advertiser.campaigns.pixels.store');

        Route::get('/tracking/conversions', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'index'])->name('advertiser.tracking.conversions');
        Route::post('/tracking/conversions', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'store'])->name('advertiser.tracking.conversions.store');
        Route::put('/tracking/conversions/{id}', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'update'])->name('advertiser.tracking.conversions.update');
        Route::get('/tracking/conversions/{id}/code', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'code'])->name('advertiser.tracking.conversions.code');
        Route::post('/tracking/conversions/{id}/link', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'link'])->name('advertiser.tracking.conversions.link');
        Route::delete('/tracking/conversions/{id}', [\App\Http\Controllers\Advertiser\ConversionTrackingController::class, 'destroy'])->name('advertiser.tracking.conversions.destroy');
        Route::get('/tracking/goals', [\App\Http\Controllers\Advertiser\GoalController::class, 'index'])->name('advertiser.tracking.goals');
        Route::post('/tracking/goals', [\App\Http\Controllers\Advertiser\GoalController::class, 'store'])->name('advertiser.tracking.goals.store');
        Route::put('/tracking/goals/{id}', [\App\Http\Controllers\Advertiser\GoalController::class, 'update'])->name('advertiser.tracking.goals.update');
        Route::get('/tracking/goals/{id}/code', [\App\Http\Controllers\Advertiser\GoalController::class, 'code'])->name('advertiser.tracking.goals.code');
        Route::delete('/tracking/goals/{id}', [\App\Http\Controllers\Advertiser\GoalController::class, 'destroy'])->name('advertiser.tracking.goals.destroy');
        Route::get('/tracking/event-log', [\App\Http\Controllers\Advertiser\EventLogController::class, 'index'])->name('advertiser.tracking.event-log');
        Route::get('/tracking/event-log/export', [\App\Http\Controllers\Advertiser\EventLogController::class, 'export'])->name('advertiser.tracking.event-log.export');

        Route::get('/direct-campaigns', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'index'])->name('advertiser.direct-campaigns');
        Route::get('/direct-campaigns/create', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'create'])->name('advertiser.direct-campaigns.create');
        Route::post('/direct-campaigns', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'store'])->name('advertiser.direct-campaigns.store');
        Route::patch('/direct-campaigns/{id}/status', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'updateStatus'])->name('advertiser.direct-campaigns.updateStatus');
        Route::delete('/direct-campaigns/{id}', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'destroy'])->name('advertiser.direct-campaigns.destroy');
        Route::post('/direct-campaigns/{id}/duplicate', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'duplicate'])->name('advertiser.direct-campaigns.duplicate');
        Route::get('/direct-campaigns/export', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'export'])->name('advertiser.direct-campaigns.export');
        Route::get('/direct-campaigns/{id}', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'show'])->name('advertiser.direct-campaigns.show');
        Route::get('/direct-campaigns/{id}/edit', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'edit'])->name('advertiser.direct-campaigns.edit');
        Route::put('/direct-campaigns/{id}', [\App\Http\Controllers\Advertiser\DirectCampaignController::class, 'update'])->name('advertiser.direct-campaigns.update');

        Route::get('/audiences', [\App\Http\Controllers\Advertiser\AudienceController::class, 'index'])->name('advertiser.audiences');
        Route::post('/audiences', [\App\Http\Controllers\Advertiser\AudienceController::class, 'store'])->name('advertiser.audiences.store');
        Route::put('/audiences/{audience}', [\App\Http\Controllers\Advertiser\AudienceController::class, 'update'])->name('advertiser.audiences.update');
        Route::post('/audiences/attach', [\App\Http\Controllers\Advertiser\AudienceController::class, 'attach'])->name('advertiser.audiences.attach');
        Route::delete('/audiences/{campaign}/{audience}/detach', [\App\Http\Controllers\Advertiser\AudienceController::class, 'detach'])->name('advertiser.audiences.detach');
        Route::delete('/audiences/{audience}', [\App\Http\Controllers\Advertiser\AudienceController::class, 'destroy'])->name('advertiser.audiences.destroy');

        Route::get('/campaign-admarket', [\App\Http\Controllers\Advertiser\CampaignAdMarketController::class, 'index'])->name('advertiser.campaign-admarket');
        Route::patch('/campaign-admarket/{campaign}/publish', [\App\Http\Controllers\Advertiser\CampaignAdMarketController::class, 'publish'])->name('advertiser.campaign-admarket.publish');
        Route::patch('/campaign-admarket/{campaign}/unpublish', [\App\Http\Controllers\Advertiser\CampaignAdMarketController::class, 'unpublish'])->name('advertiser.campaign-admarket.unpublish');
        Route::put('/campaign-admarket/{campaign}/settings', [\App\Http\Controllers\Advertiser\CampaignAdMarketController::class, 'updateSettings'])->name('advertiser.campaign-admarket.settings');

        Route::get('/referrals', [\App\Http\Controllers\Advertiser\ReferralProgramController::class, 'index'])->name('advertiser.referrals');
        Route::post('/referrals', [\App\Http\Controllers\Advertiser\ReferralProgramController::class, 'store'])->name('advertiser.referrals.store');
        Route::get('/referrals/export', [\App\Http\Controllers\Advertiser\ReferralProgramController::class, 'export'])->name('advertiser.referrals.export');
        Route::patch('/referrals/{referralLink}/status', [\App\Http\Controllers\Advertiser\ReferralProgramController::class, 'updateStatus'])->name('advertiser.referrals.status');
        Route::delete('/referrals/{referralLink}', [\App\Http\Controllers\Advertiser\ReferralProgramController::class, 'destroy'])->name('advertiser.referrals.destroy');

        Route::get('/teams', [\App\Http\Controllers\Advertiser\TeamController::class, 'index'])->name('advertiser.teams');
        Route::post('/teams/invitations', [\App\Http\Controllers\Advertiser\TeamController::class, 'invite'])->name('advertiser.teams.invite');
        Route::patch('/teams/invitations/{invitation}/revoke', [\App\Http\Controllers\Advertiser\TeamController::class, 'revokeInvitation'])->name('advertiser.teams.invitations.revoke');
        Route::put('/teams/members/{member}', [\App\Http\Controllers\Advertiser\TeamController::class, 'updateMember'])->name('advertiser.teams.members.update');
        Route::patch('/teams/members/{member}/status', [\App\Http\Controllers\Advertiser\TeamController::class, 'updateStatus'])->name('advertiser.teams.members.status');
        Route::delete('/teams/members/{member}', [\App\Http\Controllers\Advertiser\TeamController::class, 'destroyMember'])->name('advertiser.teams.members.destroy');

        Route::get('/kyc-verification', [\App\Http\Controllers\Advertiser\KycVerificationController::class, 'index'])->name('advertiser.kyc-verification');
        Route::post('/kyc-verification', [\App\Http\Controllers\Advertiser\KycVerificationController::class, 'store'])->name('advertiser.kyc-verification.store');
        Route::put('/kyc-verification/{verification}', [\App\Http\Controllers\Advertiser\KycVerificationController::class, 'update'])->name('advertiser.kyc-verification.update');

        Route::get('/help-center', [\App\Http\Controllers\Advertiser\HelpCenterController::class, 'index'])->name('advertiser.help-center');
        Route::post('/help-center/tickets', [\App\Http\Controllers\Advertiser\HelpCenterController::class, 'store'])->name('advertiser.help-center.tickets.store');
        Route::get('/help-center/tickets/{ticket}', [\App\Http\Controllers\Advertiser\HelpCenterController::class, 'show'])->name('advertiser.help-center.tickets.show');
        Route::post('/help-center/tickets/{ticket}/reply', [\App\Http\Controllers\Advertiser\HelpCenterController::class, 'reply'])->name('advertiser.help-center.tickets.reply');
        Route::patch('/help-center/tickets/{ticket}/close', [\App\Http\Controllers\Advertiser\HelpCenterController::class, 'close'])->name('advertiser.help-center.tickets.close');
        Route::get('/support-tickets', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'index'])->name('advertiser.support-tickets');
        Route::post('/support-tickets', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'store'])->name('advertiser.support-tickets.store');
        Route::get('/support-tickets/export', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'export'])->name('advertiser.support-tickets.export');
        Route::get('/support-tickets/{ticket}', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'show'])->name('advertiser.support-tickets.show');
        Route::post('/support-tickets/{ticket}/reply', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'reply'])->name('advertiser.support-tickets.reply');
        Route::patch('/support-tickets/{ticket}/close', [\App\Http\Controllers\Advertiser\SupportTicketController::class, 'close'])->name('advertiser.support-tickets.close');
        Route::get('/feedback', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'index'])->name('advertiser.feedback');
        Route::post('/feedback', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'store'])->name('advertiser.feedback.store');
        Route::get('/feedback/export', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'export'])->name('advertiser.feedback.export');
        Route::get('/feedback/{feedback}', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'show'])->name('advertiser.feedback.show');
        Route::put('/feedback/{feedback}', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'update'])->name('advertiser.feedback.update');
        Route::patch('/feedback/{feedback}/close', [\App\Http\Controllers\Advertiser\FeedbackController::class, 'close'])->name('advertiser.feedback.close');
        Route::get('/contacts', [\App\Http\Controllers\Advertiser\ContactController::class, 'index'])->name('advertiser.contacts');
        Route::post('/contacts', [\App\Http\Controllers\Advertiser\ContactController::class, 'store'])->name('advertiser.contacts.store');
        Route::get('/contacts/export', [\App\Http\Controllers\Advertiser\ContactController::class, 'export'])->name('advertiser.contacts.export');
        Route::put('/contacts/{contact}', [\App\Http\Controllers\Advertiser\ContactController::class, 'update'])->name('advertiser.contacts.update');
        Route::patch('/contacts/{contact}/touch', [\App\Http\Controllers\Advertiser\ContactController::class, 'touch'])->name('advertiser.contacts.touch');
        Route::delete('/contacts/{contact}', [\App\Http\Controllers\Advertiser\ContactController::class, 'destroy'])->name('advertiser.contacts.destroy');

        Route::get('/ad-formats', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'index'])->name('advertiser.adformats');
        Route::get('/ad-formats/export', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'export'])->name('advertiser.adformats.export');
        Route::get('/ad-formats/{id}/edit', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'edit'])->name('advertiser.adformats.edit');
        Route::put('/ad-formats/{id}', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'update'])->name('advertiser.adformats.update');
        Route::get('/ad-formats/{id}/demo', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'demo'])->name('advertiser.adformats.demo');
        Route::get('/ad-formats/{id}/reports', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'reports'])->name('advertiser.adformats.reports');
        Route::get('/ad-formats/{id}/reports/export', [\App\Http\Controllers\Advertiser\AdCreativeController::class, 'exportReports'])->name('advertiser.adformats.reports.export');

        Route::get('/reports/overview', [\App\Http\Controllers\Advertiser\OverviewReportController::class, 'index'])->name('advertiser.reports.overview');
        Route::get('/reports/overview/export', [\App\Http\Controllers\Advertiser\OverviewReportController::class, 'export'])->name('advertiser.reports.overview.export');
        Route::get('/reports/graphical', [\App\Http\Controllers\Advertiser\GraphicalReportController::class, 'index'])->name('advertiser.reports.graphical');
        Route::get('/reports/graphical/export', [\App\Http\Controllers\Advertiser\GraphicalReportController::class, 'export'])->name('advertiser.reports.graphical.export');
        Route::get('/reports/campaign', [\App\Http\Controllers\Advertiser\CampaignReportController::class, 'index'])->name('advertiser.reports.campaign');
        Route::get('/reports/campaign/export', [\App\Http\Controllers\Advertiser\CampaignReportController::class, 'export'])->name('advertiser.reports.campaign.export');
        Route::get('/reports/creative', [\App\Http\Controllers\Advertiser\CreativeReportController::class, 'index'])->name('advertiser.reports.creative');
        Route::get('/reports/creative/export', [\App\Http\Controllers\Advertiser\CreativeReportController::class, 'export'])->name('advertiser.reports.creative.export');
        Route::get('/reports/video-creative', [\App\Http\Controllers\Advertiser\VideoCreativeReportController::class, 'index'])->name('advertiser.reports.video-creative');
        Route::get('/reports/video-creative/export', [\App\Http\Controllers\Advertiser\VideoCreativeReportController::class, 'export'])->name('advertiser.reports.video-creative.export');
        Route::get('/reports/site-url', [\App\Http\Controllers\Advertiser\SiteUrlReportController::class, 'index'])->name('advertiser.reports.site-url');
        Route::get('/reports/site-url/export', [\App\Http\Controllers\Advertiser\SiteUrlReportController::class, 'export'])->name('advertiser.reports.site-url.export');
        Route::get('/reports/group-settings', [\App\Http\Controllers\Advertiser\GroupSettingsReportController::class, 'index'])->name('advertiser.reports.group-settings');

        Route::get('/payments/history', [\App\Http\Controllers\Advertiser\PaymentHistoryController::class, 'index'])->name('advertiser.payments.history');
        Route::get('/payments/deposit-history', [\App\Http\Controllers\Advertiser\DepositHistoryController::class, 'index'])->name('advertiser.payments.deposit-history');
        Route::get('/payments/invoices', [\App\Http\Controllers\Advertiser\InvoiceHistoryController::class, 'index'])->name('advertiser.payments.invoices');
        Route::get('/payments/invoices/export', [\App\Http\Controllers\Advertiser\InvoiceHistoryController::class, 'export'])->name('advertiser.payments.invoices.export');
        Route::post('/payments/invoices/generate', [\App\Http\Controllers\Advertiser\InvoiceHistoryController::class, 'generateMissing'])->name('advertiser.payments.invoices.generate');
        Route::get('/payments/invoices/{id}/download', [\App\Http\Controllers\Advertiser\InvoiceHistoryController::class, 'download'])->name('advertiser.payments.invoices.download');
        Route::get('/payments/subscription-plan', [\App\Http\Controllers\Advertiser\SubscriptionPlanController::class, 'index'])->name('advertiser.payments.subscription-plan');
        Route::post('/payments/subscription-plan/{plan}', [\App\Http\Controllers\Advertiser\SubscriptionPlanController::class, 'subscribe'])->name('advertiser.payments.subscription-plan.subscribe');
        Route::get('/payments/subscription-plan/{subscription}/return', [\App\Http\Controllers\Advertiser\SubscriptionPlanController::class, 'paymentReturn'])->name('advertiser.payments.subscription-plan.return');
        Route::get('/payments/subscription-plan/{subscription}/payment-cancel', [\App\Http\Controllers\Advertiser\SubscriptionPlanController::class, 'paymentCancel'])->name('advertiser.payments.subscription-plan.payment-cancel');
        Route::patch('/payments/subscription-plan/subscriptions/{subscription}/cancel', [\App\Http\Controllers\Advertiser\SubscriptionPlanController::class, 'cancel'])->name('advertiser.payments.subscription-plan.cancel');
        Route::get('/payments/add-funds', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'create'])->name('advertiser.payments.add-funds');
        Route::post('/payments/add-funds', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'store'])->name('advertiser.payments.add-funds.store');
        Route::get('/payments/add-funds/{transaction}', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'confirm'])->name('advertiser.payments.add-funds.confirm');
        Route::post('/payments/add-funds/{transaction}/pay', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'pay'])->name('advertiser.payments.add-funds.pay');
        Route::get('/payments/add-funds/{transaction}/return', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'paymentReturn'])->name('advertiser.payments.add-funds.return');
        Route::get('/payments/add-funds/{transaction}/cancel', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'cancel'])->name('advertiser.payments.add-funds.cancel');
        Route::get('/payments/add-funds/{transaction}/authorize-hosted', [\App\Http\Controllers\Advertiser\AddFundsController::class, 'authorizeHosted'])->name('advertiser.payments.add-funds.authorize-hosted');
        Route::get('/account-settings', [\App\Http\Controllers\Advertiser\AccountSettingsController::class, 'show'])->name('advertiser.account-settings');
        Route::put('/account-settings', [\App\Http\Controllers\Advertiser\AccountSettingsController::class, 'update'])->name('advertiser.account-settings.update');
        Route::get('/personal-information', [\App\Http\Controllers\Advertiser\PersonalInformationController::class, 'show'])->name('advertiser.personal-information');
        Route::put('/personal-information', [\App\Http\Controllers\Advertiser\PersonalInformationController::class, 'update'])->name('advertiser.personal-information.update');
        Route::get('/company-information', [\App\Http\Controllers\Advertiser\CompanyInformationController::class, 'show'])->name('advertiser.company-information');
        Route::put('/company-information', [\App\Http\Controllers\Advertiser\CompanyInformationController::class, 'update'])->name('advertiser.company-information.update');
        Route::get('/audit-logs', [\App\Http\Controllers\Advertiser\AuditLogController::class, 'index'])->name('advertiser.audit-logs');
        Route::delete('/audit-logs/{auditLog}', [\App\Http\Controllers\Advertiser\AuditLogController::class, 'destroy'])->name('advertiser.audit-logs.destroy');
        Route::get('/two-factor-authentication', [\App\Http\Controllers\Advertiser\TwoFactorAuthenticationController::class, 'show'])->name('advertiser.two-factor-authentication');
        Route::put('/two-factor-authentication', [\App\Http\Controllers\Advertiser\TwoFactorAuthenticationController::class, 'update'])->name('advertiser.two-factor-authentication.update');
        Route::get('/notification-settings', [\App\Http\Controllers\Advertiser\NotificationSettingsController::class, 'show'])->name('advertiser.notification-settings');
        Route::put('/notification-settings', [\App\Http\Controllers\Advertiser\NotificationSettingsController::class, 'update'])->name('advertiser.notification-settings.update');

        Route::get('/network/country-wise-bidding', [\App\Http\Controllers\Advertiser\CountryWiseBiddingController::class, 'index'])->name('advertiser.network.country-wise-bidding');
        Route::post('/network/country-wise-bidding', [\App\Http\Controllers\Advertiser\CountryWiseBiddingController::class, 'store'])->name('advertiser.network.country-wise-bidding.store');
        Route::get('/network/country-wise-bidding/{id}', [\App\Http\Controllers\Advertiser\CountryWiseBiddingController::class, 'show'])->name('advertiser.network.country-wise-bidding.show');
        Route::put('/network/country-wise-bidding/{id}', [\App\Http\Controllers\Advertiser\CountryWiseBiddingController::class, 'update'])->name('advertiser.network.country-wise-bidding.update');
        Route::delete('/network/country-wise-bidding/{id}', [\App\Http\Controllers\Advertiser\CountryWiseBiddingController::class, 'destroy'])->name('advertiser.network.country-wise-bidding.destroy');
        Route::get('/network/traffic-sources', [\App\Http\Controllers\Advertiser\TrafficSourceController::class, 'index'])->name('advertiser.network.traffic-sources');
        Route::post('/network/traffic-sources', [\App\Http\Controllers\Advertiser\TrafficSourceController::class, 'store'])->name('advertiser.network.traffic-sources.store');
        Route::delete('/network/traffic-sources/{id}', [\App\Http\Controllers\Advertiser\TrafficSourceController::class, 'destroy'])->name('advertiser.network.traffic-sources.destroy');
        Route::get('/network/zone-limitations', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'index'])->name('advertiser.network.zone-limitations');
        Route::post('/network/zone-limitations', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'store'])->name('advertiser.network.zone-limitations.store');
        Route::get('/network/zone-limitations/zones', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'getZones'])->name('advertiser.network.zone-limitations.zones');
        Route::get('/network/zone-limitations/{id}', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'show'])->name('advertiser.network.zone-limitations.show');
        Route::put('/network/zone-limitations/{id}', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'update'])->name('advertiser.network.zone-limitations.update');
        Route::delete('/network/zone-limitations/{id}', [\App\Http\Controllers\Advertiser\ZoneLimitationController::class, 'destroy'])->name('advertiser.network.zone-limitations.destroy');
        Route::get('/network/pixel-trackers', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'index'])->name('advertiser.network.pixel-trackers');
        Route::post('/network/pixel-trackers', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'store'])->name('advertiser.network.pixel-trackers.store');
        Route::get('/network/pixel-trackers/{id}', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'show'])->name('advertiser.network.pixel-trackers.show');
        Route::put('/network/pixel-trackers/{id}', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'update'])->name('advertiser.network.pixel-trackers.update');
        Route::get('/network/pixel-trackers/{id}/code', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'getCode'])->name('advertiser.network.pixel-trackers.code');
        Route::post('/network/pixel-trackers/{id}/link', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'linkCampaign'])->name('advertiser.network.pixel-trackers.link');
        Route::delete('/network/pixel-trackers/{id}', [\App\Http\Controllers\Advertiser\PixelTrackerController::class, 'destroy'])->name('advertiser.network.pixel-trackers.destroy');
        Route::get('/network/network-kit', [\App\Http\Controllers\Advertiser\NetworkKitController::class, 'index'])->name('advertiser.network.network-kit');
    });
    Route::get('/advertisers/notifications', [AdvertiserController::class, 'notifications'])->middleware('role:advertiser')->name('advertiser.notifications');
    Route::post('/advertisers/notifications/{id}/read', [AdvertiserController::class, 'markNotificationRead'])->middleware('role:advertiser')->name('advertiser.notifications.read');
    Route::post('/advertisers/notifications/read-all', [AdvertiserController::class, 'markAllNotificationsRead'])->middleware('role:advertiser')->name('advertiser.notifications.readAll');
    Route::get('/advertisers/messages', [\App\Http\Controllers\Advertiser\AdvertiserMessageController::class, 'getUnread'])->middleware('role:advertiser')->name('advertiser.messages.unread');
    Route::post('/advertisers/messages/{message}/read', [\App\Http\Controllers\Advertiser\AdvertiserMessageController::class, 'markRead'])->middleware('role:advertiser')->name('advertiser.messages.read');
    Route::post('/advertisers/messages/read-all', [\App\Http\Controllers\Advertiser\AdvertiserMessageController::class, 'markAllRead'])->middleware('role:advertiser')->name('advertiser.messages.readAll');
    Route::get('/advertisers/push/vapid-key', [\App\Http\Controllers\Advertiser\PushNotificationController::class, 'getVapidKey'])->middleware('role:advertiser')->name('advertiser.push.vapid-key');
    Route::post('/advertisers/push/subscribe', [\App\Http\Controllers\Advertiser\PushNotificationController::class, 'subscribe'])->middleware('role:advertiser')->name('advertiser.push.subscribe');
    Route::post('/advertisers/push/unsubscribe', [\App\Http\Controllers\Advertiser\PushNotificationController::class, 'unsubscribe'])->middleware('role:advertiser')->name('advertiser.push.unsubscribe');
    Route::get('/advertisers/push/status', [\App\Http\Controllers\Advertiser\PushNotificationController::class, 'status'])->middleware('role:advertiser')->name('advertiser.push.status');
    Route::post('/advertisers/push/test', [\App\Http\Controllers\Advertiser\PushNotificationController::class, 'test'])->middleware('role:advertiser')->name('advertiser.push.test');

    Route::get('/publisher', [\App\Http\Controllers\PublisherController::class, 'dashboard'])->middleware('role:publisher')->name('publisher.dashboard');
    Route::get('/publisher/earnings', [\App\Http\Controllers\PublisherEarningsController::class, 'index'])->middleware('role:publisher')->name('publisher.earnings');
    Route::get('/publisher/sites', [\App\Http\Controllers\PublisherSiteController::class, 'index'])->middleware('role:publisher')->name('publisher.sites');
    Route::post('/publisher/sites', [\App\Http\Controllers\PublisherSiteController::class, 'store'])->middleware('role:publisher')->name('publisher.sites.store');
    Route::get('/publisher/sites/{id}', [\App\Http\Controllers\PublisherSiteController::class, 'show'])->middleware('role:publisher')->name('publisher.sites.show');
    Route::put('/publisher/sites/{id}', [\App\Http\Controllers\PublisherSiteController::class, 'update'])->middleware('role:publisher')->name('publisher.sites.update');
    Route::delete('/publisher/sites/{id}', [\App\Http\Controllers\PublisherSiteController::class, 'destroy'])->middleware('role:publisher')->name('publisher.sites.destroy');
    Route::get('/publisher/apps', [\App\Http\Controllers\PublisherApplicationController::class, 'index'])->middleware('role:publisher')->name('publisher.apps');
    Route::post('/publisher/apps', [\App\Http\Controllers\PublisherApplicationController::class, 'store'])->middleware('role:publisher')->name('publisher.apps.store');
    Route::get('/publisher/apps/{id}', [\App\Http\Controllers\PublisherApplicationController::class, 'show'])->middleware('role:publisher')->name('publisher.apps.show');
    Route::put('/publisher/apps/{id}', [\App\Http\Controllers\PublisherApplicationController::class, 'update'])->middleware('role:publisher')->name('publisher.apps.update');
    Route::delete('/publisher/apps/{id}', [\App\Http\Controllers\PublisherApplicationController::class, 'destroy'])->middleware('role:publisher')->name('publisher.apps.destroy');
    Route::post('/publisher/apps/{id}/adblocks', [\App\Http\Controllers\PublisherAppAdBlockController::class, 'store'])->middleware('role:publisher')->name('publisher.apps.adblocks.store');
    Route::get('/publisher/adblocks', [\App\Http\Controllers\PublisherAdBlockController::class, 'index'])->middleware('role:publisher')->name('publisher.adblocks');
    Route::post('/publisher/adblocks', [\App\Http\Controllers\PublisherAdBlockController::class, 'store'])->middleware('role:publisher')->name('publisher.adblocks.store');
    Route::get('/publisher/adblocks/{id}', [\App\Http\Controllers\PublisherAdBlockController::class, 'show'])->middleware('role:publisher')->name('publisher.adblocks.show');
    Route::put('/publisher/adblocks/{id}', [\App\Http\Controllers\PublisherAdBlockController::class, 'update'])->middleware('role:publisher')->name('publisher.adblocks.update');
    Route::delete('/publisher/adblocks/{id}', [\App\Http\Controllers\PublisherAdBlockController::class, 'destroy'])->middleware('role:publisher')->name('publisher.adblocks.destroy');
    Route::get('/publisher/adblocks/{id}/tag', [\App\Http\Controllers\PublisherAdBlockController::class, 'getTag'])->middleware('role:publisher')->name('publisher.adblocks.tag');
    Route::get('/publisher/notifications', [\App\Http\Controllers\PublisherController::class, 'notifications'])->middleware('role:publisher')->name('publisher.notifications');
    Route::post('/publisher/notifications/{id}/read', [\App\Http\Controllers\PublisherController::class, 'markNotificationRead'])->middleware('role:publisher')->name('publisher.notifications.read');
    Route::post('/publisher/notifications/read-all', [\App\Http\Controllers\PublisherController::class, 'markAllNotificationsRead'])->middleware('role:publisher')->name('publisher.notifications.readAll');
    Route::get('/publisher/referrals', [\App\Http\Controllers\PublisherReferralController::class, 'index'])->middleware('role:publisher')->name('publisher.referrals');
    Route::get('/publisher/admarket', [\App\Http\Controllers\PublisherAdMarketController::class, 'index'])->middleware('role:publisher')->name('publisher.admarket');
    Route::get('/publisher/admarket/campaigns', [\App\Http\Controllers\PublisherAdMarketController::class, 'campaigns'])->middleware('role:publisher')->name('publisher.admarket.campaigns');
    Route::get('/publisher/admarket/campaign/{id}', [\App\Http\Controllers\PublisherAdMarketController::class, 'getCampaignDetail'])->middleware('role:publisher')->name('publisher.admarket.campaign.detail');
    Route::get('/publisher/admarket/zones', [\App\Http\Controllers\PublisherAdMarketController::class, 'getPublisherZones'])->middleware('role:publisher')->name('publisher.admarket.zones');
    Route::post('/publisher/admarket/{id}/favorite', [\App\Http\Controllers\PublisherAdMarketController::class, 'toggleFavorite'])->middleware('role:publisher')->name('publisher.admarket.favorite');
    Route::post('/publisher/admarket/generate-tag', [\App\Http\Controllers\PublisherAdMarketController::class, 'generateCampaignTag'])->middleware('role:publisher')->name('publisher.admarket.generate-tag');
    Route::post('/publisher/admarket/generate-rotator', [\App\Http\Controllers\PublisherAdMarketController::class, 'generateRotator'])->middleware('role:publisher')->name('publisher.admarket.generate-rotator');
    Route::get('/publisher/direct-campaigns', [\App\Http\Controllers\PublisherDirectCampaignController::class, 'index'])->middleware('role:publisher')->name('publisher.direct-campaigns');
    Route::get('/publisher/direct-campaigns/{id}', [\App\Http\Controllers\PublisherDirectCampaignController::class, 'show'])->middleware('role:publisher')->name('publisher.direct-campaigns.show');
    Route::post('/publisher/direct-campaigns/{id}/tag', [\App\Http\Controllers\PublisherDirectCampaignController::class, 'generateTag'])->middleware('role:publisher')->name('publisher.direct-campaigns.tag');
    Route::get('/publisher/reports/overview', [\App\Http\Controllers\PublisherOverviewReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.overview');
    Route::get('/publisher/reports/overview/export', [\App\Http\Controllers\PublisherOverviewReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.overview.export');
    Route::get('/publisher/reports/geo', [\App\Http\Controllers\PublisherGeoReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.geo');
    Route::get('/publisher/reports/geo/export', [\App\Http\Controllers\PublisherGeoReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.geo.export');
    Route::get('/publisher/reports/sites', [\App\Http\Controllers\PublisherSiteReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.sites');
    Route::get('/publisher/reports/sites/export', [\App\Http\Controllers\PublisherSiteReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.sites.export');
    Route::get('/publisher/reports/apps', [\App\Http\Controllers\PublisherAppReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.apps');
    Route::get('/publisher/reports/apps/export', [\App\Http\Controllers\PublisherAppReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.apps.export');
    Route::get('/publisher/reports/adblocks', [\App\Http\Controllers\PublisherAdBlockReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.adblocks');
    Route::get('/publisher/reports/adblocks/export', [\App\Http\Controllers\PublisherAdBlockReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.adblocks.export');
    Route::get('/publisher/reports/requests', [\App\Http\Controllers\PublisherRequestReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.requests');
    Route::get('/publisher/reports/requests/export', [\App\Http\Controllers\PublisherRequestReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.requests.export');
    Route::get('/publisher/reports/groups', [\App\Http\Controllers\PublisherGroupReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.groups');
    Route::get('/publisher/reports/groups/export', [\App\Http\Controllers\PublisherGroupReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.groups.export');
    Route::get('/publisher/reports/traffic-sources', [\App\Http\Controllers\PublisherTrafficSourceReportController::class, 'index'])->middleware('role:publisher')->name('publisher.reports.traffic-sources');
    Route::get('/publisher/reports/traffic-sources/export', [\App\Http\Controllers\PublisherTrafficSourceReportController::class, 'export'])->middleware('role:publisher')->name('publisher.reports.traffic-sources.export');
    Route::get('/publisher/personal-information', [\App\Http\Controllers\Publisher\PersonalInformationController::class, 'show'])->middleware('role:publisher')->name('publisher.personal-information');
    Route::put('/publisher/personal-information', [\App\Http\Controllers\Publisher\PersonalInformationController::class, 'update'])->middleware('role:publisher')->name('publisher.personal-information.update');
    Route::get('/publisher/company-information', [\App\Http\Controllers\Publisher\CompanyInformationController::class, 'show'])->middleware('role:publisher')->name('publisher.company-information');
    Route::put('/publisher/company-information', [\App\Http\Controllers\Publisher\CompanyInformationController::class, 'update'])->middleware('role:publisher')->name('publisher.company-information.update');
    Route::get('/publisher/payment-settings', [\App\Http\Controllers\Publisher\PaymentSettingsController::class, 'show'])->middleware('role:publisher')->name('publisher.payment-settings');
    Route::put('/publisher/payment-settings', [\App\Http\Controllers\Publisher\PaymentSettingsController::class, 'update'])->middleware('role:publisher')->name('publisher.payment-settings.update');
    Route::get('/publisher/api-keys', [\App\Http\Controllers\Publisher\ApiKeyController::class, 'index'])->middleware('role:publisher')->name('publisher.api-keys');
    Route::post('/publisher/api-keys', [\App\Http\Controllers\Publisher\ApiKeyController::class, 'store'])->middleware('role:publisher')->name('publisher.api-keys.store');
    Route::patch('/publisher/api-keys/{apiKey}/revoke', [\App\Http\Controllers\Publisher\ApiKeyController::class, 'revoke'])->middleware('role:publisher')->name('publisher.api-keys.revoke');
    Route::patch('/publisher/api-keys/{apiKey}/activate', [\App\Http\Controllers\Publisher\ApiKeyController::class, 'activate'])->middleware('role:publisher')->name('publisher.api-keys.activate');
    Route::get('/publisher/kyc-verification', [\App\Http\Controllers\Publisher\KycVerificationController::class, 'index'])->middleware('role:publisher')->name('publisher.kyc-verification');
    Route::post('/publisher/kyc-verification', [\App\Http\Controllers\Publisher\KycVerificationController::class, 'store'])->middleware('role:publisher')->name('publisher.kyc-verification.store');
    Route::put('/publisher/kyc-verification/{verification}', [\App\Http\Controllers\Publisher\KycVerificationController::class, 'update'])->middleware('role:publisher')->name('publisher.kyc-verification.update');
    Route::get('/publisher/help-center', [\App\Http\Controllers\Publisher\HelpCenterController::class, 'index'])->middleware('role:publisher')->name('publisher.help-center');
    Route::post('/publisher/help-center/tickets', [\App\Http\Controllers\Publisher\HelpCenterController::class, 'store'])->middleware('role:publisher')->name('publisher.help-center.tickets.store');
    Route::get('/publisher/help-center/tickets/{ticket}', [\App\Http\Controllers\Publisher\HelpCenterController::class, 'show'])->middleware('role:publisher')->name('publisher.help-center.tickets.show');
    Route::post('/publisher/help-center/tickets/{ticket}/reply', [\App\Http\Controllers\Publisher\HelpCenterController::class, 'reply'])->middleware('role:publisher')->name('publisher.help-center.tickets.reply');
    Route::patch('/publisher/help-center/tickets/{ticket}/close', [\App\Http\Controllers\Publisher\HelpCenterController::class, 'close'])->middleware('role:publisher')->name('publisher.help-center.tickets.close');
    Route::get('/publisher/support-tickets', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'index'])->middleware('role:publisher')->name('publisher.support-tickets');
    Route::post('/publisher/support-tickets', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'store'])->middleware('role:publisher')->name('publisher.support-tickets.store');
    Route::get('/publisher/support-tickets/export', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'export'])->middleware('role:publisher')->name('publisher.support-tickets.export');
    Route::get('/publisher/support-tickets/{ticket}', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'show'])->middleware('role:publisher')->name('publisher.support-tickets.show');
    Route::post('/publisher/support-tickets/{ticket}/reply', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'reply'])->middleware('role:publisher')->name('publisher.support-tickets.reply');
    Route::patch('/publisher/support-tickets/{ticket}/close', [\App\Http\Controllers\Publisher\SupportTicketController::class, 'close'])->middleware('role:publisher')->name('publisher.support-tickets.close');
    Route::get('/publisher/feedback', [\App\Http\Controllers\Publisher\FeedbackController::class, 'index'])->middleware('role:publisher')->name('publisher.feedback');
    Route::post('/publisher/feedback', [\App\Http\Controllers\Publisher\FeedbackController::class, 'store'])->middleware('role:publisher')->name('publisher.feedback.store');
    Route::get('/publisher/feedback/export', [\App\Http\Controllers\Publisher\FeedbackController::class, 'export'])->middleware('role:publisher')->name('publisher.feedback.export');
    Route::get('/publisher/feedback/{feedback}', [\App\Http\Controllers\Publisher\FeedbackController::class, 'show'])->middleware('role:publisher')->name('publisher.feedback.show');
    Route::put('/publisher/feedback/{feedback}', [\App\Http\Controllers\Publisher\FeedbackController::class, 'update'])->middleware('role:publisher')->name('publisher.feedback.update');
    Route::patch('/publisher/feedback/{feedback}/close', [\App\Http\Controllers\Publisher\FeedbackController::class, 'close'])->middleware('role:publisher')->name('publisher.feedback.close');
    Route::get('/publisher/contacts', [\App\Http\Controllers\Publisher\ContactController::class, 'index'])->middleware('role:publisher')->name('publisher.contacts');
    Route::post('/publisher/contacts', [\App\Http\Controllers\Publisher\ContactController::class, 'store'])->middleware('role:publisher')->name('publisher.contacts.store');
    Route::get('/publisher/contacts/export', [\App\Http\Controllers\Publisher\ContactController::class, 'export'])->middleware('role:publisher')->name('publisher.contacts.export');
    Route::put('/publisher/contacts/{contact}', [\App\Http\Controllers\Publisher\ContactController::class, 'update'])->middleware('role:publisher')->name('publisher.contacts.update');
    Route::patch('/publisher/contacts/{contact}/touch', [\App\Http\Controllers\Publisher\ContactController::class, 'touch'])->middleware('role:publisher')->name('publisher.contacts.touch');
    Route::delete('/publisher/contacts/{contact}', [\App\Http\Controllers\Publisher\ContactController::class, 'destroy'])->middleware('role:publisher')->name('publisher.contacts.destroy');
    Route::get('/publisher/subscription-plan', [\App\Http\Controllers\PublisherSubscriptionPlanController::class, 'index'])->middleware('role:publisher')->name('publisher.subscription-plan');
    Route::post('/publisher/subscription-plan/{plan}', [\App\Http\Controllers\PublisherSubscriptionPlanController::class, 'subscribe'])->middleware('role:publisher')->name('publisher.subscription-plan.subscribe');
    Route::get('/publisher/subscription-plan/{subscription}/return', [\App\Http\Controllers\PublisherSubscriptionPlanController::class, 'paymentReturn'])->middleware('role:publisher')->name('publisher.subscription-plan.return');
    Route::get('/publisher/subscription-plan/{subscription}/payment-cancel', [\App\Http\Controllers\PublisherSubscriptionPlanController::class, 'paymentCancel'])->middleware('role:publisher')->name('publisher.subscription-plan.payment-cancel');
    Route::patch('/publisher/subscription-plan/{subscription}/cancel', [\App\Http\Controllers\PublisherSubscriptionPlanController::class, 'cancel'])->middleware('role:publisher')->name('publisher.subscription-plan.cancel');
    Route::get('/publisher/wallet', [\App\Http\Controllers\PublisherWalletController::class, 'index'])->middleware('role:publisher')->name('publisher.wallet');
    Route::get('/publisher/wallet/export', [\App\Http\Controllers\PublisherWalletController::class, 'export'])->middleware('role:publisher')->name('publisher.wallet.export');
    Route::get('/publisher/direct-links', [\App\Http\Controllers\PublisherDirectLinkController::class, 'index'])->middleware('role:publisher')->name('publisher.direct-links');
    Route::get('/publisher/direct-links/export', [\App\Http\Controllers\PublisherDirectLinkController::class, 'export'])->middleware('role:publisher')->name('publisher.direct-links.export');
    Route::get('/publisher/direct-links/{id}', [\App\Http\Controllers\PublisherDirectLinkController::class, 'show'])->middleware('role:publisher')->name('publisher.direct-links.show');
    Route::get('/publisher/invoices', [\App\Http\Controllers\PublisherInvoiceHistoryController::class, 'index'])->middleware('role:publisher')->name('publisher.invoices');
    Route::get('/publisher/invoices/export', [\App\Http\Controllers\PublisherInvoiceHistoryController::class, 'export'])->middleware('role:publisher')->name('publisher.invoices.export');
    Route::get('/publisher/invoices/{id}', [\App\Http\Controllers\PublisherInvoiceHistoryController::class, 'show'])->middleware('role:publisher')->name('publisher.invoices.show');
    Route::get('/publisher/invoices/{id}/download', [\App\Http\Controllers\PublisherInvoiceHistoryController::class, 'download'])->middleware('role:publisher')->name('publisher.invoices.download');
    Route::get('/publisher/payouts', [\App\Http\Controllers\PublisherPayoutController::class, 'index'])->middleware('role:publisher')->name('publisher.payouts');
    Route::post('/publisher/payouts', [\App\Http\Controllers\PublisherPayoutController::class, 'store'])->middleware('role:publisher')->name('publisher.payouts.store');
    Route::get('/publisher/payouts/{id}', [\App\Http\Controllers\PublisherPayoutController::class, 'show'])->middleware('role:publisher')->name('publisher.payouts.show');
    Route::get('/publisher/payment-history', [\App\Http\Controllers\PublisherPaymentHistoryController::class, 'index'])->middleware('role:publisher')->name('publisher.payment-history');
    Route::get('/publisher/payment-history/{year}/{month}', [\App\Http\Controllers\PublisherPaymentHistoryController::class, 'show'])->middleware('role:publisher')->name('publisher.payment-history.show');
    Route::get('/publisher/account-settings', [\App\Http\Controllers\Publisher\AccountSettingsController::class, 'show'])->middleware('role:publisher')->name('publisher.account-settings');
    Route::put('/publisher/account-settings', [\App\Http\Controllers\Publisher\AccountSettingsController::class, 'update'])->middleware('role:publisher')->name('publisher.account-settings.update');
    Route::get('/publisher/two-factor-authentication', [\App\Http\Controllers\Publisher\TwoFactorAuthenticationController::class, 'show'])->middleware('role:publisher')->name('publisher.two-factor-authentication');
    Route::put('/publisher/two-factor-authentication', [\App\Http\Controllers\Publisher\TwoFactorAuthenticationController::class, 'update'])->middleware('role:publisher')->name('publisher.two-factor-authentication.update');
    Route::get('/publisher/notification-settings', [\App\Http\Controllers\Publisher\NotificationSettingsController::class, 'show'])->middleware('role:publisher')->name('publisher.notification-settings');
    Route::put('/publisher/notification-settings', [\App\Http\Controllers\Publisher\NotificationSettingsController::class, 'update'])->middleware('role:publisher')->name('publisher.notification-settings.update');
    Route::get('/publisher/messages', [\App\Http\Controllers\PublisherController::class, 'messages'])->middleware('role:publisher')->name('publisher.messages');
    Route::get('/publisher/activity-log', [\App\Http\Controllers\PublisherActivityLogController::class, 'index'])->middleware('role:publisher')->name('publisher.activity-log');
    Route::delete('/publisher/activity-log/{id}', [\App\Http\Controllers\PublisherActivityLogController::class, 'destroy'])->middleware('role:publisher')->name('publisher.activity-log.destroy');
    Route::delete('/publisher/activity-log', [\App\Http\Controllers\PublisherActivityLogController::class, 'destroyAll'])->middleware('role:publisher')->name('publisher.activity-log.destroyAll');
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
        Route::get('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'index'])->name('admin.api-keys');
        Route::post('/api-keys', [\App\Http\Controllers\Admin\ApiKeyController::class, 'store'])->name('admin.api-keys.store');
        Route::patch('/api-keys/{apiKey}/revoke', [\App\Http\Controllers\Admin\ApiKeyController::class, 'revoke'])->name('admin.api-keys.revoke');
        Route::patch('/api-keys/{apiKey}/activate', [\App\Http\Controllers\Admin\ApiKeyController::class, 'activate'])->name('admin.api-keys.activate');
        Route::get('/api-docs', [\App\Http\Controllers\Admin\ApiDocController::class, 'index'])->name('admin.api-docs');
        Route::post('/api-docs', [\App\Http\Controllers\Admin\ApiDocController::class, 'store'])->name('admin.api-docs.store');
        Route::put('/api-docs/{apiDoc}', [\App\Http\Controllers\Admin\ApiDocController::class, 'update'])->name('admin.api-docs.update');
        Route::patch('/api-docs/{apiDoc}/publish', [\App\Http\Controllers\Admin\ApiDocController::class, 'publish'])->name('admin.api-docs.publish');
        Route::patch('/api-docs/{apiDoc}/unpublish', [\App\Http\Controllers\Admin\ApiDocController::class, 'unpublish'])->name('admin.api-docs.unpublish');
        Route::delete('/api-docs/{apiDoc}', [\App\Http\Controllers\Admin\ApiDocController::class, 'destroy'])->name('admin.api-docs.destroy');
        Route::get('/system-providers', [\App\Http\Controllers\Admin\SystemProviderController::class, 'index'])->name('admin.system-providers');
        Route::post('/system-providers', [\App\Http\Controllers\Admin\SystemProviderController::class, 'store'])->name('admin.system-providers.store');
        Route::post('/system-providers/sync', [\App\Http\Controllers\Admin\SystemProviderController::class, 'sync'])->name('admin.system-providers.sync');
        Route::put('/system-providers/{systemProvider}', [\App\Http\Controllers\Admin\SystemProviderController::class, 'update'])->name('admin.system-providers.update');
        Route::patch('/system-providers/{systemProvider}/test', [\App\Http\Controllers\Admin\SystemProviderController::class, 'test'])->name('admin.system-providers.test');
        Route::patch('/system-providers/{systemProvider}/activate', [\App\Http\Controllers\Admin\SystemProviderController::class, 'activate'])->name('admin.system-providers.activate');
        Route::patch('/system-providers/{systemProvider}/deactivate', [\App\Http\Controllers\Admin\SystemProviderController::class, 'deactivate'])->name('admin.system-providers.deactivate');
        Route::delete('/system-providers/{systemProvider}', [\App\Http\Controllers\Admin\SystemProviderController::class, 'destroy'])->name('admin.system-providers.destroy');
        Route::get('/data-export-jobs', [\App\Http\Controllers\Admin\DataExportJobController::class, 'index'])->name('admin.data-export-jobs');
        Route::post('/data-export-jobs', [\App\Http\Controllers\Admin\DataExportJobController::class, 'store'])->name('admin.data-export-jobs.store');
        Route::post('/data-export-jobs/{dataExportJob}/retry', [\App\Http\Controllers\Admin\DataExportJobController::class, 'retry'])->name('admin.data-export-jobs.retry');
        Route::get('/data-export-jobs/{dataExportJob}/download', [\App\Http\Controllers\Admin\DataExportJobController::class, 'download'])->name('admin.data-export-jobs.download');
        Route::delete('/data-export-jobs/clear-expired', [\App\Http\Controllers\Admin\DataExportJobController::class, 'clearExpired'])->name('admin.data-export-jobs.clear-expired');
        Route::delete('/data-export-jobs/{dataExportJob}', [\App\Http\Controllers\Admin\DataExportJobController::class, 'destroy'])->name('admin.data-export-jobs.destroy');
        Route::get('/sessions-security', [\App\Http\Controllers\Admin\SessionSecurityController::class, 'index'])->name('admin.sessions-security');
        Route::delete('/sessions-security/{adminSession}', [\App\Http\Controllers\Admin\SessionSecurityController::class, 'revoke'])->name('admin.sessions-security.revoke');
        Route::post('/sessions-security/clear-expired', [\App\Http\Controllers\Admin\SessionSecurityController::class, 'clearExpired'])->name('admin.sessions-security.clear-expired');
        Route::get('/telegram-integration', [\App\Http\Controllers\Admin\TelegramIntegrationController::class, 'index'])->name('admin.telegram-integration');
        Route::post('/telegram-integration/mini-apps', [\App\Http\Controllers\Admin\TelegramIntegrationController::class, 'storeMiniApp'])->name('admin.telegram-integration.store-mini-app');
        Route::put('/telegram-integration', [\App\Http\Controllers\Admin\TelegramIntegrationController::class, 'update'])->name('admin.telegram-integration.update');
        Route::patch('/telegram-integration/{telegramMiniApp}/activate', [\App\Http\Controllers\Admin\TelegramIntegrationController::class, 'activate'])->name('admin.telegram-integration.activate');
        Route::patch('/telegram-integration/{telegramMiniApp}/suspend', [\App\Http\Controllers\Admin\TelegramIntegrationController::class, 'suspend'])->name('admin.telegram-integration.suspend');

        // Account Managers
        Route::get('/account-managers', [\App\Http\Controllers\Admin\AccountManagerController::class, 'index'])->name('admin.account-managers');
        Route::post('/account-managers/assign', [\App\Http\Controllers\Admin\AccountManagerController::class, 'assign'])->name('admin.account-managers.assign');
        Route::post('/account-managers/bulk-assign', [\App\Http\Controllers\Admin\AccountManagerController::class, 'bulkAssign'])->name('admin.account-managers.bulk-assign');
        Route::get('/account-managers/{user}', [\App\Http\Controllers\Admin\AccountManagerController::class, 'show'])->name('admin.account-managers.show');

        Route::get('/transactions', [\App\Http\Controllers\Admin\TransactionController::class, 'index'])->name('admin.transactions');
        Route::get('/newsletters', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('admin.newsletters');
        Route::get('/newsletters/export', [\App\Http\Controllers\Admin\NewsletterController::class, 'export'])->name('admin.newsletters.export');
        Route::patch('/newsletters/{newsletter}/unsubscribe', [\App\Http\Controllers\Admin\NewsletterController::class, 'unsubscribe'])->name('admin.newsletters.unsubscribe');
        Route::patch('/newsletters/{newsletter}/resubscribe', [\App\Http\Controllers\Admin\NewsletterController::class, 'resubscribe'])->name('admin.newsletters.resubscribe');
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
        Route::get('/ad-serving-logs', [\App\Http\Controllers\Admin\AdServingLogController::class, 'index'])->name('admin.ad-serving-logs');
        Route::get('/ad-serving-logs/export', [\App\Http\Controllers\Admin\AdServingLogController::class, 'export'])->name('admin.ad-serving-logs.export');
        Route::delete('/ad-serving-logs/clear', [\App\Http\Controllers\Admin\AdServingLogController::class, 'clear'])->name('admin.ad-serving-logs.clear');
        Route::delete('/ad-serving-logs/{adServingLog}', [\App\Http\Controllers\Admin\AdServingLogController::class, 'destroy'])->name('admin.ad-serving-logs.destroy');

        // Graphical Reports (Geographic)
        Route::get('/reports/graphical', [\App\Http\Controllers\Admin\GraphicalReportController::class, 'index'])->name('admin.reports.graphical');
        Route::get('/reports/graphical/export', [\App\Http\Controllers\Admin\GraphicalReportController::class, 'export'])->name('admin.reports.graphical.export');
        Route::get('/geo-analytics', [\App\Http\Controllers\Admin\GeoAnalyticsController::class, 'index'])->name('admin.geo-analytics');
        Route::get('/geo-analytics/export', [\App\Http\Controllers\Admin\GeoAnalyticsController::class, 'export'])->name('admin.geo-analytics.export');

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
        Route::get('/video-analytics', [\App\Http\Controllers\Admin\VideoAnalyticsController::class, 'index'])->name('admin.video-analytics');
        Route::get('/video-analytics/export', [\App\Http\Controllers\Admin\VideoAnalyticsController::class, 'export'])->name('admin.video-analytics.export');

        // Anti-fraud Clicks
        Route::get('/anti-fraud', [\App\Http\Controllers\Admin\AntiFraudController::class, 'index'])->name('admin.anti-fraud');
        Route::get('/anti-fraud/export', [\App\Http\Controllers\Admin\AntiFraudController::class, 'export'])->name('admin.anti-fraud.export');
        Route::get('/fraud-rules', [\App\Http\Controllers\Admin\FraudRuleController::class, 'index'])->name('admin.fraud-rules');
        Route::post('/fraud-rules', [\App\Http\Controllers\Admin\FraudRuleController::class, 'store'])->name('admin.fraud-rules.store');
        Route::put('/fraud-rules/{fraudRule}', [\App\Http\Controllers\Admin\FraudRuleController::class, 'update'])->name('admin.fraud-rules.update');
        Route::patch('/fraud-rules/{fraudRule}/activate', [\App\Http\Controllers\Admin\FraudRuleController::class, 'activate'])->name('admin.fraud-rules.activate');
        Route::patch('/fraud-rules/{fraudRule}/deactivate', [\App\Http\Controllers\Admin\FraudRuleController::class, 'deactivate'])->name('admin.fraud-rules.deactivate');
        Route::delete('/fraud-rules/{fraudRule}', [\App\Http\Controllers\Admin\FraudRuleController::class, 'destroy'])->name('admin.fraud-rules.destroy');
        Route::get('/fraud-events', [\App\Http\Controllers\Admin\FraudEventController::class, 'index'])->name('admin.fraud-events');
        Route::get('/fraud-events/export', [\App\Http\Controllers\Admin\FraudEventController::class, 'export'])->name('admin.fraud-events.export');
        Route::delete('/fraud-events/clear', [\App\Http\Controllers\Admin\FraudEventController::class, 'clear'])->name('admin.fraud-events.clear');
        Route::patch('/fraud-events/records/{publisherFraudRecord}/resolve', [\App\Http\Controllers\Admin\FraudEventController::class, 'resolvePublisherRecord'])->name('admin.fraud-events.records.resolve');
        Route::delete('/fraud-events/{fraudEvent}', [\App\Http\Controllers\Admin\FraudEventController::class, 'destroy'])->name('admin.fraud-events.destroy');

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
        Route::patch('/advertiser-deposits/{deposit}/complete', [\App\Http\Controllers\Admin\AdvertiserDepositController::class, 'complete'])->name('admin.advertiser-deposits.complete');
        Route::patch('/advertiser-deposits/{deposit}/reject', [\App\Http\Controllers\Admin\AdvertiserDepositController::class, 'reject'])->name('admin.advertiser-deposits.reject');

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
        Route::get('/invoices', [\App\Http\Controllers\Admin\InvoiceController::class, 'index'])->name('admin.invoices');
        Route::get('/invoices/export', [\App\Http\Controllers\Admin\InvoiceController::class, 'export'])->name('admin.invoices.export');
        Route::get('/invoices/{id}/download', [\App\Http\Controllers\Admin\InvoiceController::class, 'download'])->name('admin.invoices.download');
        Route::get('/pricing-plans', [\App\Http\Controllers\Admin\PricingPlanController::class, 'index'])->name('admin.pricing-plans');
        Route::post('/pricing-plans', [\App\Http\Controllers\Admin\PricingPlanController::class, 'store'])->name('admin.pricing-plans.store');
        Route::put('/pricing-plans/{pricingPlan}', [\App\Http\Controllers\Admin\PricingPlanController::class, 'update'])->name('admin.pricing-plans.update');
        Route::patch('/pricing-plans/{pricingPlan}/block', [\App\Http\Controllers\Admin\PricingPlanController::class, 'block'])->name('admin.pricing-plans.block');
        Route::patch('/pricing-plans/{pricingPlan}/unblock', [\App\Http\Controllers\Admin\PricingPlanController::class, 'unblock'])->name('admin.pricing-plans.unblock');
        Route::get('/support-tickets', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('admin.support-tickets');
        Route::get('/support-tickets/{supportTicket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('admin.support-tickets.show');
        Route::put('/support-tickets/{supportTicket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'update'])->name('admin.support-tickets.update');
        Route::post('/support-tickets/{supportTicket}/reply', [\App\Http\Controllers\Admin\SupportTicketController::class, 'reply'])->name('admin.support-tickets.reply');
        Route::get('/feedback', [\App\Http\Controllers\Admin\AdvertiserFeedbackController::class, 'index'])->name('admin.feedback');
        Route::get('/feedback/export', [\App\Http\Controllers\Admin\AdvertiserFeedbackController::class, 'export'])->name('admin.feedback.export');
        Route::get('/feedback/{feedback}', [\App\Http\Controllers\Admin\AdvertiserFeedbackController::class, 'show'])->name('admin.feedback.show');
        Route::put('/feedback/{feedback}', [\App\Http\Controllers\Admin\AdvertiserFeedbackController::class, 'update'])->name('admin.feedback.update');
        Route::post('/feedback/{feedback}/testimonial', [\App\Http\Controllers\Admin\AdvertiserFeedbackController::class, 'createTestimonial'])->name('admin.feedback.testimonial');
        Route::get('/contacts', [\App\Http\Controllers\Admin\AdvertiserContactController::class, 'index'])->name('admin.contacts');
        Route::get('/contacts/export', [\App\Http\Controllers\Admin\AdvertiserContactController::class, 'export'])->name('admin.contacts.export');
        Route::get('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'index'])->name('admin.notifications');
        Route::post('/notifications', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'store'])->name('admin.notifications.store');
        Route::patch('/notifications/{notification}/read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markRead'])->name('admin.notifications.read');
        Route::patch('/notifications/{notification}/unread', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markUnread'])->name('admin.notifications.unread');
        Route::post('/notifications/read-all', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.read-all');
        Route::get('/notifications/api/list', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'getForUser'])->name('admin.notifications.api.list');
        Route::post('/notifications/{notification}/api/read', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markReadAjax'])->name('admin.notifications.api.read');
        Route::post('/notifications/api/read-all', [\App\Http\Controllers\Admin\AdminNotificationController::class, 'markAllReadAjax'])->name('admin.notifications.api.read-all');

        // Admin Messages (Internal)
        Route::get('/messages', [\App\Http\Controllers\Admin\AdminMessageController::class, 'index'])->name('admin.messages');
        Route::post('/messages', [\App\Http\Controllers\Admin\AdminMessageController::class, 'store'])->name('admin.messages.store');
        Route::get('/messages/api/unread', [\App\Http\Controllers\Admin\AdminMessageController::class, 'getUnread'])->name('admin.messages.api.unread');
        Route::get('/messages/{message}', [\App\Http\Controllers\Admin\AdminMessageController::class, 'show'])->name('admin.messages.show');
        Route::patch('/messages/{message}/read', [\App\Http\Controllers\Admin\AdminMessageController::class, 'markRead'])->name('admin.messages.read');
        Route::post('/messages/read-all', [\App\Http\Controllers\Admin\AdminMessageController::class, 'markAllRead'])->name('admin.messages.read-all');
        Route::patch('/messages/{message}/archive', [\App\Http\Controllers\Admin\AdminMessageController::class, 'archive'])->name('admin.messages.archive');

        // Admin Global Search
        Route::get('/search', [\App\Http\Controllers\Admin\AdminSearchController::class, 'search'])->name('admin.search');

        // Push Notifications
        Route::get('/push/vapid-key', [\App\Http\Controllers\Admin\PushNotificationController::class, 'getVapidKey'])->name('admin.push.vapid-key');
        Route::post('/push/subscribe', [\App\Http\Controllers\Admin\PushNotificationController::class, 'subscribe'])->name('admin.push.subscribe');
        Route::post('/push/unsubscribe', [\App\Http\Controllers\Admin\PushNotificationController::class, 'unsubscribe'])->name('admin.push.unsubscribe');
        Route::get('/push/status', [\App\Http\Controllers\Admin\PushNotificationController::class, 'status'])->name('admin.push.status');
        Route::post('/push/test', [\App\Http\Controllers\Admin\PushNotificationController::class, 'test'])->name('admin.push.test');
        Route::post('/push/send', [\App\Http\Controllers\Admin\PushNotificationController::class, 'sendToUser'])->name('admin.push.send');
        Route::post('/push/broadcast', [\App\Http\Controllers\Admin\PushNotificationController::class, 'broadcast'])->name('admin.push.broadcast');

        Route::get('/referral-codes', [\App\Http\Controllers\Admin\ReferralCodeController::class, 'index'])->name('admin.referral-codes');
        Route::post('/referral-codes', [\App\Http\Controllers\Admin\ReferralCodeController::class, 'store'])->name('admin.referral-codes.store');
        Route::patch('/referral-codes/{referralLink}/status', [\App\Http\Controllers\Admin\ReferralCodeController::class, 'updateStatus'])->name('admin.referral-codes.update-status');

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

        // FAQ Management
        Route::get('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('admin.faqs');
        Route::post('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('admin.faqs.store');
        Route::get('/faqs/{id}', [\App\Http\Controllers\Admin\FaqController::class, 'show'])->name('admin.faqs.show');
        Route::put('/faqs/{id}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('admin.faqs.update');
        Route::patch('/faqs/{id}/publish', [\App\Http\Controllers\Admin\FaqController::class, 'publish'])->name('admin.faqs.publish');
        Route::patch('/faqs/{id}/unpublish', [\App\Http\Controllers\Admin\FaqController::class, 'unpublish'])->name('admin.faqs.unpublish');
        Route::delete('/faqs/{id}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('admin.faqs.destroy');

        // Testimonials Management
        Route::get('/testimonials', [\App\Http\Controllers\Admin\TestimonialController::class, 'index'])->name('admin.testimonials');
        Route::post('/testimonials', [\App\Http\Controllers\Admin\TestimonialController::class, 'store'])->name('admin.testimonials.store');
        Route::get('/testimonials/{id}', [\App\Http\Controllers\Admin\TestimonialController::class, 'show'])->name('admin.testimonials.show');
        Route::put('/testimonials/{id}', [\App\Http\Controllers\Admin\TestimonialController::class, 'update'])->name('admin.testimonials.update');
        Route::patch('/testimonials/{id}/publish', [\App\Http\Controllers\Admin\TestimonialController::class, 'publish'])->name('admin.testimonials.publish');
        Route::patch('/testimonials/{id}/unpublish', [\App\Http\Controllers\Admin\TestimonialController::class, 'unpublish'])->name('admin.testimonials.unpublish');
        Route::patch('/testimonials/{id}/toggle-featured', [\App\Http\Controllers\Admin\TestimonialController::class, 'toggleFeatured'])->name('admin.testimonials.toggle-featured');
        Route::delete('/testimonials/{id}', [\App\Http\Controllers\Admin\TestimonialController::class, 'destroy'])->name('admin.testimonials.destroy');

        // Platform Announcements Management
        Route::get('/platform-announcements', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'index'])->name('admin.platform-announcements');
        Route::post('/platform-announcements', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'store'])->name('admin.platform-announcements.store');
        Route::put('/platform-announcements/{announcement}', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'update'])->name('admin.platform-announcements.update');
        Route::patch('/platform-announcements/{announcement}/publish', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'publish'])->name('admin.platform-announcements.publish');
        Route::patch('/platform-announcements/{announcement}/unpublish', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'unpublish'])->name('admin.platform-announcements.unpublish');
        Route::patch('/platform-announcements/{announcement}/archive', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'archive'])->name('admin.platform-announcements.archive');
        Route::post('/platform-announcements/{announcement}/notify', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'notify'])->name('admin.platform-announcements.notify');
        Route::delete('/platform-announcements/{announcement}', [\App\Http\Controllers\Admin\PlatformAnnouncementController::class, 'destroy'])->name('admin.platform-announcements.destroy');

        // Case Studies Management
        Route::get('/case-studies', [\App\Http\Controllers\Admin\CaseStudyController::class, 'index'])->name('admin.case-studies');
        Route::post('/case-studies', [\App\Http\Controllers\Admin\CaseStudyController::class, 'store'])->name('admin.case-studies.store');
        Route::put('/case-studies/{caseStudy}', [\App\Http\Controllers\Admin\CaseStudyController::class, 'update'])->name('admin.case-studies.update');
        Route::patch('/case-studies/{caseStudy}/publish', [\App\Http\Controllers\Admin\CaseStudyController::class, 'publish'])->name('admin.case-studies.publish');
        Route::patch('/case-studies/{caseStudy}/unpublish', [\App\Http\Controllers\Admin\CaseStudyController::class, 'unpublish'])->name('admin.case-studies.unpublish');
        Route::delete('/case-studies/{caseStudy}', [\App\Http\Controllers\Admin\CaseStudyController::class, 'destroy'])->name('admin.case-studies.destroy');
    });
});

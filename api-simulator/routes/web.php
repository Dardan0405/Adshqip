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
        Route::get('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'show'])->name('admin.sites.show');
        Route::put('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'update'])->name('admin.sites.update');
        Route::delete('/sites/{id}', [\App\Http\Controllers\Admin\SitesController::class, 'destroy'])->name('admin.sites.destroy');
        Route::patch('/sites/{id}/status', [\App\Http\Controllers\Admin\SitesController::class, 'updateStatus'])->name('admin.sites.updateStatus');

        // AdBlocks Management (Zones)
        Route::get('/adblocks', [\App\Http\Controllers\Admin\AdBlocksController::class, 'index'])->name('admin.adblocks');
        Route::post('/adblocks/serve-settings', [\App\Http\Controllers\Admin\AdBlocksController::class, 'updateServeSettings'])->name('admin.adblocks.serveSettings');
        Route::post('/adblocks', [\App\Http\Controllers\Admin\AdBlocksController::class, 'store'])->name('admin.adblocks.store');
        Route::get('/adblocks/sizes-by-format/{formatKey}', [\App\Http\Controllers\Admin\AdBlocksController::class, 'getSizesByFormat'])->name('admin.adblocks.sizes');
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
    });
});

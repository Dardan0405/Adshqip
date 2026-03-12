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

// Protected dashboards (role-restricted)
Route::middleware('auth')->group(function () {
    Route::get('/advertisers', [AdvertiserController::class, 'dashboard'])->middleware('role:advertiser')->name('advertiser.dashboard');
    Route::get('/publisher', [\App\Http\Controllers\PublisherController::class, 'dashboard'])->middleware('role:publisher')->name('publisher.dashboard');
    Route::get('/admin', [\App\Http\Controllers\AdminController::class, 'dashboard'])->middleware('role:admin,manager')->name('admin.dashboard');
});

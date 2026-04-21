<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminSession;
use App\Models\User;
use App\Support\SessionTracker;
use Illuminate\Http\Request;

class SessionSecurityController extends Controller
{
    public function index(Request $request)
    {
        $sessionsQuery = AdminSession::query()->with('user');

        if ($search = trim((string) $request->input('search'))) {
            $sessionsQuery->where(function ($query) use ($search) {
                $query->where('ip_address', 'like', '%' . $search . '%')
                    ->orWhere('browser', 'like', '%' . $search . '%')
                    ->orWhere('os', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
            });
        }

        if ($deviceType = $request->input('device_type')) {
            $sessionsQuery->where('device_type', $deviceType);
        }

        if ($request->filled('state')) {
            $isActive = $request->input('state') === 'active';
            $sessionsQuery->where($isActive ? 'expires_at' : 'expires_at', $isActive ? '>' : '<=', now());
        }

        $sessions = $sessionsQuery->latest('created_at')->paginate(15)->withQueryString();

        $summary = [
            'total' => AdminSession::count(),
            'active' => AdminSession::where('expires_at', '>', now())->count(),
            'expired' => AdminSession::where('expires_at', '<=', now())->count(),
            'two_factor_users' => User::where('two_factor_enabled', true)->count(),
            'telegram_linked_users' => User::whereNotNull('telegram_user_id')->count(),
        ];

        return view('admin.sessions-security.index', compact('sessions', 'summary'));
    }

    public function revoke(AdminSession $adminSession, SessionTracker $sessionTracker)
    {
        $sessionTracker->revokeToken($adminSession->token);

        return redirect()->route('admin.sessions-security')->with('success', 'Session revoked successfully.');
    }

    public function clearExpired(SessionTracker $sessionTracker)
    {
        $deleted = $sessionTracker->clearExpired();

        return redirect()->route('admin.sessions-security')->with('success', $deleted . ' expired sessions cleared.');
    }
}

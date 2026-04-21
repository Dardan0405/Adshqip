<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorBackupCode;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\SessionTracker;
use App\Support\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class WebLoginController extends Controller
{
    public function login(Request $request, TwoFactorAuth $twoFactorAuth, ActivityLogger $activityLogger, SessionTracker $sessionTracker)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_deleted', false)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json(['success' => false, 'message' => 'Invalid email or password.'], 401);
        }

        if ($user->status !== 'active') {
            $message = $this->inactiveAccountMessageFor($user);

            return response()->json(['success' => false, 'message' => $message], 403);
        }

        if ($user->two_factor_enabled) {
            $availableMethods = $twoFactorAuth->availableMethods($user);

            if ($availableMethods === []) {
                return response()->json([
                    'success' => false,
                    'message' => '2 Factor Authentication is enabled but not configured correctly. Please update your account settings.',
                ], 422);
            }

            if ($twoFactorAuth->shouldRequireChallenge($user, $request)) {
                $pending = $this->prepareChallengeSession($request, $user, $request->boolean('remember'), $twoFactorAuth, $availableMethods);

                return response()->json([
                    'success' => true,
                    'requires_two_factor' => true,
                    'message' => $this->challengePromptFor($pending['current_method']),
                    'redirect' => route('two-factor.challenge'),
                    'user' => [
                        'id' => $user->id,
                        'email' => $user->email,
                        'role' => $user->role,
                    ],
                ]);
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $sessionTracker->trackLogin($request, $user);
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
        if ($user->two_factor_enabled) {
            $twoFactorAuth->rememberTrustedContext($user, $request);
        }
        $activityLogger->logRequest($request, 200, [
            'action' => 'auth_login',
            'description' => 'Auth login',
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'redirect' => $this->redirectFor($user),
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function showChallenge(Request $request, TwoFactorAuth $twoFactorAuth)
    {
        $pending = $request->session()->get('two_factor_login');

        if (! $pending || empty($pending['user_id'])) {
            return redirect()->route('signin');
        }

        $user = User::find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('two_factor_login');

            return redirect()->route('signin');
        }

        $availableMethods = $pending['available_methods'] ?? [];
        $currentMethod = $pending['current_method'] ?? ($availableMethods[0] ?? TwoFactorAuth::METHOD_TOTP);

        $emailPayload = data_get($pending, 'issued_codes.' . TwoFactorAuth::METHOD_EMAIL);
        $emailPayloadExpired = ! is_array($emailPayload)
            || empty($emailPayload['expires_at'])
            || now()->greaterThan($emailPayload['expires_at']);

        if (in_array(TwoFactorAuth::METHOD_EMAIL, $availableMethods, true) && $emailPayloadExpired) {
            $code = $twoFactorAuth->generateOtpCode();
            $twoFactorAuth->deliverChannelCode($user, TwoFactorAuth::METHOD_EMAIL, $code);
            $pending['issued_codes'][TwoFactorAuth::METHOD_EMAIL] = $twoFactorAuth->issueOtpPayload($code);
            $request->session()->put('two_factor_login', $pending);
        }

        return view('auth.two-factor-challenge', [
            'user' => $user,
            'availableMethods' => $availableMethods,
            'currentMethod' => $currentMethod,
            'backupQuestion' => $user->two_factor_backup_question,
            'debugCode' => app()->isLocal()
                ? data_get($pending, 'issued_codes.' . $currentMethod . '.debug_code')
                : null,
        ]);
    }

    public function verifyChallenge(Request $request, TwoFactorAuth $twoFactorAuth, ActivityLogger $activityLogger, SessionTracker $sessionTracker)
    {
        $pending = $request->session()->get('two_factor_login');

        if (! $pending || empty($pending['user_id'])) {
            return redirect()->route('signin')->withErrors([
                'code' => 'Your login session expired. Please sign in again.',
            ]);
        }

        $request->validate([
            'method' => 'required|string|max:30',
            'code' => 'nullable|string|max:255',
        ]);

        $user = User::find($pending['user_id']);

        if (! $user) {
            $request->session()->forget('two_factor_login');

            return redirect()->route('signin')->withErrors([
                'code' => 'Account not found. Please sign in again.',
            ]);
        }

        $availableMethods = $pending['available_methods'] ?? [];
        $method = $request->input('method');

        if (! in_array($method, $availableMethods, true)) {
            return back()->withErrors([
                'code' => 'The selected verification method is not available.',
            ]);
        }

        $secret = $twoFactorAuth->decryptSecret($user->two_factor_secret);
        $submittedCode = trim((string) $request->input('code'));
        $verified = false;

        if ($method === TwoFactorAuth::METHOD_TOTP && $secret && $twoFactorAuth->verifyCode($secret, $submittedCode)) {
            $verified = true;
        } elseif (in_array($method, [TwoFactorAuth::METHOD_SMS, TwoFactorAuth::METHOD_EMAIL], true)) {
            $payload = data_get($pending, 'issued_codes.' . $method, []);
            $verified = is_array($payload) && $twoFactorAuth->verifyOtpPayload($payload, $submittedCode);

            if (app()->isLocal()) {
                Log::info('Two-factor OTP verification attempt', [
                    'user_id' => $user->id,
                    'method' => $method,
                    'submitted_code' => $submittedCode,
                    'has_payload' => is_array($payload),
                    'payload_debug_code' => is_array($payload) ? ($payload['debug_code'] ?? null) : null,
                    'payload_expires_at' => is_array($payload) ? ($payload['expires_at'] ?? null) : null,
                    'verified' => $verified,
                ]);
            }
        } else {
            $normalizedCode = $twoFactorAuth->normalizeBackupCode($submittedCode);
            $backupCode = TwoFactorBackupCode::where('user_id', $user->id)
                ->where('used', false)
                ->orderByDesc('id')
                ->get()
                ->first(function (TwoFactorBackupCode $code) use ($normalizedCode) {
                    return Hash::check($normalizedCode, $code->code_hash);
                });

            if ($backupCode) {
                $backupCode->update([
                    'used' => true,
                    'used_at' => now(),
                    'used_ip' => $request->ip(),
                ]);
                $verified = true;
            } elseif (filled($user->two_factor_backup_answer_hash) && Hash::check(strtolower($submittedCode), $user->two_factor_backup_answer_hash)) {
                $verified = true;
            }
        }

        DB::table('aq_two_factor_challenges')->insert([
            'user_id' => $user->id,
            'method' => $method,
            'result' => $verified ? 'success' : 'failed',
            'code_used' => substr(preg_replace('/\s+/', '', $submittedCode), 0, 10),
            'failure_count' => $verified ? 0 : 1,
            'locked_until' => null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'created_at' => now(),
        ]);

        if (! $verified) {
            return back()->withErrors([
                'code' => 'The authentication code or backup code is invalid.',
            ]);
        }

        Auth::login($user, (bool) ($pending['remember'] ?? false));
        $request->session()->forget('two_factor_login');
        $request->session()->regenerate();
        $sessionTracker->trackLogin($request, $user);

        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);
        $twoFactorAuth->rememberTrustedContext($user, $request);
        $activityLogger->logRequest($request, 200, [
            'action' => 'auth_two_factor_verified',
            'description' => 'Auth two factor verified',
            'entity_type' => 'user',
            'entity_id' => $user->id,
        ]);

        return redirect($this->redirectFor($user))->with('success', 'Two-factor verification successful.');
    }

    public function cancelChallenge(Request $request)
    {
        $request->session()->forget('two_factor_login');

        return redirect()->route('signin');
    }

    private function redirectFor(User $user): string
    {
        return match ($user->role) {
            'admin', 'manager', 'operational' => '/admin',
            'publisher' => '/publisher',
            'advertiser' => '/advertisers',
            default => '/',
        };
    }

    public static function inactiveAccountMessageFor(User $user): string
    {
        if (in_array($user->role, ['advertiser', 'publisher'], true) && $user->status === 'inactive' && $user->email_verified_at === null) {
            return 'Please verify your email before signing in.';
        }

        if ($user->role === 'advertiser' && $user->status === 'pending_verification') {
            return 'Your advertiser account is waiting for admin approval.';
        }

        if ($user->role === 'publisher' && $user->status === 'pending_verification') {
            return 'Your publisher account is waiting for admin approval.';
        }

        return 'Account not active.';
    }

    private function prepareChallengeSession(Request $request, User $user, bool $remember, TwoFactorAuth $twoFactorAuth, array $availableMethods): array
    {
        $issuedCodes = [];

        foreach ([TwoFactorAuth::METHOD_EMAIL, TwoFactorAuth::METHOD_SMS] as $method) {
            if (! in_array($method, $availableMethods, true)) {
                continue;
            }

            $code = $twoFactorAuth->generateOtpCode();
            $twoFactorAuth->deliverChannelCode($user, $method, $code);
            $issuedCodes[$method] = $twoFactorAuth->issueOtpPayload($code);
        }

        $currentMethod = in_array(TwoFactorAuth::METHOD_EMAIL, $availableMethods, true)
            ? TwoFactorAuth::METHOD_EMAIL
            : (in_array(TwoFactorAuth::METHOD_SMS, $availableMethods, true)
                ? TwoFactorAuth::METHOD_SMS
                : ($availableMethods[0] ?? TwoFactorAuth::METHOD_TOTP));

        $payload = [
            'user_id' => $user->id,
            'remember' => $remember,
            'started_at' => now()->toDateTimeString(),
            'available_methods' => $availableMethods,
            'current_method' => $currentMethod,
            'issued_codes' => $issuedCodes,
        ];

        $request->session()->put('two_factor_login', $payload);

        return $payload;
    }

    private function challengePromptFor(string $method): string
    {
        return match ($method) {
            TwoFactorAuth::METHOD_EMAIL => 'Two-factor verification required. We sent a code to your configured email address.',
            TwoFactorAuth::METHOD_SMS => 'Two-factor verification required. We sent a code to your configured phone number.',
            TwoFactorAuth::METHOD_BACKUP => 'Two-factor verification required. Use a backup code or answer your recovery question.',
            default => 'Two-factor verification required.',
        };
    }
}

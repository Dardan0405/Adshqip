<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformSetting;
use App\Models\ReferralConversion;
use App\Models\ReferralLink;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\TwoFactorAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login a user and return a JSON response with role-based redirect.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_deleted', false)
            ->first();

        if (! $user || ! Hash::check($request->password, $user->password_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account is not active. Current status: ' . $user->status,
            ], 403);
        }

        // Security question challenge (for users who have set a backup question without full 2FA)
        if (! $user->two_factor_enabled && filled($user->two_factor_backup_question) && filled($user->two_factor_backup_answer_hash)) {
            $tokenTypes = is_array($user->two_factor_token_types) ? $user->two_factor_token_types : [];
            if (in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true)) {
                return response()->json([
                    'success'                 => true,
                    'needs_security_question' => true,
                    'question'                => $user->two_factor_backup_question,
                ]);
            }
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Determine redirect URL based on role
        $redirectUrl = match ($user->role) {
            'admin'      => '/admin',
            'publisher'  => '/publisher',
            'advertiser' => '/advertisers',
            'manager'    => '/admin',
            default      => '/',
        };

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'redirect' => $redirectUrl,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'preferred_language' => $user->preferred_language,
                'theme_preference' => $user->theme_preference,
            ],
        ]);
    }

    /**
     * Stateless security-question verification (used by static HTML signin).
     * Does NOT log the user in — caller must then hit /auto-login to get a session.
     */
    public function verifySecurityQuestion(Request $request): JsonResponse
    {
        $request->validate([
            'email'  => 'required|email',
            'answer' => 'required|string|max:500',
        ]);

        $user = User::where('email', $request->email)
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->first();

        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Account not found.'], 404);
        }

        if (! filled($user->two_factor_backup_answer_hash)) {
            return response()->json(['success' => false, 'message' => 'No security question configured.'], 422);
        }

        if (! Hash::check(strtolower(trim((string) $request->input('answer'))), $user->two_factor_backup_answer_hash)) {
            return response()->json(['success' => false, 'message' => 'Incorrect answer. Please try again.'], 422);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Get the currently authenticated user (placeholder).
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Not implemented yet — use session or token auth.',
        ]);
    }

    /**
     * Register a new user (API endpoint for static HTML).
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'first_name'   => 'required|string|max:100',
                'last_name'    => 'required|string|max:100',
                'email'        => 'required|email|max:255|unique:aq_users,email',
                'password'     => 'required|string|min:8|confirmed',
                'role'         => ['required', Rule::in(['advertiser', 'publisher'])],
                'company_name' => 'nullable|string|max:255',
                'website_url'  => 'nullable|url|max:500',
                'country_code' => 'nullable|string|size:2',
                'ref_code'     => 'nullable|string|max:32',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }

        $refCode = strtoupper(trim((string) ($request->input('ref_code') ?? '')));
        $referralLink = $refCode !== ''
            ? ReferralLink::where('code', $refCode)
                ->where('status', 'active')
                ->where('is_deleted', false)
                ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                ->where(fn ($q) => $q->where('target_role', $request->role)->orWhere('target_role', 'any'))
                ->first()
            : null;

        $user = DB::transaction(function () use ($request, $referralLink) {
            $approvalType = $this->approvalTypeForRole($request->role);
            $requiresEmailVerification = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION;
            $requiresAdminApproval = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_ADMIN;

            $user = User::create([
                'email'          => $request->email,
                'password_hash'  => Hash::make($request->password),
                'role'           => $request->role,
                'status'         => $requiresAdminApproval ? 'pending_verification' : ($requiresEmailVerification ? 'inactive' : 'active'),
                'email_verified_at' => $requiresEmailVerification ? null : now(),
                'referral_code'  => strtoupper(Str::random(8)),
                'referred_by'    => $referralLink?->referrer_id,
                'referred_at'    => $referralLink ? now() : null,
            ]);

            UserProfile::create([
                'user_id'      => $user->id,
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'company_name' => $request->company_name,
                'website_url'  => $request->website_url,
                'country_code' => $request->country_code ?? 'AL',
            ]);

            if ($referralLink) {
                ReferralConversion::create([
                    'link_id'          => $referralLink->id,
                    'referrer_id'      => $referralLink->referrer_id,
                    'referred_user_id' => $user->id,
                    'referred_role'    => $user->role,
                    'signup_ip'        => request()->ip(),
                    'commission_ends_at' => $referralLink->commission_duration_days
                        ? now()->addDays($referralLink->commission_duration_days)
                        : null,
                    'status' => 'pending',
                ]);

                $referralLink->increment('total_signups');
            }

            return $user;
        });

        return response()->json([
            'success' => true,
            'message' => $this->registrationMessageFor($user),
            'user' => [
                'id'    => $user->id,
                'email' => $user->email,
                'role'  => $user->role,
                'status' => $user->status,
            ],
            'verification_url' => $this->approvalTypeForRole($user->role) === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION
                ? $this->verificationUrlFor($user)
                : null,
        ], 201);
    }

    private function registrationMessageFor(User $user): string
    {
        return match ($this->approvalTypeForRole($user->role)) {
            PlatformSetting::ADVERTISER_APPROVAL_ADMIN => $user->role === 'publisher'
                ? 'Publisher account created successfully. Your account is waiting for admin approval.'
                : 'Advertiser account created successfully. Your account is waiting for admin approval.',
            PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION => $user->role === 'publisher'
                ? 'Publisher account created successfully. Please verify your email before signing in.'
                : 'Advertiser account created successfully. Please verify your email before signing in.',
            default => 'Account created successfully! Please sign in.',
        };
    }

    private function verificationUrlFor(User $user): string
    {
        return URL::temporarySignedRoute(
            'account.email.verify',
            now()->addHours(24),
            [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]
        );
    }

    private function approvalTypeForRole(string $role): ?string
    {
        return match ($role) {
            'advertiser' => PlatformSetting::getAdvertiserApprovalType(),
            'publisher' => PlatformSetting::getPublisherApprovalType(),
            default => null,
        };
    }
}

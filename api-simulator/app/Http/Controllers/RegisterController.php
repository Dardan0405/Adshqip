<?php

namespace App\Http\Controllers;

use App\Models\PlatformSetting;
use App\Models\User;
use App\Models\UserProfile;
use App\Support\MessageDeliveryManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function showForm()
    {
        if (auth()->check()) {
            return redirect(match (auth()->user()->role) {
                'admin', 'manager', 'operational' => '/admin',
                'publisher' => '/publisher',
                'advertiser' => '/advertisers',
                default => '/',
            });
        }

        return view('auth.register');
    }

    /**
     * Handle the registration (web form POST).
     */
    public function register(Request $request)
    {
        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|max:255|unique:aq_users,email',
            'password'     => 'required|string|min:8|confirmed',
            'role'         => ['required', Rule::in(['advertiser', 'publisher'])],
            'company_name' => 'nullable|string|max:255',
            'website_url'  => 'nullable|url|max:500',
            'country_code' => 'nullable|string|size:2',
            'terms'        => 'accepted',
        ]);

        $user = DB::transaction(function () use ($request) {
            $approvalType = $this->approvalTypeForRole($request->role);
            $requiresEmailVerification = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION;
            $requiresAdminApproval = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_ADMIN;

            $user = User::create([
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'role'          => $request->role,
                'status'        => $requiresAdminApproval ? 'pending_verification' : ($requiresEmailVerification ? 'inactive' : 'active'),
                'email_verified_at' => $requiresEmailVerification ? null : now(),
                'referral_code' => strtoupper(Str::random(8)),
            ]);

            UserProfile::create([
                'user_id'      => $user->id,
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'company_name' => $request->company_name,
                'website_url'  => $request->website_url,
                'country_code' => $request->country_code ?? 'AL',
            ]);

            return $user;
        });

        $approvalType = $this->approvalTypeForRole($user->role);
        $verificationUrl = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION
            ? $this->verificationUrlFor($user)
            : null;
        $successMessage = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_ADMIN
            ? $this->adminApprovalMessageFor($user->role)
            : ($approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION
                ? $this->emailVerificationMessageFor($user->role)
                : 'Account created successfully! Please sign in.');
        $deliveryTitle = $approvalType === PlatformSetting::ADVERTISER_APPROVAL_ADMIN
            ? ucfirst($user->role) . ' account pending admin approval'
            : ($approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION
                ? ucfirst($user->role) . ' account email verification'
                : ucfirst($user->role) . ' account created');

        MessageDeliveryManager::deliverRegistrationMessage(
            $user,
            $deliveryTitle,
            $successMessage,
            $verificationUrl,
        );

        if ($approvalType === PlatformSetting::ADVERTISER_APPROVAL_ADMIN) {
            return redirect()->route('signin')->with('success', $this->adminApprovalMessageFor($user->role));
        }

        if ($approvalType === PlatformSetting::ADVERTISER_APPROVAL_EMAIL_VERIFICATION) {
            return redirect()->route('signin')->with('success', $this->emailVerificationMessageFor($user->role))
                ->with('verification_url', $verificationUrl);
        }

        return redirect()->route('signin')->with('success', 'Account created successfully! Please sign in.');
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $user = User::whereIn('role', ['advertiser', 'publisher'])
            ->where('is_deleted', false)
            ->findOrFail($id);

        if (! hash_equals(sha1($user->email), $hash)) {
            abort(403);
        }

        if (! $request->hasValidSignature()) {
            abort(403);
        }

        $user->update([
            'email_verified_at' => now(),
            'status' => 'active',
        ]);

        return redirect()->route('signin')->with('success', 'Email verified successfully. You can now sign in.');
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

    private function adminApprovalMessageFor(string $role): string
    {
        return match ($role) {
            'advertiser' => 'Advertiser account created successfully. Your account is waiting for admin approval.',
            'publisher' => 'Publisher account created successfully. Your account is waiting for admin approval.',
            default => 'Account created successfully! Please sign in.',
        };
    }

    private function emailVerificationMessageFor(string $role): string
    {
        return match ($role) {
            'advertiser' => 'Advertiser account created successfully. Please verify your email before signing in.',
            'publisher' => 'Publisher account created successfully. Please verify your email before signing in.',
            default => 'Account created successfully! Please sign in.',
        };
    }
}

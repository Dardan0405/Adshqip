<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorBackupCode;
use App\Models\UserProfile;
use App\Support\AdvertiserNotificationManager;
use App\Support\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountSettingsController extends Controller
{
    public function show(Request $request, TwoFactorAuth $twoFactorAuth)
    {
        $user = $request->user()->load('profile');
        $twoFactorSecret = $twoFactorAuth->decryptSecret($user->two_factor_secret);

        $setupUri = $twoFactorSecret
            ? $twoFactorAuth->provisioningUri('Adshqip', $user->email, $twoFactorSecret)
            : null;

        return view('advertiser.account-settings', [
            'user' => $user,
            'currencies' => [
                'EUR' => 'Euro (EUR)',
                'USD' => 'US Dollar (USD)',
                'GBP' => 'British Pound (GBP)',
                'ALL' => 'Albanian Lek (ALL)',
                'CHF' => 'Swiss Franc (CHF)',
                'CAD' => 'Canadian Dollar (CAD)',
            ],
            'twoFactorSecret' => $twoFactorSecret,
            'setupUri' => $setupUri,
            'backupCodes' => session('two_factor_backup_codes', []),
            'backupCodeCount' => TwoFactorBackupCode::where('user_id', $user->id)->where('used', false)->count(),
        ]);
    }

    public function update(Request $request, TwoFactorAuth $twoFactorAuth, AdvertiserNotificationManager $notifier)
    {
        $user = $request->user()->load('profile');
        $wasTwoFactorEnabled = (bool) $user->two_factor_enabled;

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('aq_users', 'email')->ignore($user->id)],
            'alternative_email' => ['nullable', 'email', 'max:255', Rule::unique('aq_user_profiles', 'alternative_email')->ignore($user->profile?->id)],
            'website_url' => 'nullable|url|max:500',
            'currency' => ['required', Rule::in(['EUR', 'USD', 'GBP', 'ALL', 'CHF', 'CAD'])],
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
            'two_factor_enabled' => 'nullable|boolean',
            'current_password' => 'nullable|string|required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed|different:current_password',
            'regenerate_backup_codes' => 'nullable|boolean',
        ]);

        if (! empty($validated['new_password']) && ! Hash::check($validated['current_password'], $user->password_hash)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.'])->withInput();
        }

        $newBackupCodes = [];
        $enableTwoFactor = $request->boolean('two_factor_enabled');

        DB::transaction(function () use ($request, $user, $validated) {
            $profileData = [
                'alternative_email' => $validated['alternative_email'] ?? null,
                'website_url' => $validated['website_url'] ?? null,
                'currency' => $validated['currency'],
            ];

            if ($request->hasFile('avatar')) {
                if ($user->profile?->avatar_url) {
                    $existingPath = parse_url($user->profile->avatar_url, PHP_URL_PATH) ?: $user->profile->avatar_url;
                    $existingPath = ltrim((string) str_replace('/storage/', '', $existingPath), '/');
                    if ($existingPath !== '') {
                        Storage::disk('public')->delete($existingPath);
                    }
                }

                $storedPath = $request->file('avatar')->store('profile-images', 'public');
                $profileData['avatar_url'] = Storage::disk('public')->url($storedPath);
            }

            $user->update(['email' => $validated['email']]);

            if (! empty($validated['new_password'])) {
                $user->update(['password_hash' => Hash::make($validated['new_password'])]);
            }

            UserProfile::query()->updateOrCreate(['user_id' => $user->id], $profileData);
        });

        DB::transaction(function () use ($request, $user, $twoFactorAuth, $enableTwoFactor, &$newBackupCodes) {
            if (! $enableTwoFactor) {
                $user->forceFill([
                    'two_factor_enabled' => false,
                    'two_factor_secret' => null,
                ])->save();

                TwoFactorBackupCode::where('user_id', $user->id)->delete();

                return;
            }

            $currentSecret = $twoFactorAuth->decryptSecret($user->two_factor_secret);

            if (blank($currentSecret)) {
                $currentSecret = $twoFactorAuth->generateSecret();
                $user->forceFill([
                    'two_factor_secret' => $twoFactorAuth->encryptSecret($currentSecret),
                ])->save();
            }

            if (! $user->two_factor_enabled) {
                $user->forceFill(['two_factor_enabled' => true])->save();
            }

            if ($request->boolean('regenerate_backup_codes') || TwoFactorBackupCode::where('user_id', $user->id)->count() === 0) {
                TwoFactorBackupCode::where('user_id', $user->id)->delete();
                $newBackupCodes = $twoFactorAuth->generateBackupCodes();

                foreach ($newBackupCodes as $code) {
                    TwoFactorBackupCode::create([
                        'user_id' => $user->id,
                        'code_hash' => Hash::make($twoFactorAuth->normalizeBackupCode($code)),
                        'used' => false,
                        'created_at' => now(),
                    ]);
                }
            }
        });

        if ($enableTwoFactor && $newBackupCodes !== []) {
            $request->session()->flash('two_factor_backup_codes', $newBackupCodes);
        }

        if ($wasTwoFactorEnabled && ! $enableTwoFactor) {
            $notifier->deliver(
                $user->fresh('profile'),
                'security_disable',
                'Security Disabled',
                'Two-factor authentication has been disabled on your advertiser account.',
                route('advertiser.account-settings'),
                $user->id,
                true
            );
        }

        return redirect()
            ->route('advertiser.account-settings')
            ->with('success', 'Account settings updated successfully.');
    }
}

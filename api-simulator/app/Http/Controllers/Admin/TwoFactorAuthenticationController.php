<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorBackupCode;
use App\Support\TwoFactorAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TwoFactorAuthenticationController extends Controller
{
    public function show(Request $request, TwoFactorAuth $twoFactorAuth)
    {
        $user = $request->user()->load('profile');

        return view('admin.two-factor-authentication', [
            'user' => $user,
            'verificationOptions' => $twoFactorAuth->verificationOptionLabels(),
            'tokenTypes' => $twoFactorAuth->tokenTypeLabels(),
            'enabledVerificationOptions' => $twoFactorAuth->enabledVerificationOptions($user),
            'enabledTokenTypes' => $twoFactorAuth->enabledTokenTypes($user),
            'backupCodeCount' => TwoFactorBackupCode::where('user_id', $user->id)
                ->where('used', false)
                ->count(),
            'debugTrustedContext' => $twoFactorAuth->trustedContext($request),
        ]);
    }

    public function update(Request $request, TwoFactorAuth $twoFactorAuth)
    {
        $user = $request->user();

        $validated = $request->validate([
            'verification_options' => 'array',
            'verification_options.*' => 'string',
            'token_types' => 'array',
            'token_types.*' => 'string',
            'two_factor_phone' => 'nullable|string|max:30',
            'two_factor_email' => 'nullable|email|max:255',
            'two_factor_backup_question' => 'nullable|string|max:255',
            'two_factor_backup_answer' => 'nullable|string|max:255',
            'regenerate_backup_codes' => 'nullable|boolean',
        ]);

        $verificationOptions = array_values(array_intersect(
            array_keys($twoFactorAuth->verificationOptionLabels()),
            $validated['verification_options'] ?? []
        ));

        $tokenTypes = array_values(array_intersect(
            array_keys($twoFactorAuth->tokenTypeLabels()),
            $validated['token_types'] ?? []
        ));

        if (in_array(TwoFactorAuth::METHOD_SMS, $tokenTypes, true) && blank($validated['two_factor_phone'] ?? null)) {
            return back()->withErrors(['two_factor_phone' => 'Phone number is required when SMS is enabled.'])->withInput();
        }

        if (in_array(TwoFactorAuth::METHOD_EMAIL, $tokenTypes, true) && blank($validated['two_factor_email'] ?? null)) {
            return back()->withErrors(['two_factor_email' => 'Email is required when Email is enabled.'])->withInput();
        }

        if (
            in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true) &&
            blank($validated['two_factor_backup_question'] ?? null)
        ) {
            return back()->withErrors(['two_factor_backup_question' => 'Question is required when Backup code is enabled.'])->withInput();
        }

        if (
            in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true) &&
            blank($validated['two_factor_backup_answer'] ?? null) &&
            blank($user->two_factor_backup_answer_hash)
        ) {
            return back()->withErrors(['two_factor_backup_answer' => 'Answer is required when Backup code is enabled.'])->withInput();
        }

        $newBackupCodes = [];

        DB::transaction(function () use ($request, $user, $verificationOptions, $tokenTypes, $validated, $twoFactorAuth, &$newBackupCodes) {
            $payload = [
                'two_factor_verification_options' => $verificationOptions,
                'two_factor_token_types' => $tokenTypes,
                'two_factor_phone' => $validated['two_factor_phone'] ?? null,
                'two_factor_email' => $validated['two_factor_email'] ?? null,
                'two_factor_backup_question' => in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true)
                    ? ($validated['two_factor_backup_question'] ?? null)
                    : null,
            ];

            if (filled($validated['two_factor_backup_answer'] ?? null)) {
                $payload['two_factor_backup_answer_hash'] = Hash::make(strtolower(trim((string) $validated['two_factor_backup_answer'])));
            } elseif (! in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true)) {
                $payload['two_factor_backup_answer_hash'] = null;
            }

            $user->forceFill($payload)->save();

            if (! in_array(TwoFactorAuth::METHOD_BACKUP, $tokenTypes, true)) {
                TwoFactorBackupCode::where('user_id', $user->id)->delete();

                return;
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

        if ($newBackupCodes !== []) {
            $request->session()->flash('two_factor_backup_codes', $newBackupCodes);
        }

        return redirect()
            ->route('admin.two-factor-authentication')
            ->with('success', 'Two Factor Authentication settings updated successfully.');
    }
}

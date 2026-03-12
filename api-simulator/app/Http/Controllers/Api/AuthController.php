<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }

        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'email'         => $request->email,
                'password_hash' => Hash::make($request->password),
                'role'          => $request->role,
                'status'        => 'active',
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

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully! Please sign in.',
            'user' => [
                'id'    => $user->id,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 201);
    }
}

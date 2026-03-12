<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
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
                'admin', 'manager' => '/admin',
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

        return redirect()->route('signin')->with('success', 'Account created successfully! Please sign in.');
    }
}

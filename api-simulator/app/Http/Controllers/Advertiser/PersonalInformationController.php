<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PersonalInformationController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');

        return view('advertiser.personal-information', [
            'user' => $user,
            'genders' => $this->genders(),
            'countries' => $this->countries(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('profile');

        $validated = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'gender' => ['nullable', Rule::in(array_keys($this->genders()))],
            'date_of_birth' => 'nullable|date|before:today',
            'mobile_number' => 'nullable|string|max:30',
            'skype_address' => 'nullable|string|max:255',
            'icq_address' => 'nullable|string|max:255',
            'jabber_address' => 'nullable|string|max:255',
            'vat_number' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_region' => 'nullable|string|max:100',
            'country_code' => ['nullable', 'string', 'size:2', Rule::in(array_keys($this->countries()))],
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'first_name' => $validated['first_name'] ?? '',
                'last_name' => $validated['last_name'] ?? '',
                'gender' => $validated['gender'] ?? null,
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'mobile_number' => $validated['mobile_number'] ?? null,
                'skype_address' => $validated['skype_address'] ?? null,
                'icq_address' => $validated['icq_address'] ?? null,
                'jabber_address' => $validated['jabber_address'] ?? null,
                'vat_number' => $validated['vat_number'] ?? null,
                'city' => $validated['city'] ?? null,
                'state_region' => $validated['state_region'] ?? null,
                'country_code' => $validated['country_code'] ?? ($user->profile?->country_code ?? 'AL'),
            ]
        );

        return redirect()
            ->route('advertiser.personal-information')
            ->with('success', 'Personal information updated successfully.');
    }

    private function genders(): array
    {
        return [
            'male' => 'Male',
            'female' => 'Female',
            'other' => 'Other',
            'prefer_not_to_say' => 'Prefer not to say',
        ];
    }

    private function countries(): array
    {
        return [
            'AL' => 'Albania',
            'DE' => 'Germany',
            'FR' => 'France',
            'GB' => 'United Kingdom',
            'IT' => 'Italy',
            'XK' => 'Kosovo',
            'ME' => 'Montenegro',
            'MK' => 'North Macedonia',
            'CH' => 'Switzerland',
            'US' => 'United States',
        ];
    }
}

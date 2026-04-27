<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Support\PublisherNotificationManager;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CompanyInformationController extends Controller
{
    public function __construct(private readonly PublisherNotificationManager $notifier) {}

    public function show(Request $request)
    {
        return view('publisher.company-information', [
            'user'      => $request->user()->load('profile'),
            'countries' => $this->countries(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('profile');

        $validated = $request->validate([
            'company_name'          => 'nullable|string|max:255',
            'company_address_line1' => 'nullable|string|max:255',
            'company_address_line2' => 'nullable|string|max:255',
            'company_city'          => 'nullable|string|max:100',
            'company_state_region'  => 'nullable|string|max:100',
            'company_country_code'  => ['nullable', 'string', 'size:2', Rule::in(array_keys($this->countries()))],
        ]);

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'company_name'          => $validated['company_name']          ?? null,
                'company_address_line1' => $validated['company_address_line1'] ?? null,
                'company_address_line2' => $validated['company_address_line2'] ?? null,
                'company_city'          => $validated['company_city']          ?? null,
                'company_state_region'  => $validated['company_state_region']  ?? null,
                'company_country_code'  => $validated['company_country_code']  ?? ($user->profile?->company_country_code ?? 'AL'),
            ]
        );

        $this->notifier->deliver(
            $user->fresh('profile'),
            'company_information',
            'Company Information Updated',
            'Your company information has been updated successfully.',
            route('publisher.company-information')
        );

        return redirect()
            ->route('publisher.company-information')
            ->with('success', 'Company information updated successfully.');
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

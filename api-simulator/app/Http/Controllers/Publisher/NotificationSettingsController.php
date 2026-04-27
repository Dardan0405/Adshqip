<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');
        $settings = $user->profile?->notification_settings ?? [];

        return view('publisher.notification-settings', [
            'user'       => $user,
            'definition' => $this->definition(),
            'settings'   => $this->mergeWithDefaults($settings),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user()->load('profile');
        $definition = $this->definition();

        $allowedEvents = collect($definition['sections'])
            ->flatMap(fn (array $section) => array_keys($section['items']))
            ->values()
            ->all();

        $validated = $request->validate([
            'receive_newsletter'         => 'nullable|boolean',
            'delivery_channels'          => 'array',
            'delivery_channels.*'        => 'string|in:email,platform_message',
            'enabled_events'             => 'array',
            'enabled_events.*'           => 'string|in:' . implode(',', $allowedEvents),
            'campaign_newsletter_filter' => 'nullable|string|in:only_member,only_favorite,member_and_favorite',
        ]);

        $settings = [
            'receive_newsletter'         => $request->boolean('receive_newsletter'),
            'delivery_channels'          => array_values(array_unique($validated['delivery_channels'] ?? [])),
            'enabled_events'             => array_values(array_unique($validated['enabled_events'] ?? [])),
            'campaign_newsletter_filter' => $validated['campaign_newsletter_filter'] ?? null,
        ];

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['notification_settings' => $settings]
        );

        return redirect()
            ->route('publisher.notification-settings')
            ->with('success', 'Notification settings updated successfully.');
    }

    private function mergeWithDefaults(array $settings): array
    {
        return [
            'receive_newsletter'         => (bool) ($settings['receive_newsletter'] ?? false),
            'delivery_channels'          => array_values($settings['delivery_channels'] ?? ['email']),
            'enabled_events'             => array_values($settings['enabled_events'] ?? []),
            'campaign_newsletter_filter' => $settings['campaign_newsletter_filter'] ?? null,
        ];
    }

    private function definition(): array
    {
        return [
            'channels' => [
                'email'            => 'E-mail',
                'platform_message' => 'Adshqip Message',
            ],
            'sections' => [
                'site_management' => [
                    'title' => 'Site Management',
                    'items' => [
                        'site_add'    => 'Site Add',
                        'site_edit'   => 'Site Edit',
                        'site_delete' => 'Site Delete',
                    ],
                ],
                'adblock_management' => [
                    'title' => 'Adblock Management',
                    'items' => [
                        'adblock_add_update' => 'Adblock Add/Update',
                        'adblock_delete'     => 'Adblock Delete',
                    ],
                ],
                'payment_settings' => [
                    'title' => 'Payment Settings',
                    'items' => [
                        'payout_request'          => 'Payout Request',
                        'payment_settings_updated' => 'Payment Settings',
                    ],
                ],
                'settings_information' => [
                    'title' => 'Settings Information',
                    'items' => [
                        'account_settings'    => 'Account Settings',
                        'company_information' => 'Company Information',
                        'personal_information' => 'Personal Information',
                        'forget_password'     => 'Forget Password',
                        'block_unblock_account' => 'Block/Unblock Account',
                    ],
                ],
                'login_security' => [
                    'title' => 'Login Security',
                    'items' => [
                        'security_disable'   => 'Security Disable',
                        'password_incorrect' => 'Password Incorrect',
                        'blocked_user'       => 'Blocked User',
                    ],
                ],
            ],
            'campaign_newsletter_options' => [
                'only_member'         => 'Only Member',
                'only_favorite'       => 'Only Favorite',
                'member_and_favorite' => 'Member and Favorite',
            ],
        ];
    }
}

<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load('profile');
        $settings = $user->profile?->notification_settings ?? [];

        return view('advertiser.notification-settings', [
            'user' => $user,
            'definition' => $this->definition(),
            'settings' => $this->mergeWithDefaults($settings),
        ]);
    }

    public function update(Request $request, ActivityLogger $activityLogger)
    {
        $user = $request->user()->load('profile');
        $definition = $this->definition();
        $allowedEvents = collect($definition['sections'])
            ->flatMap(fn (array $section) => array_keys($section['items']))
            ->values()
            ->all();

        $validated = $request->validate([
            'receive_newsletter' => 'nullable|boolean',
            'delivery_channels' => 'array',
            'delivery_channels.*' => 'string|in:email,platform_message',
            'enabled_events' => 'array',
            'enabled_events.*' => 'string|in:' . implode(',', $allowedEvents),
        ]);

        $settings = [
            'receive_newsletter' => $request->boolean('receive_newsletter'),
            'delivery_channels' => array_values(array_unique($validated['delivery_channels'] ?? [])),
            'enabled_events' => array_values(array_unique($validated['enabled_events'] ?? [])),
        ];

        UserProfile::query()->updateOrCreate(
            ['user_id' => $user->id],
            ['notification_settings' => $settings]
        );

        $activityLogger->logRequest($request, 200, [
            'action' => 'notification_settings',
            'entity_type' => 'notification settings',
            'entity_id' => $user->id,
            'description' => 'Notification settings updated',
        ]);

        return redirect()
            ->route('advertiser.notification-settings')
            ->with('success', 'Notification settings updated successfully.');
    }

    private function mergeWithDefaults(array $settings): array
    {
        return [
            'receive_newsletter' => (bool) ($settings['receive_newsletter'] ?? false),
            'delivery_channels' => array_values($settings['delivery_channels'] ?? ['email']),
            'enabled_events' => array_values($settings['enabled_events'] ?? []),
        ];
    }

    private function definition(): array
    {
        return [
            'channels' => [
                'email' => 'Email',
                'platform_message' => 'Adshqip Message',
            ],
            'sections' => [
                'campaign_settings' => [
                    'title' => 'Campaign Settings',
                    'items' => [
                        'campaign_approved' => 'Campaign Approved',
                        'campaign_rejected' => 'Campaign Rejected',
                        'campaign_create' => 'Campaign Create',
                        'campaign_edit' => 'Campaign Edit',
                        'campaign_delete' => 'Campaign Delete',
                        'campaign_activated' => 'Campaign Activated',
                        'campaign_deactivated' => 'Campaign Deactivated',
                        'campaign_moved' => 'Campaign Moved',
                        'campaign_completed' => 'Campaign Completed',
                        'creative_approved' => 'Creative Approved',
                        'creative_rejected' => 'Creative Rejected',
                        'creative_added' => 'Creative Added',
                        'creative_edited' => 'Creative Edited',
                        'creative_deleted' => 'Creative Deleted',
                    ],
                ],
                'campaign_tracking' => [
                    'title' => 'Campaign Tracking',
                    'items' => [
                        'conversion' => 'Conversion',
                        'conversion_tracking_threshold' => 'Per tracking threshold reached',
                        'impression' => 'Impression',
                        'view_threshold' => 'Per views threshold reached',
                        'budget_completed' => 'Budget Completed',
                        'campaign_stopped_total_budget_limit' => 'Campaign stopped due to total budget limit',
                        'campaign_policy_violation' => 'Campaign stopped due to policy violation',
                        'balance_zero' => 'Balance is $0',
                        'balance_below_threshold' => 'Balance is below threshold',
                    ],
                ],
                'payment_settings' => [
                    'title' => 'Payment Settings',
                    'items' => [
                        'payment_approved' => 'Payment Approved',
                        'payment_rejected' => 'Payment Rejected',
                        'payment_requested' => 'Payment Requested',
                    ],
                ],
                'network_settings' => [
                    'title' => 'Network Settings',
                    'items' => [
                        'pixel_tracker_added' => 'New Pixel Tracker added',
                        'traffic_sources_create' => 'Create Traffic Sources',
                        'traffic_sources_remove' => 'Remove Traffic Sources',
                        'zonelimitation_add' => 'Zonelimitation Add',
                        'zonelimitation_edit' => 'Zonelimitation Edit',
                        'zonelimitation_delete' => 'Zonelimitation Delete',
                    ],
                ],
                'settings_information' => [
                    'title' => 'Setting Information',
                    'items' => [
                        'personal_information' => 'Personal Information',
                        'company_information' => 'Company Information',
                        'account_settings' => 'Account Settings',
                        'forget_password' => 'Forget Password',
                        'user_block' => 'User Block',
                        'user_unblock' => 'User Unblock',
                    ],
                ],
                'login_security' => [
                    'title' => 'Login Security',
                    'items' => [
                        'security_disable' => 'Security Disable',
                        'password_incorrect' => 'Password Incorrect',
                        'blocked_user' => 'Blocked User',
                    ],
                ],
            ],
        ];
    }
}

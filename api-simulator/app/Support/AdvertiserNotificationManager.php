<?php

namespace App\Support;

use App\Mail\AdvertiserEventMail;
use App\Models\AdminMessage;
use App\Models\Campaign;
use App\Models\Notification;
use App\Models\Payout;
use App\Models\Transaction;
use App\Models\User;
use App\Models\ZoneLimitation;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class AdvertiserNotificationManager
{
    public function deliver(
        User $user,
        string $eventKey,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?int $senderId = null,
        bool $dedupeToday = false
    ): bool
    {
        $settings = $user->profile?->notification_settings ?? [];
        $enabledEvents = array_values($settings['enabled_events'] ?? []);
        $channels = array_values($settings['delivery_channels'] ?? []);

        if (! in_array($eventKey, $enabledEvents, true) || $channels === []) {
            return false;
        }

        if ($dedupeToday && Notification::query()
            ->where('user_id', $user->id)
            ->where('title', $title)
            ->where('message', $message)
            ->where('action_url', $actionUrl)
            ->whereDate('created_at', today())
            ->exists()) {
            return false;
        }

        $delivered = false;
        $type = $this->notificationTypeFor($eventKey);

        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'is_read' => false,
            'created_at' => now(),
        ]);

        if (in_array('platform_message', $channels, true)) {
            AdminMessage::create([
                'sender_id' => $senderId ?? $user->id,
                'recipient_id' => $user->id,
                'subject' => $title,
                'message' => $message . ($actionUrl ? "\n\nAction link: {$actionUrl}" : ''),
                'priority' => 'normal',
                'is_read' => false,
                'is_archived' => false,
                'created_at' => now(),
            ]);

            $delivered = true;
        }

        if (in_array('email', $channels, true)) {
            Mail::to($user->email)->send(new AdvertiserEventMail($title, $message, $actionUrl));

            $delivered = true;
        }

        return $delivered;
    }

    public function dispatchForRequest(Request $request): bool
    {
        $routeName = (string) ($request->route()?->getName() ?? '');
        $user = $request->user();

        if (! $user || $user->role !== 'advertiser') {
            return false;
        }

        $resolved = match ($routeName) {
            'advertiser.campaigns.store' => $this->campaignCreatedEvents($request),
            'advertiser.campaigns.update' => [$this->campaignEvent($request, 'campaign_edit', 'Campaign Updated', 'Campaign updated successfully.')],
            'advertiser.campaigns.destroy' => [$this->campaignEvent($request, 'campaign_delete', 'Campaign Deleted', 'Campaign deleted successfully.')],
            'advertiser.campaigns.updateStatus' => [$this->campaignStatusEvent($request)],
            'advertiser.campaigns.moveToGroup' => [$this->campaignEvent($request, 'campaign_moved', 'Campaign Moved', 'Campaign moved successfully.')],
            'advertiser.adformats.update' => [['creative_edited', 'Creative Updated', 'Your creative has been updated.', route('advertiser.adformats')]],
            'advertiser.payments.add-funds.store' => [['payment_requested', 'Payment Requested', 'Your add funds request has been created.', route('advertiser.payments.history')]],
            'advertiser.network.pixel-trackers.store' => [['pixel_tracker_added', 'Pixel Tracker Added', 'A new pixel tracker has been added.', route('advertiser.network.pixel-trackers')]],
            'advertiser.network.traffic-sources.store' => [['traffic_sources_create', 'Traffic Source Created', 'A traffic source has been created.', route('advertiser.network.traffic-sources')]],
            'advertiser.network.traffic-sources.destroy' => [['traffic_sources_remove', 'Traffic Source Removed', 'A traffic source has been removed.', route('advertiser.network.traffic-sources')]],
            'advertiser.network.zone-limitations.store' => [['zonelimitation_add', 'Zone Limitation Added', 'A zone limitation has been created.', route('advertiser.network.zone-limitations')]],
            'advertiser.network.zone-limitations.update' => [['zonelimitation_edit', 'Zone Limitation Updated', 'A zone limitation has been updated.', route('advertiser.network.zone-limitations')]],
            'advertiser.network.zone-limitations.destroy' => [['zonelimitation_delete', 'Zone Limitation Deleted', 'A zone limitation has been deleted.', route('advertiser.network.zone-limitations')]],
            'advertiser.personal-information.update' => [['personal_information', 'Personal Information Updated', 'Your personal information has been updated.', route('advertiser.personal-information')]],
            'advertiser.company-information.update' => [['company_information', 'Company Information Updated', 'Your company information has been updated.', route('advertiser.company-information')]],
            'advertiser.account-settings.update' => [$this->accountSettingsEvent($request)],
            default => null,
        };

        if (! is_array($resolved) || $resolved === []) {
            return false;
        }

        $delivered = false;

        foreach ($resolved as $event) {
            if (! is_array($event) || count($event) < 4) {
                continue;
            }

            [$eventKey, $title, $message, $actionUrl] = $event;

            $delivered = $this->deliver($user, $eventKey, $title, $message, $actionUrl, $user->id) || $delivered;
        }

        return $delivered;
    }

    private function campaignCreatedEvents(Request $request): array
    {
        $events = [
            $this->campaignEvent($request, 'campaign_create', 'Campaign Created', 'Campaign created successfully.'),
        ];

        if (! empty($request->input('ad_formats', []))) {
            $events[] = ['creative_added', 'Creative Added', 'A creative was added with your new campaign.', route('advertiser.adformats')];
        }

        return $events;
    }

    private function campaignEvent(Request $request, string $eventKey, string $title, string $fallbackMessage): array
    {
        $campaignId = (int) ($request->route('id') ?? 0);
        $campaign = $campaignId > 0 ? Campaign::find($campaignId) : null;
        $campaignName = $campaign?->name ?: ($request->input('name') ?: 'campaign');
        $message = 'Campaign "' . $campaignName . '" ' . strtolower(str_replace('Campaign ', '', $title)) . '.';

        return [$eventKey, $title, $message ?: $fallbackMessage, $campaignId > 0 ? route('advertiser.campaigns.show', $campaignId) : route('advertiser.campaigns')];
    }

    private function campaignStatusEvent(Request $request): array
    {
        $status = strtolower((string) ($request->input('status') ?? $request->input('campaign_status') ?? ''));
        $isDeactivate = in_array($status, ['paused', 'inactive', 'disabled', 'deactivated'], true);
        $title = $isDeactivate ? 'Campaign Deactivated' : 'Campaign Activated';
        $eventKey = $isDeactivate ? 'campaign_deactivated' : 'campaign_activated';

        return $this->campaignEvent($request, $eventKey, $title, $title . ' successfully.');
    }

    private function accountSettingsEvent(Request $request): array
    {
        $user = $request->user();
        $disabledSecurity = $user?->two_factor_enabled && ! $request->boolean('two_factor_enabled');

        if ($disabledSecurity) {
            return ['security_disable', 'Security Disabled', 'Two-factor authentication has been disabled on your account.', route('advertiser.account-settings')];
        }

        return ['account_settings', 'Account Settings Updated', 'Your account settings have been updated.', route('advertiser.account-settings')];
    }

    public function notifyInvalidPassword(User $user): bool
    {
        return $this->deliver(
            $user,
            'password_incorrect',
            'Password Incorrect',
            'A login attempt with an incorrect password was detected for your advertiser account.',
            route('signin'),
            $user->id,
            true
        );
    }

    public function notifyBlockedUser(User $user): bool
    {
        return $this->deliver(
            $user,
            'blocked_user',
            'Blocked User',
            'A blocked or inactive login attempt was detected for your advertiser account.',
            route('signin'),
            $user->id,
            true
        );
    }

    public function notifyTrackingStat(User $user, string $eventKey, string $title, string $message, ?string $actionUrl = null): bool
    {
        return $this->deliver($user, $eventKey, $title, $message, $actionUrl, $user->id, true);
    }

    private function notificationTypeFor(string $eventKey): string
    {
        return match (true) {
            str_contains($eventKey, 'payment') => 'payment',
            str_contains($eventKey, 'campaign'), str_contains($eventKey, 'creative') => 'campaign',
            in_array($eventKey, ['user_block', 'blocked_user', 'campaign_rejected', 'creative_rejected', 'payment_rejected'], true) => 'warning',
            default => 'info',
        };
    }
}

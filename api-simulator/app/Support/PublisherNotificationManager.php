<?php

namespace App\Support;

use App\Mail\PublisherEventMail;
use App\Models\AdminMessage;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class PublisherNotificationManager
{
    /**
     * Always creates an in-app notification, then conditionally sends
     * email / platform_message based on the user's notification_settings.
     */
    public function deliver(
        User $user,
        string $eventKey,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?int $senderId = null,
        bool $dedupeToday = false
    ): void {
        if ($dedupeToday && Notification::query()
            ->where('user_id', $user->id)
            ->where('title', $title)
            ->where('message', $message)
            ->whereDate('created_at', today())
            ->exists()) {
            return;
        }

        Notification::create([
            'user_id'    => $user->id,
            'type'       => $this->typeFor($eventKey),
            'title'      => $title,
            'message'    => $message,
            'action_url' => $actionUrl,
            'is_read'    => false,
            'created_at' => now(),
        ]);

        $settings      = $user->profile?->notification_settings ?? [];
        $enabledEvents = array_values($settings['enabled_events'] ?? []);
        $channels      = array_values($settings['delivery_channels'] ?? []);

        if (! in_array($eventKey, $enabledEvents, true) || $channels === []) {
            return;
        }

        if (in_array('platform_message', $channels, true)) {
            AdminMessage::create([
                'sender_id'    => $senderId ?? $user->id,
                'recipient_id' => $user->id,
                'subject'      => $title,
                'message'      => $message . ($actionUrl ? "\n\nAction link: {$actionUrl}" : ''),
                'priority'     => 'normal',
                'is_read'      => false,
                'is_archived'  => false,
                'created_at'   => now(),
            ]);
        }

        if (in_array('email', $channels, true)) {
            Mail::to($user->email)->send(new PublisherEventMail($title, $message, $actionUrl));
        }
    }

    public function notifyInvalidPassword(User $user): void
    {
        $this->deliver(
            $user,
            'password_incorrect',
            'Password Incorrect',
            'A login attempt with an incorrect password was detected for your publisher account.',
            null,
            $user->id,
            true
        );
    }

    public function notifyBlockedUser(User $user): void
    {
        $this->deliver(
            $user,
            'blocked_user',
            'Account Blocked',
            'A blocked or inactive login attempt was detected for your publisher account.',
            null,
            $user->id,
            true
        );
    }

    private function typeFor(string $eventKey): string
    {
        return match (true) {
            in_array($eventKey, ['payout_request', 'payment_settings_updated'], true) => 'payment',
            in_array($eventKey, ['adblock_delete', 'site_delete', 'security_disable', 'block_unblock_account', 'blocked_user'], true) => 'warning',
            in_array($eventKey, ['password_incorrect'], true) => 'error',
            in_array($eventKey, ['site_add', 'adblock_add_update'], true) => 'success',
            default => 'info',
        };
    }
}

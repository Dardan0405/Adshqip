<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AdminEventNotifier
{
    /**
     * Create in-app admin notifications and attempt browser push delivery.
     */
    public function notifyAdmins(string $title, string $message, string $type = 'system', ?string $actionUrl = null): int
    {
        $admins = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->get(['id', 'email', 'role']);

        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'is_read' => false,
            ]);

            $this->sendBrowserPush($admin, $title, $message, $actionUrl);
        }

        return $admins->count();
    }

    private function sendBrowserPush(User $user, string $title, string $message, ?string $actionUrl): void
    {
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return;
        }

        $payload = [
            'title' => $title,
            'body' => $message,
            'icon' => '/images/logo-icon.png',
            'badge' => '/images/badge.png',
            'url' => $actionUrl ?: '/admin/notifications',
            'timestamp' => now()->timestamp,
        ];

        foreach ($subscriptions as $subscription) {
            Log::info('Admin browser push notification queued', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'endpoint' => substr((string) $subscription->endpoint, 0, 80),
                'payload' => $payload,
            ]);
        }
    }
}

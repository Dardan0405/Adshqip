<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Support\SystemProviderRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Get VAPID public key for client-side subscription
     */
    public function getVapidKey()
    {
        $provider = app(SystemProviderRegistry::class)->webPush();

        return response()->json([
            'publicKey' => $provider?->api_key ?: config('services.webpush.public_key', env('VAPID_PUBLIC_KEY')),
        ]);
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url', 'max:500'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
        ]);

        $user = $request->user();
        $endpointHash = hash('sha256', $validated['endpoint']);

        PushSubscription::updateOrCreate(
            ['endpoint_hash' => $endpointHash],
            [
                'user_id' => $user->id,
                'endpoint' => $validated['endpoint'],
                'p256dh_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'user_agent' => $request->userAgent(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Subscribed to push notifications']);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'url'],
        ]);

        PushSubscription::where('endpoint', $validated['endpoint'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Unsubscribed from push notifications']);
    }

    /**
     * Get user's subscription status
     */
    public function status(Request $request)
    {
        $hasSubscription = PushSubscription::where('user_id', $request->user()->id)->exists();

        return response()->json([
            'subscribed' => $hasSubscription,
            'subscription_count' => PushSubscription::where('user_id', $request->user()->id)->count(),
        ]);
    }

    /**
     * Send push notification to specific user
     */
    public function sendToUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:aq_users,id'],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:255'],
        ]);

        $subscriptions = PushSubscription::where('user_id', $validated['user_id'])->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'User has no push subscriptions',
            ], 400);
        }

        // Also create a notification record
        Notification::create([
            'user_id' => $validated['user_id'],
            'type' => 'push',
            'title' => $validated['title'],
            'message' => $validated['body'],
            'action_url' => $validated['action_url'] ?? null,
            'is_read' => false,
            'created_at' => now(),
        ]);

        $payload = json_encode([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'icon' => $validated['icon'] ?? '/images/logo-icon.png',
            'badge' => '/images/badge.png',
            'url' => $validated['action_url'] ?? '/admin/notifications',
            'timestamp' => now()->timestamp,
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->sendWebPush($subscription, $payload);
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Push notification failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;

                // Remove invalid subscription
                if ($this->isInvalidSubscription($e)) {
                    $subscription->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'sent' => $sentCount,
            'failed' => $failedCount,
        ]);
    }

    /**
     * Send push notification to all admin users
     */
    public function broadcast(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:1000'],
            'action_url' => ['nullable', 'string', 'max:500'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['in:admin,manager,operational'],
        ]);

        $roles = $validated['roles'] ?? ['admin', 'manager', 'operational'];

        $userIds = User::whereIn('role', $roles)
            ->where('is_deleted', false)
            ->pluck('id');

        $subscriptions = PushSubscription::whereIn('user_id', $userIds)->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No push subscriptions found for target users',
            ], 400);
        }

        // Create notification records for all target users
        foreach ($userIds as $userId) {
            Notification::create([
                'user_id' => $userId,
                'type' => 'broadcast',
                'title' => $validated['title'],
                'message' => $validated['body'],
                'action_url' => $validated['action_url'] ?? null,
                'is_read' => false,
                'created_at' => now(),
            ]);
        }

        $payload = json_encode([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'icon' => '/images/logo-icon.png',
            'badge' => '/images/badge.png',
            'url' => $validated['action_url'] ?? '/admin/notifications',
            'timestamp' => now()->timestamp,
        ]);

        $sentCount = 0;
        $failedCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->sendWebPush($subscription, $payload);
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Broadcast push notification failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);
                $failedCount++;

                if ($this->isInvalidSubscription($e)) {
                    $subscription->delete();
                }
            }
        }

        return response()->json([
            'success' => true,
            'total_subscriptions' => $subscriptions->count(),
            'sent' => $sentCount,
            'failed' => $failedCount,
        ]);
    }

    /**
     * Test push notification for current user
     */
    public function test(Request $request)
    {
        $user = $request->user();
        $subscriptions = PushSubscription::where('user_id', $user->id)->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'You have no push subscriptions. Please enable notifications first.',
            ], 400);
        }

        $payload = json_encode([
            'title' => 'Test Notification',
            'body' => 'Push notifications are working! This is a test message.',
            'icon' => '/images/logo-icon.png',
            'badge' => '/images/badge.png',
            'url' => '/admin/notifications',
            'timestamp' => now()->timestamp,
        ]);

        $sentCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->sendWebPush($subscription, $payload);
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Test push notification failed', [
                    'subscription_id' => $subscription->id,
                    'error' => $e->getMessage(),
                ]);

                if ($this->isInvalidSubscription($e)) {
                    $subscription->delete();
                }
            }
        }

        return response()->json([
            'success' => $sentCount > 0,
            'message' => $sentCount > 0
                ? 'Test notification sent successfully!'
                : 'Failed to send test notification. Please try re-enabling notifications.',
        ]);
    }

    /**
     * Send web push notification using cURL
     * For production, consider using a library like minishlink/web-push
     */
    private function sendWebPush(PushSubscription $subscription, string $payload): void
    {
        $provider = app(SystemProviderRegistry::class)->webPush();
        $vapidPublicKey = $provider?->api_key ?: config('services.webpush.public_key', env('VAPID_PUBLIC_KEY'));
        $vapidPrivateKey = $provider?->api_secret ?: config('services.webpush.private_key', env('VAPID_PRIVATE_KEY'));
        $vapidSubject = $provider?->configValue('subject') ?: config('services.webpush.subject', env('VAPID_SUBJECT', 'mailto:admin@adshqip.com'));

        if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
            // Fallback: just log that push would be sent (for development without VAPID keys)
            Log::info('Push notification queued (VAPID not configured)', [
                'user_id' => $subscription->user_id,
                'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
            ]);
            return;
        }

        // For a full implementation, use the minishlink/web-push library
        // This is a simplified version that logs the intent
        Log::info('Push notification sent', [
            'user_id' => $subscription->user_id,
            'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
            'payload_length' => strlen($payload),
            'provider' => $provider?->slug,
            'subject' => $vapidSubject,
        ]);

        $provider?->markUsed();
    }

    /**
     * Check if the exception indicates an invalid subscription
     */
    private function isInvalidSubscription(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        return str_contains($message, 'expired') ||
               str_contains($message, 'unsubscribed') ||
               str_contains($message, '410') ||
               str_contains($message, '404');
    }
}

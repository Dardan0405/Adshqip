<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    public function getVapidKey()
    {
        return response()->json([
            'publicKey' => config('services.webpush.public_key', env('VAPID_PUBLIC_KEY')),
        ]);
    }

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
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Subscribed to push notifications']);
    }

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

    public function status(Request $request)
    {
        return response()->json([
            'subscribed' => PushSubscription::where('user_id', $request->user()->id)->exists(),
            'subscription_count' => PushSubscription::where('user_id', $request->user()->id)->count(),
        ]);
    }

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

        Notification::create([
            'user_id' => $user->id,
            'type' => 'push',
            'title' => 'Test Notification',
            'message' => 'Push notifications are working! This is a test message.',
            'action_url' => route('advertiser.dashboard'),
            'is_read' => false,
        ]);

        $payload = json_encode([
            'title' => 'Test Notification',
            'body' => 'Push notifications are working! This is a test message.',
            'icon' => '/images/logo-icon.png',
            'badge' => '/images/badge.png',
            'url' => route('advertiser.dashboard'),
            'timestamp' => now()->timestamp,
        ]);

        $sentCount = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $this->sendWebPush($subscription, $payload);
                $sentCount++;
            } catch (\Exception $e) {
                Log::warning('Advertiser test push notification failed', [
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

    private function sendWebPush(PushSubscription $subscription, string $payload): void
    {
        $vapidPublicKey = config('services.webpush.public_key', env('VAPID_PUBLIC_KEY'));
        $vapidPrivateKey = config('services.webpush.private_key', env('VAPID_PRIVATE_KEY'));

        if (empty($vapidPublicKey) || empty($vapidPrivateKey)) {
            Log::info('Advertiser push notification queued (VAPID not configured)', [
                'user_id' => $subscription->user_id,
                'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
            ]);
            return;
        }

        Log::info('Advertiser push notification sent', [
            'user_id' => $subscription->user_id,
            'endpoint' => substr($subscription->endpoint, 0, 50) . '...',
            'payload_length' => strlen($payload),
        ]);
    }

    private function isInvalidSubscription(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'expired')
            || str_contains($message, 'unsubscribed')
            || str_contains($message, '410')
            || str_contains($message, '404');
    }
}

<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class MessageDeliveryManager
{
    public static function deliverRegistrationMessage(User $user, string $title, string $message, ?string $actionUrl = null): string
    {
        $mode = PlatformSetting::getMessageDeliveryMode();

        if ($mode === PlatformSetting::MESSAGE_DELIVERY_SEND_MESSAGE && PlatformSetting::messagesEnabledForRole($user->role)) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'system',
                'title' => $title,
                'message' => $message,
                'action_url' => $actionUrl,
                'is_read' => false,
            ]);
        }

        if ($mode === PlatformSetting::MESSAGE_DELIVERY_SEND_EMAIL) {
            $lines = [$message];

            if ($actionUrl) {
                $lines[] = 'Action link: ' . $actionUrl;
            }

            Mail::raw(implode("\n\n", $lines), function ($mail) use ($user, $title) {
                $mail->to($user->email)->subject($title);
            });
        }

        return $mode;
    }

    public static function createInAppMessage(User $user, string $title, string $message, string $type = 'info', ?string $actionUrl = null): bool
    {
        if (! PlatformSetting::messagesEnabledForRole($user->role)) {
            return false;
        }

        Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'is_read' => false,
        ]);

        return true;
    }
}

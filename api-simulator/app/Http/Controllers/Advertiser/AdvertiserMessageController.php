<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdvertiserMessageController extends Controller
{
    public function getUnread(Request $request)
    {
        $user = $request->user();

        $messages = AdminMessage::query()
            ->with('sender')
            ->where('recipient_id', $user->id)
            ->notArchived()
            ->latest('created_at')
            ->limit(8)
            ->get()
            ->map(fn ($message) => [
                'id' => $message->id,
                'subject' => $message->subject,
                'preview' => Str::limit($message->message, 90),
                'sender' => $message->sender?->email ?? 'Unknown',
                'sender_initials' => strtoupper(substr($message->sender?->email ?? 'U', 0, 2)),
                'priority' => $message->priority,
                'is_read' => (bool) $message->is_read,
                'created_at' => optional($message->created_at)->toISOString(),
            ]);

        $unreadCount = AdminMessage::forUser($user->id)->notArchived()->unread()->count();

        return response()->json([
            'messages' => $messages,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markRead(AdminMessage $message, Request $request)
    {
        if ($message->recipient_id !== $request->user()->id) {
            abort(403);
        }

        $message->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function markAllRead(Request $request)
    {
        AdminMessage::where('recipient_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }
}

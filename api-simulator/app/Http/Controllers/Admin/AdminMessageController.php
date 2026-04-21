<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminMessage;
use App\Models\User;
use Illuminate\Http\Request;

class AdminMessageController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messagesQuery = AdminMessage::query()
            ->with(['sender', 'recipient'])
            ->where('recipient_id', $user->id)
            ->notArchived();

        if ($search = trim((string) $request->input('search'))) {
            $messagesQuery->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhereHas('sender', fn($sq) => $sq->where('email', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('read_state')) {
            $messagesQuery->where('is_read', $request->input('read_state') === 'read');
        }

        $messages = $messagesQuery
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => AdminMessage::forUser($user->id)->notArchived()->count(),
            'unread' => AdminMessage::forUser($user->id)->notArchived()->unread()->count(),
            'sent' => AdminMessage::where('sender_id', $user->id)->count(),
        ];

        $admins = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->where('id', '!=', $user->id)
            ->orderBy('email')
            ->get(['id', 'email', 'role']);

        return view('admin.messages.index', compact('messages', 'summary', 'admins'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'recipient_id' => ['required', 'exists:aq_users,id'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
        ]);

        AdminMessage::create([
            'sender_id' => $request->user()->id,
            'recipient_id' => $validated['recipient_id'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'priority' => $validated['priority'],
        ]);

        return redirect()->route('admin.messages')->with('success', 'Message sent successfully.');
    }

    public function show(AdminMessage $message, Request $request)
    {
        if ($message->recipient_id !== $request->user()->id && $message->sender_id !== $request->user()->id) {
            abort(403);
        }

        if ($message->recipient_id === $request->user()->id && !$message->is_read) {
            $message->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('admin.messages.show', compact('message'));
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

        return redirect()->route('admin.messages')->with('success', 'Message marked as read.');
    }

    public function markAllRead(Request $request)
    {
        AdminMessage::where('recipient_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return redirect()->route('admin.messages')->with('success', 'All messages marked as read.');
    }

    public function archive(AdminMessage $message, Request $request)
    {
        if ($message->recipient_id !== $request->user()->id) {
            abort(403);
        }

        $message->update(['is_archived' => true]);

        return redirect()->route('admin.messages')->with('success', 'Message archived.');
    }

    // API endpoints for header dropdown
    public function getUnread(Request $request)
    {
        $user = $request->user();

        $messages = AdminMessage::query()
            ->with('sender')
            ->where('recipient_id', $user->id)
            ->notArchived()
            ->unread()
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn($m) => [
                'id' => $m->id,
                'subject' => $m->subject,
                'preview' => \Str::limit($m->message, 60),
                'sender' => $m->sender?->email ?? 'Unknown',
                'sender_initials' => strtoupper(substr($m->sender?->email ?? 'U', 0, 2)),
                'priority' => $m->priority,
                'created_at' => $m->created_at->diffForHumans(),
                'url' => route('admin.messages.show', $m->id),
            ]);

        $unreadCount = AdminMessage::forUser($user->id)->notArchived()->unread()->count();

        return response()->json([
            'messages' => $messages,
            'unread_count' => $unreadCount,
        ]);
    }
}

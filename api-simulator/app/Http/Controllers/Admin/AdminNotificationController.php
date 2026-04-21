<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    public function index(Request $request)
    {
        $notificationsQuery = Notification::query()->with(['user.userProfile']);

        if ($search = trim((string) $request->input('search'))) {
            $notificationsQuery->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                    ->orWhere('message', 'like', '%' . $search . '%')
                    ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', '%' . $search . '%'));
            });
        }

        if ($type = $request->input('type')) {
            $notificationsQuery->where('type', $type);
        }

        if ($request->filled('read_state')) {
            $notificationsQuery->where('is_read', $request->input('read_state') === 'read');
        }

        if ($userId = $request->input('user_id')) {
            $notificationsQuery->where('user_id', $userId);
        }

        $notifications = $notificationsQuery
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Notification::count(),
            'unread' => Notification::where('is_read', false)->count(),
            'read' => Notification::where('is_read', true)->count(),
            'today' => Notification::whereDate('created_at', today())->count(),
        ];

        $users = User::query()
            ->where('is_deleted', false)
            ->orderBy('email')
            ->get(['id', 'email', 'role']);

        return view('admin.notifications.index', compact('notifications', 'summary', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:aq_users,id'],
            'type' => ['required', 'in:success,warning,error,info,payment,campaign,system,push,broadcast'],
            'title' => ['required', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:5000'],
            'action_url' => ['nullable', 'url', 'max:500'],
        ]);

        Notification::create($validated);

        return redirect()
            ->route('admin.notifications')
            ->with('success', 'Notification sent successfully.');
    }

    public function markRead(Notification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->route('admin.notifications')->with('success', 'Notification marked as read.');
    }

    public function markUnread(Notification $notification)
    {
        $notification->update([
            'is_read' => false,
            'read_at' => null,
        ]);

        return redirect()->route('admin.notifications')->with('success', 'Notification marked as unread.');
    }

    public function markAllRead()
    {
        Notification::where('is_read', false)->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return redirect()->route('admin.notifications')->with('success', 'All notifications marked as read.');
    }

    /**
     * API endpoint for header dropdown - get user's notifications
     */
    public function getForUser(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(5)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => \Str::limit($n->message, 80),
                'action_url' => $n->action_url,
                'is_read' => $n->is_read,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Mark a single notification as read (AJAX)
     */
    public function markReadAjax(Notification $notification, Request $request)
    {
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Mark all user's notifications as read (AJAX)
     */
    public function markAllReadAjax(Request $request)
    {
        Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }
}

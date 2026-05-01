<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\PlatformAnnouncement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PlatformAnnouncementController extends Controller
{
    private const AUDIENCES = ['all', 'admins', 'advertisers', 'publishers'];
    private const PLACEMENTS = ['dashboard', 'banner', 'modal', 'notification_center'];
    private const TYPES = ['info', 'success', 'warning', 'maintenance', 'release', 'incident'];
    private const STATUSES = ['draft', 'scheduled', 'published', 'archived'];

    public function index(Request $request)
    {
        $announcements = PlatformAnnouncement::query()
            ->with('createdBy:id,email,role')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $query->where(function ($nested) use ($search) {
                    $nested->where('title', 'like', "%{$search}%")
                        ->orWhere('summary', 'like', "%{$search}%")
                        ->orWhere('body', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->input('status')))
            ->when($request->filled('audience'), fn ($query) => $query->where('audience', $request->input('audience')))
            ->when($request->filled('type'), fn ($query) => $query->where('type', $request->input('type')))
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => PlatformAnnouncement::count(),
            'published' => PlatformAnnouncement::where('status', 'published')->count(),
            'scheduled' => PlatformAnnouncement::where('status', 'scheduled')->count(),
            'pinned' => PlatformAnnouncement::where('is_pinned', true)->count(),
        ];

        return view('admin.platform-announcements.index', [
            'announcements' => $announcements,
            'summary' => $summary,
            'audiences' => self::AUDIENCES,
            'placements' => self::PLACEMENTS,
            'types' => self::TYPES,
            'statuses' => self::STATUSES,
        ]);
    }

    public function store(Request $request)
    {
        $payload = $this->payload($request);
        $payload['created_by'] = Auth::id();

        PlatformAnnouncement::create($payload);

        return redirect()->route('admin.platform-announcements')->with('success', 'Platform announcement created.');
    }

    public function update(Request $request, PlatformAnnouncement $announcement)
    {
        $announcement->update($this->payload($request, $announcement));

        return redirect()->route('admin.platform-announcements')->with('success', 'Platform announcement updated.');
    }

    public function publish(PlatformAnnouncement $announcement)
    {
        $announcement->update([
            'status' => 'published',
            'published_at' => $announcement->published_at ?: now(),
        ]);

        return redirect()->route('admin.platform-announcements')->with('success', 'Announcement published.');
    }

    public function unpublish(PlatformAnnouncement $announcement)
    {
        $announcement->update(['status' => 'draft']);

        return redirect()->route('admin.platform-announcements')->with('success', 'Announcement moved to draft.');
    }

    public function archive(PlatformAnnouncement $announcement)
    {
        $announcement->update(['status' => 'archived']);

        return redirect()->route('admin.platform-announcements')->with('success', 'Announcement archived.');
    }

    public function notify(PlatformAnnouncement $announcement)
    {
        $users = User::query()
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->when($announcement->audience === 'admins', fn ($query) => $query->whereIn('role', ['admin', 'manager', 'operational']))
            ->when($announcement->audience === 'advertisers', fn ($query) => $query->where('role', 'advertiser'))
            ->when($announcement->audience === 'publishers', fn ($query) => $query->where('role', 'publisher'))
            ->get(['id']);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => $this->notificationType($announcement->type),
                'title' => $announcement->title,
                'message' => $announcement->summary ?: Str::limit(strip_tags($announcement->body), 500),
                'action_url' => $announcement->cta_url,
            ]);
        }

        $announcement->increment('notification_count', $users->count(), ['last_notified_at' => now()]);

        return redirect()
            ->route('admin.platform-announcements')
            ->with('success', 'Announcement notification sent to ' . number_format($users->count()) . ' users.');
    }

    public function destroy(PlatformAnnouncement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.platform-announcements')->with('success', 'Announcement deleted.');
    }

    private function payload(Request $request, ?PlatformAnnouncement $announcement = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:aq_platform_announcements,slug' . ($announcement ? ',' . $announcement->id : '')],
            'audience' => ['required', 'in:' . implode(',', self::AUDIENCES)],
            'placement' => ['required', 'in:' . implode(',', self::PLACEMENTS)],
            'type' => ['required', 'in:' . implode(',', self::TYPES)],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'summary' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string'],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'string', 'max:500'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_pinned' => ['nullable', 'boolean'],
        ]);

        $status = $validated['status'];
        $publishedAt = $announcement?->published_at;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = now();
        }

        return [
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['slug'] ?: $validated['title'], $announcement?->id),
            'audience' => $validated['audience'],
            'placement' => $validated['placement'],
            'type' => $validated['type'],
            'status' => $status,
            'summary' => $validated['summary'] ?? null,
            'body' => $validated['body'],
            'cta_label' => $validated['cta_label'] ?? null,
            'cta_url' => $validated['cta_url'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'published_at' => $publishedAt,
            'is_pinned' => (bool) ($validated['is_pinned'] ?? false),
        ];
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'announcement';
        $slug = $base;
        $counter = 2;

        while (PlatformAnnouncement::where('slug', $slug)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function notificationType(string $type): string
    {
        return match ($type) {
            'success', 'warning', 'info' => $type,
            'incident' => 'error',
            default => 'system',
        };
    }
}

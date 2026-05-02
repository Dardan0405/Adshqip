<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectLink;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;

class AddDirectLinkController extends Controller
{
    public function index()
    {
        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->with(['profile', 'sites.zones' => function ($q) {
                $q->where('is_deleted', false)->where('status', 'active');
            }])
            ->orderBy('email')
            ->get();

        $adblocks = Zone::where('is_deleted', false)
            ->where('status', 'active')
            ->with(['site.publisher.profile', 'format', 'size'])
            ->orderBy('name')
            ->get();

        $directLinks = DirectLink::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.add-direct-link', [
            'publishers' => $publishers,
            'adblocks' => $adblocks,
            'directLinks' => $directLinks,
            'stats' => [
                'totalPublishers' => $publishers->count(),
                'totalAdblocks' => $adblocks->count(),
                'totalLinks' => DirectLink::count(),
                'activeLinks' => DirectLink::where('status', 'active')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'publisher_scope' => ['required', 'in:all,selected'],
            'selected_publishers' => ['required_if:publisher_scope,selected', 'array'],
            'selected_publishers.*' => ['integer', 'exists:aq_users,id'],
            'adblock_scope' => ['required', 'in:all,selected'],
            'selected_adblocks' => ['required_if:adblock_scope,selected', 'array'],
            'selected_adblocks.*' => ['integer', 'exists:aq_zones,id'],
            'destination_url' => ['nullable', 'url', 'max:2048'],
            'expires_at' => ['nullable', 'date', 'after:today'],
        ]);

        $publisherIds = null;
        $zoneIds = null;

        if ($validated['publisher_scope'] === 'selected') {
            $publisherIds = User::whereIn('id', $validated['selected_publishers'])
                ->where('role', 'publisher')
                ->where('is_deleted', false)
                ->where('status', 'active')
                ->pluck('id')
                ->toArray();

            if (empty($publisherIds)) {
                return redirect()
                    ->route('admin.add-direct-link')
                    ->with('error', 'No valid publishers found for the selected criteria.');
            }
        }

        if ($validated['adblock_scope'] === 'selected') {
            $zonesQuery = Zone::whereIn('id', $validated['selected_adblocks'])
                ->where('is_deleted', false)
                ->where('status', 'active');

            if ($validated['publisher_scope'] === 'selected' && !empty($publisherIds)) {
                $zonesQuery->whereHas('site', function ($q) use ($publisherIds) {
                    $q->whereIn('publisher_id', $publisherIds);
                });
            }

            $zoneIds = $zonesQuery->pluck('id')->toArray();

            if (empty($zoneIds)) {
                return redirect()
                    ->route('admin.add-direct-link')
                    ->with('error', 'No valid adblocks found for the selected criteria.');
            }
        }

        $directLink = DirectLink::create([
            'name' => trim($validated['name']),
            'publisher_scope' => $validated['publisher_scope'],
            'publisher_ids' => $publisherIds,
            'adblock_scope' => $validated['adblock_scope'],
            'zone_ids' => $zoneIds,
            'link_code' => DirectLink::generateLinkCode(),
            'destination_url' => $validated['destination_url'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'status' => 'active',
            'created_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.add-direct-link')
            ->with('success', "Direct link created successfully. Code: {$directLink->link_code}");
    }

    public function getAdblocksByPublisher(Request $request)
    {
        $publisherIds = $request->input('publisher_ids', []);

        if (empty($publisherIds)) {
            $adblocks = Zone::where('is_deleted', false)
                ->where('status', 'active')
                ->with(['site.publisher.profile', 'format', 'size'])
                ->orderBy('name')
                ->get();
        } else {
            $adblocks = Zone::where('is_deleted', false)
                ->where('status', 'active')
                ->whereHas('site', function ($q) use ($publisherIds) {
                    $q->whereIn('publisher_id', $publisherIds);
                })
                ->with(['site.publisher.profile', 'format', 'size'])
                ->orderBy('name')
                ->get();
        }

        return response()->json([
            'adblocks' => $adblocks->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'site_name' => $zone->site?->name,
                    'publisher_email' => $zone->site?->publisher?->email,
                    'format' => $zone->format?->name,
                    'size' => $zone->size?->name ?? "{$zone->size_key}",
                ];
            }),
        ]);
    }

    public function updateStatus(DirectLink $directLink, Request $request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,paused,expired'],
        ]);

        $directLink->update(['status' => $validated['status']]);

        return redirect()
            ->route('admin.add-direct-link')
            ->with('success', 'Direct link status updated successfully.');
    }

    public function destroy(DirectLink $directLink)
    {
        $directLink->delete();

        return redirect()
            ->route('admin.add-direct-link')
            ->with('success', 'Direct link deleted successfully.');
    }

    public function serve(string $code)
    {
        $directLink = DirectLink::where('link_code', $code)->first();

        if (!$directLink) {
            abort(404, 'Direct link not found.');
        }

        if ($directLink->status !== 'active') {
            abort(403, 'This direct link is not active.');
        }

        if ($directLink->expires_at && $directLink->expires_at->isPast()) {
            $directLink->update(['status' => 'expired']);
            abort(410, 'This direct link has expired.');
        }

        $directLink->increment('view_count');
        $directLink->increment('click_count');

        if ($directLink->destination_url) {
            return redirect()->away($directLink->destination_url);
        }

        $zones = $directLink->zones;

        return response()->json([
            'success' => true,
            'link' => [
                'name' => $directLink->name,
                'code' => $directLink->link_code,
                'publisher_scope' => $directLink->publisher_scope,
                'adblock_scope' => $directLink->adblock_scope,
            ],
            'zones' => $zones->map(function ($zone) {
                return [
                    'id' => $zone->id,
                    'name' => $zone->name,
                    'ad_code' => $zone->ad_code,
                    'site' => $zone->site?->name,
                    'format' => $zone->format?->name,
                    'size' => $zone->size_key,
                    'serve_url' => url("/d/{$zone->ad_code}.js"),
                ];
            }),
        ]);
    }
}

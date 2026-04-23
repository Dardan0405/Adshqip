<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\Zone;
use App\Models\ZoneLimitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoneLimitationController extends Controller
{
    public function index(Request $request)
    {
        $advertiserId = $request->user()->id;
        $query = ZoneLimitation::where('advertiser_id', $advertiserId);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        $limitations = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $statsQuery = ZoneLimitation::where('advertiser_id', $advertiserId);

        return view('advertiser.network.zone-limitations.index', [
            'limitations' => $limitations,
            'totalLimitations' => (clone $statsQuery)->count(),
            'whitelistCount' => (clone $statsQuery)->where('type', 'adblock_whitelist')->count(),
            'blacklistCount' => (clone $statsQuery)->where('type', 'adblock_blacklist')->count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->merge(['zone_ids' => $this->normalizeZoneIds($request->input('zone_ids', []))]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:adblock_whitelist,adblock_blacklist'],
            'zone_ids' => ['required', 'array', 'min:1'],
            'zone_ids.*' => ['exists:aq_zones,id'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        ZoneLimitation::create([
            'advertiser_id' => $request->user()->id,
            'name' => $request->name,
            'type' => $request->type,
            'zone_ids' => $request->zone_ids,
        ]);

        return redirect()->route('advertiser.network.zone-limitations')->with('success', 'Zone limitation list created successfully.');
    }

    public function show(Request $request, int $id)
    {
        $limitation = ZoneLimitation::where('advertiser_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'id' => $limitation->id,
            'name' => $limitation->name,
            'type' => $limitation->type,
            'zone_ids' => $limitation->zone_ids ?? [],
        ]);
    }

    public function update(Request $request, int $id)
    {
        $limitation = ZoneLimitation::where('advertiser_id', $request->user()->id)->findOrFail($id);
        $request->merge(['zone_ids' => $this->normalizeZoneIds($request->input('zone_ids', []))]);

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:adblock_whitelist,adblock_blacklist'],
            'zone_ids' => ['required', 'array', 'min:1'],
            'zone_ids.*' => ['exists:aq_zones,id'],
        ]);

        if ($validator->fails()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => $validator->errors()->first()], 422)
                : back()->withErrors($validator)->withInput();
        }

        $limitation->update($request->only(['name', 'type', 'zone_ids']));

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Zone limitation updated successfully.'])
            : redirect()->route('advertiser.network.zone-limitations')->with('success', 'Zone limitation updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        ZoneLimitation::where('advertiser_id', $request->user()->id)->findOrFail($id)->delete();

        return $request->expectsJson()
            ? response()->json(['success' => true, 'message' => 'Zone limitation deleted successfully.'])
            : redirect()->route('advertiser.network.zone-limitations')->with('success', 'Zone limitation deleted successfully.');
    }

    public function getZones(Request $request)
    {
        $query = Zone::where('is_deleted', false);

        if ($search = trim((string) $request->get('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        return response()->json($query->orderBy('name')->limit(50)->get(['id', 'name'])->map(fn (Zone $zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'label' => "#{$zone->id} - {$zone->name}",
        ]));
    }

    private function normalizeZoneIds(array|string|null $zoneIds): array
    {
        return collect((array) $zoneIds)
            ->flatMap(fn ($value) => explode(',', (string) $value))
            ->map(fn ($value) => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}

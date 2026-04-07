<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Zone;
use App\Models\ZoneLimitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ZoneLimitationController extends Controller
{
    public function index(Request $request)
    {
        $query = ZoneLimitation::with(['advertiser.profile']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('advertiser', function ($subQ) use ($search) {
                      $subQ->where('email', 'like', "%{$search}%")
                           ->orWhereHas('profile', function ($profQ) use ($search) {
                               $profQ->where('first_name', 'like', "%{$search}%")
                                     ->orWhere('last_name', 'like', "%{$search}%")
                                     ->orWhere('company_name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        $limitations = $query->orderBy('created_at', 'desc')->paginate(20);

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get();

        $totalLimitations = ZoneLimitation::count();
        $whitelistCount = ZoneLimitation::where('type', 'adblock_whitelist')->count();
        $blacklistCount = ZoneLimitation::where('type', 'adblock_blacklist')->count();
        $totalAdvertisers = ZoneLimitation::distinct('advertiser_id')->count();

        return view('admin.zone-limitations.index', compact(
            'limitations',
            'advertisers',
            'totalLimitations',
            'whitelistCount',
            'blacklistCount',
            'totalAdvertisers'
        ));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'advertiser_id' => 'required|exists:aq_users,id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:adblock_whitelist,adblock_blacklist',
            'zone_ids' => 'required|array|min:1',
            'zone_ids.*' => 'exists:aq_zones,id',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        ZoneLimitation::create([
            'advertiser_id' => $request->advertiser_id,
            'name' => $request->name,
            'type' => $request->type,
            'zone_ids' => $request->zone_ids,
        ]);

        return redirect()->route('admin.zone-limitations')->with('success', 'Zone limitation list created successfully.');
    }

    public function destroy($id)
    {
        $limitation = ZoneLimitation::findOrFail($id);
        $limitation->delete();

        return response()->json(['success' => true, 'message' => 'Zone limitation deleted successfully.']);
    }

    /**
     * Get zones for adblock whitelist/blacklist dropdown (AJAX)
     */
    public function getZones(Request $request)
    {
        $type = $request->get('type');
        $search = $request->get('search');

        $query = Zone::where('is_deleted', false);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('id', $search);
            });
        }

        $zones = $query->orderBy('name')->limit(50)->get(['id', 'name', 'site_id']);

        return response()->json($zones->map(fn (Zone $zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'label' => "#{$zone->id} - {$zone->name}",
        ]));
    }

    public function export(Request $request)
    {
        $query = ZoneLimitation::with(['advertiser.profile']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhereHas('advertiser', function ($subQ) use ($search) {
                      $subQ->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($type = $request->get('type')) {
            $query->where('type', $type);
        }

        if ($advertiserId = $request->get('advertiser_id')) {
            $query->where('advertiser_id', $advertiserId);
        }

        $limitations = $query->orderBy('created_at', 'desc')->get();

        $filename = 'zone_limitations_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($limitations) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Type', 'Advertiser', 'Zone IDs', 'Created At']);

            foreach ($limitations as $limitation) {
                $advertiserName = trim(($limitation->advertiser->profile->first_name ?? '') . ' ' . ($limitation->advertiser->profile->last_name ?? ''));
                if (!$advertiserName) {
                    $advertiserName = $limitation->advertiser->email;
                }

                fputcsv($file, [
                    $limitation->id,
                    $limitation->name,
                    $limitation->type === 'adblock_whitelist' ? 'Whitelist' : 'Blacklist',
                    $advertiserName,
                    implode(', ', $limitation->zone_ids ?? []),
                    $limitation->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\User;
use App\Models\StatDaily;
use Illuminate\Http\Request;

class SitesController extends Controller
{
    public function index(Request $request)
    {
        $query = Site::where('is_deleted', false)
            ->with('publisher');

        // Search by name or domain
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('domain', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by publisher
        if ($publisherId = $request->input('publisher_id')) {
            $query->where('publisher_id', $publisherId);
        }

        $sites = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Get stats for each site
        $siteIds = $sites->pluck('id');
        $stats = StatDaily::whereIn('site_id', $siteIds)
            ->selectRaw('site_id, 
                sum(impressions) as total_impressions,
                sum(clicks) as total_clicks,
                sum(revenue) as total_revenue,
                case when sum(impressions) > 0 then (sum(clicks) / sum(impressions) * 100) else 0 end as ctr')
            ->groupBy('site_id')
            ->get()
            ->keyBy('site_id');

        // Get all publishers for filter dropdown
        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with('profile')
            ->orderBy('email')
            ->get();

        return view('admin.sites.index', compact('sites', 'stats', 'publishers'));
    }

    public function show($id)
    {
        $site = Site::where('is_deleted', false)
            ->with('publisher')
            ->findOrFail($id);

        $stats = StatDaily::where('site_id', $id)
            ->selectRaw('sum(impressions) as total_impressions,
                sum(clicks) as total_clicks,
                sum(revenue) as total_revenue,
                case when sum(impressions) > 0 then (sum(clicks) / sum(impressions) * 100) else 0 end as ctr')
            ->first();

        return response()->json([
            'id' => $site->id,
            'name' => $site->name,
            'domain' => $site->domain,
            'description' => $site->description,
            'category' => $site->category,
            'language' => $site->language,
            'status' => $site->status,
            'publisher_id' => $site->publisher_id,
            'publisher_name' => $site->publisher ? 
                trim(($site->publisher->profile->first_name ?? '') . ' ' . ($site->publisher->profile->last_name ?? '')) ?: $site->publisher->email 
                : 'Unknown',
            'impressions' => $stats->total_impressions ?? 0,
            'clicks' => $stats->total_clicks ?? 0,
            'revenue' => $stats->total_revenue ?? 0,
            'ctr' => round($stats->ctr ?? 0, 2),
            'created_at' => $site->created_at?->format('M d, Y'),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'publisher_id' => 'required|exists:aq_users,id',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:5',
            'status' => 'nullable|in:active,pending_review,rejected,suspended',
        ]);

        $site = Site::create([
            'name' => $request->name,
            'domain' => $request->domain,
            'publisher_id' => $request->publisher_id,
            'description' => $request->description,
            'category' => $request->category,
            'language' => $request->language ?? 'sq',
            'status' => $request->status ?? 'pending_review',
        ]);

        return redirect()->route('admin.sites')->with('success', 'Site created successfully.');
    }

    public function update(Request $request, $id)
    {
        $site = Site::where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|max:255',
            'publisher_id' => 'required|exists:aq_users,id',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:5',
            'status' => 'nullable|in:active,pending_review,rejected,suspended',
        ]);

        $site->update([
            'name' => $request->name,
            'domain' => $request->domain,
            'publisher_id' => $request->publisher_id,
            'description' => $request->description,
            'category' => $request->category,
            'language' => $request->language ?? $site->language,
            'status' => $request->status ?? $site->status,
        ]);

        return redirect()->route('admin.sites')->with('success', 'Site updated successfully.');
    }

    public function destroy($id)
    {
        $site = Site::where('is_deleted', false)->findOrFail($id);
        $site->update(['is_deleted' => true]);

        return response()->json(['success' => true, 'message' => 'Site deleted.']);
    }

    public function updateStatus(Request $request, $id)
    {
        $site = Site::where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,pending_review,rejected,suspended',
        ]);

        $site->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Status updated.']);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Site;
use App\Models\Zone;
use App\Models\StatDaily;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PublisherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with(['profile', 'sites' => function ($q) {
                $q->where('is_deleted', false);
            }]);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhereHas('profile', function ($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                         ->orWhere('last_name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $publishers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        // Aggregate stats
        $totalPublishers = User::where('role', 'publisher')->where('is_deleted', false)->count();
        $activePublishers = User::where('role', 'publisher')->where('is_deleted', false)->where('status', 'active')->count();
        $blockedPublishers = User::where('role', 'publisher')->where('is_deleted', false)->where('status', 'suspended')->count();

        $publisherIds = User::where('role', 'publisher')->where('is_deleted', false)->pluck('id');

        // Total Sites
        $totalSites = Site::whereIn('publisher_id', $publisherIds)
            ->where('is_deleted', false)
            ->count();

        // Total Adblocks (Zones)
        $siteIds = Site::whereIn('publisher_id', $publisherIds)
            ->where('is_deleted', false)
            ->pluck('id');
        $totalAdblocks = Zone::whereIn('site_id', $siteIds)
            ->where('is_deleted', false)
            ->count();

        // Total Earnings
        $totalEarnings = StatDaily::whereIn('publisher_id', $publisherIds)->sum('revenue');

        // Per-publisher stats (site counts + earnings)
        $siteCounts = Site::whereIn('publisher_id', $publisherIds)
            ->where('is_deleted', false)
            ->selectRaw('publisher_id, count(*) as total')
            ->groupBy('publisher_id')
            ->pluck('total', 'publisher_id');

        $earningsPerPublisher = StatDaily::whereIn('publisher_id', $publisherIds)
            ->selectRaw('publisher_id, sum(revenue) as total')
            ->groupBy('publisher_id')
            ->pluck('total', 'publisher_id');

        return view('admin.publishers.index', compact(
            'publishers',
            'totalPublishers',
            'activePublishers',
            'blockedPublishers',
            'totalSites',
            'totalAdblocks',
            'totalEarnings',
            'siteCounts',
            'earningsPerPublisher',
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email'        => 'required|email|unique:aq_users,email',
            'password'     => 'required|string|min:6',
            'first_name'   => 'nullable|string|max:100',
            'last_name'    => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'phone'        => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'website_url'  => 'nullable|url|max:500',
        ]);

        $user = User::create([
            'email'         => $request->email,
            'password_hash' => Hash::make($request->password),
            'role'          => 'publisher',
            'status'        => 'active',
        ]);

        $user->profile()->create([
            'first_name'   => $request->first_name,
            'last_name'    => $request->last_name,
            'company_name' => $request->company_name,
            'phone'        => $request->phone,
            'country_code' => $request->country_code,
            'website_url'  => $request->website_url,
        ]);

        return redirect()->route('admin.publishers')->with('success', 'Publisher created successfully.');
    }

    public function show($id)
    {
        $publisher = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with(['profile', 'sites' => function ($q) {
                $q->where('is_deleted', false);
            }])
            ->findOrFail($id);

        $siteCount = $publisher->sites->count();
        $siteIds = $publisher->sites->pluck('id');
        $adblockCount = Zone::whereIn('site_id', $siteIds)
            ->where('is_deleted', false)
            ->count();
        $totalEarnings = StatDaily::where('publisher_id', $id)->sum('revenue');

        return response()->json([
            'id'             => $publisher->id,
            'email'          => $publisher->email,
            'status'         => $publisher->status,
            'first_name'     => $publisher->profile->first_name ?? '',
            'last_name'      => $publisher->profile->last_name ?? '',
            'company_name'   => $publisher->profile->company_name ?? '',
            'phone'          => $publisher->profile->phone ?? '',
            'country_code'   => $publisher->profile->country_code ?? '',
            'website_url'    => $publisher->profile->website_url ?? '',
            'balance'        => $publisher->profile->balance ?? 0,
            'site_count'     => $siteCount,
            'adblock_count'  => $adblockCount,
            'total_earnings' => round($totalEarnings, 2),
            'created_at'     => $publisher->created_at?->format('M d, Y'),
            'last_login_at'  => $publisher->last_login_at?->format('M d, Y H:i'),
            'last_login_ip'  => $publisher->last_login_ip,
        ]);
    }

    public function update(Request $request, $id)
    {
        $publisher = User::where('role', 'publisher')->findOrFail($id);

        $request->validate([
            'email'        => 'required|email|unique:aq_users,email,' . $id,
            'first_name'   => 'nullable|string|max:100',
            'last_name'    => 'nullable|string|max:100',
            'company_name' => 'nullable|string|max:200',
            'phone'        => 'nullable|string|max:20',
            'country_code' => 'nullable|string|max:5',
            'website_url'  => 'nullable|url|max:500',
            'password'     => 'nullable|string|min:6',
        ]);

        $publisher->update([
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $publisher->update(['password_hash' => Hash::make($request->password)]);
        }

        $publisher->profile()->updateOrCreate(
            ['user_id' => $publisher->id],
            [
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'company_name' => $request->company_name,
                'phone'        => $request->phone,
                'country_code' => $request->country_code,
                'website_url'  => $request->website_url,
            ]
        );

        return redirect()->route('admin.publishers')->with('success', 'Publisher updated successfully.');
    }

    public function destroy($id)
    {
        $publisher = User::where('role', 'publisher')->findOrFail($id);
        $publisher->update(['is_deleted' => true]);

        return response()->json(['success' => true, 'message' => 'Publisher deleted.']);
    }

    public function block($id)
    {
        $publisher = User::where('role', 'publisher')->findOrFail($id);
        $publisher->update(['status' => 'suspended']);

        return response()->json(['success' => true, 'message' => 'Publisher blocked.']);
    }

    public function unblock($id)
    {
        $publisher = User::where('role', 'publisher')->findOrFail($id);
        $publisher->update(['status' => 'active']);

        return response()->json(['success' => true, 'message' => 'Publisher unblocked.']);
    }

    public function loginAs($id)
    {
        $publisher = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->findOrFail($id);

        Auth::login($publisher);
        session()->regenerate();

        return redirect('/publisher');
    }

    public function sendNotification(Request $request, $id)
    {
        $publisher = User::where('role', 'publisher')->where('is_deleted', false)->findOrFail($id);

        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|in:success,warning,error,info,payment,campaign,system',
        ]);

        Notification::create([
            'user_id'    => $publisher->id,
            'type'       => $request->input('type', 'info'),
            'title'      => $request->title,
            'message'    => $request->message,
            'action_url' => $request->input('action_url'),
        ]);

        return response()->json(['success' => true, 'message' => 'Notification sent to ' . $publisher->email]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        $publishers = User::where('role', 'publisher')
            ->where('is_deleted', false)
            ->with('profile')
            ->get();

        $data = $publishers->map(function ($p) {
            $siteCount = Site::where('publisher_id', $p->id)->where('is_deleted', false)->count();
            $earnings = StatDaily::where('publisher_id', $p->id)->sum('revenue');
            return [
                'ID'              => $p->id,
                'Email'           => $p->email,
                'Name'            => trim(($p->profile->first_name ?? '') . ' ' . ($p->profile->last_name ?? '')),
                'Company'         => $p->profile->company_name ?? '',
                'Status'          => $p->status,
                'Total Sites'     => $siteCount,
                'Total Earnings'  => round($earnings, 2),
                'Created'         => $p->created_at?->format('Y-m-d'),
            ];
        });

        if ($format === 'json') {
            return response()->json($data);
        }

        // CSV
        $csv = implode(',', array_keys($data->first() ?? [])) . "\n";
        foreach ($data as $row) {
            $csv .= implode(',', array_map(function ($v) {
                return '"' . str_replace('"', '""', $v) . '"';
            }, $row)) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="publishers.csv"',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Ad;
use App\Models\StatDaily;
use App\Support\AdvertiserNotificationManager;
use App\Support\MessageDeliveryManager;
use App\Models\PlatformSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdvertiserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with(['profile', 'accountManager', 'campaigns' => function ($q) {
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

        if ($accountManagerId = $request->input('account_manager')) {
            if ($accountManagerId === 'unassigned') {
                $query->whereNull('account_manager_id');
            } else {
                $query->where('account_manager_id', $accountManagerId);
            }
        }

        $advertisers = $query->orderByDesc('created_at')->paginate(20)->withQueryString();
        $accountManagers = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->orderBy('email')
            ->get(['id', 'email', 'role']);

        // Aggregate stats
        $totalAdvertisers = User::where('role', 'advertiser')->where('is_deleted', false)->count();
        $activeAdvertisers = User::where('role', 'advertiser')->where('is_deleted', false)->where('status', 'active')->count();
        $blockedAdvertisers = User::where('role', 'advertiser')->where('is_deleted', false)->where('status', 'suspended')->count();

        $advertiserIds = User::where('role', 'advertiser')->where('is_deleted', false)->pluck('id');

        $totalCampaigns = Campaign::whereIn('advertiser_id', $advertiserIds)->where('is_deleted', false)->count();
        $totalCreatives = Ad::whereHas('campaign', function ($q) use ($advertiserIds) {
            $q->whereIn('advertiser_id', $advertiserIds)->where('is_deleted', false);
        })->where('is_deleted', false)->count();
        $totalSpend = StatDaily::whereIn('advertiser_id', $advertiserIds)->sum('revenue');

        // Per-advertiser stats (campaign counts + spend)
        $campaignCounts = Campaign::whereIn('advertiser_id', $advertiserIds)
            ->where('is_deleted', false)
            ->selectRaw('advertiser_id, count(*) as total')
            ->groupBy('advertiser_id')
            ->pluck('total', 'advertiser_id');

        $spendPerAdvertiser = StatDaily::whereIn('advertiser_id', $advertiserIds)
            ->selectRaw('advertiser_id, sum(revenue) as total')
            ->groupBy('advertiser_id')
            ->pluck('total', 'advertiser_id');

        return view('admin.advertisers.index', compact(
            'advertisers',
            'totalAdvertisers',
            'activeAdvertisers',
            'blockedAdvertisers',
            'totalCampaigns',
            'totalCreatives',
            'totalSpend',
            'campaignCounts',
            'spendPerAdvertiser',
            'accountManagers',
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
            'role'          => 'advertiser',
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

        return redirect()->route('admin.advertisers')->with('success', 'Advertiser created successfully.');
    }

    public function show($id)
    {
        $advertiser = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with(['profile', 'campaigns' => function ($q) {
                $q->where('is_deleted', false);
            }])
            ->findOrFail($id);

        $campaignCount = $advertiser->campaigns->count();
        $creativeCount = Ad::whereIn('campaign_id', $advertiser->campaigns->pluck('id'))
            ->where('is_deleted', false)->count();
        $totalSpend = StatDaily::where('advertiser_id', $id)->sum('revenue');

        return response()->json([
            'id'             => $advertiser->id,
            'email'          => $advertiser->email,
            'status'         => $advertiser->status,
            'first_name'     => $advertiser->profile->first_name ?? '',
            'last_name'      => $advertiser->profile->last_name ?? '',
            'company_name'   => $advertiser->profile->company_name ?? '',
            'phone'          => $advertiser->profile->phone ?? '',
            'country_code'   => $advertiser->profile->country_code ?? '',
            'website_url'    => $advertiser->profile->website_url ?? '',
            'balance'        => $advertiser->profile->balance ?? 0,
            'campaign_count' => $campaignCount,
            'creative_count' => $creativeCount,
            'total_spend'    => round($totalSpend, 2),
            'created_at'     => $advertiser->created_at?->format('M d, Y'),
            'last_login_at'  => $advertiser->last_login_at?->format('M d, Y H:i'),
            'last_login_ip'  => $advertiser->last_login_ip,
            'account_manager' => $advertiser->accountManager?->email,
        ]);
    }

    public function update(Request $request, $id)
    {
        $advertiser = User::where('role', 'advertiser')->findOrFail($id);

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

        $advertiser->update([
            'email' => $request->email,
        ]);

        if ($request->filled('password')) {
            $advertiser->update(['password_hash' => Hash::make($request->password)]);
        }

        $advertiser->profile()->updateOrCreate(
            ['user_id' => $advertiser->id],
            [
                'first_name'   => $request->first_name,
                'last_name'    => $request->last_name,
                'company_name' => $request->company_name,
                'phone'        => $request->phone,
                'country_code' => $request->country_code,
                'website_url'  => $request->website_url,
            ]
        );

        return redirect()->route('admin.advertisers')->with('success', 'Advertiser updated successfully.');
    }

    public function destroy($id)
    {
        $advertiser = User::where('role', 'advertiser')->findOrFail($id);
        $advertiser->update(['is_deleted' => true]);

        return response()->json(['success' => true, 'message' => 'Advertiser deleted.']);
    }

    public function block($id)
    {
        $advertiser = User::where('role', 'advertiser')->findOrFail($id);
        $advertiser->update(['status' => 'suspended']);
        app(AdvertiserNotificationManager::class)->deliver(
            $advertiser->fresh('profile'),
            'user_block',
            'User Blocked',
            'Your advertiser account has been blocked by admin.',
            route('signin'),
            Auth::id(),
            true
        );

        return response()->json(['success' => true, 'message' => 'Advertiser blocked.']);
    }

    public function unblock($id)
    {
        $advertiser = User::where('role', 'advertiser')->findOrFail($id);
        $advertiser->update(['status' => 'active']);
        app(AdvertiserNotificationManager::class)->deliver(
            $advertiser->fresh('profile'),
            'user_unblock',
            'User Unblocked',
            'Your advertiser account has been unblocked by admin.',
            route('signin'),
            Auth::id(),
            true
        );

        return response()->json(['success' => true, 'message' => 'Advertiser unblocked.']);
    }

    public function loginAs($id)
    {
        $advertiser = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->findOrFail($id);

        Auth::login($advertiser);
        session()->regenerate();

        return redirect('/advertisers');
    }

    public function sendNotification(Request $request, $id)
    {
        $advertiser = User::where('role', 'advertiser')->where('is_deleted', false)->findOrFail($id);

        if (! PlatformSetting::getAdvertiserMessagesEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Advertiser messages are disabled in app configurations.',
            ], 422);
        }

        $request->validate([
            'title'   => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'nullable|in:success,warning,error,info,payment,campaign,system',
        ]);

        MessageDeliveryManager::createInAppMessage(
            $advertiser,
            $request->title,
            $request->message,
            $request->input('type', 'info'),
            $request->input('action_url'),
        );

        return response()->json(['success' => true, 'message' => 'Notification sent to ' . $advertiser->email]);
    }

    public function export(Request $request)
    {
        $format = $request->input('format', 'csv');

        $advertisers = User::where('role', 'advertiser')
            ->where('is_deleted', false)
            ->with('profile')
            ->get();

        $data = $advertisers->map(function ($a) {
            $campaignCount = Campaign::where('advertiser_id', $a->id)->where('is_deleted', false)->count();
            $spend = StatDaily::where('advertiser_id', $a->id)->sum('revenue');
            return [
                'ID'              => $a->id,
                'Email'           => $a->email,
                'Name'            => trim(($a->profile->first_name ?? '') . ' ' . ($a->profile->last_name ?? '')),
                'Company'         => $a->profile->company_name ?? '',
                'Status'          => $a->status,
                'Total Campaigns' => $campaignCount,
                'Total Spend'     => round($spend, 2),
                'Created'         => $a->created_at?->format('Y-m-d'),
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
            'Content-Disposition' => 'attachment; filename="advertisers.csv"',
        ]);
    }
}

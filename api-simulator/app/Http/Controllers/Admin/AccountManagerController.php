<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AccountManagerController extends Controller
{
    public function index(Request $request)
    {
        // Get all admin/manager/operational users who can be account managers
        $accountManagers = User::query()
            ->whereIn('role', ['admin', 'manager', 'operational'])
            ->where('is_deleted', false)
            ->where('status', 'active')
            ->withCount('managedAccounts')
            ->orderBy('email')
            ->get();

        // Get filter parameters
        $search = $request->input('search');
        $roleFilter = $request->input('role');
        $managerFilter = $request->input('manager');

        // Get all members (publishers and advertisers) with their account managers
        $membersQuery = User::query()
            ->with(['accountManager', 'profile'])
            ->whereIn('role', ['publisher', 'advertiser'])
            ->where('is_deleted', false);

        if ($search) {
            $membersQuery->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhereHas('profile', function ($pq) use ($search) {
                        $pq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('company_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($roleFilter) {
            $membersQuery->where('role', $roleFilter);
        }

        if ($managerFilter === 'unassigned') {
            $membersQuery->whereNull('account_manager_id');
        } elseif ($managerFilter && $managerFilter !== 'all') {
            $membersQuery->where('account_manager_id', $managerFilter);
        }

        $members = $membersQuery->latest()->paginate(20)->withQueryString();

        // Summary stats
        $summary = [
            'total_members' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_deleted', false)->count(),
            'advertisers' => User::where('role', 'advertiser')->where('is_deleted', false)->count(),
            'publishers' => User::where('role', 'publisher')->where('is_deleted', false)->count(),
            'assigned' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_deleted', false)->whereNotNull('account_manager_id')->count(),
            'unassigned' => User::whereIn('role', ['publisher', 'advertiser'])->where('is_deleted', false)->whereNull('account_manager_id')->count(),
            'active_managers' => User::whereIn('role', ['admin', 'manager', 'operational'])->where('is_deleted', false)->where('status', 'active')->whereHas('managedAccounts')->count(),
        ];

        return view('admin.account-managers.index', [
            'accountManagers' => $accountManagers,
            'members' => $members,
            'summary' => $summary,
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
                'manager' => $managerFilter,
            ],
        ]);
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:aq_users,id'],
            'account_manager_id' => ['nullable', 'exists:aq_users,id'],
        ]);

        // Verify the account manager is admin/manager/operational
        if ($validated['account_manager_id']) {
            $manager = User::findOrFail($validated['account_manager_id']);
            if (!in_array($manager->role, ['admin', 'manager', 'operational'])) {
                return back()->withErrors(['account_manager_id' => 'Selected user cannot be an account manager.']);
            }
        }

        // Update only publisher/advertiser members, never admin users.
        $count = User::whereIn('id', $validated['user_ids'])
            ->whereIn('role', ['publisher', 'advertiser'])
            ->where('is_deleted', false)
            ->update(['account_manager_id' => $validated['account_manager_id']]);

        $action = $validated['account_manager_id'] ? 'assigned' : 'unassigned';

        return redirect()->route('admin.account-managers')->with('success', "Successfully {$action} {$count} member(s).");
    }

    public function bulkAssign(Request $request)
    {
        $validated = $request->validate([
            'filter_role' => ['nullable', 'in:publisher,advertiser'],
            'filter_status' => ['nullable', 'in:assigned,unassigned'],
            'account_manager_id' => ['nullable', 'exists:aq_users,id'],
        ]);

        // Verify the account manager is admin/manager/operational
        if ($validated['account_manager_id']) {
            $manager = User::findOrFail($validated['account_manager_id']);
            if (!in_array($manager->role, ['admin', 'manager', 'operational'])) {
                return back()->withErrors(['account_manager_id' => 'Selected user cannot be an account manager.']);
            }
        }

        $query = User::query()
            ->whereIn('role', ['publisher', 'advertiser'])
            ->where('is_deleted', false);

        if ($validated['filter_role']) {
            $query->where('role', $validated['filter_role']);
        }

        if ($validated['filter_status'] === 'assigned') {
            $query->whereNotNull('account_manager_id');
        } elseif ($validated['filter_status'] === 'unassigned') {
            $query->whereNull('account_manager_id');
        }

        $count = $query->count();
        $query->update(['account_manager_id' => $validated['account_manager_id']]);

        $action = $validated['account_manager_id'] ? 'assigned' : 'unassigned';

        return redirect()->route('admin.account-managers')->with('success', "Successfully {$action} {$count} member(s).");
    }

    public function show(User $user)
    {
        // Show details of a specific account manager and their managed accounts
        if (!in_array($user->role, ['admin', 'manager', 'operational'])) {
            abort(404);
        }

        $managedAccounts = $user->managedAccounts()
            ->with('profile')
            ->whereIn('role', ['publisher', 'advertiser'])
            ->where('is_deleted', false)
            ->latest()
            ->paginate(20);

        return view('admin.account-managers.show', [
            'manager' => $user,
            'managedAccounts' => $managedAccounts,
        ]);
    }
}

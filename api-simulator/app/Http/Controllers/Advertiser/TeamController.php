<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserTeamInvitation;
use App\Models\AdvertiserTeamMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $advertiser = $request->user()->load('profile');
        $this->ensureOwnerMember($advertiser);

        $members = AdvertiserTeamMember::query()
            ->with(['user.profile', 'inviter'])
            ->where('owner_advertiser_id', $advertiser->id)
            ->orderByRaw("CASE team_role WHEN 'owner' THEN 0 WHEN 'admin' THEN 1 ELSE 2 END")
            ->orderBy('email')
            ->get();

        $invitations = AdvertiserTeamInvitation::query()
            ->with(['member', 'inviter'])
            ->where('owner_advertiser_id', $advertiser->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(function (AdvertiserTeamInvitation $invitation) {
                $invitation->accept_url = route('advertiser.team-invitations.accept', $invitation->token);
                return $invitation;
            });

        return view('advertiser.teams.index', [
            'companyName' => $advertiser->profile?->company_name,
            'members' => $members,
            'invitations' => $invitations,
            'roles' => $this->roles(),
            'permissions' => $this->permissions(),
            'summary' => [
                'active' => $members->where('status', 'active')->count(),
                'pending' => $members->where('status', 'pending')->count(),
                'disabled' => $members->where('status', 'disabled')->count(),
                'open_invitations' => $invitations->where('status', 'pending')->count(),
            ],
        ]);
    }

    public function invite(Request $request)
    {
        $advertiser = $request->user()->load('profile');

        if (blank($advertiser->profile?->company_name)) {
            return back()->withErrors(['company_name' => 'Add company information before inviting team members.']);
        }

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'team_role' => ['required', Rule::in(array_keys($this->roles()))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys($this->permissions()))],
        ]);

        $email = Str::lower(trim($data['email']));

        if ($email === Str::lower($advertiser->email)) {
            return back()->withErrors(['email' => 'You are already the owner of this advertiser team.']);
        }

        $permissions = $this->permissionsForRole($data['team_role'], $data['permissions'] ?? []);
        $existingUser = User::query()
            ->where('email', $email)
            ->where('is_deleted', false)
            ->first();

        if ($existingUser && $existingUser->role !== 'advertiser') {
            return back()->withErrors(['email' => 'Only advertiser users can be invited to advertiser teams.']);
        }

        $invitation = null;

        DB::transaction(function () use ($advertiser, $data, $email, $permissions, $existingUser, &$invitation) {
            $member = AdvertiserTeamMember::query()->updateOrCreate([
                'owner_advertiser_id' => $advertiser->id,
                'email' => $email,
            ], [
                'user_id' => $existingUser?->id,
                'name' => $data['name'] ?: $existingUser?->email,
                'team_role' => $data['team_role'],
                'permissions' => $permissions,
                'status' => 'pending',
                'invited_by' => $advertiser->id,
            ]);

            AdvertiserTeamInvitation::query()
                ->where('owner_advertiser_id', $advertiser->id)
                ->where('email', $email)
                ->where('status', 'pending')
                ->update(['status' => 'revoked']);

            $invitation = AdvertiserTeamInvitation::create([
                'owner_advertiser_id' => $advertiser->id,
                'member_id' => $member->id,
                'email' => $email,
                'name' => $data['name'] ?: $member->name,
                'team_role' => $data['team_role'],
                'permissions' => $permissions,
                'token' => Str::random(64),
                'status' => 'pending',
                'expires_at' => now()->addDays(14),
                'invited_by' => $advertiser->id,
            ]);
        });

        return redirect()
            ->route('advertiser.teams')
            ->with('success', 'Invitation created. Share this link: ' . route('advertiser.team-invitations.accept', $invitation->token));
    }

    public function updateMember(Request $request, AdvertiserTeamMember $member)
    {
        $this->authorizeMember($request, $member);

        if ($member->team_role === 'owner') {
            return back()->withErrors(['member' => 'The owner role cannot be changed.']);
        }

        $data = $request->validate([
            'team_role' => ['required', Rule::in(array_keys($this->roles()))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys($this->permissions()))],
        ]);

        $member->update([
            'team_role' => $data['team_role'],
            'permissions' => $this->permissionsForRole($data['team_role'], $data['permissions'] ?? []),
        ]);

        return redirect()->route('advertiser.teams')->with('success', 'Team member updated.');
    }

    public function updateStatus(Request $request, AdvertiserTeamMember $member)
    {
        $this->authorizeMember($request, $member);

        if ($member->team_role === 'owner') {
            return back()->withErrors(['member' => 'The owner cannot be disabled.']);
        }

        $data = $request->validate([
            'status' => ['required', 'in:active,disabled'],
        ]);

        $member->update(['status' => $data['status']]);

        return redirect()->route('advertiser.teams')->with('success', 'Team member status updated.');
    }

    public function revokeInvitation(Request $request, AdvertiserTeamInvitation $invitation)
    {
        if ((int) $invitation->owner_advertiser_id !== (int) $request->user()->id) {
            abort(404);
        }

        if ($invitation->status === 'pending') {
            $invitation->update(['status' => 'revoked']);
            $invitation->member?->update(['status' => 'disabled']);
        }

        return redirect()->route('advertiser.teams')->with('success', 'Invitation revoked.');
    }

    public function destroyMember(Request $request, AdvertiserTeamMember $member)
    {
        $this->authorizeMember($request, $member);

        if ($member->team_role === 'owner') {
            return back()->withErrors(['member' => 'The owner cannot be removed.']);
        }

        $member->delete();

        return redirect()->route('advertiser.teams')->with('success', 'Team member removed.');
    }

    private function ensureOwnerMember(User $advertiser): void
    {
        AdvertiserTeamMember::query()->firstOrCreate([
            'owner_advertiser_id' => $advertiser->id,
            'email' => Str::lower($advertiser->email),
        ], [
            'user_id' => $advertiser->id,
            'name' => $advertiser->profile?->company_name ?: $advertiser->email,
            'team_role' => 'owner',
            'permissions' => array_keys($this->permissions()),
            'status' => 'active',
            'accepted_at' => now(),
        ]);
    }

    private function authorizeMember(Request $request, AdvertiserTeamMember $member): void
    {
        if ((int) $member->owner_advertiser_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function permissionsForRole(string $role, array $selected): array
    {
        if ($role === 'admin') {
            return array_keys($this->permissions());
        }

        if ($role === 'billing') {
            return array_values(array_unique(array_merge($selected, ['billing.manage'])));
        }

        if ($role === 'analyst') {
            return array_values(array_unique(array_merge($selected, ['reports.view'])));
        }

        return array_values(array_intersect($selected, array_keys($this->permissions())));
    }

    private function roles(): array
    {
        return [
            'admin' => 'Admin',
            'manager' => 'Campaign Manager',
            'analyst' => 'Analyst',
            'billing' => 'Billing',
            'viewer' => 'Viewer',
        ];
    }

    private function permissions(): array
    {
        return [
            'campaigns.manage' => 'Manage campaigns',
            'creatives.manage' => 'Manage creatives',
            'audiences.manage' => 'Manage audiences',
            'reports.view' => 'View reports',
            'tracking.manage' => 'Manage tracking',
            'billing.manage' => 'Manage billing',
            'team.manage' => 'Manage team',
        ];
    }
}

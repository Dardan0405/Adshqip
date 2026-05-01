<?php

namespace App\Http\Controllers\Advertiser;

use App\Http\Controllers\Controller;
use App\Models\AdvertiserTeamInvitation;
use App\Models\User;
use Illuminate\Http\Request;

class TeamInvitationController extends Controller
{
    public function accept(Request $request, string $token)
    {
        $invitation = AdvertiserTeamInvitation::query()
            ->with(['owner.profile', 'member'])
            ->where('token', $token)
            ->firstOrFail();

        if (! $invitation->isAcceptable()) {
            if ($invitation->status === 'pending') {
                $invitation->update(['status' => 'expired']);
            }

            return redirect()->route('signin')->withErrors(['email' => 'This team invitation is no longer valid.']);
        }

        $user = $request->user();

        if (! $user) {
            $existingUser = User::query()->where('email', $invitation->email)->where('is_deleted', false)->first();

            if ($existingUser) {
                return redirect()->route('signin')->with('success', 'Sign in with ' . $invitation->email . ' to accept the team invitation.');
            }

            return redirect()
                ->route('register', [
                    'role' => 'advertiser',
                    'email' => $invitation->email,
                    'team_invite' => $invitation->token,
                ])
                ->with('success', 'Create an advertiser account to join ' . ($invitation->owner->profile?->company_name ?: $invitation->owner->email) . '.');
        }

        if (strtolower($user->email) !== strtolower($invitation->email)) {
            return redirect()->route('advertiser.teams')->withErrors(['email' => 'This invitation belongs to ' . $invitation->email . '.']);
        }

        if ($user->role !== 'advertiser') {
            return redirect('/')->withErrors(['email' => 'Only advertiser accounts can join advertiser teams.']);
        }

        $invitation->member?->update([
            'user_id' => $user->id,
            'status' => 'active',
            'accepted_at' => now(),
        ]);

        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return redirect()->route('advertiser.teams')->with('success', 'Team invitation accepted.');
    }
}

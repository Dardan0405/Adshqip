<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\User;
use App\Models\Site;
use App\Models\Ad;
use Illuminate\Http\Request;

class AdminSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = trim((string) $request->input('q'));

        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];

        // Search Users (Advertisers & Publishers)
        $users = User::query()
            ->with('profile')
            ->where('is_deleted', false)
            ->where(function ($q) use ($query) {
                $q->where('email', 'like', "%{$query}%")
                    ->orWhereHas('profile', function ($pq) use ($query) {
                        $pq->where('first_name', 'like', "%{$query}%")
                            ->orWhere('last_name', 'like', "%{$query}%")
                            ->orWhere('company_name', 'like', "%{$query}%");
                    });
            })
            ->limit(5)
            ->get();

        foreach ($users as $user) {
            $name = $user->profile?->first_name
                ? $user->profile->first_name . ' ' . $user->profile->last_name
                : $user->email;

            $results[] = [
                'type' => 'user',
                'icon' => 'user',
                'label' => $name,
                'sublabel' => ucfirst($user->role) . ' - ' . $user->email,
                'url' => $user->role === 'advertiser'
                    ? route('admin.advertisers.show', $user->id)
                    : ($user->role === 'publisher'
                        ? route('admin.publishers.show', $user->id)
                        : '#'),
            ];
        }

        // Search Campaigns
        $campaigns = Campaign::query()
            ->with('advertiser')
            ->where('is_deleted', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('id', $query);
            })
            ->limit(5)
            ->get();

        foreach ($campaigns as $campaign) {
            $results[] = [
                'type' => 'campaign',
                'icon' => 'campaign',
                'label' => $campaign->name,
                'sublabel' => 'Campaign #' . $campaign->id . ' - ' . ($campaign->advertiser?->email ?? 'Unknown'),
                'url' => route('admin.campaigns.show', $campaign->id),
            ];
        }

        // Search Sites
        $sites = Site::query()
            ->with('publisher')
            ->where('is_deleted', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('domain', 'like', "%{$query}%");
            })
            ->limit(5)
            ->get();

        foreach ($sites as $site) {
            $results[] = [
                'type' => 'site',
                'icon' => 'site',
                'label' => $site->name,
                'sublabel' => $site->domain . ' - ' . ($site->publisher?->email ?? 'Unknown'),
                'url' => route('admin.sites.show', $site->id),
            ];
        }

        // Search Ads/Creatives
        $ads = Ad::query()
            ->with('campaign')
            ->where('is_deleted', false)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('id', $query);
            })
            ->limit(5)
            ->get();

        foreach ($ads as $ad) {
            $results[] = [
                'type' => 'creative',
                'icon' => 'creative',
                'label' => $ad->name,
                'sublabel' => 'Ad #' . $ad->id . ' - ' . ($ad->campaign?->name ?? 'No campaign'),
                'url' => route('admin.adformats.edit', $ad->id),
            ];
        }

        return response()->json([
            'results' => array_slice($results, 0, 15),
            'query' => $query,
        ]);
    }
}

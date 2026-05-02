<?php

namespace App\Http\Controllers;

use App\Models\DirectLink;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublisherDirectLinkController extends Controller
{
    public function index(Request $request): View
    {
        $userId = (int) $request->user()->id;
        $filters = $this->filters($request);

        $query = $this->baseQuery($userId);

        if ($filters['status'] !== null) {
            $query->where('status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('link_code', 'like', '%' . $search . '%')
                    ->orWhere('destination_url', 'like', '%' . $search . '%');
            });
        }

        $links = $query->orderByDesc('created_at')->paginate(15)->withQueryString();

        return view('publisher.direct-links.index', [
            'links' => $links,
            'summary' => $this->summary($userId),
            'filters' => $filters,
            'defaults' => [
                'status' => 'all',
            ],
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $link = $this->baseQuery((int) $request->user()->id)
            ->with('creator:id,email')
            ->findOrFail($id);

        $selectedPublisherNames = $link->publisher_scope === 'selected'
            ? $this->publisherNames($link->publisher_ids ?? [])
            : collect();

        $selectedZones = $link->adblock_scope === 'selected'
            ? $this->zoneNames($link->zone_ids ?? [])
            : collect();

        return view('publisher.direct-links.show', [
            'link' => $link,
            'selectedPublisherNames' => $selectedPublisherNames,
            'selectedZones' => $selectedZones,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $links = $this->baseQuery((int) $request->user()->id);

        if ($filters['status'] !== null) {
            $links->where('status', $filters['status']);
        }

        if ($filters['search'] !== '') {
            $search = $filters['search'];

            $links->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('link_code', 'like', '%' . $search . '%')
                    ->orWhere('destination_url', 'like', '%' . $search . '%');
            });
        }

        $rows = $links->orderByDesc('created_at')->get();
        $filename = 'publisher_direct_links_' . now()->format('Y-m-d_His') . '.csv';

        return response()->stream(function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Code', 'URL', 'Destination URL', 'Status', 'Publisher Scope', 'AdBlock Scope', 'Clicks', 'Views', 'Expires At', 'Created At']);

            foreach ($rows as $link) {
                fputcsv($file, [
                    $link->id,
                    $link->name,
                    $link->link_code,
                    $link->full_url,
                    $link->destination_url,
                    $link->status,
                    $link->publisher_scope,
                    $link->adblock_scope,
                    (int) $link->click_count,
                    (int) $link->view_count,
                    optional($link->expires_at)->format('Y-m-d H:i:s'),
                    optional($link->created_at)->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function baseQuery(int $userId)
    {
        return DirectLink::query()
            ->with('creator:id,email')
            ->where(function ($query) use ($userId) {
                $query->where('publisher_scope', 'all')
                    ->orWhere(function ($builder) use ($userId) {
                        $builder->where('publisher_scope', 'selected')
                            ->whereJsonContains('publisher_ids', $userId);
                    });
            });
    }

    private function filters(Request $request): array
    {
        return [
            'search' => trim((string) $request->query('search', '')),
            'status' => $request->query('status') && $request->query('status') !== 'all'
                ? (string) $request->query('status')
                : null,
        ];
    }

    private function summary(int $userId): array
    {
        $links = $this->baseQuery($userId)->get();

        return [
            'total' => $links->count(),
            'active' => $links->where('status', 'active')->count(),
            'paused' => $links->where('status', 'paused')->count(),
            'expired' => $links->where('status', 'expired')->count(),
            'clicks' => (int) $links->sum('click_count'),
            'views' => (int) $links->sum('view_count'),
        ];
    }

    private function publisherNames(array $publisherIds): Collection
    {
        if ($publisherIds === []) {
            return collect();
        }

        return \App\Models\User::query()
            ->whereIn('id', $publisherIds)
            ->where('role', 'publisher')
            ->where('is_deleted', false)
            ->orderBy('email')
            ->get(['id', 'email']);
    }

    private function zoneNames(array $zoneIds): Collection
    {
        if ($zoneIds === []) {
            return collect();
        }

        return Zone::query()
            ->with(['site:id,name'])
            ->whereIn('id', $zoneIds)
            ->where('is_deleted', false)
            ->orderBy('name')
            ->get(['id', 'site_id', 'name', 'ad_code']);
    }
}

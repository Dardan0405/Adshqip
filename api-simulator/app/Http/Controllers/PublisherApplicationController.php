<?php

namespace App\Http\Controllers;

use App\Models\TelegramMiniApp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublisherApplicationController extends Controller
{
    public function index(Request $request): View
    {
        $publisherId = (int) $request->user()->id;
        $search = trim((string) $request->input('search', ''));

        $query = TelegramMiniApp::query()
            ->where('user_id', $publisherId)
            ->where('is_deleted', false);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('app_name', 'like', '%' . $search . '%')
                    ->orWhere('app_url', 'like', '%' . $search . '%')
                    ->orWhere('category', 'like', '%' . $search . '%')
                    ->orWhere('application_type', 'like', '%' . $search . '%');
            });
        }

        $apps = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        $stats = collect();
        if (Schema::hasTable('aq_telegram_mini_app_events')) {
            $stats = DB::table('aq_telegram_mini_app_events')
                ->whereIn('mini_app_id', $apps->pluck('id'))
                ->selectRaw("
                    mini_app_id,
                    SUM(CASE WHEN event_type = 'ad_impression' THEN 1 ELSE 0 END) as impressions,
                    SUM(CASE WHEN event_type = 'ad_click' THEN 1 ELSE 0 END) as clicks,
                    SUM(COALESCE(revenue, 0)) as revenue
                ")
                ->groupBy('mini_app_id')
                ->get()
                ->mapWithKeys(function ($row) {
                    $impressions = (int) ($row->impressions ?? 0);
                    $clicks = (int) ($row->clicks ?? 0);
                    $revenue = (float) ($row->revenue ?? 0);

                    return [$row->mini_app_id => (object) [
                        'impressions' => $impressions,
                        'clicks' => $clicks,
                        'ctr' => $impressions > 0 ? round(($clicks / $impressions) * 100, 2) : 0.0,
                        'ecpm' => $impressions > 0 ? round(($revenue / $impressions) * 1000, 2) : 0.0,
                        'revenue' => round($revenue, 2),
                    ]];
                });
        }

        return view('publisher.apps.index', [
            'apps' => $apps,
            'stats' => $stats,
            'search' => $search,
            'applicationTypes' => $this->applicationTypes(),
            'categories' => $this->categories(),
            'balance' => 17491.53,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_type' => ['required', 'in:' . implode(',', array_keys($this->applicationTypes()))],
            'app_url' => ['required', 'string', 'max:500'],
            'app_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:' . implode(',', array_keys($this->categories()))],
        ]);

        TelegramMiniApp::query()->create([
            'user_id' => $request->user()->id,
            'application_type' => $validated['application_type'],
            'app_name' => trim($validated['app_name']),
            'app_short_name' => $this->uniqueShortName($validated['app_name']),
            'bot_username' => $this->uniqueBotUsername($validated['app_name']),
            'bot_token_hash' => hash('sha256', Str::uuid()->toString()),
            'app_url' => $this->normalizeUrl($validated['app_url']),
            'category' => $validated['category'],
            'status' => 'pending_review',
            'admin_approved' => false,
            'is_deleted' => false,
        ]);

        return redirect()->route('publisher.apps')->with('success', 'Application created successfully.');
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $app = $this->findPublisherApp($request, $id);

        return response()->json([
            'id' => $app->id,
            'application_type' => $app->application_type ?? 'web',
            'app_url' => $app->app_url,
            'app_name' => $app->app_name,
            'category' => $app->category,
            'status' => $app->status,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $app = $this->findPublisherApp($request, $id);

        $validated = $request->validate([
            'application_type' => ['required', 'in:' . implode(',', array_keys($this->applicationTypes()))],
            'app_url' => ['required', 'string', 'max:500'],
            'app_name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:' . implode(',', array_keys($this->categories()))],
        ]);

        $app->update([
            'application_type' => $validated['application_type'],
            'app_url' => $this->normalizeUrl($validated['app_url']),
            'app_name' => trim($validated['app_name']),
            'category' => $validated['category'],
        ]);

        return redirect()->route('publisher.apps')->with('success', 'Application updated successfully.');
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $app = $this->findPublisherApp($request, $id);
        $app->update(['is_deleted' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Application deleted successfully.',
        ]);
    }

    private function findPublisherApp(Request $request, int $id): TelegramMiniApp
    {
        return TelegramMiniApp::query()
            ->where('user_id', $request->user()->id)
            ->where('is_deleted', false)
            ->findOrFail($id);
    }

    private function normalizeUrl(string $value): string
    {
        $candidate = trim($value);
        if ($candidate === '') {
            return $candidate;
        }

        return preg_match('#^https?://#i', $candidate) ? $candidate : 'https://' . $candidate;
    }

    private function uniqueShortName(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 40, '');
        $base = $base !== '' ? $base : 'publisher_app';

        do {
            $candidate = Str::limit($base . '_' . strtolower(Str::random(6)), 64, '');
        } while (TelegramMiniApp::query()->where('app_short_name', $candidate)->exists());

        return $candidate;
    }

    private function uniqueBotUsername(string $name): string
    {
        $base = preg_replace('/[^A-Za-z0-9_]/', '', Str::snake($name));
        $base = $base !== '' ? strtolower($base) : 'publisherapp';

        do {
            $candidate = Str::limit($base . strtolower(Str::random(6)) . 'bot', 100, '');
        } while (TelegramMiniApp::query()->where('bot_username', $candidate)->exists());

        return $candidate;
    }

    private function applicationTypes(): array
    {
        return [
            'web' => 'Web App',
            'android' => 'Android App',
            'ios' => 'iOS App',
            'telegram' => 'Telegram Mini App',
        ];
    }

    private function categories(): array
    {
        return [
            'monetization' => 'Monetization',
            'analytics' => 'Analytics',
            'campaign_manager' => 'Campaign Manager',
            'ad_preview' => 'Ad Preview',
            'custom' => 'Custom',
        ];
    }
}

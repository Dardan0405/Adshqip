<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ApiKeyController extends Controller
{
    public function index(Request $request)
    {
        $apiKeys = ApiKey::query()
            ->with('creator')
            ->where('user_id', $request->user()->id)
            ->latest('created_at')
            ->paginate(15);

        $summary = [
            'total' => ApiKey::where('user_id', $request->user()->id)->count(),
            'active' => ApiKey::where('user_id', $request->user()->id)->where('status', 'active')->count(),
            'revoked' => ApiKey::where('user_id', $request->user()->id)->where('status', 'revoked')->count(),
            'expiring' => ApiKey::where('user_id', $request->user()->id)
                ->where('status', 'active')
                ->whereNotNull('expires_at')
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->count(),
        ];

        return view('publisher.api-keys.index', [
            'apiKeys' => $apiKeys,
            'summary' => $summary,
            'generatedApiKey' => session('generated_api_key'),
        ]);
    }

    public function store(Request $request)
    {
        $allowedPermissions = $this->permissions();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys($allowedPermissions))],
            'rate_limit_per_minute' => ['required', 'integer', 'min:1', 'max:100000'],
            'allowed_ips' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        $publicKey = 'AK_' . Str::upper(Str::random(24));
        $plainSecret = 'SK_' . Str::lower(Str::random(40));

        $allowedIps = collect(preg_split('/[\s,]+/', (string) ($validated['allowed_ips'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn ($ip) => trim($ip))
            ->filter()
            ->values()
            ->all();

        ApiKey::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'api_key' => $publicKey,
            'api_secret_hash' => hash('sha256', $plainSecret),
            'api_secret_encrypted' => $plainSecret,
            'permissions' => array_values(array_intersect($validated['permissions'] ?? [], array_keys($allowedPermissions))),
            'rate_limit_per_minute' => (int) $validated['rate_limit_per_minute'],
            'allowed_ips' => $allowedIps === [] ? null : $allowedIps,
            'status' => 'active',
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('publisher.api-keys')
            ->with('success', 'API key created successfully.')
            ->with('generated_api_key', [
                'api_key' => $publicKey,
                'api_secret' => $plainSecret,
            ]);
    }

    public function revoke(Request $request, ApiKey $apiKey)
    {
        $this->authorizeApiKey($request, $apiKey);
        $apiKey->update(['status' => 'revoked']);

        return redirect()->route('publisher.api-keys')->with('success', 'API key revoked successfully.');
    }

    public function activate(Request $request, ApiKey $apiKey)
    {
        $this->authorizeApiKey($request, $apiKey);
        $apiKey->update(['status' => 'active']);

        return redirect()->route('publisher.api-keys')->with('success', 'API key activated successfully.');
    }

    private function authorizeApiKey(Request $request, ApiKey $apiKey): void
    {
        if ((int) $apiKey->user_id !== (int) $request->user()->id) {
            abort(404);
        }
    }

    private function permissions(): array
    {
        return [
            'read_reports' => 'Read Reports',
            'manage_campaigns' => 'Manage Campaigns',
            'manage_sites' => 'Manage Sites',
            'manage_adblocks' => 'Manage AdBlocks',
        ];
    }
}

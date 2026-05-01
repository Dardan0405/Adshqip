<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemProvider;
use App\Support\SystemProviderRegistry;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SystemProviderController extends Controller
{
    private const PROVIDER_TYPES = [
        'ad_network' => 'Ad Network',
        'payment' => 'Payment',
        'analytics' => 'Analytics',
        'messaging' => 'Messaging',
        'storage' => 'Storage',
        'identity' => 'Identity',
        'fraud' => 'Fraud Protection',
        'other' => 'Other',
    ];

    private const AUTH_TYPES = [
        'none' => 'No Auth',
        'api_key' => 'API Key',
        'bearer_token' => 'Bearer Token',
        'basic' => 'Basic Auth',
        'oauth2' => 'OAuth 2.0',
        'custom' => 'Custom',
    ];

    private const ENVIRONMENTS = [
        'production' => 'Production',
        'staging' => 'Staging',
        'sandbox' => 'Sandbox',
        'development' => 'Development',
    ];

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $type = (string) $request->input('type', '');
        $status = (string) $request->input('status', '');

        $providers = SystemProvider::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%')
                        ->orWhere('base_url', 'like', '%' . $search . '%');
                });
            })
            ->when(array_key_exists($type, self::PROVIDER_TYPES), fn ($query) => $query->where('provider_type', $type))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('status', $status))
            ->orderBy('priority')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => SystemProvider::count(),
            'active' => SystemProvider::where('status', 'active')->count(),
            'inactive' => SystemProvider::where('status', 'inactive')->count(),
            'healthy' => SystemProvider::where('last_check_status', 'success')->count(),
        ];

        return view('admin.system-providers.index', [
            'providers' => $providers,
            'providerTypes' => self::PROVIDER_TYPES,
            'authTypes' => self::AUTH_TYPES,
            'environments' => self::ENVIRONMENTS,
            'stats' => $stats,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedProvider($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['name']);
        $validated['config'] = $this->decodeConfig($request->input('config_json'));

        SystemProvider::create($validated);

        return redirect()->route('admin.system-providers')->with('success', 'System provider created successfully.');
    }

    public function update(Request $request, SystemProvider $systemProvider)
    {
        $validated = $this->validatedProvider($request, $systemProvider);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? $validated['name'], $systemProvider->id);
        $validated['config'] = $this->decodeConfig($request->input('config_json'));

        if (blank($request->input('api_secret'))) {
            unset($validated['api_secret']);
        }

        $systemProvider->update($validated);

        return redirect()->route('admin.system-providers')->with('success', 'System provider updated successfully.');
    }

    public function activate(SystemProvider $systemProvider)
    {
        $systemProvider->update(['status' => 'active']);

        return redirect()->route('admin.system-providers')->with('success', 'System provider activated successfully.');
    }

    public function deactivate(SystemProvider $systemProvider)
    {
        $systemProvider->update(['status' => 'inactive']);

        return redirect()->route('admin.system-providers')->with('success', 'System provider deactivated successfully.');
    }

    public function test(SystemProvider $systemProvider)
    {
        $result = app(SystemProviderRegistry::class)->test($systemProvider);

        $systemProvider->update([
            'last_check_status' => $result['status'],
            'last_check_message' => $result['message'],
            'last_checked_at' => now(),
        ]);

        return redirect()->route('admin.system-providers')->with('success', 'System provider test completed.');
    }

    public function sync(Request $request, SystemProviderRegistry $registry)
    {
        $synced = $registry->syncFromPlatformSettings($request->user()?->id);

        return redirect()
            ->route('admin.system-providers')
            ->with('success', count($synced) . ' system provider integration(s) synced from existing settings.');
    }

    public function destroy(SystemProvider $systemProvider)
    {
        $systemProvider->delete();

        return redirect()->route('admin.system-providers')->with('success', 'System provider deleted successfully.');
    }

    private function validatedProvider(Request $request, ?SystemProvider $systemProvider = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => [
                'nullable',
                'string',
                'max:140',
                Rule::unique('aq_system_providers', 'slug')->ignore($systemProvider?->id),
            ],
            'provider_type' => ['required', Rule::in(array_keys(self::PROVIDER_TYPES))],
            'environment' => ['required', Rule::in(array_keys(self::ENVIRONMENTS))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'base_url' => ['nullable', 'url', 'max:255'],
            'webhook_url' => ['nullable', 'url', 'max:255'],
            'auth_type' => ['required', Rule::in(array_keys(self::AUTH_TYPES))],
            'api_key' => ['nullable', 'string', 'max:255'],
            'api_secret' => [$systemProvider ? 'nullable' : 'nullable', 'string', 'max:2000'],
            'priority' => ['required', 'integer', 'min:1', 'max:999'],
            'timeout_seconds' => ['required', 'integer', 'min:1', 'max:60'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);
    }

    private function decodeConfig(?string $config): ?array
    {
        if (blank($config)) {
            return null;
        }

        $decoded = json_decode($config, true);

        if (!is_array($decoded) || json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                'config_json' => 'Config must be valid JSON.',
            ]);
        }

        return $decoded;
    }

    private function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: 'provider';
        $slug = $base;
        $counter = 2;

        while (SystemProvider::where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists()) {
            $slug = $base . '-' . $counter++;
        }

        return $slug;
    }
}

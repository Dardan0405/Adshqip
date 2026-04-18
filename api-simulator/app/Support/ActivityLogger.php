<?php

namespace App\Support;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function logRequest(Request $request, ?int $statusCode = null, array $overrides = []): void
    {
        $user = $request->user();
        $route = $request->route();
        $routeName = $route?->getName();

        if (! $user || blank($routeName)) {
            return;
        }

        if (! ActivityLogSettings::isEnabled($request, $overrides)) {
            return;
        }

        $agent = UserAgentDetails::parse($request->userAgent());
        $entity = $this->resolveEntity($request);

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => $overrides['action'] ?? $this->normalizeAction($routeName, $request),
            'entity_type' => $overrides['entity_type'] ?? $entity['type'],
            'entity_id' => $overrides['entity_id'] ?? $entity['id'],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'browser' => $agent['browser'],
            'os' => $agent['os'],
            'metadata' => array_filter([
                'route' => $routeName,
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $statusCode,
                'description' => $overrides['description'] ?? $this->describeAction($routeName, $request),
            ], static fn ($value) => $value !== null && $value !== ''),
            'created_at' => now(),
        ]);
    }

    private function normalizeAction(string $routeName, Request $request): string
    {
        $action = preg_replace('/^admin\./', '', $routeName) ?: $routeName;
        $action = str_replace('.', '_', $action);

        return substr($action, 0, 100);
    }

    private function describeAction(string $routeName, Request $request): string
    {
        $label = preg_replace('/^admin\./', '', $routeName) ?: $request->path();
        $label = str_replace(['.', '-', '_'], ' ', $label);

        return ucfirst(trim($label));
    }

    private function resolveEntity(Request $request): array
    {
        $route = $request->route();
        if (! $route) {
            return ['type' => null, 'id' => null];
        }

        foreach ($route->parameters() as $key => $value) {
            if (is_scalar($value) && preg_match('/(^id$|_id$|report$|campaign$|creative$|site$|request$|payout$|invoice$|category$|keyword$|device$)/i', (string) $key)) {
                return [
                    'type' => substr(str_replace('_', ' ', (string) $key), 0, 50),
                    'id' => is_numeric($value) ? (int) $value : null,
                ];
            }
        }

        return ['type' => null, 'id' => null];
    }
}

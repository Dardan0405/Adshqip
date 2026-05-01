<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function handle(Request $request, Closure $next, ?string $permission = null): Response
    {
        $publicKey = (string) $request->header('X-API-Key', '');
        $secret = (string) $request->header('X-API-Secret', '');

        if ($publicKey === '' || $secret === '') {
            return response()->json([
                'message' => 'Missing API credentials.',
            ], 401);
        }

        $apiKey = ApiKey::query()
            ->where('api_key', $publicKey)
            ->first();

        if (! $apiKey || ! hash_equals((string) $apiKey->api_secret_hash, hash('sha256', $secret))) {
            return response()->json([
                'message' => 'Invalid API credentials.',
            ], 401);
        }

        if ($apiKey->status !== 'active') {
            return response()->json([
                'message' => 'API key is not active.',
            ], 403);
        }

        if ($apiKey->expires_at && $apiKey->expires_at->isPast()) {
            return response()->json([
                'message' => 'API key has expired.',
            ], 403);
        }

        $allowedIps = $apiKey->allowed_ips ?? [];
        if ($allowedIps !== [] && ! in_array($request->ip(), $allowedIps, true)) {
            return response()->json([
                'message' => 'API key is not allowed from this IP address.',
            ], 403);
        }

        if ($permission && ! in_array($permission, $apiKey->permissions ?? [], true)) {
            return response()->json([
                'message' => 'API key does not have the required permission.',
                'required_permission' => $permission,
            ], 403);
        }

        $apiKey->forceFill(['last_used_at' => now()])->save();

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}

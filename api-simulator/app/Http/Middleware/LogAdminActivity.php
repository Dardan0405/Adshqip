<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdminActivity
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldLog($request, $response)) {
            return $response;
        }

        $this->activityLogger->logRequest($request, $response->getStatusCode());

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (! $request->user()) {
            return false;
        }

        if (! $request->is('admin/*')) {
            return false;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return ! $request->routeIs('admin.audit-logs*');
    }
}

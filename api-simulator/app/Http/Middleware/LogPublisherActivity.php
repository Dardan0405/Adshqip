<?php

namespace App\Http\Middleware;

use App\Support\ActivityLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPublisherActivity
{
    public function __construct(private readonly ActivityLogger $activityLogger)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldLog($request, $response)) {
            $this->activityLogger->logRequest($request, $response->getStatusCode());
        }

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        if (! $request->user() || $request->user()->role !== 'publisher') {
            return false;
        }

        if (! $request->is('publisher/*')) {
            return false;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return ! $request->routeIs(
            'publisher.notifications*',
            'publisher.messages*',
            'publisher.activity-log*',
            'publisher.notification-settings*'
        );
    }
}

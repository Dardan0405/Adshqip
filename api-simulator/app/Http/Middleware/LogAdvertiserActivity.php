<?php

namespace App\Http\Middleware;

use App\Support\AdvertiserNotificationManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAdvertiserActivity
{
    public function __construct(private readonly AdvertiserNotificationManager $notifier)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $this->shouldNotify($request, $response)) {
            return $response;
        }

        $this->notifier->dispatchForRequest($request);

        return $response;
    }

    private function shouldNotify(Request $request, Response $response): bool
    {
        if (! $request->user() || $request->user()->role !== 'advertiser') {
            return false;
        }

        if (! $request->is('advertisers/*')) {
            return false;
        }

        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        if ($response->getStatusCode() >= 400) {
            return false;
        }

        return ! $request->routeIs(
            'advertiser.notifications*',
            'advertiser.messages.*',
            'advertiser.push.*',
            'advertiser.notification-settings*'
        );
    }
}

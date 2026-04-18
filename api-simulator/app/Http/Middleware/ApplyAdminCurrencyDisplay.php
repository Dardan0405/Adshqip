<?php

namespace App\Http\Middleware;

use App\Support\AdminCurrency;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplyAdminCurrencyDisplay
{
    public function __construct(private readonly AdminCurrency $adminCurrency)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        if (! $request->is('admin*') || ! $request->user()) {
            return $response;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();

        if (! is_string($content) || $content === '') {
            return $response;
        }

        $prefix = in_array($this->adminCurrency->code(), ['ALL', 'CHF'], true)
            ? $this->adminCurrency->symbol() . ' '
            : $this->adminCurrency->symbol();

        $content = preg_replace('/(?:&euro;|€|EUR\s+)(\s*)(\d[\d,]*(?:\.\d+)?)/u', $prefix . '$2', $content);

        if ($this->adminCurrency->code() !== 'USD') {
            $content = preg_replace('/\$(\d[\d,]*(?:\.\d+)?)/u', $prefix . '$1', $content);
        }

        $response->setContent($content);

        return $response;
    }
}

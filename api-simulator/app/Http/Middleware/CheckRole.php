<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            // Redirect the user to their own dashboard instead
            if ($user) {
                $redirect = match ($user->role) {
                    'admin', 'manager' => '/admin',
                    'advertiser'       => '/advertisers',
                    'publisher'        => '/publisher',
                    default            => '/',
                };

                return redirect($redirect)->with('error', 'You do not have access to that area.');
            }

            return redirect('/signin');
        }

        return $next($request);
    }
}

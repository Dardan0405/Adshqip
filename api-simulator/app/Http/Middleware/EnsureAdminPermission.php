<?php

namespace App\Http\Middleware;

use App\Support\AdminRoutePermissionMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['admin', 'manager', 'operational'], true)) {
            return $next($request);
        }

        $permission = AdminRoutePermissionMap::resolve($request);

        if ($permission === null) {
            return $next($request);
        }

        if ($user->hasRolePermission($permission)) {
            return $next($request);
        }

        return redirect('/admin')->with('error', 'You do not have permission to access that page.');
    }
}

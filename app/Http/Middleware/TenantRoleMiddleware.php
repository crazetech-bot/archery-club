<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Like Spatie's RoleMiddleware but temporarily switches the user's DB
 * connection to 'tenant' before checking roles, so Spatie queries the
 * correct tenant database instead of the central 'mysql' database.
 */
class TenantRoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role, ?string $guard = null): Response
    {
        $user = $request->user($guard);

        if (! $user) {
            throw UnauthorizedException::notLoggedIn();
        }

        $roles = explode('|', $role);

        // Switch connection to tenant so Spatie reads from the right DB
        $user->setConnection('tenant');
        $hasRole = $user->hasAnyRole($roles);
        $user->setConnection('mysql');

        if (! $hasRole) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}

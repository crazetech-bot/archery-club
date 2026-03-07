<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscribed
{
    /**
     * Placeholder — will enforce active subscription when billing is implemented.
     * Currently passes all requests through.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Database\Models\Tenant;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host         = $request->getHost();
        $centralDomain = env('TENANT_DOMAIN', 'fmsport.biz');

        $subdomain = str_replace('.' . $centralDomain, '', $host);

        /** @var Tenant|null $tenant */
        $tenant = Tenant::find($subdomain);

        if (! $tenant) {
            abort(404, 'Club not found.');
        }

        if (($tenant->status ?? 'active') === 'suspended') {
            abort(403, 'This club account has been suspended. Please contact support.');
        }

        // Build the tenant database name using Stancl's convention: prefix + id + suffix
        $dbName = config('tenancy.database.prefix', 'tenant')
                . $tenant->id
                . config('tenancy.database.suffix', '');

        Config::set('database.connections.tenant', [
            'driver'    => 'mysql',
            'host'      => env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $dbName,
            'username'  => env('DB_USERNAME'),
            'password'  => env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]);

        DB::purge('tenant');

        // Make tenant available anywhere via app('tenant')
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}

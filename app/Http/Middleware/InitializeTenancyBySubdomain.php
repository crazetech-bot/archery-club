<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyBySubdomain
{
    public function handle(Request $request, Closure $next): Response
    {
        $host          = $request->getHost();
        $centralDomain = env('TENANT_DOMAIN', 'fmsport.biz');

        $subdomain = str_replace('.' . $centralDomain, '', $host);

        /** @var Tenant|null $tenant */
        $tenant = Tenant::where('slug', $subdomain)->first();

        if (! $tenant) {
            abort(404, 'Club not found.');
        }

        if ($tenant->status === 'suspended') {
            abort(403, 'This club account has been suspended. Please contact support.');
        }

        Config::set('database.connections.tenant', [
            'driver'    => 'mysql',
            'host'      => $tenant->db_host ?: env('DB_HOST', '127.0.0.1'),
            'port'      => env('DB_PORT', '3306'),
            'database'  => $tenant->db_name,
            'username'  => $tenant->db_username ?: env('DB_USERNAME'),
            'password'  => $tenant->db_password ?: env('DB_PASSWORD'),
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix'    => '',
            'strict'    => true,
        ]);

        DB::purge('tenant');

        // Switch default connection so all tenant models use the tenant DB
        DB::setDefaultConnection('tenant');

        // Make tenant available anywhere via app('tenant')
        app()->instance('tenant', $tenant);

        return $next($request);
    }
}

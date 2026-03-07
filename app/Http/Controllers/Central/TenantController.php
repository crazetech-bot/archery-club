<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * GET /admin/tenants
     */
    public function index(): Response
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get()->map(fn ($t) => [
            'id'         => $t->id,
            'name'       => $t->name,
            'slug'       => $t->slug,
            'plan'       => $t->plan ?? 'free',
            'status'     => $t->status ?? 'active',
            'created_at' => $t->created_at,
        ]);

        return Inertia::render('Admin/Tenants/Index', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * GET /admin/tenants/{tenant}
     */
    public function show(Tenant $tenant): Response
    {
        return Inertia::render('Admin/Tenants/Show', [
            'tenant' => [
                'id'         => $tenant->id,
                'name'       => $tenant->name,
                'slug'       => $tenant->slug,
                'plan'       => $tenant->plan ?? 'free',
                'status'     => $tenant->status ?? 'active',
                'created_at' => $tenant->created_at,
            ],
        ]);
    }

    /**
     * GET /admin/tenants/create
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    /**
     * POST /admin/tenants
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'max:63', 'unique:tenants,slug'],
            'plan' => ['required', 'string', 'in:free,starter,pro,enterprise'],
        ]);

        Tenant::create([
            'name'        => $data['name'],
            'slug'        => $data['slug'],
            'db_name'     => env('DB_DATABASE_PREFIX', 'fmsport_') . $data['slug'],
            'db_host'     => env('DB_HOST', '127.0.0.1'),
            'db_username' => env('DB_USERNAME'),
            'db_password' => env('DB_PASSWORD'),
            'plan'        => $data['plan'],
            'status'      => 'active',
        ]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created successfully.');
    }

    /**
     * GET /admin/tenants/{tenant}/edit
     */
    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Admin/Tenants/Edit', [
            'tenant' => [
                'id'     => $tenant->id,
                'name'   => $tenant->name,
                'slug'   => $tenant->slug,
                'plan'   => $tenant->plan ?? 'free',
                'status' => $tenant->status ?? 'active',
            ],
        ]);
    }

    /**
     * PUT /admin/tenants/{tenant}
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', 'in:free,starter,pro,enterprise'],
        ]);

        $tenant->update($data);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated.');
    }

    /**
     * DELETE /admin/tenants/{tenant}
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted.');
    }

    /**
     * POST /admin/tenants/{tenant}/impersonate
     */
    public function impersonate(Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->slug . '.' . env('TENANT_DOMAIN', 'fmsport.biz');

        return redirect("https://{$domain}");
    }

    /**
     * PATCH /admin/tenants/{tenant}/status
     */
    public function updateStatus(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'string', 'in:active,suspended'],
        ]);

        $tenant->update($data);

        return redirect()->back()->with('success', "Tenant status updated to {$data['status']}.");
    }
}

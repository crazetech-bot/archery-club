<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Database\Models\Tenant;

class TenantController extends Controller
{
    /**
     * GET /admin/tenants
     */
    public function index(): Response
    {
        $tenants = Tenant::orderBy('created_at', 'desc')->get()->map(fn ($t) => [
            'id'         => $t->id,
            'name'       => $t->name ?? $t->id,
            'slug'       => $t->id,
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
                'name'       => $tenant->name ?? $tenant->id,
                'plan'       => $tenant->plan ?? 'free',
                'status'     => $tenant->status ?? 'active',
                'created_at' => $tenant->created_at,
            ],
        ]);
    }

    /**
     * POST /admin/tenants/{tenant}/impersonate
     */
    public function impersonate(Tenant $tenant): RedirectResponse
    {
        $domain = $tenant->id . '.' . env('TENANT_DOMAIN', 'fmsport.biz');

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

    // Resource stubs — expand as needed
    public function create(): Response
    {
        return Inertia::render('Admin/Tenants/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'alpha_dash', 'unique:tenants,id'],
            'plan' => ['required', 'string', 'in:free,starter,pro,enterprise'],
        ]);

        Tenant::create(['id' => $data['slug'], 'name' => $data['name'], 'plan' => $data['plan']]);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant created.');
    }

    public function edit(Tenant $tenant): Response
    {
        return Inertia::render('Admin/Tenants/Edit', ['tenant' => $tenant]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', 'in:free,starter,pro,enterprise'],
        ]);

        $tenant->update($data);

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant updated.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('admin.tenants.index')->with('success', 'Tenant deleted.');
    }
}

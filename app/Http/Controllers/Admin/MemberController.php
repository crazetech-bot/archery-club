<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MemberController extends Controller
{
    /**
     * GET /admin/members
     */
    public function index(Request $request): Response
    {
        $search     = $request->string('search');
        $filterRole = $request->string('role');

        // Build a unified member list from archers + coaches
        $archers = Archer::with('coach')
            ->when($search, fn ($q) => $q->whereHas(
                'user', fn ($u) => $u->where('name', 'like', "%{$search}%")
            ))
            ->get()
            ->map(fn (Archer $a) => [
                'id'         => $a->id,
                'user_id'    => $a->user_id,
                'name'       => $a->user?->name ?? "Archer #{$a->id}",
                'email'      => $a->user?->email,
                'role'       => 'archer',
                'category'   => $a->category,
                'coach_name' => $a->coach?->user?->name,
                'joined_at'  => $a->created_at,
            ]);

        $coaches = Coach::all()->map(fn (Coach $c) => [
            'id'         => $c->id,
            'user_id'    => $c->user_id,
            'name'       => $c->user?->name ?? "Coach #{$c->id}",
            'email'      => $c->user?->email,
            'role'       => 'coach',
            'category'   => null,
            'coach_name' => null,
            'joined_at'  => $c->created_at,
        ]);

        $members = $archers->concat($coaches)->sortBy('name')->values();

        if ($filterRole) {
            $members = $members->filter(fn ($m) => $m['role'] === $filterRole)->values();
        }

        return Inertia::render('Admin/Members', [
            'members' => $members,
            'coaches' => Coach::with('user')->get()->map(fn ($c) => [
                'id'   => $c->id,
                'name' => $c->user?->name ?? "Coach #{$c->id}",
            ]),
            'filters' => [
                'search' => (string) $search,
                'role'   => (string) $filterRole,
            ],
        ]);
    }

    /**
     * POST /admin/members/archers
     */
    public function storeArcher(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', Rule::unique('mysql.users', 'email')],
            'category'      => ['required', 'string', 'in:U12,U15,U18,U21,Senior,Master'],
            'coach_id'      => ['nullable', 'integer', 'exists:coaches,id'],
            'date_of_birth' => ['nullable', 'date'],
            'dominant_hand' => ['nullable', 'string', 'in:right,left'],
        ]);

        // Create central user
        $user = \App\Models\Central\User::on('mysql')->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
        ]);

        // Assign tenant role
        $tenant = tenant();
        \DB::connection('mysql')->table('tenant_user')->insert([
            'tenant_id' => $tenant->id,
            'user_id'   => $user->id,
            'role'      => 'archer',
        ]);

        // Create tenant archer record
        Archer::create([
            'user_id'        => $user->id,
            'category'       => $data['category'],
            'coach_id'       => $data['coach_id'],
            'date_of_birth'  => $data['date_of_birth'],
            'dominant_hand'  => $data['dominant_hand'],
        ]);

        // TODO: dispatch welcome email

        return redirect()->route('admin.members.index')
            ->with('success', "{$data['name']} added as archer.");
    }

    /**
     * PUT /admin/members/archers/{archer}
     */
    public function updateArcher(Request $request, Archer $archer)
    {
        $data = $request->validate([
            'category'  => ['required', 'string', 'in:U12,U15,U18,U21,Senior,Master'],
            'coach_id'  => ['nullable', 'integer', 'exists:coaches,id'],
        ]);

        $archer->update($data);

        return redirect()->back()->with('success', 'Archer updated.');
    }

    /**
     * DELETE /admin/members/archers/{archer}
     */
    public function destroyArcher(Archer $archer)
    {
        $archer->delete();

        return redirect()->route('admin.members.index')
            ->with('success', 'Archer removed.');
    }
}

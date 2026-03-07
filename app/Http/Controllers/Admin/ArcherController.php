<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Coach;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ArcherController extends Controller
{
    /**
     * GET /admin/archers
     */
    public function index(Request $request): Response
    {
        $query = Archer::with('coach')
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
            )
            ->when($request->bow_type, fn ($q, $b) =>
                $q->where('bow_type', $b)
            )
            ->when($request->experience_level, fn ($q, $e) =>
                $q->where('experience_level', $e)
            )
            ->when($request->filled('is_active'), fn ($q) =>
                $q->where('is_active', $request->boolean('is_active'))
            )
            ->orderBy('name');

        $archers = $query->paginate(25)->through(fn (Archer $a) => [
            'id'               => $a->id,
            'name'             => $a->name ?? $a->user?->name ?? "Archer #{$a->id}",
            'gender'           => $a->gender,
            'bow_type'         => $a->bow_type,
            'experience_level' => $a->experience_level,
            'category'         => $a->category,
            'age'              => $a->age,
            'coach_name'       => $a->coach?->name,
            'is_active'        => $a->is_active,
            'created_at'       => $a->created_at,
        ]);

        $coaches = Coach::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Archers/Index', [
            'archers'  => $archers,
            'coaches'  => $coaches,
            'filters'  => $request->only(['search', 'bow_type', 'experience_level', 'is_active']),
            'stats'    => [
                'total'   => Archer::count(),
                'active'  => Archer::where('is_active', true)->count(),
                'by_bow'  => Archer::selectRaw('bow_type, count(*) as total')
                                   ->whereNotNull('bow_type')
                                   ->groupBy('bow_type')
                                   ->pluck('total', 'bow_type'),
            ],
        ]);
    }

    /**
     * GET /admin/archers/create
     */
    public function create(): Response
    {
        $coaches = Coach::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Archers/Create', [
            'coaches'           => $coaches,
            'bowTypes'          => ['Recurve', 'Compound', 'Barebow', 'Longbow'],
            'experienceLevels'  => ['Beginner', 'Intermediate', 'Advanced', 'Elite'],
            'categories'        => ['U12', 'U15', 'U18', 'U21', 'Senior', 'Master'],
        ]);
    }

    /**
     * POST /admin/archers
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'gender'           => ['nullable', 'string', 'in:male,female,other'],
            'bow_type'         => ['nullable', 'string', 'in:Recurve,Compound,Barebow,Longbow'],
            'experience_level' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,Elite'],
            'category'         => ['nullable', 'string', 'in:U12,U15,U18,U21,Senior,Master'],
            'date_of_birth'    => ['nullable', 'date', 'before:today'],
            'dominant_hand'    => ['nullable', 'string', 'in:right,left'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'coach_id'         => ['nullable', 'integer', 'exists:coaches,id'],
            'is_active'        => ['boolean'],
        ]);

        Archer::create($data);

        return redirect()->route('admin.archers.index')
            ->with('success', 'Archer created successfully.');
    }

    /**
     * GET /admin/archers/{archer}/edit
     */
    public function edit(Archer $archer): Response
    {
        $coaches = Coach::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Archers/Edit', [
            'archer'            => [
                'id'               => $archer->id,
                'name'             => $archer->name,
                'gender'           => $archer->gender,
                'bow_type'         => $archer->bow_type,
                'experience_level' => $archer->experience_level,
                'category'         => $archer->category,
                'date_of_birth'    => $archer->date_of_birth?->format('Y-m-d'),
                'dominant_hand'    => $archer->dominant_hand,
                'phone'            => $archer->phone,
                'coach_id'         => $archer->coach_id,
                'is_active'        => $archer->is_active,
            ],
            'coaches'           => $coaches,
            'bowTypes'          => ['Recurve', 'Compound', 'Barebow', 'Longbow'],
            'experienceLevels'  => ['Beginner', 'Intermediate', 'Advanced', 'Elite'],
            'categories'        => ['U12', 'U15', 'U18', 'U21', 'Senior', 'Master'],
        ]);
    }

    /**
     * PUT /admin/archers/{archer}
     */
    public function update(Request $request, Archer $archer)
    {
        $data = $request->validate([
            'name'             => ['required', 'string', 'max:255'],
            'gender'           => ['nullable', 'string', 'in:male,female,other'],
            'bow_type'         => ['nullable', 'string', 'in:Recurve,Compound,Barebow,Longbow'],
            'experience_level' => ['nullable', 'string', 'in:Beginner,Intermediate,Advanced,Elite'],
            'category'         => ['nullable', 'string', 'in:U12,U15,U18,U21,Senior,Master'],
            'date_of_birth'    => ['nullable', 'date', 'before:today'],
            'dominant_hand'    => ['nullable', 'string', 'in:right,left'],
            'phone'            => ['nullable', 'string', 'max:30'],
            'coach_id'         => ['nullable', 'integer', 'exists:coaches,id'],
            'is_active'        => ['boolean'],
        ]);

        $archer->update($data);

        return redirect()->route('admin.archers.index')
            ->with('success', 'Archer updated successfully.');
    }

    /**
     * DELETE /admin/archers/{archer}
     */
    public function destroy(Archer $archer)
    {
        $archer->delete();

        return redirect()->route('admin.archers.index')
            ->with('success', 'Archer removed.');
    }

    /**
     * GET /admin/archers/{archer}  — admin view of a specific archer's profile
     */
    public function show(Archer $archer): Response
    {
        $archer->load(['coach', 'currentEquipment', 'coachNotes.coach']);

        $recentSessions = $archer->trainingSessions()
            ->with('liveSession')
            ->orderByDesc('started_at')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'round_type'      => $s->round_type,
                'distance_metres' => $s->distance_metres,
                'started_at'      => $s->started_at,
                'total_score'     => $s->liveSession?->total_score,
                'max_score'       => $s->max_score,
            ]);

        $competitionResults = $archer->competitionResults()
            ->with('competition')
            ->orderByDesc('competed_at')
            ->limit(5)
            ->get()
            ->map(fn ($r) => [
                'competition_name' => $r->competition?->name,
                'score'            => $r->score,
                'max_score'        => $r->max_score,
                'placing'          => $r->placing,
                'competed_at'      => $r->competed_at,
            ]);

        return Inertia::render('Archers/Show', [
            'archer' => [
                'id'               => $archer->id,
                'name'             => $archer->name ?? $archer->user?->name ?? "Archer #{$archer->id}",
                'gender'           => $archer->gender,
                'bow_type'         => $archer->bow_type,
                'experience_level' => $archer->experience_level,
                'category'         => $archer->category,
                'age'              => $archer->age,
                'dominant_hand'    => $archer->dominant_hand,
                'phone'            => $archer->phone,
                'is_active'        => $archer->is_active,
                'coach_name'       => $archer->coach?->name,
                'equipment'        => $archer->currentEquipment ? [
                    'bow_type'        => $archer->currentEquipment->bow_type,
                    'bow_brand'       => $archer->currentEquipment->bow_brand,
                    'bow_model'       => $archer->currentEquipment->bow_model,
                    'draw_weight_lbs' => $archer->currentEquipment->draw_weight_lbs,
                ] : null,
                'coach_notes'      => $archer->coachNotes->map(fn ($n) => [
                    'id'         => $n->id,
                    'note'       => $n->note,
                    'coach_name' => $n->coach?->name,
                    'created_at' => $n->created_at,
                ]),
            ],
            'recentSessions'     => $recentSessions,
            'competitionResults' => $competitionResults,
        ]);
    }
}

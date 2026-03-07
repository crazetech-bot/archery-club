<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Competition;
use App\Models\CompetitionResult;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    /**
     * GET /admin/competitions
     */
    public function index(): Response
    {
        $competitions = Competition::withCount('results')
            ->orderByDesc('date')
            ->get()
            ->map(fn (Competition $c) => [
                'id'              => $c->id,
                'name'            => $c->name,
                'date'            => $c->date,
                'location'        => $c->location,
                'round_type'      => $c->round_type,
                'distance_metres' => $c->distance_metres,
                'results_count'   => $c->results_count,
                'is_upcoming'     => $c->date >= today()->toDateString(),
            ]);

        return Inertia::render('Admin/Competitions', [
            'competitions' => $competitions,
        ]);
    }

    /**
     * GET /admin/competitions/{competition}
     */
    public function show(Competition $competition): Response
    {
        $results = CompetitionResult::with('archer')
            ->where('competition_id', $competition->id)
            ->orderBy('placing')
            ->get()
            ->map(fn (CompetitionResult $r) => [
                'id'           => $r->id,
                'archer_id'    => $r->archer_id,
                'archer_name'  => $r->archer?->user?->name ?? "Archer #{$r->archer_id}",
                'category'     => $r->category,
                'score'        => $r->score,
                'max_score'    => $r->max_score,
                'placing'      => $r->placing,
                'notes'        => $r->notes,
            ]);

        $archers = Archer::with('user')->get()->map(fn (Archer $a) => [
            'id'       => $a->id,
            'name'     => $a->user?->name ?? "Archer #{$a->id}",
            'category' => $a->category,
        ]);

        return Inertia::render('Admin/CompetitionDetail', [
            'competition' => [
                'id'              => $competition->id,
                'name'            => $competition->name,
                'date'            => $competition->date,
                'location'        => $competition->location,
                'round_type'      => $competition->round_type,
                'distance_metres' => $competition->distance_metres,
            ],
            'results' => $results,
            'archers' => $archers,
        ]);
    }

    /**
     * POST /admin/competitions
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'date'            => ['required', 'date'],
            'location'        => ['nullable', 'string', 'max:255'],
            'round_type'      => ['nullable', 'string', 'max:100'],
            'distance_metres' => ['nullable', 'integer', 'min:1'],
        ]);

        Competition::create($data);

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Competition created.');
    }

    /**
     * PUT /admin/competitions/{competition}
     */
    public function update(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'date'            => ['required', 'date'],
            'location'        => ['nullable', 'string', 'max:255'],
            'round_type'      => ['nullable', 'string', 'max:100'],
            'distance_metres' => ['nullable', 'integer', 'min:1'],
        ]);

        $competition->update($data);

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Competition updated.');
    }

    /**
     * DELETE /admin/competitions/{competition}
     */
    public function destroy(Competition $competition)
    {
        $competition->results()->delete();
        $competition->delete();

        return redirect()->route('admin.competitions.index')
            ->with('success', 'Competition deleted.');
    }

    /**
     * POST /admin/competitions/{competition}/results
     */
    public function storeResult(Request $request, Competition $competition)
    {
        $data = $request->validate([
            'archer_id' => ['required', 'integer', 'exists:archers,id'],
            'category'  => ['nullable', 'string', 'max:50'],
            'score'     => ['required', 'integer', 'min:0'],
            'max_score' => ['nullable', 'integer', 'min:1'],
            'placing'   => ['nullable', 'integer', 'min:1'],
            'notes'     => ['nullable', 'string', 'max:1000'],
        ]);

        $data['competition_id'] = $competition->id;
        $data['competed_at']    = $competition->date;

        CompetitionResult::updateOrCreate(
            ['competition_id' => $competition->id, 'archer_id' => $data['archer_id']],
            $data
        );

        return redirect()->back()->with('success', 'Result saved.');
    }
}

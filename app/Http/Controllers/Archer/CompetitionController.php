<?php

namespace App\Http\Controllers\Archer;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\CompetitionResult;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CompetitionController extends Controller
{
    /**
     * GET /archer/competitions
     */
    public function index(): Response
    {
        $archer = Archer::where('user_id', Auth::id())->firstOrFail();

        $results = CompetitionResult::with('competition')
            ->where('archer_id', $archer->id)
            ->orderByDesc('competed_at')
            ->get()
            ->map(fn (CompetitionResult $r) => [
                'id'               => $r->id,
                'competition_name' => $r->competition?->name ?? 'Unknown Competition',
                'round_type'       => $r->competition?->round_type ?? $r->round_type,
                'distance_metres'  => $r->competition?->distance_metres,
                'category'         => $r->category,
                'score'            => $r->score,
                'max_score'        => $r->max_score,
                'placing'          => $r->placing,
                'competed_at'      => $r->competed_at,
                'notes'            => $r->notes,
            ]);

        return Inertia::render('Archer/Competitions', [
            'results' => $results,
        ]);
    }
}

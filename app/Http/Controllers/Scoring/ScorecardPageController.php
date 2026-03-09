<?php

namespace App\Http\Controllers\Scoring;

use App\Http\Controllers\Controller;
use App\Models\Archers\Archer;
use App\Models\Scoring\Scorecard;
use App\Models\Training\ScoringTemplate;
use App\Models\Training\TrainingSession;
use Inertia\Inertia;

class ScorecardPageController extends Controller
{
    public function index()
    {
        $scorecards = Scorecard::with(['archer.user', 'session', 'template'])
            ->latest()
            ->paginate(20);

        return Inertia::render('Scoring/ScorecardList', [
            'scorecards' => $scorecards,
        ]);
    }

    public function create()
    {
        return Inertia::render('Scoring/ScorecardCreate', [
            'sessions'  => TrainingSession::select('id', 'title', 'start_time')
                ->latest()
                ->get(),
            'archers'   => Archer::with('user:id,name')
                ->get()
                ->map(fn ($a) => [
                    'id'   => $a->id,
                    'name' => $a->user->name ?? '—',
                ]),
            'templates' => ScoringTemplate::select('id', 'name', 'type', 'config')
                ->get(),
        ]);
    }

    public function show(Scorecard $scorecard)
    {
        return Inertia::render('Scoring/ScorecardView', [
            'scorecard' => $scorecard->load([
                'archer.user',
                'session',
                'template',
                'ends.shots',
                'metrics',
            ]),
        ]);
    }
}

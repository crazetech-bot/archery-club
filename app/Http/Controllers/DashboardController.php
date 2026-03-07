<?php

namespace App\Http\Controllers;

use App\Models\Archer;
use App\Models\Coach;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\TrainingSession;
use App\Models\LiveSession;
use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Archer dashboard — /archer/dashboard
     */
    public function archer(Request $request): Response
    {
        $user   = Auth::user();
        $archer = Archer::with(['currentEquipment', 'coach'])->where('user_id', $user->id)->firstOrFail();

        // ── Recent sessions (last 8) ──────────────────────────────────────────
        $recentSessions = TrainingSession::with(['liveSession.ends'])
            ->where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->orderByDesc('started_at')
            ->limit(8)
            ->get()
            ->map(fn ($s) => [
                'id'              => $s->id,
                'round_type'      => $s->round_type,
                'distance_metres' => $s->distance_metres,
                'started_at'      => $s->started_at,
                'ended_at'        => $s->ended_at,
                'total_score'     => $s->liveSession?->total_score ?? 0,
                'max_score'       => $s->max_score,
                'x_count'         => $s->liveSession?->x_count ?? 0,
                'ten_count'       => $s->liveSession?->ten_count ?? 0,
                'ends_count'      => $s->liveSession?->ends->count() ?? 0,
            ]);

        // ── Aggregate stats ───────────────────────────────────────────────────
        $sessions = TrainingSession::with('liveSession')
            ->where('archer_id', $archer->id)
            ->whereNotNull('ended_at')
            ->get();

        $scores  = $sessions->pluck('liveSession.total_score')->filter();
        $xCounts = $sessions->pluck('liveSession.x_count')->filter();

        $stats = [
            'total_sessions'     => $sessions->count(),
            'avg_score'          => $scores->count() ? round($scores->avg(), 1) : null,
            'best_score'         => $scores->max(),
            'total_x_count'      => $xCounts->sum(),
            'sessions_this_week' => $sessions->filter(
                fn ($s) => $s->started_at >= now()->startOfWeek()
            )->count(),
        ];

        // ── Upcoming bookings (next 3) ────────────────────────────────────────
        $upcomingBookings = LaneBooking::with('lane')
            ->where('archer_id', $archer->id)
            ->where('start_time', '>', now())
            ->orderBy('start_time')
            ->limit(3)
            ->get()
            ->map(fn ($b) => [
                'id'      => $b->id,
                'lane'    => ['name' => $b->lane?->name, 'number' => $b->lane?->number],
                'start_time' => $b->start_time,
                'end_time'   => $b->end_time,
                'purpose'    => $b->purpose,
            ]);

        // ── Current equipment ─────────────────────────────────────────────────
        $eq = $archer->currentEquipment;

        return Inertia::render('Dashboard/ArcherDashboard', [
            'archer'           => [
                'id'           => $archer->id,
                'name'         => $user->name,
                'category'     => $archer->category,
                'age'          => $archer->age,
                'dominant_hand'=> $archer->dominant_hand,
                'coach_name'   => $archer->coach?->user?->name,
            ],
            'stats'            => $stats,
            'recentSessions'   => $recentSessions,
            'upcomingBookings' => $upcomingBookings,
            'currentEquipment' => $eq ? [
                'id'                 => $eq->id,
                'name'               => $eq->name,
                'bow_type'           => $eq->bow_type,
                'bow_brand'          => $eq->bow_brand,
                'bow_model'          => $eq->bow_model,
                'draw_weight_lbs'    => $eq->draw_weight_lbs,
                'draw_length_inches' => $eq->draw_length_inches,
                'arrow_brand'        => $eq->arrow_brand,
                'arrow_model'        => $eq->arrow_model,
                'arrow_spine'        => $eq->arrow_spine,
            ] : null,
        ]);
    }

    /**
     * Coach dashboard — /coach/dashboard
     */
    public function coach(Request $request): Response
    {
        $user  = Auth::user();
        $coach = Coach::where('user_id', $user->id)->firstOrFail();

        $archers = Archer::where('coach_id', $coach->id)
            ->with(['trainingSessions' => fn ($q) => $q->orderByDesc('started_at')->limit(1)])
            ->get()
            ->map(function ($archer) {
                $recentSessions = TrainingSession::with('liveSession')
                    ->where('archer_id', $archer->id)
                    ->whereNotNull('ended_at')
                    ->orderByDesc('started_at')
                    ->limit(6)
                    ->get();

                $scores = $recentSessions->pluck('liveSession.total_score')->filter()->values();

                $improvementRate = null;
                if ($scores->count() >= 6) {
                    $firstAvg = $scores->take(3)->avg();
                    $lastAvg  = $scores->slice(-3)->avg();
                    if ($firstAvg > 0) {
                        $improvementRate = round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1);
                    }
                }

                $hasActive = LiveSession::whereHas('trainingSession', fn ($q) => $q->where('archer_id', $archer->id))
                    ->where('status', 'active')
                    ->exists();

                return [
                    'id'                  => $archer->id,
                    'name'                => $archer->user?->name ?? "Archer #{$archer->id}",
                    'category'            => $archer->category,
                    'avg_score'           => $scores->count() ? round($scores->avg(), 1) : null,
                    'improvement_rate'    => $improvementRate,
                    'last_session_date'   => $recentSessions->first()?->started_at,
                    'has_active_session'  => $hasActive,
                ];
            });

        $stats = [
            'sessions_this_week' => TrainingSession::whereHas(
                    'archer', fn ($q) => $q->where('coach_id', $coach->id)
                )
                ->where('started_at', '>=', now()->startOfWeek())
                ->count(),
            'group_avg_score' => $this->coachGroupAvg($coach->id),
        ];

        $recentActivity = $this->buildCoachActivity($coach->id);

        return Inertia::render('Dashboard/CoachDashboard', [
            'coach'          => [
                'id'    => $coach->id,
                'name'  => $user->name,
                'level' => $coach->level ?? 1,
            ],
            'archers'        => $archers,
            'stats'          => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Admin dashboard — /admin/dashboard
     */
    public function admin(Request $request): Response
    {
        $tenant = tenant();

        // ── Club stats ─────────────────────────────────────────────────────────
        $totalArchers  = Archer::count();
        $totalCoaches  = Coach::count();
        $activeSessions = LiveSession::where('status', 'active')->count();

        $newThisMonth = Archer::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count()
                      + Coach::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $newLastMonth = Archer::whereMonth('created_at', now()->subMonthNoOverflow()->month)->whereYear('created_at', now()->subMonthNoOverflow()->year)->count()
                      + Coach::whereMonth('created_at', now()->subMonthNoOverflow()->month)->whereYear('created_at', now()->subMonthNoOverflow()->year)->count();

        $clubStats = [
            'total_members'       => $totalArchers + $totalCoaches,
            'members_delta'       => $newThisMonth - $newLastMonth,
            'sessions_this_month' => TrainingSession::whereMonth('started_at', now()->month)
                                        ->whereYear('started_at', now()->year)->count(),
            'active_sessions'     => $activeSessions,
            'bookings_today'      => LaneBooking::whereDate('start_time', today())->count(),
        ];

        // ── Recent members (archers ordered by join date) ──────────────────────
        $recentMembers = Archer::with('user')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get()
            ->map(function ($archer) {
                return [
                    'id'            => $archer->id,
                    'name'          => $archer->user?->name ?? "Archer #{$archer->id}",
                    'email'         => $archer->user?->email ?? '',
                    'role'          => 'archer',
                    'session_count' => TrainingSession::where('archer_id', $archer->id)->count(),
                    'last_active'   => TrainingSession::where('archer_id', $archer->id)
                                          ->orderByDesc('started_at')->value('started_at'),
                ];
            });

        // ── Lane status grid ───────────────────────────────────────────────────
        $laneStatus = Lane::orderBy('number')
            ->get()
            ->map(function ($lane) {
                $current = LaneBooking::where('lane_id', $lane->id)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->with('archer')
                    ->first();

                return [
                    'id'             => $lane->id,
                    'name'           => $lane->name,
                    'is_occupied'    => (bool) $current,
                    'current_archer' => $current ? ($current->archer?->user?->name ?? 'Unknown') : null,
                    'bookings_today' => LaneBooking::where('lane_id', $lane->id)
                        ->whereDate('start_time', today())
                        ->count(),
                ];
            });

        // ── Upcoming competitions (next 3) ─────────────────────────────────────
        $upcomingCompetitions = Competition::where('date', '>=', today())
            ->orderBy('date')
            ->limit(3)
            ->get()
            ->map(fn ($c) => [
                'id'       => $c->id,
                'name'     => $c->name,
                'date'     => $c->date,
                'location' => $c->location,
                'level'    => $c->level,
            ]);

        return Inertia::render('Dashboard/AdminDashboard', [
            'tenant'               => ['name' => $tenant?->name ?? 'Club'],
            'subscription'         => $this->tenantSubscription(),
            'clubStats'            => $clubStats,
            'recentMembers'        => $recentMembers,
            'laneStatus'           => $laneStatus,
            'upcomingCompetitions' => $upcomingCompetitions,
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function coachGroupAvg(int $coachId): ?float
    {
        $scores = TrainingSession::with('liveSession')
            ->whereHas('archer', fn ($q) => $q->where('coach_id', $coachId))
            ->whereNotNull('ended_at')
            ->get()
            ->pluck('liveSession.total_score')
            ->filter();

        return $scores->count() ? round($scores->avg(), 1) : null;
    }

    private function buildCoachActivity(int $coachId): array
    {
        return TrainingSession::with(['archer', 'liveSession'])
            ->whereHas('archer', fn ($q) => $q->where('coach_id', $coachId))
            ->orderByDesc('started_at')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'id'          => $s->id,
                'archer_name' => $s->archer?->user?->name ?? "Archer #{$s->archer_id}",
                'type'        => $s->ended_at ? 'session_completed' : 'session_started',
                'description' => $s->ended_at
                    ? "Completed session" . ($s->liveSession?->total_score ? " · Score: {$s->liveSession->total_score}" : '')
                    : 'Started a training session',
                'occurred_at' => $s->ended_at ?? $s->started_at,
            ])
            ->toArray();
    }

    private function tenantSubscription(): array
    {
        $tenant = tenant();
        return [
            'plan'      => $tenant?->plan ?? 'free',
            'status'    => $tenant?->status ?? 'active',
            'renews_at' => $tenant?->renews_at ?? null,
        ];
    }
}

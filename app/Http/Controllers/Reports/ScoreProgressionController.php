<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ScoreProgressionRequest;
use App\Http\Resources\Reports\ScoreProgressionResource;
use App\Models\Archer;
use App\Services\Reports\ScoreProgressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class ScoreProgressionController extends Controller
{
    public function __construct(
        private readonly ScoreProgressionService $service
    ) {}

    // -------------------------------------------------------------------------
    // GET /reports/archer/{archer}/score-progression
    // -------------------------------------------------------------------------

    /**
     * Render the Inertia report page.
     *
     * Accessible by:
     *   - club_admin  : any archer in the tenant
     *   - coach       : only archers assigned to them
     *   - archer      : only themselves
     */
    public function show(Request $request, Archer $archer): InertiaResponse
    {
        $this->authoriseAccess($request, $archer);

        // Build filter defaults for the initial page load (no filters applied yet)
        $filters = [
            'archer_id'  => $archer->id,
            'date_from'  => now()->subMonths(3)->toDateString(),
            'date_to'    => now()->toDateString(),
            'round_type' => null,
            'distance'   => null,
            'group_by'   => null,
        ];

        $report = $this->service->generate($filters);

        return Inertia::render('Reports/ScoreProgression', [
            'report'     => new ScoreProgressionResource($report),
            'archer'     => $archer->only('id', 'name', 'category'),
            // Pass available round types for the filter dropdown
            'roundTypes' => $this->availableRoundTypes($archer),
        ]);
    }

    // -------------------------------------------------------------------------
    // GET /reports/archer/{archer}/score-progression/data  (JSON / API)
    // -------------------------------------------------------------------------

    /**
     * Return filtered report data as JSON.
     * Called by the Vue component when the user changes filters.
     */
    public function data(ScoreProgressionRequest $request, Archer $archer): JsonResponse
    {
        $this->authoriseAccess($request, $archer);

        // Merge the validated archer_id from the route model with query filters
        $filters = array_merge(
            $request->filters(),
            ['archer_id' => $archer->id]
        );

        $report = $this->service->generate($filters);

        return (new ScoreProgressionResource($report))
            ->response()
            ->setStatusCode(200);
    }

    // -------------------------------------------------------------------------
    // Authorisation
    // -------------------------------------------------------------------------

    /**
     * Gate check based on the authenticated user's role in this tenant.
     *
     * - club_admin : unrestricted
     * - coach      : only their assigned archers (sessions with coach_id matching)
     * - archer     : only their own profile
     */
    private function authoriseAccess(Request $request, Archer $archer): void
    {
        $user = $request->user();

        // Super admins always pass
        if ($user->is_super_admin) {
            return;
        }

        if ($user->hasRole('club_admin')) {
            return;
        }

        if ($user->hasRole('coach')) {
            // Coach may only view archers they have coached in at least one session
            $coachHasSession = $archer
                ->trainingSessions()
                ->whereNotNull('coach_id')
                ->whereHas('coach', fn ($q) => $q->where('user_id', $user->id))
                ->exists();

            abort_unless($coachHasSession, 403, 'You are not assigned to this archer.');
            return;
        }

        if ($user->hasRole('archer')) {
            // Archer may only view their own record
            abort_unless(
                $archer->user_id === $user->id,
                403,
                'You may only view your own progression.'
            );
            return;
        }

        abort(403);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Return distinct round types used by this archer, for the filter dropdown.
     */
    private function availableRoundTypes(Archer $archer): array
    {
        return $archer
            ->trainingSessions()
            ->whereNotNull('round_type')
            ->distinct()
            ->pluck('round_type')
            ->sort()
            ->values()
            ->all();
    }
}

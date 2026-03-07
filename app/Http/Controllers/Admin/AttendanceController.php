<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Attendance;
use App\Models\Coach;
use App\Models\GroupSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    /**
     * GET /admin/sessions
     * List all group sessions with attendance summary.
     */
    public function index(): Response
    {
        $sessions = GroupSession::with('coach')
            ->withCount('attendances')
            ->orderByDesc('scheduled_at')
            ->paginate(20)
            ->through(fn (GroupSession $s) => [
                'id'               => $s->id,
                'title'            => $s->title,
                'type'             => $s->type,
                'scheduled_at'     => $s->scheduled_at,
                'duration_minutes' => $s->duration_minutes,
                'location'         => $s->location,
                'status'           => $s->status,
                'coach_name'       => $s->coach?->user?->name,
                'attendances_count'=> $s->attendances_count,
                'present_count'    => $s->attendances()->where('status', 'present')->count(),
            ]);

        $coaches = Coach::with('user')->get()->map(fn ($c) => [
            'id'   => $c->id,
            'name' => $c->user?->name ?? "Coach #{$c->id}",
        ]);

        return Inertia::render('Sessions/Index', [
            'sessions' => $sessions,
            'coaches'  => $coaches,
        ]);
    }

    /**
     * POST /admin/sessions
     * Create a new group session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['required', 'string', 'max:255'],
            'type'             => ['required', Rule::in(['technique', 'fitness', 'competition_prep', 'beginner', 'general'])],
            'scheduled_at'     => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'location'         => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:2000'],
            'coach_id'         => ['nullable', 'exists:coaches,id'],
        ]);

        GroupSession::create($validated);

        return back()->with('success', 'Session created.');
    }

    /**
     * PATCH /admin/sessions/{session}
     * Update a group session's details or status.
     */
    public function update(Request $request, GroupSession $session): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => ['sometimes', 'string', 'max:255'],
            'type'             => ['sometimes', Rule::in(['technique', 'fitness', 'competition_prep', 'beginner', 'general'])],
            'scheduled_at'     => ['sometimes', 'date'],
            'duration_minutes' => ['sometimes', 'integer', 'min:15', 'max:480'],
            'location'         => ['nullable', 'string', 'max:255'],
            'notes'            => ['nullable', 'string', 'max:2000'],
            'coach_id'         => ['nullable', 'exists:coaches,id'],
            'status'           => ['sometimes', Rule::in(['scheduled', 'completed', 'cancelled'])],
        ]);

        $session->update($validated);

        return back()->with('success', 'Session updated.');
    }

    /**
     * DELETE /admin/sessions/{session}
     */
    public function destroy(GroupSession $session): RedirectResponse
    {
        $session->delete();

        return redirect()->route('admin.sessions.index')->with('success', 'Session deleted.');
    }

    /**
     * GET /admin/sessions/{session}/attendance
     * Show the attendance marking page for a group session.
     */
    public function show(GroupSession $session): Response
    {
        $session->load('coach');

        // Get all archers with their existing attendance record for this session
        $archers = Archer::with('user')
            ->orderBy('id')
            ->get()
            ->map(function (Archer $archer) use ($session) {
                $attendance = $session->attendances->firstWhere('archer_id', $archer->id);
                return [
                    'id'          => $archer->id,
                    'name'        => $archer->user?->name ?? "Archer #{$archer->id}",
                    'category'    => $archer->category,
                    'status'      => $attendance?->status ?? null,
                    'notes'       => $attendance?->notes ?? null,
                    'marked_at'   => $attendance?->marked_at,
                ];
            });

        $session->load('attendances');

        return Inertia::render('Sessions/Attendance/Mark', [
            'session' => [
                'id'               => $session->id,
                'title'            => $session->title,
                'type'             => $session->type,
                'scheduled_at'     => $session->scheduled_at,
                'duration_minutes' => $session->duration_minutes,
                'location'         => $session->location,
                'status'           => $session->status,
                'coach_name'       => $session->coach?->user?->name,
            ],
            'archers' => $archers,
        ]);
    }

    /**
     * POST /admin/sessions/{session}/attendance
     * Bulk-upsert attendance for all archers in a session.
     * Expects: { records: [{ archer_id, status, notes }] }
     */
    public function mark(Request $request, GroupSession $session): RedirectResponse
    {
        $request->validate([
            'records'                => ['required', 'array'],
            'records.*.archer_id'    => ['required', 'exists:archers,id'],
            'records.*.status'       => ['required', Rule::in(['present', 'absent', 'late', 'excused'])],
            'records.*.notes'        => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($request->records as $record) {
            Attendance::updateOrCreate(
                [
                    'group_session_id' => $session->id,
                    'archer_id'        => $record['archer_id'],
                ],
                [
                    'status'    => $record['status'],
                    'notes'     => $record['notes'] ?? null,
                    'marked_at' => now(),
                ]
            );
        }

        // Mark session as completed if it was just scheduled
        if ($session->status === 'scheduled') {
            $session->update(['status' => 'completed']);
        }

        return back()->with('success', 'Attendance saved.');
    }
}

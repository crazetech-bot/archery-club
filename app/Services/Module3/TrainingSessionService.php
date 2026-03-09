<?php

namespace App\Services\Module3;

use App\Models\Training\TrainingSession;
use Illuminate\Support\Facades\DB;

class TrainingSessionService
{
    public function listSessions()
    {
        return TrainingSession::with(['coaches.user', 'archers.user'])
            ->orderBy('start_time', 'desc')
            ->paginate(20);
    }

    public function createSession(array $data): TrainingSession
    {
        return DB::transaction(function () use ($data) {
            return TrainingSession::create([
                'club_id'     => $data['club_id'],
                'title'       => $data['title'],
                'description' => $data['description'] ?? null,
                'start_time'  => $data['start_time'],
                'end_time'    => $data['end_time'] ?? null,
                'status'      => $data['status'] ?? 'scheduled',
            ]);
        });
    }

    public function getSession(TrainingSession $session): TrainingSession
    {
        return $session->load([
            'archers.user',
            'coaches.user',
            'notes.coach.user',
            'notes.archer.user',
        ]);
    }

    public function updateSession(TrainingSession $session, array $data): TrainingSession
    {
        return DB::transaction(function () use ($session, $data) {
            $session->update([
                'title'       => $data['title'] ?? $session->title,
                'description' => $data['description'] ?? $session->description,
                'start_time'  => $data['start_time'] ?? $session->start_time,
                'end_time'    => $data['end_time'] ?? $session->end_time,
                'status'      => $data['status'] ?? $session->status,
            ]);

            return $session;
        });
    }

    public function deleteSession(TrainingSession $session): bool
    {
        return DB::transaction(function () use ($session) {
            $session->archers()->detach();
            $session->coaches()->detach();
            $session->notes()->delete();

            return $session->delete();
        });
    }
}

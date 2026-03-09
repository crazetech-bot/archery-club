<?php

namespace App\Services\Module3;

use App\Models\Training\TrainingSession;
use App\Models\Training\TrainingSessionNote;
use Illuminate\Support\Facades\DB;

class TrainingSessionNoteService
{
    public function listNotes(TrainingSession $session)
    {
        return $session->notes()
            ->with(['coach.user', 'archer.user'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function createNote(TrainingSession $session, array $data): TrainingSessionNote
    {
        return DB::transaction(function () use ($session, $data) {
            return $session->notes()->create([
                'coach_id'  => $data['coach_id'] ?? null,
                'archer_id' => $data['archer_id'] ?? null,
                'note'      => $data['note'],
            ]);
        });
    }

    public function updateNote(TrainingSession $session, TrainingSessionNote $note, array $data): TrainingSessionNote
    {
        return DB::transaction(function () use ($note, $data) {
            $note->update([
                'note' => $data['note'] ?? $note->note,
            ]);

            return $note;
        });
    }

    public function deleteNote(TrainingSession $session, TrainingSessionNote $note): bool
    {
        return DB::transaction(function () use ($note) {
            return $note->delete();
        });
    }
}

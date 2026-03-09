<?php

namespace App\Services\Module3;

use App\Models\Training\TrainingSession;
use App\Models\Archers\Archer;
use Illuminate\Support\Facades\DB;

class TrainingSessionArcherService
{
    public function addArcher(TrainingSession $session, Archer $archer)
    {
        return DB::transaction(function () use ($session, $archer) {
            $session->archers()->syncWithoutDetaching([
                $archer->id => ['attendance_status' => 'pending'],
            ]);

            return $session->load('archers.user');
        });
    }

    public function removeArcher(TrainingSession $session, Archer $archer)
    {
        return DB::transaction(function () use ($session, $archer) {
            $session->archers()->detach($archer->id);

            return $session->load('archers.user');
        });
    }

    public function updateAttendance(TrainingSession $session, Archer $archer, string $status)
    {
        return DB::transaction(function () use ($session, $archer, $status) {
            $session->archers()->updateExistingPivot($archer->id, [
                'attendance_status' => $status,
            ]);

            return $session->load('archers.user');
        });
    }

    public function listArchers(TrainingSession $session)
    {
        return $session->archers()->with('user')->get();
    }
}

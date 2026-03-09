<?php

namespace App\Services\Module2;

use App\Models\Coaches\Coach;
use Illuminate\Support\Facades\DB;

class CoachService
{
    public function listCoaches()
    {
        return Coach::with('user')->paginate(20);
    }

    public function createCoach(array $data): Coach
    {
        return DB::transaction(function () use ($data) {
            return Coach::create([
                'user_id'             => $data['user_id'],
                'certification_level' => $data['certification_level'] ?? null,
                'bio'                 => $data['bio'] ?? null,
            ]);
        });
    }

    public function getCoach(Coach $coach): Coach
    {
        return $coach->load(['user', 'archers']);
    }

    public function updateCoach(Coach $coach, array $data): Coach
    {
        return DB::transaction(function () use ($coach, $data) {
            $coach->update([
                'certification_level' => $data['certification_level'] ?? $coach->certification_level,
                'bio'                 => $data['bio'] ?? $coach->bio,
            ]);

            return $coach;
        });
    }

    public function deleteCoach(Coach $coach): bool
    {
        return DB::transaction(function () use ($coach) {
            $coach->archers()->detach();

            return $coach->delete();
        });
    }

    public function listArchers(Coach $coach)
    {
        return $coach->archers()->with('user')->get();
    }
}

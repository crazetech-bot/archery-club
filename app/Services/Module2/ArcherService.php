<?php

namespace App\Services\Module2;

use App\Models\Archers\Archer;
use App\Models\Coaches\Coach;
use Illuminate\Support\Facades\DB;

class ArcherService
{
    public function listArchers()
    {
        return Archer::with('user')->paginate(20);
    }

    public function createArcher(array $data): Archer
    {
        return DB::transaction(function () use ($data) {
            return Archer::create([
                'user_id'       => $data['user_id'],
                'bow_type'      => $data['bow_type'],
                'dominant_eye'  => $data['dominant_eye'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'notes'         => $data['notes'] ?? null,
            ]);
        });
    }

    public function getArcher(Archer $archer): Archer
    {
        return $archer->load(['user', 'coaches']);
    }

    public function updateArcher(Archer $archer, array $data): Archer
    {
        return DB::transaction(function () use ($archer, $data) {
            $archer->update([
                'bow_type'      => $data['bow_type'],
                'dominant_eye'  => $data['dominant_eye'] ?? $archer->dominant_eye,
                'date_of_birth' => $data['date_of_birth'] ?? $archer->date_of_birth,
                'notes'         => $data['notes'] ?? $archer->notes,
            ]);

            return $archer;
        });
    }

    public function deleteArcher(Archer $archer): bool
    {
        return DB::transaction(function () use ($archer) {
            $archer->coaches()->detach();

            return $archer->delete();
        });
    }

    public function assignCoach(Archer $archer, Coach $coach)
    {
        return DB::transaction(function () use ($archer, $coach) {
            $archer->coaches()->syncWithoutDetaching([
                $coach->id => [
                    'relationship_type' => 'primary',
                    'started_at'        => now(),
                ],
            ]);

            return $archer->load('coaches');
        });
    }

    public function removeCoach(Archer $archer, Coach $coach)
    {
        return DB::transaction(function () use ($archer, $coach) {
            $archer->coaches()->detach($coach->id);

            return $archer->load('coaches');
        });
    }

    public function listCoaches(Archer $archer)
    {
        return $archer->coaches()->with('user')->get();
    }
}

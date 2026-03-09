<?php

namespace App\Models\Archers;

use App\Models\Core\User;
use App\Models\Coaches\Coach;
use Illuminate\Database\Eloquent\Model;

class Archer extends Model
{
    protected $table = 'archers';

    protected $fillable = [
        'user_id',
        'bow_type',
        'dominant_eye',
        'date_of_birth',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    /**
     * Archer belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Archer may have multiple coaches.
     */
    public function coaches()
    {
        return $this->belongsToMany(Coach::class, 'archer_coach')
            ->using(ArcherCoach::class)
            ->withPivot(['relationship_type', 'started_at', 'ended_at'])
            ->withTimestamps();
    }
}

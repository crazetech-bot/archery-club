<?php

namespace App\Models\Archers;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ArcherCoach extends Pivot
{
    protected $table = 'archer_coach';

    protected $fillable = [
        'archer_id',
        'coach_id',
        'relationship_type',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'date',
        'ended_at'   => 'date',
    ];
}

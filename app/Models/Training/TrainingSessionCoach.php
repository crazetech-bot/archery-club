<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrainingSessionCoach extends Pivot
{
    protected $table = 'training_session_coaches';

    protected $fillable = [
        'training_session_id',
        'coach_id',
    ];
}

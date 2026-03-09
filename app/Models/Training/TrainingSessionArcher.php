<?php

namespace App\Models\Training;

use Illuminate\Database\Eloquent\Relations\Pivot;

class TrainingSessionArcher extends Pivot
{
    protected $table = 'training_session_archers';

    protected $fillable = [
        'training_session_id',
        'archer_id',
        'attendance_status',
    ];

    protected $casts = [
        'attendance_status' => 'string',
    ];
}

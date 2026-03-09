<?php

namespace App\Models\Training;

use App\Models\Core\Club;
use App\Models\Archers\Archer;
use App\Models\Coaches\Coach;
use Illuminate\Database\Eloquent\Model;

class TrainingSession extends Model
{
    protected $table = 'training_sessions';

    protected $fillable = [
        'club_id',
        'title',
        'description',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function archers()
    {
        return $this->belongsToMany(Archer::class, 'training_session_archers')
            ->using(TrainingSessionArcher::class)
            ->withPivot(['attendance_status'])
            ->withTimestamps();
    }

    public function coaches()
    {
        return $this->belongsToMany(Coach::class, 'training_session_coaches')
            ->using(TrainingSessionCoach::class)
            ->withTimestamps();
    }

    public function notes()
    {
        return $this->hasMany(TrainingSessionNote::class);
    }
}

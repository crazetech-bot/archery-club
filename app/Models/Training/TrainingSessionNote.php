<?php

namespace App\Models\Training;

use App\Models\Archers\Archer;
use App\Models\Coaches\Coach;
use Illuminate\Database\Eloquent\Model;

class TrainingSessionNote extends Model
{
    protected $table = 'training_session_notes';

    protected $fillable = [
        'training_session_id',
        'coach_id',
        'archer_id',
        'note',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function coach()
    {
        return $this->belongsTo(Coach::class);
    }

    public function archer()
    {
        return $this->belongsTo(Archer::class);
    }
}

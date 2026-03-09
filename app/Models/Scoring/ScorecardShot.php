<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class ScorecardShot extends Model
{
    protected $table = 'scorecard_shots';

    protected $fillable = [
        'scorecard_id',
        'scorecard_end_id',
        'shot_number',
        'score',
        'is_x',
        'is_miss',
    ];

    public function scorecard()
    {
        return $this->belongsTo(Scorecard::class);
    }

    public function end()
    {
        return $this->belongsTo(ScorecardEnd::class, 'scorecard_end_id');
    }
}

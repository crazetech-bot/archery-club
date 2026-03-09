<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class ScorecardEnd extends Model
{
    protected $table = 'scorecard_ends';

    protected $fillable = [
        'scorecard_id',
        'end_number',
        'end_score',
    ];

    public function scorecard()
    {
        return $this->belongsTo(Scorecard::class);
    }

    public function shots()
    {
        return $this->hasMany(ScorecardShot::class, 'scorecard_end_id');
    }
}

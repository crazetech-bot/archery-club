<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class ScorecardMetric extends Model
{
    protected $table = 'scorecard_metrics';

    protected $fillable = [
        'scorecard_id',
        'average_arrow_score',
        'hit_rate',
        'x_count',
        'miss_count',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function scorecard()
    {
        return $this->belongsTo(Scorecard::class);
    }
}

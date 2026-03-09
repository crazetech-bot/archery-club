<?php

namespace App\Models\Scoring;

use App\Models\Training\TrainingSession;
use App\Models\Archers\Archer;
use App\Models\Training\ScoringTemplate;
use Illuminate\Database\Eloquent\Model;

class Scorecard extends Model
{
    protected $table = 'scorecards';

    protected $fillable = [
        'training_session_id',
        'archer_id',
        'scoring_template_id',
        'status',
        'total_score',
        'x_count',
        'arrow_count',
    ];

    public function session()
    {
        return $this->belongsTo(TrainingSession::class, 'training_session_id');
    }

    public function archer()
    {
        return $this->belongsTo(Archer::class);
    }

    public function template()
    {
        return $this->belongsTo(ScoringTemplate::class, 'scoring_template_id');
    }

    public function ends()
    {
        return $this->hasMany(ScorecardEnd::class);
    }

    public function shots()
    {
        return $this->hasMany(ScorecardShot::class);
    }

    public function metrics()
    {
        return $this->hasOne(ScorecardMetric::class);
    }
}

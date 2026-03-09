<?php

namespace App\Models\Training;

use App\Models\Core\Club;
use Illuminate\Database\Eloquent\Model;

class ScoringTemplate extends Model
{
    protected $table = 'scoring_templates';

    protected $fillable = [
        'club_id',
        'name',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function division()
    {
        return $this->belongsTo(\App\Models\Scoring\Division::class);
    }

    public function classification()
    {
        return $this->belongsTo(\App\Models\Scoring\Classification::class);
    }

    public function distance()
    {
        return $this->belongsTo(\App\Models\Scoring\Distance::class);
    }

    public function targetFace()
    {
        return $this->belongsTo(\App\Models\Scoring\TargetFace::class);
    }

    public function targetFaceSize()
    {
        return $this->belongsTo(\App\Models\Scoring\TargetFaceSize::class);
    }
}

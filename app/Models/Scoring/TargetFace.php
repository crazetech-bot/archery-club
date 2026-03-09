<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class TargetFace extends Model
{
    protected $table = 'target_faces';

    protected $fillable = [
        'name',
        'scoring_min',
        'scoring_max',
        'x_value',
        'has_x_ring',
        'description',
    ];

    public function sizes()
    {
        return $this->hasMany(TargetFaceSize::class);
    }
}

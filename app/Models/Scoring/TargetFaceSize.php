<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class TargetFaceSize extends Model
{
    protected $table = 'target_face_sizes';

    protected $fillable = [
        'distance_id',
        'target_face_id',
        'size_cm',
    ];

    public function distance()
    {
        return $this->belongsTo(Distance::class);
    }

    public function targetFace()
    {
        return $this->belongsTo(TargetFace::class);
    }
}

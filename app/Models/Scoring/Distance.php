<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class Distance extends Model
{
    protected $table = 'distances';

    protected $fillable = [
        'meters',
    ];
}

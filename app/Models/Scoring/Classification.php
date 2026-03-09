<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class Classification extends Model
{
    protected $table = 'classifications';

    protected $fillable = [
        'name',
        'min_age',
        'max_age',
    ];
}

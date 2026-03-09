<?php

namespace App\Models\Scoring;

use Illuminate\Database\Eloquent\Model;

class Division extends Model
{
    protected $table = 'divisions';

    protected $fillable = [
        'name',
    ];
}

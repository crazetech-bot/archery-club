<?php

namespace App\Models\Coaches;

use App\Models\Core\User;
use App\Models\Archers\Archer;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    protected $table = 'coaches';

    protected $fillable = [
        'user_id',
        'certification_level',
        'bio',
    ];

    /**
     * Coach belongs to a User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Coach may train multiple archers.
     */
    public function archers()
    {
        return $this->belongsToMany(Archer::class, 'archer_coach')
            ->using(\App\Models\Archers\ArcherCoach::class)
            ->withPivot(['relationship_type', 'started_at', 'ended_at'])
            ->withTimestamps();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Competition extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'name',
        'location',
        'date',
        'level',
        'round_type',
        'distance_metres',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Results recorded for this competition.
     */
    public function results(): HasMany
    {
        return $this->hasMany(CompetitionResult::class);
    }

    /**
     * Archers who participated in this competition (through results).
     */
    public function archers(): HasMany
    {
        return $this->hasMany(CompetitionResult::class)->with('archer');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coach extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'user_id',
        'level',
        'notes',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The central User record for this coach (cross-DB — no FK constraint).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\User::class, 'user_id')
                    ->on('mysql');
    }

    /**
     * Archers directly assigned to this coach.
     */
    public function archers(): HasMany
    {
        return $this->hasMany(Archer::class);
    }

    /**
     * Training sessions led by this coach.
     */
    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

}

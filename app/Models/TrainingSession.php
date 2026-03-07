<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrainingSession extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'archer_id',
        'coach_id',
        'round_type',
        'distance_metres',
        'max_score',
        'started_at',
        'ended_at',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The archer this session belongs to.
     */
    public function archer(): BelongsTo
    {
        return $this->belongsTo(Archer::class);
    }

    /**
     * The coach who led this session (optional).
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    /**
     * The live scoring session linked to this training session.
     */
    public function liveSession(): HasOne
    {
        return $this->hasOne(LiveSession::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check if this training session has an active live session.
     */
    public function hasActiveLiveSession(): bool
    {
        return $this->liveSession()->where('status', 'active')->exists();
    }
}

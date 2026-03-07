<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class LiveSession extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'training_session_id',
        'status',
        'arrows_per_end',
        'started_at',
        'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The training session this live session belongs to.
     */
    public function trainingSession(): BelongsTo
    {
        return $this->belongsTo(TrainingSession::class);
    }

    /**
     * All ends shot in this live session.
     */
    public function ends(): HasMany
    {
        return $this->hasMany(LiveEnd::class)->orderBy('end_number');
    }

    /**
     * All arrows in this live session (through ends).
     */
    public function arrows(): HasManyThrough
    {
        return $this->hasManyThrough(LiveArrow::class, LiveEnd::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate the running total score across all ends.
     */
    public function getTotalScoreAttribute(): int
    {
        return $this->ends->sum('total_score');
    }

    /**
     * Calculate total X count across all ends.
     */
    public function getXCountAttribute(): int
    {
        return $this->ends->sum('x_count');
    }

    /**
     * Calculate total 10 count across all ends.
     */
    public function getTenCountAttribute(): int
    {
        return $this->ends->sum('ten_count');
    }

    /**
     * Calculate the average score per end.
     */
    public function getAveragePerEndAttribute(): float
    {
        $count = $this->ends->count();

        return $count > 0 ? round($this->total_score / $count, 2) : 0.0;
    }

    /**
     * Check if the session is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark the session as completed.
     */
    public function complete(): void
    {
        $this->update([
            'status'   => 'completed',
            'ended_at' => now(),
        ]);
    }
}

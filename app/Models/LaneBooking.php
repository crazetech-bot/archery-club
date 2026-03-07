<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaneBooking extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'lane_id',
        'archer_id',
        'group_id',
        'start_time',
        'end_time',
        'purpose',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time'   => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The lane that was booked.
     */
    public function lane(): BelongsTo
    {
        return $this->belongsTo(Lane::class);
    }

    /**
     * The archer who made the booking (nullable — could be a group).
     */
    public function archer(): BelongsTo
    {
        return $this->belongsTo(Archer::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Duration of this booking in minutes.
     */
    public function getDurationInMinutesAttribute(): int
    {
        return (int) $this->start_time->diffInMinutes($this->end_time);
    }

    /**
     * Check if this booking is currently active.
     */
    public function isOngoing(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }
}

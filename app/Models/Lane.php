<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lane extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'number',
        'name',
        'distance_metres',
        'target_face',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All bookings for this lane.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(LaneBooking::class);
    }

    /**
     * Upcoming bookings for this lane.
     */
    public function upcomingBookings(): HasMany
    {
        return $this->hasMany(LaneBooking::class)
                    ->where('start_time', '>=', now())
                    ->orderBy('start_time');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check if this lane is available for a given time window.
     */
    /**
     * Check if this lane is available for a given time window.
     *
     * @param string $startTime
     * @param string $endTime
     * @param int|null $excludeBookingId  Exclude a specific booking (for update checks)
     */
    public function isAvailable(string $startTime, string $endTime, ?int $excludeBookingId = null): bool
    {
        return ! $this->bookings()
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($query) use ($startTime, $endTime) {
                // Any booking that overlaps the requested window
                $query->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
            })
            ->exists();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Archer extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     * Do not specify $connection here; tenancy initializes the correct DB.
     */

    protected $fillable = [
        'user_id',
        'coach_id',
        'category',
        'date_of_birth',
        'dominant_hand',
        'phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The central User record for this archer (cross-DB — no FK constraint).
     * Uses the central 'mysql' connection.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Central\User::class, 'user_id')
                    ->on('mysql');
    }

    /**
     * The coach assigned to this archer.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(Coach::class);
    }

    /**
     * The archer's current equipment setup (marked is_current = true).
     */
    public function currentEquipment(): HasOne
    {
        return $this->hasOne(EquipmentSetup::class)->where('is_current', true)->latestOfMany();
    }

    /**
     * All training sessions for this archer.
     */
    public function trainingSessions(): HasMany
    {
        return $this->hasMany(TrainingSession::class);
    }

    /**
     * Equipment setups configured for this archer.
     */
    public function equipmentSetups(): HasMany
    {
        return $this->hasMany(EquipmentSetup::class);
    }

    /**
     * Competition results for this archer.
     */
    public function competitionResults(): HasMany
    {
        return $this->hasMany(CompetitionResult::class);
    }

    /**
     * Lane bookings made by this archer.
     */
    public function laneBookings(): HasMany
    {
        return $this->hasMany(LaneBooking::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Calculate the archer's age from date_of_birth.
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth?->age;
    }
}

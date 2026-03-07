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
        'name',
        'gender',
        'bow_type',
        'experience_level',
        'category',
        'date_of_birth',
        'dominant_hand',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_active'     => 'boolean',
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

    /**
     * Coach notes written about this archer.
     */
    public function coachNotes(): HasMany
    {
        return $this->hasMany(CoachNote::class)->latest();
    }

    /**
     * Group session attendance records for this archer.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
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

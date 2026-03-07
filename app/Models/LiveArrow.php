<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveArrow extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'live_end_id',
        'arrow_number',
        'score',
        'position_x',
        'position_y',
    ];

    protected $casts = [
        'arrow_number' => 'integer',
        'position_x'   => 'float',
        'position_y'   => 'float',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The end this arrow belongs to.
     */
    public function end(): BelongsTo
    {
        return $this->belongsTo(LiveEnd::class, 'live_end_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Resolve the numeric value of the score.
     * X = 10, M = 0, numeric = face value.
     */
    public function getNumericScoreAttribute(): int
    {
        $score = strtoupper(trim($this->score));

        return match (true) {
            $score === 'X' => 10,
            $score === 'M' => 0,
            is_numeric($score) => (int) $score,
            default => 0,
        };
    }

    /**
     * Check if this arrow has target coordinates for heatmap rendering.
     */
    public function hasCoordinates(): bool
    {
        return $this->position_x !== null && $this->position_y !== null;
    }
}

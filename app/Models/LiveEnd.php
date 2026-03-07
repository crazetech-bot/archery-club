<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LiveEnd extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'live_session_id',
        'end_number',
        'total_score',
        'x_count',
        'ten_count',
        'tag',
        'notes',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'x_count'     => 'integer',
        'ten_count'   => 'integer',
        'end_number'  => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The live session this end belongs to.
     */
    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    /**
     * All arrows in this end.
     */
    public function arrows(): HasMany
    {
        return $this->hasMany(LiveArrow::class)->orderBy('arrow_number');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Recalculate and persist total_score, x_count, and ten_count from arrows.
     * Called after arrows are added or updated.
     */
    public function recalculate(): void
    {
        $arrows = $this->arrows;

        $totalScore = 0;
        $xCount     = 0;
        $tenCount   = 0;

        foreach ($arrows as $arrow) {
            $score = strtoupper(trim($arrow->score));

            if ($score === 'X') {
                $totalScore += 10;
                $xCount++;
                $tenCount++;
            } elseif (is_numeric($score)) {
                $totalScore += (int) $score;

                if ((int) $score === 10) {
                    $tenCount++;
                }
            }
            // 'M' (miss) contributes 0
        }

        $this->update([
            'total_score' => $totalScore,
            'x_count'     => $xCount,
            'ten_count'   => $tenCount,
        ]);
    }
}

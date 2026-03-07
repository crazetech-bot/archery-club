<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompetitionResult extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'competition_id',
        'archer_id',
        'category',
        'score',
        'max_score',
        'placing',
        'competed_at',
        'notes',
    ];

    protected $casts = [
        'score'       => 'integer',
        'max_score'   => 'integer',
        'placing'     => 'integer',
        'competed_at' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The competition this result belongs to.
     */
    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /**
     * The archer this result belongs to.
     */
    public function archer(): BelongsTo
    {
        return $this->belongsTo(Archer::class);
    }
}

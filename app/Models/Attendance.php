<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'group_session_id',
        'archer_id',
        'status',
        'notes',
        'marked_at',
    ];

    protected $casts = [
        'marked_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The group session this attendance record belongs to.
     */
    public function groupSession(): BelongsTo
    {
        return $this->belongsTo(GroupSession::class);
    }

    /**
     * The archer this attendance record is for.
     */
    public function archer(): BelongsTo
    {
        return $this->belongsTo(Archer::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Status badge color for use in the UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'present' => 'green',
            'absent'  => 'red',
            'late'    => 'yellow',
            'excused' => 'gray',
            default   => 'gray',
        };
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenance extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'equipment_setup_id',
        'type',
        'description',
        'details',
        'cost',
        'performed_at',
        'next_due_at',
        'performed_by',
    ];

    protected $casts = [
        'performed_at' => 'date',
        'next_due_at'  => 'date',
        'cost'         => 'float',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The equipment setup this maintenance log belongs to.
     */
    public function equipmentSetup(): BelongsTo
    {
        return $this->belongsTo(EquipmentSetup::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Whether maintenance is overdue (next_due_at is in the past).
     */
    public function isOverdue(): bool
    {
        return $this->next_due_at !== null && $this->next_due_at->isPast();
    }
}

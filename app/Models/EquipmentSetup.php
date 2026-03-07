<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentSetup extends Model
{
    /**
     * Tenant DB model — resolved automatically by Stancl Tenancy.
     */

    protected $fillable = [
        'archer_id',
        'name',
        'bow_type',
        'bow_brand',
        'bow_model',
        'draw_weight_lbs',
        'draw_length_inches',
        'arrow_brand',
        'arrow_model',
        'arrow_spine',
        'is_current',
    ];

    protected $casts = [
        'is_current'         => 'boolean',
        'draw_weight_lbs'    => 'float',
        'draw_length_inches' => 'float',
        'arrow_spine'        => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The archer who owns this equipment setup.
     */
    public function archer(): BelongsTo
    {
        return $this->belongsTo(Archer::class);
    }
}

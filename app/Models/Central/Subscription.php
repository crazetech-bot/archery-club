<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /**
     * The database connection to use (central DB).
     * Maps to the Cashier-managed `subscriptions` table.
     */
    protected $connection = 'mysql';

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at'        => 'datetime',
        'quantity'       => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The user (tenant admin) this subscription belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->stripe_status === 'active';
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isTrialing(): bool
    {
        return $this->stripe_status === 'trialing';
    }

    /**
     * Check if the subscription has ended.
     */
    public function hasEnded(): bool
    {
        return $this->ends_at !== null && $this->ends_at->isPast();
    }
}

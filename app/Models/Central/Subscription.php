<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    /**
     * The database connection to use (central DB).
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'subscriptions';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'tenant_id',
        'provider',
        'provider_customer_id',
        'provider_subscription_id',
        'plan',
        'status',
        'renews_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'renews_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The tenant this subscription belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Check if the subscription is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isTrialing(): bool
    {
        return $this->status === 'trialing';
    }
}

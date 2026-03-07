<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;
    use HasDomains;

    /**
     * The database connection to use (central DB).
     */
    protected $connection = 'mysql';

    /**
     * The table associated with the model.
     */
    protected $table = 'tenants';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'db_name',
        'db_host',
        'db_username',
        'db_password',
        'plan',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'db_password',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'db_password' => 'encrypted',
    ];

    /**
     * Custom columns used to build the tenant's database config.
     * Stancl Tenancy reads these to resolve the tenant DB connection.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'db_name',
            'db_host',
            'db_username',
            'db_password',
            'plan',
            'status',
        ];
    }

    /**
     * Build the tenant's database connection config from custom columns.
     */
    public function getDatabaseConfig(): array
    {
        return [
            'driver'   => 'mysql',
            'host'     => $this->db_host,
            'database' => $this->db_name,
            'username' => $this->db_username,
            'password' => $this->db_password,
            'charset'  => 'utf8mb4',
            'prefix'   => '',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Users belonging to this tenant via the pivot table.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'tenant_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Subscriptions for this tenant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id');
    }

    /**
     * Active subscription shortcut.
     */
    public function activeSubscription(): HasMany
    {
        return $this->hasMany(Subscription::class, 'tenant_id')
                    ->where('status', 'active');
    }
}

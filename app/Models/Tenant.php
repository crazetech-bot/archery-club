<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $connection = 'mysql';

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

    protected $hidden = [
        'db_password',
    ];

    protected function casts(): array
    {
        return [];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'tenant_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function billing()
    {
        return $this->hasOne(TenantBilling::class);
    }
}

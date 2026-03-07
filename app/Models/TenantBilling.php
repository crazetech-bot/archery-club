<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantBilling extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan',
        'status',
        'renews_at',
    ];

    protected function casts(): array
    {
        return [
            'renews_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}

<?php

namespace App\Models\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Club extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'timezone',
        'country',
        'city',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * All users who are members of this club.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'club_user')
            ->withPivot(['primary_role', 'joined_at'])
            ->withTimestamps();
    }

    /**
     * All membership pivot records for this club.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(ClubUser::class);
    }
}

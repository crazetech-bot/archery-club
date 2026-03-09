<?php

namespace App\Services\Module1;

use App\Models\Core\Club;
use Illuminate\Support\Str;

/**
 * ClubService — Module 1: Core Accounts & Roles
 *
 * Centralises all club metadata logic:
 *   - Settings updates with automatic slug regeneration
 *   - Logo path persistence (storage handled by caller)
 *   - Logo clearing
 */
class ClubService
{
    // -------------------------------------------------------------------------
    // Settings
    // -------------------------------------------------------------------------

    /**
     * Persist updated club settings.
     * Slug is regenerated automatically when the name changes.
     *
     * @param  array{name: string, timezone: string, country?: string|null, city?: string|null}  $data
     */
    public function update(Club $club, array $data): Club
    {
        if ($data['name'] !== $club->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $club->update($data);

        return $club->fresh();
    }

    // -------------------------------------------------------------------------
    // Logo
    // -------------------------------------------------------------------------

    /**
     * Persist a new logo path for the club.
     * File upload and storage are handled upstream.
     */
    public function updateLogo(Club $club, string $storedPath): Club
    {
        $club->update(['logo_path' => $storedPath]);

        return $club->fresh();
    }

    /**
     * Remove the club's logo by clearing the stored path.
     */
    public function clearLogo(Club $club): Club
    {
        $club->update(['logo_path' => null]);

        return $club->fresh();
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateClubRequest;
use App\Models\Core\Club;
use App\Services\Module1\ClubService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ClubController extends Controller
{
    public function __construct(
        private readonly ClubService $clubService,
    ) {}

    /**
     * Show the club settings form.
     *
     * GET /admin/club
     */
    public function edit(): Response
    {
        $club = Club::firstOrFail();

        return Inertia::render('Admin/ClubSettings', [
            'club' => [
                'id'        => $club->id,
                'name'      => $club->name,
                'slug'      => $club->slug,
                'logo_path' => $club->logo_path,
                'timezone'  => $club->timezone,
                'country'   => $club->country,
                'city'      => $club->city,
            ],
            'timezones' => \DateTimeZone::listIdentifiers(),
        ]);
    }

    /**
     * Persist updated club settings.
     *
     * PUT /admin/club
     */
    public function update(UpdateClubRequest $request): RedirectResponse
    {
        $club = Club::firstOrFail();

        $this->clubService->update($club, $request->validated());

        return redirect()->route('admin.club.edit')
            ->with('success', 'Club settings updated.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lane;
use App\Models\LaneBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class LaneController extends Controller
{
    /**
     * GET /admin/lanes
     */
    public function index(): Response
    {
        $lanes = Lane::orderBy('number')
            ->get()
            ->map(function (Lane $lane) {
                $current = LaneBooking::where('lane_id', $lane->id)
                    ->where('start_time', '<=', now())
                    ->where('end_time', '>=', now())
                    ->with('archer')
                    ->first();

                return [
                    'id'               => $lane->id,
                    'number'           => $lane->number,
                    'name'             => $lane->name,
                    'distance_metres'  => $lane->distance_metres,
                    'target_face'      => $lane->target_face,
                    'is_active'        => $lane->is_active,
                    'current_booking'  => $current ? [
                        'archer_name' => $current->archer?->user?->name ?? 'Unknown',
                        'end_time'    => $current->end_time,
                    ] : null,
                    'todays_bookings_count' => LaneBooking::where('lane_id', $lane->id)
                        ->whereDate('start_time', today())
                        ->count(),
                ];
            });

        return Inertia::render('Admin/Lanes', [
            'lanes' => $lanes,
        ]);
    }

    /**
     * POST /admin/lanes
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'number'          => ['required', 'integer', 'min:1', Rule::unique('lanes', 'number')],
            'name'            => ['required', 'string', 'max:100'],
            'distance_metres' => ['required', 'integer', 'min:1'],
            'target_face'     => ['required', 'string', 'in:40cm,60cm,80cm,122cm'],
            'is_active'       => ['boolean'],
        ]);

        Lane::create($data);

        return redirect()->route('admin.lanes.index')
            ->with('success', 'Lane created.');
    }

    /**
     * PUT /admin/lanes/{lane}
     */
    public function update(Request $request, Lane $lane)
    {
        $data = $request->validate([
            'number'          => ['required', 'integer', 'min:1', Rule::unique('lanes', 'number')->ignore($lane->id)],
            'name'            => ['required', 'string', 'max:100'],
            'distance_metres' => ['required', 'integer', 'min:1'],
            'target_face'     => ['required', 'string', 'in:40cm,60cm,80cm,122cm'],
            'is_active'       => ['boolean'],
        ]);

        $lane->update($data);

        return redirect()->route('admin.lanes.index')
            ->with('success', 'Lane updated.');
    }

    /**
     * DELETE /admin/lanes/{lane}
     */
    public function destroy(Lane $lane)
    {
        $lane->bookings()->delete();
        $lane->delete();

        return redirect()->route('admin.lanes.index')
            ->with('success', 'Lane deleted.');
    }
}

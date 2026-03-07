<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Archer;
use App\Models\Lane;
use App\Models\LaneBooking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LaneBookingController extends Controller
{
    /**
     * GET /admin/lanes/{lane}/bookings   — single-lane view
     * GET /admin/bookings                — all lanes view
     */
    public function index(?Lane $lane = null): Response
    {
        $bookingQuery = LaneBooking::with(['archer', 'lane'])
            ->orderBy('start_time');

        if ($lane) {
            $bookingQuery->where('lane_id', $lane->id);
        }

        $bookings = $bookingQuery->get()->map(fn (LaneBooking $b) => [
            'id'              => $b->id,
            'lane_id'         => $b->lane_id,
            'lane_number'     => $b->lane?->number,
            'archer_id'       => $b->archer_id,
            'archer_name'     => $b->archer?->user?->name ?? "Archer #{$b->archer_id}",
            'archer_category' => $b->archer?->category,
            'start_time'      => $b->start_time,
            'end_time'        => $b->end_time,
        ]);

        $lanes = Lane::orderBy('number')->get()->map(fn (Lane $l) => [
            'id'   => $l->id,
            'name' => $l->name,
        ]);

        $archers = Archer::with('user')->get()->map(fn (Archer $a) => [
            'id'   => $a->id,
            'name' => $a->user?->name ?? "Archer #{$a->id}",
        ]);

        return Inertia::render('Admin/LaneBookings', [
            'lane'     => $lane ? [
                'id'   => $lane->id,
                'name' => $lane->name,
            ] : null,
            'bookings' => $bookings,
            'lanes'    => $lanes,
            'archers'  => $archers,
        ]);
    }

    /**
     * POST /admin/lanes/{lane}/bookings
     */
    public function store(Request $request, Lane $lane)
    {
        $data = $request->validate([
            'archer_id'  => ['required', 'integer', 'exists:archers,id'],
            'start_time' => ['required', 'date', 'after:now'],
            'end_time'   => ['required', 'date', 'after:start_time'],
        ]);

        if (! $lane->isAvailable($data['start_time'], $data['end_time'])) {
            return back()->withErrors([
                'overlap' => 'This lane is already booked for the selected time slot.',
            ]);
        }

        $lane->bookings()->create([
            'archer_id'  => $data['archer_id'],
            'start_time' => $data['start_time'],
            'end_time'   => $data['end_time'],
        ]);

        return redirect()->back()->with('success', 'Booking created.');
    }

    /**
     * PUT /admin/lanes/{lane}/bookings/{booking}
     */
    public function update(Request $request, Lane $lane, LaneBooking $booking)
    {
        $data = $request->validate([
            'archer_id'  => ['required', 'integer', 'exists:archers,id'],
            'start_time' => ['required', 'date'],
            'end_time'   => ['required', 'date', 'after:start_time'],
        ]);

        if (! $lane->isAvailable($data['start_time'], $data['end_time'], $booking->id)) {
            return back()->withErrors([
                'overlap' => 'This lane is already booked for the selected time slot.',
            ]);
        }

        $booking->update($data);

        return redirect()->back()->with('success', 'Booking updated.');
    }

    /**
     * DELETE /admin/lanes/{lane}/bookings/{booking}
     */
    public function destroy(Lane $lane, LaneBooking $booking)
    {
        $booking->delete();

        return redirect()->back()->with('success', 'Booking cancelled.');
    }
}

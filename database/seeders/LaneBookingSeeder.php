<?php

namespace Database\Seeders;

use App\Models\Archer;
use App\Models\Lane;
use App\Models\LaneBooking;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * LaneBookingSeeder — Tenant DB
 *
 * Creates realistic lane bookings:
 *   - Past bookings (for history)
 *   - Present booking (one lane "in use" right now)
 *   - Future bookings (upcoming — visible on dashboard)
 *
 * Avoids time overlaps per lane.
 */
class LaneBookingSeeder extends Seeder
{
    public function run(): void
    {
        $archers = Archer::all();
        $lanes   = Lane::where('is_active', true)->get();

        if ($archers->isEmpty() || $lanes->isEmpty()) {
            $this->command->warn('LaneBookingSeeder: no archers or lanes found — skipping.');
            return;
        }

        LaneBooking::truncate();

        $now    = now();
        $booked = []; // track [lane_id => [intervals]] to avoid overlap

        foreach ($lanes as $lane) {
            $booked[$lane->id] = [];
        }

        // ── Past bookings (last 4 weeks, 2–3 per archer per week) ────────────

        foreach ($archers as $archer) {
            for ($weeksAgo = 4; $weeksAgo >= 1; $weeksAgo--) {
                $sessionsThisWeek = rand(2, 3);
                for ($s = 0; $s < $sessionsThisWeek; $s++) {
                    $lane  = $lanes->random();
                    $day   = $now->copy()->subWeeks($weeksAgo)->addDays(rand(0, 6));
                    $hour  = rand(8, 18);
                    $start = $day->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                    $end   = $start->copy()->addHours(rand(1, 2));

                    if ($this->overlaps($booked[$lane->id], $start, $end)) continue;

                    LaneBooking::create([
                        'lane_id'    => $lane->id,
                        'archer_id'  => $archer->id,
                        'start_time' => $start,
                        'end_time'   => $end,
                    ]);

                    $booked[$lane->id][] = [$start->timestamp, $end->timestamp];
                }
            }
        }

        // ── Present booking (1 lane currently in use) ─────────────────────────

        $presentLane   = $lanes->first();
        $presentArcher = $archers->first();
        $presentStart  = $now->copy()->subMinutes(rand(15, 45));
        $presentEnd    = $now->copy()->addMinutes(rand(30, 75));

        LaneBooking::create([
            'lane_id'    => $presentLane->id,
            'archer_id'  => $presentArcher->id,
            'start_time' => $presentStart,
            'end_time'   => $presentEnd,
        ]);

        $booked[$presentLane->id][] = [$presentStart->timestamp, $presentEnd->timestamp];

        // ── Future bookings (next 2 weeks) ────────────────────────────────────

        foreach ($archers as $archer) {
            for ($daysAhead = 1; $daysAhead <= 14; $daysAhead += rand(2, 4)) {
                $lane  = $lanes->random();
                $day   = $now->copy()->addDays($daysAhead);
                $hour  = rand(9, 19);
                $start = $day->copy()->setHour($hour)->setMinute(0)->setSecond(0);
                $end   = $start->copy()->addHours(rand(1, 2));

                if ($this->overlaps($booked[$lane->id], $start, $end)) continue;

                LaneBooking::create([
                    'lane_id'    => $lane->id,
                    'archer_id'  => $archer->id,
                    'start_time' => $start,
                    'end_time'   => $end,
                ]);

                $booked[$lane->id][] = [$start->timestamp, $end->timestamp];
            }
        }

        $this->command->info('LaneBookingSeeder: created ' . LaneBooking::count() . ' bookings.');
    }

    private function overlaps(array $intervals, Carbon $start, Carbon $end): bool
    {
        foreach ($intervals as [$s, $e]) {
            if ($start->timestamp < $e && $end->timestamp > $s) {
                return true;
            }
        }
        return false;
    }
}

<?php

namespace Database\Seeders;

use App\Models\Archer;
use App\Models\Coach;
use App\Models\Competition;
use App\Models\CompetitionResult;
use App\Models\EquipmentSetup;
use App\Models\Lane;
use App\Models\LaneBooking;
use App\Models\LiveArrow;
use App\Models\LiveEnd;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

/**
 * DemoArcherSeeder — Tenant DB
 *
 * Seeds realistic training data for the demo archers:
 *   - Equipment setups
 *   - 12 weeks of training sessions per archer (with live scoring)
 *   - 2 competitions with results
 *   - Lane bookings
 *
 * Scores are generated to show plausible progression over time.
 * Must run after archers, coaches, and lanes already exist in the tenant DB.
 */
class DemoArcherSeeder extends Seeder
{
    // ── Score profiles ────────────────────────────────────────────────────────
    // Each profile defines which scores are weighted heavily.
    // Used by generateEnd() to produce realistic, skill-appropriate ends.

    private array $scoreProfiles = [
        'beginner'     => ['M', '1', '2', '3', '4', '5', '6', '7', '8'],
        'intermediate' => ['5', '6', '7', '8', '9', '9', '9', '10'],
        'advanced'     => ['8', '9', '9', '10', '10', '10', 'X', 'X'],
        'elite'        => ['9', '10', '10', '10', 'X', 'X', 'X', 'X'],
    ];

    public function run(): void
    {
        $archers = Archer::with('trainingSessions')->get();
        $coach   = Coach::first();
        $lanes   = Lane::all();

        if ($archers->isEmpty()) {
            $this->command->warn('  No archers found — skipping DemoArcherSeeder.');
            return;
        }

        $this->command->info('  Seeding demo data for ' . $archers->count() . ' archers…');

        foreach ($archers as $index => $archer) {
            $profile = $this->skillProfile($index);

            $this->seedEquipment($archer);
            $this->seedTrainingSessions($archer, $coach, $profile);

            if ($lanes->isNotEmpty()) {
                $this->seedLaneBookings($archer, $lanes);
            }
        }

        $this->seedCompetitions($archers);

        $this->command->info('  Demo data seeded successfully.');
    }

    // ── Equipment ─────────────────────────────────────────────────────────────

    private function seedEquipment(Archer $archer): void
    {
        $setups = [
            [
                'name'               => 'Indoor Setup',
                'bow_type'           => 'Recurve',
                'bow_brand'          => 'Hoyt',
                'bow_model'          => 'Formula Xi',
                'draw_weight_lbs'    => 36.0,
                'draw_length_inches' => 28.5,
                'arrow_brand'        => 'Easton',
                'arrow_model'        => 'X10',
                'arrow_spine'        => 500,
                'is_current'         => true,
            ],
            [
                'name'               => 'Outdoor Setup',
                'bow_type'           => 'Recurve',
                'bow_brand'          => 'WNS',
                'bow_model'          => 'Gold Plus',
                'draw_weight_lbs'    => 38.0,
                'draw_length_inches' => 28.5,
                'arrow_brand'        => 'Easton',
                'arrow_model'        => 'X7',
                'arrow_spine'        => 2315,
                'is_current'         => false,
            ],
        ];

        foreach ($setups as $setup) {
            EquipmentSetup::create(array_merge($setup, ['archer_id' => $archer->id]));
        }
    }

    // ── Training sessions ─────────────────────────────────────────────────────

    private function seedTrainingSessions(Archer $archer, ?Coach $coach, string $profile): void
    {
        // 12 weeks × 2 sessions/week = 24 sessions, going back from today
        $sessionCount = 24;

        for ($i = $sessionCount; $i >= 1; $i--) {
            $date = Carbon::now()->subDays($i * 3)->startOfDay();

            // Alternate round types to vary the data
            $roundType = $i % 3 === 0 ? 'WA 70m' : 'WA 18m';
            $distance  = $roundType === 'WA 18m' ? 18 : 70;

            $startedAt = $date->copy()->setHour(rand(9, 17))->setMinute(0);
            $endedAt   = $startedAt->copy()->addMinutes(rand(60, 120));

            $trainingSession = TrainingSession::create([
                'archer_id'       => $archer->id,
                'coach_id'        => ($i % 4 === 0 && $coach) ? $coach->id : null,
                'round_type'      => $roundType,
                'distance_metres' => $distance,
                'started_at'      => $startedAt,
                'ended_at'        => $endedAt,
            ]);

            // Scoring improves slightly over time — early sessions use base
            // profile, later ones use the next tier up
            $effectiveProfile = $this->progressedProfile($profile, $i, $sessionCount);

            $this->seedLiveSession($trainingSession, $effectiveProfile, $date);
        }
    }

    // ── Live session + ends + arrows ──────────────────────────────────────────

    private function seedLiveSession(
        TrainingSession $trainingSession,
        string $profile,
        Carbon $date
    ): void {
        $startedAt = $date->copy()->setHour(10)->setMinute(0);
        $endedAt   = $startedAt->copy()->addHour();

        $liveSession = LiveSession::create([
            'training_session_id' => $trainingSession->id,
            'status'              => 'completed',
            'arrows_per_end'      => 6,
            'started_at'          => $startedAt,
            'ended_at'            => $endedAt,
        ]);

        $endsCount    = 10; // WA standard: 10 ends
        $arrowsPerEnd = 6;

        for ($endNumber = 1; $endNumber <= $endsCount; $endNumber++) {
            $arrows = $this->generateEnd($profile, $arrowsPerEnd);

            $liveEnd = LiveEnd::create([
                'live_session_id' => $liveSession->id,
                'end_number'      => $endNumber,
                'total_score'     => 0, // recalculated below
                'x_count'         => 0,
                'ten_count'       => 0,
            ]);

            foreach ($arrows as $arrowNumber => $score) {
                LiveArrow::create([
                    'live_end_id'  => $liveEnd->id,
                    'arrow_number' => $arrowNumber + 1,
                    'score'        => $score,
                    'position_x'   => $this->randomCoordinate($score),
                    'position_y'   => $this->randomCoordinate($score),
                ]);
            }

            // Use the model's recalculate() to compute totals from arrows
            $liveEnd->refresh();
            $liveEnd->recalculate();
        }
    }

    // ── Competitions ──────────────────────────────────────────────────────────

    private function seedCompetitions(iterable $archers): void
    {
        $competitions = [
            [
                'name'            => 'Regional Indoor Championships',
                'location'        => 'City Sports Hall',
                'date'            => Carbon::now()->subMonths(2)->toDateString(),
                'level'           => 'regional',
                'round_type'      => 'WA 18',
                'distance_metres' => 18,
            ],
            [
                'name'            => 'Club Open Day Shoot',
                'location'        => 'Club Range',
                'date'            => Carbon::now()->subWeeks(3)->toDateString(),
                'level'           => 'club',
                'round_type'      => 'Portsmouth',
                'distance_metres' => 20,
            ],
            [
                'name'            => 'County Outdoor Classic',
                'location'        => 'Riverside Ground, Guildford',
                'date'            => Carbon::now()->addMonths(2)->toDateString(),
                'level'           => 'regional',
                'round_type'      => 'WA 70m',
                'distance_metres' => 70,
            ],
        ];

        foreach ($competitions as $compData) {
            $competition = Competition::create($compData);

            // Only seed results for past competitions
            if ($competition->date > today()->toDateString()) {
                continue;
            }

            $scores  = collect($archers)->map(fn ($a) => rand(480, 570))->sort()->reverse()->values();
            $placing = 1;

            foreach ($archers as $idx => $archer) {
                CompetitionResult::create([
                    'competition_id' => $competition->id,
                    'archer_id'      => $archer->id,
                    'category'       => $archer->category ?? 'Senior',
                    'score'          => $scores[$idx] ?? rand(480, 570),
                    'max_score'      => 600,
                    'placing'        => $placing++,
                    'competed_at'    => $competition->date,
                    'notes'          => null,
                ]);
            }
        }

        $this->command->info('  Competitions and results seeded.');
    }

    // ── Lane bookings ─────────────────────────────────────────────────────────

    private function seedLaneBookings(Archer $archer, $lanes): void
    {
        // 3 upcoming bookings per archer
        for ($i = 1; $i <= 3; $i++) {
            $start = Carbon::now()->addDays($i * 2)->setHour(18)->setMinute(0);

            LaneBooking::create([
                'lane_id'    => $lanes->random()->id,
                'archer_id'  => $archer->id,
                'group_id'   => null,
                'start_time' => $start,
                'end_time'   => $start->copy()->addHour(),
                'purpose'    => 'training',
            ]);
        }
    }

    // ── Score generation helpers ──────────────────────────────────────────────

    /**
     * Generate an array of arrow score strings for one end.
     *
     * @return string[]  e.g. ['X', '10', '9', '9', '8', '7']
     */
    private function generateEnd(string $profile, int $count): array
    {
        $pool   = $this->scoreProfiles[$profile];
        $arrows = [];

        for ($i = 0; $i < $count; $i++) {
            $arrows[] = $pool[array_rand($pool)];
        }

        // Sort descending so X/10s appear first (conventional score entry order)
        usort($arrows, fn ($a, $b) => $this->numericScore($b) <=> $this->numericScore($a));

        return $arrows;
    }

    private function numericScore(string $score): int
    {
        return match (strtoupper($score)) {
            'X'     => 11, // Sort X above 10
            'M'     => 0,
            default => (int) $score,
        };
    }

    /**
     * As sessions get more recent (lower $i), nudge the profile one tier up
     * to simulate improvement over time.
     */
    private function progressedProfile(string $base, int $i, int $total): string
    {
        $tiers = ['beginner', 'intermediate', 'advanced', 'elite'];
        $baseIndex = array_search($base, $tiers, true) ?? 0;

        // Advance one tier after the halfway point
        if ($i < ($total / 2)) {
            $baseIndex = min($baseIndex + 1, count($tiers) - 1);
        }

        return $tiers[$baseIndex];
    }

    /**
     * Determine the starting skill profile for the nth archer (0-indexed).
     */
    private function skillProfile(int $index): string
    {
        return match ($index % 4) {
            0       => 'intermediate',
            1       => 'beginner',
            2       => 'advanced',
            default => 'intermediate',
        };
    }

    /**
     * Generate a realistic XY coordinate on the target face.
     * Better scores cluster closer to the centre (0,0).
     * Returns null occasionally to simulate missing coordinates.
     */
    private function randomCoordinate(string $score): ?float
    {
        // 30% of arrows have no coordinates recorded
        if (rand(1, 10) <= 3) {
            return null;
        }

        // Radius of scatter depends on score zone
        $maxRadius = match (strtoupper($score)) {
            'X'     => 2.0,
            '10'    => 4.0,
            '9'     => 8.0,
            '8'     => 13.0,
            '7'     => 18.0,
            '6'     => 23.0,
            default => 35.0,
        };

        // Random angle + random distance within radius
        $angle    = lcg_value() * 2 * M_PI;
        $distance = lcg_value() * $maxRadius;

        return round($distance * cos($angle), 3);
    }
}

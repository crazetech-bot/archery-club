<?php

namespace Database\Seeders;

use App\Models\Archer;
use App\Models\Coach;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * CoachNoteSeeder — Tenant DB
 *
 * Inserts realistic coach notes for each archer, showing progression
 * observations, technique corrections, and goal-setting.
 */
class CoachNoteSeeder extends Seeder
{
    private array $noteTemplates = [
        'beginner' => [
            'Focus on anchor point consistency — {name} is anchoring too far forward. Drill: 10 mins blind baling before each session.',
            '{name} showed real improvement in back tension today. Still collapsing the bow arm on release — remind to "push the wall".',
            'Stance needs work. {name} is rotating hips toward the target. Set up a T-square on the line next session.',
            'Encouraging session. {name} hit their first 7 on a 10-zone target. Confidence is building — give them positive reinforcement.',
            'Grip too tight, causing torque. Recommended the open-palm grip drill. Ask them to check this at the start of each end.',
            'Release timing is improving. Suggested switching to a lighter draw weight temporarily to ingrain the back-tension release.',
        ],
        'intermediate' => [
            '{name} is ready to move to 18m from 10m. Scores are plateauing at current distance — new challenge will help.',
            'Mental focus is the limiting factor now. Introduced the pre-shot routine: 3 deep breaths, positive word, execute.',
            'Equipment review due. Draw weight may be too light — {name} is easily holding full draw for extended periods with no shake.',
            'Clicker training session today. {name} struggled with pass-through but managed it consistently by end of session.',
            'Review video from last session — collapsing draw elbow under pressure. Needs shoulder stability work.',
            'Best session in weeks — consistent 9s with occasional 10s. Recommend entering the county round next month.',
            'Wind affected scores today. Talked through hold points and aiming off. Good learning session despite lower total.',
        ],
        'advanced' => [
            '{name} is hitting consistent X-ring at 18m. Discussed strategy for WA 18 round — pushing for 580+.',
            'Fine-tuning finger placement. Shifted to a deeper hook — immediate improvement in release consistency.',
            'Shot analysis: grouping is tight but slightly left. Checked sight picture — micro-adjust 1 click right. Confirmed at next end.',
            'Discussed mental performance under competition pressure. Introduced "process focus" cues to replace outcome thinking.',
            '{name} shot a personal best today: 576/600. Outstanding execution. Keep the pre-shot routine exactly as-is.',
            'Working on arrow selection for the outdoor season. Discussed spine tolerance and point weight at 50m.',
            'Competition debrief: {name} finished 3rd — very solid. The dropped 10 in end 8 was a timing issue under fatigue. Need end-game stamina drills.',
            'Introduced the "empty bow" training drill. {name} has excellent form — this will reinforce muscle memory in off-season.',
        ],
    ];

    public function run(): void
    {
        $coach   = Coach::first();
        $archers = Archer::all();

        if (! $coach || $archers->isEmpty()) {
            $this->command->warn('CoachNoteSeeder: no coach or archers found — skipping.');
            return;
        }

        $profiles = ['beginner', 'intermediate', 'advanced'];

        foreach ($archers as $index => $archer) {
            $profile   = $profiles[$index % count($profiles)];
            $templates = $this->noteTemplates[$profile];
            $name      = $archer->user?->name ?? "Archer #{$archer->id}";

            // Insert 4–6 notes spread over the last 12 weeks
            $noteCount = rand(4, 6);
            $templates = collect($templates)->shuffle()->take($noteCount);

            foreach ($templates as $i => $template) {
                $content = str_replace('{name}', $name, $template);
                $weeksAgo = ($noteCount - $i) * 2; // evenly spaced, oldest first

                DB::table('coach_notes')->insert([
                    'archer_id'  => $archer->id,
                    'coach_id'   => $coach->id,
                    'content'    => $content,
                    'created_at' => now()->subWeeks($weeksAgo)->addDays(rand(0, 4)),
                    'updated_at' => now()->subWeeks($weeksAgo)->addDays(rand(0, 4)),
                ]);
            }
        }

        $this->command->info('CoachNoteSeeder: inserted notes for ' . $archers->count() . ' archer(s).');
    }
}

<?php

namespace App\Listeners\Live;

use App\Events\Live\EndSubmitted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Handles side effects when an archer submits an end.
 *
 * Broadcasting is handled by the EndSubmitted event (ShouldBroadcast).
 * This listener manages additional work such as logging and cache invalidation.
 */
class BroadcastEndSubmitted implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(EndSubmitted $event): void
    {
        Log::channel('live_scoring')->info('End submitted', [
            'tenant_id'       => $event->tenantId,
            'live_session_id' => $event->liveSession->id,
            'end_id'          => $event->liveEnd->id,
            'end_number'      => $event->liveEnd->end_number,
            'total_score'     => $event->liveEnd->total_score,
            'x_count'         => $event->liveEnd->x_count,
        ]);

        // Future: push the updated running total into a Redis hash so coach
        // monitor can read it without a DB query:
        // Cache::hset(
        //     "live_session:{$event->liveSession->id}:stats",
        //     'total_score', $event->liveSession->total_score,
        //     'ends_count',  $event->liveSession->ends()->count(),
        // );
    }

    public function failed(EndSubmitted $event, \Throwable $exception): void
    {
        Log::error('BroadcastEndSubmitted listener failed', [
            'live_session_id' => $event->liveSession->id,
            'live_end_id'     => $event->liveEnd->id,
            'error'           => $exception->getMessage(),
        ]);
    }
}

<?php

namespace App\Listeners\Live;

use App\Events\Live\SessionStarted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Handles side effects when a live session starts.
 *
 * The broadcasting itself is handled by the SessionStarted event (ShouldBroadcast).
 * This listener is responsible for any additional work: logging, cache warm-up,
 * or future integrations (push notifications, webhooks, etc.).
 */
class BroadcastSessionStarted implements ShouldQueue
{
    /**
     * The queue to use for this listener.
     */
    public string $queue = 'default';

    public function handle(SessionStarted $event): void
    {
        Log::channel('live_scoring')->info('Live session started', [
            'tenant_id'          => $event->tenantId,
            'live_session_id'    => $event->liveSession->id,
            'training_session_id' => $event->liveSession->training_session_id,
        ]);

        // Future: warm up a Redis cache key for this session so the coach
        // monitor polling endpoint doesn't hit the DB on every request.
        // Cache::put("live_session:{$event->liveSession->id}", $event->liveSession, now()->addHours(4));
    }

    /**
     * Handle a job failure — log it, don't retry broadcast side effects.
     */
    public function failed(SessionStarted $event, \Throwable $exception): void
    {
        Log::error('BroadcastSessionStarted listener failed', [
            'live_session_id' => $event->liveSession->id,
            'error'           => $exception->getMessage(),
        ]);
    }
}

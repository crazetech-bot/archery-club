<?php

namespace App\Listeners\Live;

use App\Events\Live\SessionCompleted;
use App\Jobs\GenerateSessionPdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

/**
 * Handles side effects when a live session is completed.
 *
 * Broadcasting is handled by the SessionCompleted event (ShouldBroadcast).
 * This listener queues the PDF generation job and performs cleanup.
 */
class BroadcastSessionCompleted implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(SessionCompleted $event): void
    {
        Log::channel('live_scoring')->info('Live session completed', [
            'tenant_id'       => $event->tenantId,
            'live_session_id' => $event->liveSession->id,
            'ended_at'        => $event->liveSession->ended_at,
        ]);

        // Queue the PDF generation so it's ready for instant download
        // without blocking the HTTP response.
        GenerateSessionPdf::dispatch($event->liveSession)
            ->onQueue('pdf')
            ->delay(now()->addSeconds(3)); // Small delay to let DB writes settle

        // Future: flush the Redis cache for this session
        // Cache::forget("live_session:{$event->liveSession->id}");
        // Cache::forget("live_session:{$event->liveSession->id}:stats");
    }

    public function failed(SessionCompleted $event, \Throwable $exception): void
    {
        Log::error('BroadcastSessionCompleted listener failed', [
            'live_session_id' => $event->liveSession->id,
            'error'           => $exception->getMessage(),
        ]);
    }
}

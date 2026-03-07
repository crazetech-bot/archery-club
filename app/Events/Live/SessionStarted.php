<?php

namespace App\Events\Live;

use App\Models\LiveSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly LiveSession $liveSession,
        public readonly int $tenantId,
    ) {}

    // ── Broadcast config ──────────────────────────────────────────────────────

    /**
     * Broadcast on the tenant-wide live channel so all coaches in this club
     * receive the event in CoachMonitor.vue.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.live"),
        ];
    }

    /**
     * The client-side event name (.live.session.started).
     * Leading dot tells Echo this is a custom (non-Laravel-default) event name.
     */
    public function broadcastAs(): string
    {
        return 'live.session.started';
    }

    /**
     * Data sent to the client.
     * Eager-load archer + training_session so the coach card can render immediately.
     */
    public function broadcastWith(): array
    {
        $this->liveSession->loadMissing([
            'trainingSession.archer',
            'trainingSession',
        ]);

        return [
            'session' => [
                'id'                  => $this->liveSession->id,
                'status'              => $this->liveSession->status,
                'started_at'          => $this->liveSession->started_at,
                'training_session_id' => $this->liveSession->training_session_id,
                'archer' => [
                    'id'   => $this->liveSession->trainingSession->archer->id,
                    'name' => $this->liveSession->trainingSession->archer->name,
                ],
                'training_session' => [
                    'round_type' => $this->liveSession->trainingSession->round_type,
                    'distance'   => $this->liveSession->trainingSession->distance,
                ],
                'ends' => [],
            ],
        ];
    }

    /**
     * Broadcast on the default queue so it doesn't block the web worker.
     */
    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}

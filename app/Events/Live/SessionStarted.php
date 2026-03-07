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
        $this->liveSession->loadMissing(['trainingSession.archer.user', 'trainingSession']);

        $trainingSession = $this->liveSession->trainingSession;
        $archer          = $trainingSession?->archer;

        return [
            'session' => [
                'id'              => $this->liveSession->id,
                'status'          => 'active',
                'archer'          => [
                    'id'       => $archer?->id,
                    'name'     => $archer?->user?->name ?? "Archer #{$archer?->id}",
                    'category' => $archer?->category,
                ],
                'round_type'      => $trainingSession?->round_type,
                'distance_metres' => $trainingSession?->distance_metres,
                'arrows_per_end'  => $this->liveSession->arrows_per_end,
                'total_score'     => 0,
                'x_count'         => 0,
                'average_per_end' => 0,
                'started_at'      => $this->liveSession->started_at,
                'ends'            => [],
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

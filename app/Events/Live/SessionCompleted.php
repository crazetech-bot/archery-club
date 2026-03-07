<?php

namespace App\Events\Live;

use App\Models\LiveSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionCompleted implements ShouldBroadcast
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
     * Broadcast on both channels:
     *  1. tenant-wide live channel — coach monitor updates the card status
     *  2. per-session channel — archer's scoring page switches to completed view
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.live"),
            new PrivateChannel("tenant.{$this->tenantId}.session.{$this->liveSession->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'live.session.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'session_id'  => $this->liveSession->id,
            'live_session' => [
                'id'       => $this->liveSession->id,
                'status'   => $this->liveSession->status,
                'ended_at' => $this->liveSession->ended_at,
            ],
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}

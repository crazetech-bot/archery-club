<?php

namespace App\Events\Live;

use App\Models\LiveEnd;
use App\Models\LiveSession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndSubmitted implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public readonly LiveSession $liveSession,
        public readonly LiveEnd $liveEnd,
        public readonly int $tenantId,
    ) {}

    // ── Broadcast config ──────────────────────────────────────────────────────

    /**
     * Broadcast on two channels:
     *  1. tenant-wide live channel — coaches see the end appear on their monitor
     *  2. per-session channel — the archer's own scoring page can confirm the save
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
        return 'live.end.submitted';
    }

    /**
     * Send the full end payload (including arrows) so the client can push it
     * into the local ends array without a separate fetch.
     */
    public function broadcastWith(): array
    {
        $this->liveEnd->loadMissing('arrows');

        return [
            'session_id' => $this->liveSession->id,
            'end' => [
                'id'              => $this->liveEnd->id,
                'live_session_id' => $this->liveEnd->live_session_id,
                'end_number'      => $this->liveEnd->end_number,
                'total_score'     => $this->liveEnd->total_score,
                'x_count'         => $this->liveEnd->x_count,
                'ten_count'       => $this->liveEnd->ten_count,
                'tag'             => $this->liveEnd->tag,
                'notes'           => $this->liveEnd->notes,
                'arrows'          => $this->liveEnd->arrows->map(fn ($a) => [
                    'id'           => $a->id,
                    'arrow_number' => $a->arrow_number,
                    'score'        => $a->score,
                    'position_x'   => $a->position_x,
                    'position_y'   => $a->position_y,
                ])->all(),
            ],
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }
}

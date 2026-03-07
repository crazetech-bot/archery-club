<?php

namespace App\Providers;

use App\Events\Live\EndSubmitted;
use App\Events\Live\SessionCompleted;
use App\Events\Live\SessionStarted;
use App\Listeners\Live\BroadcastEndSubmitted;
use App\Listeners\Live\BroadcastSessionCompleted;
use App\Listeners\Live\BroadcastSessionStarted;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event → Listener mappings.
     *
     * Each event may have multiple listeners. Listeners implementing
     * ShouldQueue are dispatched asynchronously via the queue worker.
     */
    protected $listen = [

        // ── Live scoring events ───────────────────────────────────────────────

        SessionStarted::class => [
            BroadcastSessionStarted::class,
            // Add more listeners here as needed, e.g.:
            // SendSessionStartedNotification::class,
        ],

        EndSubmitted::class => [
            BroadcastEndSubmitted::class,
        ],

        SessionCompleted::class => [
            BroadcastSessionCompleted::class,
        ],
    ];

    /**
     * Register any additional events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be auto-discovered.
     * We register manually above for explicit control.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}

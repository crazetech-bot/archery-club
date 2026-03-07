<?php

namespace App\Providers;

use App\Models\EquipmentSetup;
use App\Models\LiveSession;
use App\Models\TrainingSession;
use App\Policies\EquipmentPolicy;
use App\Policies\ScorePolicy;
use App\Policies\SessionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ── Policy registration ───────────────────────────────────────────────
        Gate::policy(TrainingSession::class, SessionPolicy::class);
        Gate::policy(LiveSession::class,     ScorePolicy::class);
        Gate::policy(EquipmentSetup::class,  EquipmentPolicy::class);
    }
}

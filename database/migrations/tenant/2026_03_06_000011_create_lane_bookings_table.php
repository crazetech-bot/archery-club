<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — lane_bookings table
     * Tracks lane reservations by archers or groups.
     */
    public function up(): void
    {
        Schema::create('lane_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lane_id')->constrained('lanes')->onDelete('cascade');
            $table->foreignId('archer_id')->nullable()->constrained('archers')->onDelete('set null');
            $table->unsignedBigInteger('group_id')->nullable(); // Reserved for future groups module
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('purpose')->nullable(); // e.g. "training", "competition warmup", "coaching"
            $table->timestamps();

            // Prevent double-booking the same lane at the same time (app-level enforced)
            $table->index(['lane_id', 'start_time', 'end_time']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lane_bookings');
    }
};

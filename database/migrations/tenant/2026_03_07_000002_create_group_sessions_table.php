<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Group sessions — club-level training events.
 *
 * Unlike training_sessions (which are per-archer and tied to live scoring),
 * group sessions represent a scheduled class or training event that multiple
 * archers attend. Attendance is tracked in the attendances table.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();

            // coach_id references coaches.id in tenant DB — no cross-DB FK
            $table->unsignedBigInteger('coach_id')->nullable();

            $table->string('title');
            $table->enum('type', ['technique', 'fitness', 'competition_prep', 'beginner', 'general'])->default('general');
            $table->dateTime('scheduled_at');
            $table->unsignedSmallInteger('duration_minutes')->default(90);
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');

            $table->timestamps();

            $table->index('scheduled_at');
            $table->index('coach_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_sessions');
    }
};

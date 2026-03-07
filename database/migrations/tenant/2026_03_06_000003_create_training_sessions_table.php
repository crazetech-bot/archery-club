<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — training_sessions table
     * Records individual training sessions per archer.
     */
    public function up(): void
    {
        Schema::create('training_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archer_id')->constrained('archers')->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained('coaches')->nullOnDelete();
            $table->string('round_type')->nullable();             // WA 18, WA 25, Portsmouth, Practice…
            $table->unsignedSmallInteger('distance_metres')->nullable();
            $table->unsignedSmallInteger('max_score')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['archer_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_sessions');
    }
};

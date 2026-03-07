<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — live_sessions table
     * Represents an active or completed real-time scoring session.
     */
    public function up(): void
    {
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_session_id')->constrained('training_sessions')->cascadeOnDelete();
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->unsignedTinyInteger('arrows_per_end')->default(6);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();

            $table->index(['training_session_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — live_ends table
     * Each "end" is a group of arrows shot in one round during a live session.
     */
    public function up(): void
    {
        Schema::create('live_ends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->onDelete('cascade');
            $table->unsignedTinyInteger('end_number'); // Ordinal position of this end in the session
            $table->unsignedSmallInteger('total_score')->default(0);
            $table->unsignedTinyInteger('x_count')->default(0);
            $table->unsignedTinyInteger('ten_count')->default(0);
            $table->string('tag')->nullable(); // e.g. "windy", "fatigue", "good form"
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_ends');
    }
};

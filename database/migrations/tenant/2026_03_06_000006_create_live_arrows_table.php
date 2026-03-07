<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — live_arrows table
     * Individual arrow scores within an end. Optional XY coordinates for heatmap.
     */
    public function up(): void
    {
        Schema::create('live_arrows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_end_id')->constrained('live_ends')->onDelete('cascade');
            $table->unsignedTinyInteger('arrow_number'); // Position within the end (1, 2, 3...)
            $table->string('score');                     // e.g. X, 10, 9, M (miss)
            $table->decimal('position_x', 6, 3)->nullable(); // X coordinate on target face
            $table->decimal('position_y', 6, 3)->nullable(); // Y coordinate on target face
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_arrows');
    }
};

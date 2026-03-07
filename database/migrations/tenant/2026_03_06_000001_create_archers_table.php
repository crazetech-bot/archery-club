<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — archers table
     * Stores archer profiles within a club (tenant).
     */
    public function up(): void
    {
        Schema::create('archers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index(); // central users.id — no FK (cross-DB)
            $table->unsignedBigInteger('coach_id')->nullable()->index(); // coaches.id within this tenant
            $table->string('category')->nullable();          // U12 | U15 | U18 | U21 | Senior | Master
            $table->date('date_of_birth')->nullable();
            $table->string('dominant_hand', 10)->nullable(); // right | left
            $table->string('phone', 30)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archers');
    }
};

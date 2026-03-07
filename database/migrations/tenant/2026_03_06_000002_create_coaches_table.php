<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — coaches table
     * Stores coach profiles within a club (tenant).
     */
    public function up(): void
    {
        Schema::create('coaches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique()->index(); // central users.id — no FK (cross-DB)
            $table->string('level')->nullable();  // e.g. Level 1, Level 2, Master Coach
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coaches');
    }
};

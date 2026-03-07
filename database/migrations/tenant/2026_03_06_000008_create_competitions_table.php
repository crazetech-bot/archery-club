<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — competitions table
     * Stores competitions that club archers participate in.
     */
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location')->nullable();
            $table->date('date');
            $table->string('level')->nullable();          // club | regional | national | international
            $table->string('round_type')->nullable();     // WA 18, WA 70m, Portsmouth…
            $table->unsignedSmallInteger('distance_metres')->nullable();
            $table->timestamps();

            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};

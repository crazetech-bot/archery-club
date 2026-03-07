<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — lanes table
     * Defines the shooting lanes available within the club.
     */
    public function up(): void
    {
        Schema::create('lanes', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('name');                                    // e.g. "Lane 1", "Indoor A"
            $table->unsignedSmallInteger('distance_metres')->default(18);
            $table->string('target_face', 10)->default('40cm');       // 40cm | 60cm | 80cm | 122cm
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lanes');
    }
};

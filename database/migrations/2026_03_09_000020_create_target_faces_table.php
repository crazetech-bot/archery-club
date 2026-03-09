<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_faces', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name'); // Complete, Reduced, Field Archery, etc.
            $table->unsignedTinyInteger('scoring_min')->nullable(); // 1, 5, 6
            $table->unsignedTinyInteger('scoring_max')->nullable(); // 6, 10
            $table->unsignedTinyInteger('x_value')->nullable(); // 10 or 11 when X is special
            $table->boolean('has_x_ring')->default(false);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_faces');
    }
};

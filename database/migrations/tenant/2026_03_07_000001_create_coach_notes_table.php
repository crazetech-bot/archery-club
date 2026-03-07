<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coach_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('archer_id');
            $table->unsignedBigInteger('coach_id');
            $table->text('content');
            $table->timestamps();

            $table->foreign('archer_id')->references('id')->on('archers')->cascadeOnDelete();
            $table->foreign('coach_id')->references('id')->on('coaches')->cascadeOnDelete();
            $table->index(['archer_id', 'coach_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_notes');
    }
};

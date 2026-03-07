<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — competition_results table
     * Records each archer's result in a competition.
     */
    public function up(): void
    {
        Schema::create('competition_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained('competitions')->cascadeOnDelete();
            $table->foreignId('archer_id')->constrained('archers')->cascadeOnDelete();
            $table->string('category')->nullable();              // e.g. Senior Recurve
            $table->unsignedSmallInteger('score')->nullable();
            $table->unsignedSmallInteger('max_score')->nullable();
            $table->unsignedSmallInteger('placing')->nullable(); // 1 = gold, 2 = silver, 3 = bronze…
            $table->date('competed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['competition_id', 'archer_id']);
            $table->index(['archer_id', 'competed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_results');
    }
};

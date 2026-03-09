<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorecard_shots', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scorecard_id');
            $table->unsignedBigInteger('scorecard_end_id')->nullable();
            $table->unsignedSmallInteger('shot_number'); // 1..N within card or end
            $table->unsignedTinyInteger('score')->default(0);
            $table->boolean('is_x')->default(false);
            $table->boolean('is_miss')->default(false);
            $table->timestamps();

            $table->foreign('scorecard_id')
                ->references('id')
                ->on('scorecards')
                ->onDelete('cascade');

            $table->foreign('scorecard_end_id')
                ->references('id')
                ->on('scorecard_ends')
                ->onDelete('cascade');

            $table->index(['scorecard_id', 'scorecard_end_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorecard_shots');
    }
};

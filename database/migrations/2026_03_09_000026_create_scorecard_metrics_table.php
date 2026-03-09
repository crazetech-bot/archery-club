<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorecard_metrics', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scorecard_id')->unique();
            $table->decimal('average_arrow_score', 5, 2)->default(0);
            $table->decimal('hit_rate', 5, 2)->default(0); // percentage 0–100
            $table->unsignedInteger('x_count')->default(0);
            $table->unsignedInteger('miss_count')->default(0);
            $table->json('extra')->nullable(); // future analytics
            $table->timestamps();

            $table->foreign('scorecard_id')
                ->references('id')
                ->on('scorecards')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorecard_metrics');
    }
};

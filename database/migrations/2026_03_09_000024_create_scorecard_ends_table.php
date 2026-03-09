<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorecard_ends', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('scorecard_id');
            $table->unsignedSmallInteger('end_number'); // 1..N
            $table->unsignedInteger('end_score')->default(0);
            $table->timestamps();

            $table->foreign('scorecard_id')
                ->references('id')
                ->on('scorecards')
                ->onDelete('cascade');

            $table->unique(['scorecard_id', 'end_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorecard_ends');
    }
};

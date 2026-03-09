<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('club_id');
            $table->string('name');
            $table->enum('type', ['end_based', 'shot_based']);
            $table->json('config'); // flexible scoring rules
            $table->timestamps();

            $table->foreign('club_id')
                ->references('id')
                ->on('clubs')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_templates');
    }
};

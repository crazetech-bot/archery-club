<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archer_coach', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('archer_id');
            $table->unsignedBigInteger('coach_id');
            $table->enum('relationship_type', ['primary', 'assistant'])->default('primary');
            $table->date('started_at')->nullable();
            $table->date('ended_at')->nullable();
            $table->timestamps();
            $table->foreign('archer_id')
                ->references('id')
                ->on('archers')
                ->onDelete('cascade');
            $table->foreign('coach_id')
                ->references('id')
                ->on('coaches')
                ->onDelete('cascade');
            $table->unique(['archer_id', 'coach_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archer_coach');
    }
};

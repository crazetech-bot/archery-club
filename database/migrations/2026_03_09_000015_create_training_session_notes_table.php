<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_session_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('training_session_id');
            $table->unsignedBigInteger('coach_id')->nullable();
            $table->unsignedBigInteger('archer_id')->nullable();
            $table->text('note');
            $table->timestamps();

            $table->foreign('training_session_id')
                ->references('id')
                ->on('training_sessions')
                ->onDelete('cascade');

            $table->foreign('coach_id')
                ->references('id')
                ->on('coaches')
                ->onDelete('set null');

            $table->foreign('archer_id')
                ->references('id')
                ->on('archers')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_session_notes');
    }
};

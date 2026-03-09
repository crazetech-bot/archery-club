<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_session_archers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('training_session_id');
            $table->unsignedBigInteger('archer_id');
            $table->enum('attendance_status', ['pending', 'present', 'absent'])
                ->default('pending');
            $table->timestamps();

            $table->foreign('training_session_id')
                ->references('id')
                ->on('training_sessions')
                ->onDelete('cascade');

            $table->foreign('archer_id')
                ->references('id')
                ->on('archers')
                ->onDelete('cascade');

            $table->unique(['training_session_id', 'archer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_session_archers');
    }
};

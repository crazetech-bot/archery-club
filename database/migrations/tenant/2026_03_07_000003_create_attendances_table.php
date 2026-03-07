<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Attendance — tracks which archers attended each group session.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('group_session_id');
            $table->unsignedBigInteger('archer_id');

            $table->enum('status', ['present', 'absent', 'late', 'excused'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamp('marked_at')->nullable();

            $table->timestamps();

            $table->foreign('group_session_id')->references('id')->on('group_sessions')->cascadeOnDelete();
            $table->foreign('archer_id')->references('id')->on('archers')->cascadeOnDelete();
            $table->unique(['group_session_id', 'archer_id']);
            $table->index('archer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

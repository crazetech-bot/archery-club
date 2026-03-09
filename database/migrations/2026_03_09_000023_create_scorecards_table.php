<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scorecards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('training_session_id');
            $table->unsignedBigInteger('archer_id');
            $table->unsignedBigInteger('scoring_template_id');
            $table->enum('status', ['draft', 'submitted', 'locked'])->default('draft');
            $table->unsignedInteger('total_score')->default(0);
            $table->unsignedInteger('x_count')->default(0);
            $table->unsignedInteger('arrow_count')->default(0);
            $table->timestamps();

            $table->foreign('training_session_id')
                ->references('id')
                ->on('training_sessions')
                ->onDelete('cascade');

            $table->foreign('archer_id')
                ->references('id')
                ->on('archers')
                ->onDelete('cascade');

            $table->foreign('scoring_template_id')
                ->references('id')
                ->on('scoring_templates')
                ->onDelete('cascade');

            $table->unique(
                ['training_session_id', 'archer_id', 'scoring_template_id'],
                'session_archer_template_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scorecards');
    }
};

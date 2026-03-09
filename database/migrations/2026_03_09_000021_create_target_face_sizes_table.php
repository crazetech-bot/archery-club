<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('target_face_sizes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('distance_id');
            $table->unsignedBigInteger('target_face_id');
            $table->unsignedSmallInteger('size_cm'); // 40, 60, 80, 122, etc.
            $table->timestamps();

            $table->foreign('distance_id')
                ->references('id')
                ->on('distances')
                ->onDelete('cascade');

            $table->foreign('target_face_id')
                ->references('id')
                ->on('target_faces')
                ->onDelete('cascade');

            $table->unique(['distance_id', 'target_face_id', 'size_cm'], 'distance_face_size_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_face_sizes');
    }
};

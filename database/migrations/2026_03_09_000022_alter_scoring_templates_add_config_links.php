<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scoring_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('division_id')->nullable()->after('club_id');
            $table->unsignedBigInteger('classification_id')->nullable()->after('division_id');
            $table->unsignedBigInteger('distance_id')->nullable()->after('classification_id');
            $table->unsignedBigInteger('target_face_id')->nullable()->after('distance_id');
            $table->unsignedBigInteger('target_face_size_id')->nullable()->after('target_face_id');

            $table->foreign('division_id')
                ->references('id')
                ->on('divisions')
                ->onDelete('set null');

            $table->foreign('classification_id')
                ->references('id')
                ->on('classifications')
                ->onDelete('set null');

            $table->foreign('distance_id')
                ->references('id')
                ->on('distances')
                ->onDelete('set null');

            $table->foreign('target_face_id')
                ->references('id')
                ->on('target_faces')
                ->onDelete('set null');

            $table->foreign('target_face_size_id')
                ->references('id')
                ->on('target_face_sizes')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('scoring_templates', function (Blueprint $table) {
            $table->dropForeign(['division_id']);
            $table->dropForeign(['classification_id']);
            $table->dropForeign(['distance_id']);
            $table->dropForeign(['target_face_id']);
            $table->dropForeign(['target_face_size_id']);

            $table->dropColumn([
                'division_id',
                'classification_id',
                'distance_id',
                'target_face_id',
                'target_face_size_id',
            ]);
        });
    }
};

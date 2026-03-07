<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Equipment maintenance log — records checks, repairs, and replacements
 * for archer equipment setups.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_maintenances', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('equipment_setup_id');
            $table->enum('type', ['check', 'repair', 'replacement', 'tuning', 'cleaning'])->default('check');
            $table->string('description');
            $table->text('details')->nullable();
            $table->decimal('cost', 8, 2)->nullable();
            $table->date('performed_at');
            $table->date('next_due_at')->nullable();

            // performed_by is a free-text name (could be a shop/person)
            $table->string('performed_by')->nullable();

            $table->timestamps();

            $table->foreign('equipment_setup_id')->references('id')->on('equipment_setups')->cascadeOnDelete();
            $table->index(['equipment_setup_id', 'performed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenances');
    }
};

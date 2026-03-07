<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tenant DB Migration — equipment_setups table
     * Tracks bow and arrow configuration per archer.
     */
    public function up(): void
    {
        Schema::create('equipment_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('archer_id')->constrained('archers')->cascadeOnDelete();
            $table->string('name');                              // e.g. "Indoor recurve"
            $table->string('bow_type');                         // Recurve | Compound | Barebow | Longbow
            $table->string('bow_brand')->nullable();
            $table->string('bow_model')->nullable();
            $table->decimal('draw_weight_lbs', 5, 1)->nullable();
            $table->decimal('draw_length_inches', 4, 1)->nullable();
            $table->string('arrow_brand')->nullable();
            $table->string('arrow_model')->nullable();
            $table->unsignedSmallInteger('arrow_spine')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->index(['archer_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_setups');
    }
};

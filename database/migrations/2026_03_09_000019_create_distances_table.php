<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distances', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedSmallInteger('meters'); // 5, 10, 18, 70, 90, etc.
            $table->timestamps();

            $table->unique('meters');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distances');
    }
};

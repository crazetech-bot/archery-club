<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('archers', function (Blueprint $table) {
            $table->string('name')->nullable()->after('user_id');
            $table->string('gender', 20)->nullable()->after('name');         // male | female | other
            $table->string('bow_type', 30)->nullable()->after('gender');      // Recurve | Compound | Barebow | Longbow
            $table->string('experience_level', 30)->nullable()->after('bow_type'); // Beginner | Intermediate | Advanced | Elite
            $table->boolean('is_active')->default(true)->after('phone');
        });
    }

    public function down(): void
    {
        Schema::table('archers', function (Blueprint $table) {
            $table->dropColumn(['name', 'gender', 'bow_type', 'experience_level', 'is_active']);
        });
    }
};

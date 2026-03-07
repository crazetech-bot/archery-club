<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central DB Migration — tenants table
     * Stores all registered clubs (tenants) on the platform.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('db_name')->unique();
            $table->string('db_host')->default('127.0.0.1');
            $table->string('db_username');
            $table->string('db_password'); // Encrypted via Laravel Encrypter
            $table->string('plan')->default('free'); // e.g. free, pro, enterprise
            $table->enum('status', ['active', 'suspended', 'cancelled'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};

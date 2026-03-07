<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Central DB Migration — tenant_billing table
     * Internal billing state per tenant (plan, status, renewal date).
     * Cashier handles the actual Stripe subscription records in its own tables.
     */
    public function up(): void
    {
        Schema::create('tenant_billing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->string('plan')->default('free');
            $table->enum('status', [
                'active',
                'trialing',
                'past_due',
                'cancelled',
                'incomplete',
            ])->default('active');
            $table->timestamp('renews_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_billing');
    }
};

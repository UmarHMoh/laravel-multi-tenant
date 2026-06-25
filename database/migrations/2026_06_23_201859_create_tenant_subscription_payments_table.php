<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_subscription_payments', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreignId('tenant_subscription_id')
                ->nullable()
                ->constrained('tenant_subscriptions')
                ->nullOnDelete();

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained('plans')
                ->nullOnDelete();

            $table->decimal('amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('USD');

            $table->date('paid_at');
            $table->date('previous_renews_at')->nullable();
            $table->date('new_renews_at')->nullable();

            $table->string('payment_method')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'paid_at']);
            $table->index(['tenant_id', 'new_renews_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_subscription_payments');
    }
};

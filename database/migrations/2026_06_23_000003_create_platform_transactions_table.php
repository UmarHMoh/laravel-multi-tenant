<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_transactions', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('tenant_order_id')->nullable();

            $table->string('provider')->default('manual');
            $table->string('provider_transaction_id')->nullable();

            $table->string('currency', 3)->default('USD');

            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('processor_fee', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            $table->decimal('commission_rate', 5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);

            $table->decimal('tenant_payout_amount', 12, 2)->default(0);

            $table->string('payment_status')->default('paid');
            $table->string('payout_status')->default('pending');

            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payout_marked_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'payout_status']);
            $table->index(['tenant_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_transactions');
    }
};

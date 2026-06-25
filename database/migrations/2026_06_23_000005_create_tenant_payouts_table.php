<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payouts', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreignId('tenant_payout_account_id')
                ->nullable()
                ->constrained('tenant_payout_accounts')
                ->nullOnDelete();

            $table->string('currency', 3)->default('USD');

            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('total_processor_fees', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->decimal('total_commission', 12, 2)->default(0);
            $table->decimal('total_payout', 12, 2)->default(0);

            $table->string('status')->default('paid'); // pending, paid, cancelled
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payouts');
    }
};

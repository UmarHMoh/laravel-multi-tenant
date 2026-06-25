<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_payout_ledger_entries')) {
            return;
        }

        Schema::create('tenant_payout_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('platform_transaction_id')->nullable()->constrained('platform_transactions')->nullOnDelete();

            $table->string('entry_type')->default('credit'); // credit, debit, adjustment
            $table->string('source')->default('paid_order'); // paid_order, payout, adjustment
            $table->string('status')->default('available'); // pending, available, paid, cancelled

            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('processor_fee_amount', 12, 2)->default(0);
            $table->decimal('platform_fee_amount', 12, 2)->default(0);
            $table->decimal('tenant_net_amount', 12, 2)->default(0);

            $table->string('currency', 3)->default('TTD');
            $table->string('reference')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamp('available_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'entry_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payout_ledger_entries');
    }
};

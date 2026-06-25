<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payout_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('tenant_payout_id')
                ->constrained('tenant_payouts')
                ->cascadeOnDelete();

            $table->foreignId('platform_transaction_id')
                ->constrained('platform_transactions')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['tenant_payout_id', 'platform_transaction_id'], 'payout_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payout_items');
    }
};

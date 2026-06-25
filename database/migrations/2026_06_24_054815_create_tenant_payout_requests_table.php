<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_payout_requests')) {
            return;
        }

        Schema::create('tenant_payout_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('tenant_payout_account_id')->nullable()->constrained('tenant_payout_accounts')->nullOnDelete();
            $table->foreignId('tenant_payout_ledger_entry_id')->nullable()->constrained('tenant_payout_ledger_entries')->nullOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TTD');
            $table->string('status')->default('requested'); // requested, approved, rejected, paid, cancelled

            $table->string('reference')->nullable();
            $table->text('tenant_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payout_requests');
    }
};

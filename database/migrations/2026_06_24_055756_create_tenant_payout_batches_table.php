<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tenant_payout_batches')) {
            return;
        }

        Schema::create('tenant_payout_batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_number')->unique();
            $table->string('status')->default('draft'); // draft, approved, paid, cancelled

            $table->unsignedInteger('request_count')->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('currency', 3)->default('TTD');

            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::table('tenant_payout_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_payout_requests', 'tenant_payout_batch_id')) {
                $table->foreignId('tenant_payout_batch_id')
                    ->nullable()
                    ->after('tenant_payout_ledger_entry_id')
                    ->constrained('tenant_payout_batches')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('tenant_payout_requests') && Schema::hasColumn('tenant_payout_requests', 'tenant_payout_batch_id')) {
            Schema::table('tenant_payout_requests', function (Blueprint $table) {
                try {
                    $table->dropConstrainedForeignId('tenant_payout_batch_id');
                } catch (\Throwable $e) {
                    $table->dropColumn('tenant_payout_batch_id');
                }
            });
        }

        Schema::dropIfExists('tenant_payout_batches');
    }
};

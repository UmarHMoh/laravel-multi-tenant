<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_subscription_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_subscription_payments', 'status')) {
                $table->string('status')->default('paid')->after('currency');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'source')) {
                $table->string('source')->default('manual_admin')->after('status');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'provider')) {
                $table->string('provider')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'provider_transaction_id')) {
                $table->string('provider_transaction_id')->nullable()->after('provider');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'payer_name')) {
                $table->string('payer_name')->nullable()->after('provider_transaction_id');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'payer_email')) {
                $table->string('payer_email')->nullable()->after('payer_name');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'card_brand')) {
                $table->string('card_brand')->nullable()->after('payer_email');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'card_last4')) {
                $table->string('card_last4', 4)->nullable()->after('card_brand');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'receipt_url')) {
                $table->string('receipt_url')->nullable()->after('card_last4');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'recorded_by')) {
                $table->string('recorded_by')->nullable()->after('receipt_url');
            }

            if (! Schema::hasColumn('tenant_subscription_payments', 'metadata')) {
                $table->json('metadata')->nullable()->after('notes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_subscription_payments', function (Blueprint $table) {
            $columns = [
                'status',
                'source',
                'provider',
                'provider_transaction_id',
                'payer_name',
                'payer_email',
                'card_brand',
                'card_last4',
                'receipt_url',
                'recorded_by',
                'metadata',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('tenant_subscription_payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('platform_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_transactions', 'processor_fee_percent')) {
                $table->decimal('processor_fee_percent', 8, 4)->default(0)->after('processor_fee');
            }

            if (! Schema::hasColumn('platform_transactions', 'processor_fee_fixed')) {
                $table->decimal('processor_fee_fixed', 10, 2)->default(0)->after('processor_fee_percent');
            }

            if (! Schema::hasColumn('platform_transactions', 'tenant_transaction_fee_percent')) {
                $table->decimal('tenant_transaction_fee_percent', 8, 4)->default(0)->after('commission_amount');
            }

            if (! Schema::hasColumn('platform_transactions', 'tenant_transaction_fee_fixed')) {
                $table->decimal('tenant_transaction_fee_fixed', 10, 2)->default(0)->after('tenant_transaction_fee_percent');
            }

            if (! Schema::hasColumn('platform_transactions', 'total_tenant_fee_amount')) {
                $table->decimal('total_tenant_fee_amount', 10, 2)->default(0)->after('tenant_transaction_fee_fixed');
            }

            if (! Schema::hasColumn('platform_transactions', 'platform_fee_percent')) {
                $table->decimal('platform_fee_percent', 8, 4)->default(0)->after('total_tenant_fee_amount');
            }

            if (! Schema::hasColumn('platform_transactions', 'platform_fee_fixed')) {
                $table->decimal('platform_fee_fixed', 10, 2)->default(0)->after('platform_fee_percent');
            }

            if (! Schema::hasColumn('platform_transactions', 'platform_fee_amount')) {
                $table->decimal('platform_fee_amount', 10, 2)->default(0)->after('platform_fee_fixed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('platform_transactions', function (Blueprint $table) {
            foreach ([
                'processor_fee_percent',
                'processor_fee_fixed',
                'tenant_transaction_fee_percent',
                'tenant_transaction_fee_fixed',
                'total_tenant_fee_amount',
                'platform_fee_percent',
                'platform_fee_fixed',
                'platform_fee_amount',
            ] as $column) {
                if (Schema::hasColumn('platform_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

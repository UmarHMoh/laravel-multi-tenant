<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'payment_provider')) {
                $table->string('payment_provider')->nullable()->after('payment_method');
            }

            if (! Schema::hasColumn('orders', 'provider_payment_id')) {
                $table->string('provider_payment_id')->nullable()->after('payment_provider');
            }

            if (! Schema::hasColumn('orders', 'provider_checkout_url')) {
                $table->text('provider_checkout_url')->nullable()->after('provider_payment_id');
            }

            if (! Schema::hasColumn('orders', 'provider_reference')) {
                $table->string('provider_reference')->nullable()->after('provider_checkout_url');
            }

            if (! Schema::hasColumn('orders', 'payment_metadata')) {
                $table->json('payment_metadata')->nullable()->after('provider_reference');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('orders')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'payment_provider',
                'provider_payment_id',
                'provider_checkout_url',
                'provider_reference',
                'payment_metadata',
            ] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

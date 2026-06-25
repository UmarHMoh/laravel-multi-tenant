<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('platform_transactions')) {
            return;
        }

        Schema::table('platform_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('platform_transactions', 'idempotency_key')) {
                $table->string('idempotency_key')->nullable()->after('provider_transaction_id');
            }

            if (! Schema::hasColumn('platform_transactions', 'source_event')) {
                $table->string('source_event')->nullable()->after('idempotency_key');
            }

            if (! Schema::hasColumn('platform_transactions', 'recorded_at')) {
                $table->timestamp('recorded_at')->nullable()->after('source_event');
            }
        });

        try {
            Schema::table('platform_transactions', function (Blueprint $table) {
                $table->unique('idempotency_key', 'platform_transactions_idempotency_key_unique');
            });
        } catch (\Throwable $e) {
            // Index may already exist in some local/dev databases.
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('platform_transactions')) {
            return;
        }

        Schema::table('platform_transactions', function (Blueprint $table) {
            try {
                $table->dropUnique('platform_transactions_idempotency_key_unique');
            } catch (\Throwable $e) {
            }

            foreach (['recorded_at', 'source_event', 'idempotency_key'] as $column) {
                if (Schema::hasColumn('platform_transactions', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('payment_settings', 'name')) {
                $table->string('name')->nullable()->after('id');
            }

            if (! Schema::hasColumn('payment_settings', 'config_type')) {
                $table->string('config_type')->default('saved')->after('name');
            }

            if (! Schema::hasColumn('payment_settings', 'auth_type')) {
                $table->string('auth_type')->nullable()->after('fee_structure');
            }

            if (! Schema::hasColumn('payment_settings', 'base_url')) {
                $table->string('base_url')->nullable()->after('auth_type');
            }

            if (! Schema::hasColumn('payment_settings', 'checkout_endpoint')) {
                $table->string('checkout_endpoint')->nullable()->after('base_url');
            }

            if (! Schema::hasColumn('payment_settings', 'verify_endpoint')) {
                $table->string('verify_endpoint')->nullable()->after('checkout_endpoint');
            }

            if (! Schema::hasColumn('payment_settings', 'refund_endpoint')) {
                $table->string('refund_endpoint')->nullable()->after('verify_endpoint');
            }

            if (! Schema::hasColumn('payment_settings', 'payout_endpoint')) {
                $table->string('payout_endpoint')->nullable()->after('refund_endpoint');
            }

            if (! Schema::hasColumn('payment_settings', 'token_endpoint')) {
                $table->string('token_endpoint')->nullable()->after('payout_endpoint');
            }

            if (! Schema::hasColumn('payment_settings', 'public_key')) {
                $table->text('public_key')->nullable()->after('api_key');
            }

            if (! Schema::hasColumn('payment_settings', 'secret_key')) {
                $table->text('secret_key')->nullable()->after('public_key');
            }

            if (! Schema::hasColumn('payment_settings', 'private_key')) {
                $table->text('private_key')->nullable()->after('secret_key');
            }

            if (! Schema::hasColumn('payment_settings', 'webhook_secret')) {
                $table->text('webhook_secret')->nullable()->after('private_key');
            }

            if (! Schema::hasColumn('payment_settings', 'merchant_id')) {
                $table->string('merchant_id')->nullable()->after('webhook_secret');
            }

            if (! Schema::hasColumn('payment_settings', 'account_id')) {
                $table->string('account_id')->nullable()->after('merchant_id');
            }

            if (! Schema::hasColumn('payment_settings', 'username')) {
                $table->string('username')->nullable()->after('account_id');
            }

            if (! Schema::hasColumn('payment_settings', 'password')) {
                $table->text('password')->nullable()->after('username');
            }

            if (! Schema::hasColumn('payment_settings', 'bearer_token')) {
                $table->text('bearer_token')->nullable()->after('password');
            }

            if (! Schema::hasColumn('payment_settings', 'headers_template')) {
                $table->json('headers_template')->nullable()->after('bearer_token');
            }

            if (! Schema::hasColumn('payment_settings', 'request_template')) {
                $table->json('request_template')->nullable()->after('headers_template');
            }

            if (! Schema::hasColumn('payment_settings', 'response_mapping')) {
                $table->json('response_mapping')->nullable()->after('request_template');
            }

            if (! Schema::hasColumn('payment_settings', 'enabled_at')) {
                $table->timestamp('enabled_at')->nullable()->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_settings', function (Blueprint $table) {
            foreach ([
                'name',
                'config_type',
                'auth_type',
                'base_url',
                'checkout_endpoint',
                'verify_endpoint',
                'refund_endpoint',
                'payout_endpoint',
                'token_endpoint',
                'public_key',
                'secret_key',
                'private_key',
                'webhook_secret',
                'merchant_id',
                'account_id',
                'username',
                'password',
                'bearer_token',
                'headers_template',
                'request_template',
                'response_mapping',
                'enabled_at',
            ] as $column) {
                if (Schema::hasColumn('payment_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

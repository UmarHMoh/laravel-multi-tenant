<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'transaction_fee_percent')) {
                $table->decimal('transaction_fee_percent', 8, 4)->default(0)->after('commission_rate');
            }

            if (! Schema::hasColumn('plans', 'transaction_fee_fixed')) {
                $table->decimal('transaction_fee_fixed', 10, 2)->default(0)->after('transaction_fee_percent');
            }
        });

        DB::table('plans')->update([
            'transaction_fee_percent' => DB::raw('commission_rate'),
            'transaction_fee_fixed' => 0,
        ]);
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'transaction_fee_percent')) {
                $table->dropColumn('transaction_fee_percent');
            }

            if (Schema::hasColumn('plans', 'transaction_fee_fixed')) {
                $table->dropColumn('transaction_fee_fixed');
            }
        });
    }
};

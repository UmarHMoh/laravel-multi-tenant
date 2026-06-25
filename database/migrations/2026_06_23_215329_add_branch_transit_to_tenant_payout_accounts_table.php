<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_payout_accounts', 'branch_transit_number')) {
                $table->string('branch_transit_number')->nullable()->after('bank_account_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            if (Schema::hasColumn('tenant_payout_accounts', 'branch_transit_number')) {
                $table->dropColumn('branch_transit_number');
            }
        });
    }
};

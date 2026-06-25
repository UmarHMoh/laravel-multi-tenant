<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            if (! Schema::hasColumn('tenant_payout_accounts', 'review_notes')) {
                $table->text('review_notes')->nullable()->after('notes');
            }

            if (! Schema::hasColumn('tenant_payout_accounts', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable()->after('review_notes');
            }

            if (! Schema::hasColumn('tenant_payout_accounts', 'reviewed_by')) {
                $table->string('reviewed_by')->nullable()->after('reviewed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_payout_accounts', function (Blueprint $table) {
            foreach (['review_notes', 'reviewed_at', 'reviewed_by'] as $column) {
                if (Schema::hasColumn('tenant_payout_accounts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

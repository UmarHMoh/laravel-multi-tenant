<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_payout_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->string('type')->default('bank'); // bank, wipay, other
            $table->string('account_holder_name')->nullable();

            $table->string('bank_name')->nullable();
            $table->text('bank_account_number')->nullable();
            $table->string('bank_account_type')->nullable();

            $table->text('wipay_account_email')->nullable();

            $table->string('status')->default('pending'); // pending, verified, rejected
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_payout_accounts');
    }
};

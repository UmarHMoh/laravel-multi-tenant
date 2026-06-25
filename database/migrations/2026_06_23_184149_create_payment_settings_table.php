<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('wipay');
            $table->string('environment')->default('sandbox'); // sandbox or live
            $table->string('country_code')->default('TT');
            $table->string('currency')->default('TTD');

            $table->string('account_number')->nullable();
            $table->text('api_key')->nullable();

            $table->string('fee_structure')->default('merchant_absorb');
            $table->decimal('processor_fee_percent', 8, 4)->default(3.5000);
            $table->decimal('processor_fee_fixed', 10, 2)->default(0.30);

            $table->boolean('is_active')->default(false);
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};

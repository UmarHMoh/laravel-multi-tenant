<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();

            $table->string('platform_name')->default('Central Admin');
            $table->string('owner_email')->nullable();

            $table->string('subscription_currency', 3)->default('TTD');
            $table->unsignedInteger('renewal_grace_days')->default(0);
            $table->boolean('auto_deactivate_overdue_tenants')->default(true);

            $table->string('login_heading')->default('Central Admin Login');
            $table->text('login_subheading')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
    }
};

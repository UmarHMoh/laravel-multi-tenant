
<?php

use Illuminate\Database\Migrations\Migration;

use Illuminate\Database\Schema\Blueprint;

use Illuminate\Support\Facades\Schema;

return new class extends Migration

{

    public function up(): void

    {

        Schema::create('plans', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            $table->decimal('monthly_price', 10, 2)->default(0);

            $table->decimal('commission_rate', 5, 2)->default(0);

            $table->integer('max_products')->nullable();

            $table->boolean('custom_domain_enabled')->default(false);

            $table->boolean('api_payment_enabled')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

        });

    }

    public function down(): void

    {

        Schema::dropIfExists('plans');

    }

};


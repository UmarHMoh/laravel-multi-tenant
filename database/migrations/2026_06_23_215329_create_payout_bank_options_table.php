<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_bank_options', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $banks = [
            'ANSA Bank',
            'CIBC Caribbean',
            'Citibank Trinidad & Tobago',
            'First Citizens Bank',
            'JMMB Bank',
            'RBC Royal Bank',
            'Republic Bank',
            'Scotiabank Trinidad and Tobago',
        ];

        foreach ($banks as $bank) {
            DB::table('payout_bank_options')->insert([
                'name' => $bank,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_bank_options');
    }
};

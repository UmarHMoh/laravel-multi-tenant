<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'allow_multiple_builder_pages')) {
                $table->boolean('allow_multiple_builder_pages')
                    ->default(false)
                    ->after('api_payment_enabled');
            }
        });

        if (Schema::hasColumn('plans', 'slug')) {
            DB::table('plans')
                ->whereIn('slug', ['pro', 'premium', 'business', 'enterprise'])
                ->update(['allow_multiple_builder_pages' => true]);
        }
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (Schema::hasColumn('plans', 'allow_multiple_builder_pages')) {
                $table->dropColumn('allow_multiple_builder_pages');
            }
        });
    }
};

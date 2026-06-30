<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('theme_pages')) {
            return;
        }

        if (! Schema::hasColumn('theme_pages', 'product_id')) {
            Schema::table('theme_pages', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->after('type')->index();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('theme_pages') || ! Schema::hasColumn('theme_pages', 'product_id')) {
            return;
        }

        Schema::table('theme_pages', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};

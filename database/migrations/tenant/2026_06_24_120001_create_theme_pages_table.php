<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('theme_id')->constrained('themes')->cascadeOnDelete();
            $table->string('handle');
            $table->string('title');
            $table->string('type')->default('static');
            $table->string('template')->default('default');
            $table->json('draft_config')->nullable();
            $table->json('published_config')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['theme_id', 'handle']);
            $table->index(['type', 'handle']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_pages');
    }
};

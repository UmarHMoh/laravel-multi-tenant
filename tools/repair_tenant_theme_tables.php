<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Tenant;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

foreach (Tenant::all() as $tenant) {
    tenancy()->initialize($tenant);

    echo "Repairing tenant {$tenant->id}..." . PHP_EOL;

    if (! Schema::hasTable('themes')) {
        Schema::create('themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(false);
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
        });

        echo "  created themes table" . PHP_EOL;
    } else {
        echo "  themes table already exists" . PHP_EOL;
    }

    if (! Schema::hasTable('theme_pages')) {
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

        echo "  created theme_pages table" . PHP_EOL;
    } else {
        echo "  theme_pages table already exists" . PHP_EOL;
    }

    tenancy()->end();
}

echo "Tenant theme table repair complete." . PHP_EOL;

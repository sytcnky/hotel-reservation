<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_guides', function (Blueprint $table) {
            $table->bigIncrements('id');

            // i18n
            $table->jsonb('title');          // {tr: "...", en: "...", ...}
            $table->jsonb('excerpt')->nullable();
            $table->jsonb('slug')->nullable(); // {tr: "....", en: "....", ...}

            // Canonical routing key (unique)
            $table->string('canonical_slug')->unique();

            // Tags (string list)
            $table->jsonb('tags')->default('[]');

            // Builder content (blocks array)
            $table->jsonb('content')->default('[]');

            // Sidebar tour ids (list)
            $table->jsonb('sidebar_tour_ids')->default('[]');

            // Status
            $table->boolean('is_active')->default(true);
            $table->timestampTz('published_at')->nullable();

            // Sorting
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order'], 'travel_guides_is_active_sort_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_guides');
    }
};

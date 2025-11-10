<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            // eski unique(slug) kaldır
            $table->dropUnique('locations_slug_unique');

            // yeni benzersizlik: parent_id + slug
            $table->unique(['parent_id', 'slug'], 'locations_parent_slug_unique');

            // prefix aramalar için normal index
            $table->index('slug', 'locations_slug_idx');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->dropIndex('locations_slug_idx');
            $table->dropUnique('locations_parent_slug_unique');
            $table->unique('slug', 'locations_slug_unique');
        });
    }
};

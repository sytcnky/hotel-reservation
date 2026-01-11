<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('travel_guides', function (Blueprint $table) {
            if (Schema::hasColumn('travel_guides', 'canonical_slug')) {
                $table->dropColumn('canonical_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('travel_guides', function (Blueprint $table) {
            if (! Schema::hasColumn('travel_guides', 'canonical_slug')) {
                // Eski davranışa dönmek gerekirse. Burada default vermeden NOT NULL yapmayalım.
                $table->string('canonical_slug')->nullable()->after('slug');
            }
        });
    }
};

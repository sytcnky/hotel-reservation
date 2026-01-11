<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            if (Schema::hasColumn('villas', 'canonical_slug')) {
                $table->dropColumn('canonical_slug');
            }
        });
    }

    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            if (! Schema::hasColumn('villas', 'canonical_slug')) {
                $table->string('canonical_slug')->nullable()->index();
            }
        });
    }
};

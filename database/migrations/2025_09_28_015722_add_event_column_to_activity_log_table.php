<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if (! Schema::hasColumn('activity_log', 'event')) {
                $table->string('event')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('activity_log')) {
            return;
        }

        Schema::table('activity_log', function (Blueprint $table) {
            if (Schema::hasColumn('activity_log', 'event')) {
                $table->dropColumn('event');
            }
        });
    }
};

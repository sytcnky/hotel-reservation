<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('villa_rate_rules', function (Blueprint $table) {
            $table->unsignedInteger('min_nights')->nullable()->after('amount');
            $table->unsignedInteger('max_nights')->nullable()->after('min_nights');
        });
    }

    public function down(): void
    {
        Schema::table('villa_rate_rules', function (Blueprint $table) {
            $table->dropColumn(['min_nights', 'max_nights']);
        });
    }
};

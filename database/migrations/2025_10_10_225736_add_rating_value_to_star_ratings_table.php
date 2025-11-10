<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('star_ratings', function (Blueprint $table) {
            $table->smallInteger('rating_value')->default(0)->index()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('star_ratings', function (Blueprint $table) {
            $table->dropColumn('rating_value');
        });
    }
};

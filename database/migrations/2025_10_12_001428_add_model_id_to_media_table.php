<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            if (! Schema::hasColumn('media', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
                $table->index(['model_type', 'model_id'], 'media_model_type_model_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('media_model_type_model_id_index');
            $table->dropColumn('model_id');
        });
    }
};

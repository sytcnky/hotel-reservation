<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('feature_group_facility')) {
            Schema::create('feature_group_facility', function (Blueprint $table) {
                $table->foreignId('feature_group_id')
                    ->constrained('hotel_feature_groups')->cascadeOnDelete();
                $table->foreignId('facility_id')
                    ->constrained('facilities')->cascadeOnDelete();
                $table->primary(['feature_group_id', 'facility_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_group_facility');
    }
};

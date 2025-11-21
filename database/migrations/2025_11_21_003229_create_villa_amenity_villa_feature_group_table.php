<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_amenity_villa_feature_group', function (Blueprint $table) {
            $table->id();

            $table->foreignId('villa_feature_group_id')
                ->constrained('villa_feature_groups')
                ->cascadeOnDelete();

            $table->foreignId('villa_amenity_id')
                ->constrained('villa_amenities')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_amenity_villa_feature_group');
    }
};

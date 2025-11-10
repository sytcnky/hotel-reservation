<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_feature_group_facility', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_feature_group_id')->constrained('hotel_feature_groups')->cascadeOnDelete();
            $table->foreignId('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_feature_group_id', 'facility_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_feature_group_facility');
    }
};

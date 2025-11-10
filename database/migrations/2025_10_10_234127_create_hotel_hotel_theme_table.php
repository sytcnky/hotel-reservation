<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_hotel_theme', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();
            $table->foreignId('hotel_theme_id')->constrained('hotel_themes')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['hotel_id', 'hotel_theme_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_hotel_theme');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_categories', function (Blueprint $table) {
            $table->id();
            // i18n alanlar (PG'de json/jsonb)
            $table->json('name');         // { "tr": "Butik Otel", "en": "Boutique Hotel" }
            $table->json('slug');         // { "tr": "butik-otel", "en": "boutique-hotel" }
            $table->json('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_categories');
    }
};

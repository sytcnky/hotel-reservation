<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_feature_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('villa_id')
                ->constrained('villas')
                ->cascadeOnDelete();

            // Grup başlığı (i18n)
            $table->jsonb('title')->default(json_encode([]));

            // Sıralama
            $table->integer('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_feature_groups');
    }
};

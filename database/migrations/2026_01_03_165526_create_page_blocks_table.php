<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_blocks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('static_page_id')
                ->constrained('static_pages')
                ->cascadeOnDelete();

            // promo | collection (ileride başka tipler de gelebilir)
            $table->string('type');

            // Blok datası (promo içerikleri, collection kuralları, ui ayarları vs.)
            $table->jsonb('data')->default('{}');

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->index(['static_page_id', 'type']);
            $table->index(['static_page_id', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};

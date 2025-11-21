<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villas', function (Blueprint $table) {
            $table->id();

            // Kod (ör: VIL-000001)
            $table->string('code', 16)->unique();

            // İsim / Slug / Canonical
            $table->jsonb('name')->nullable();
            $table->jsonb('slug')->nullable();
            $table->string('canonical_slug', 191)->unique();

            // İçerik
            $table->jsonb('description')->nullable();
            $table->jsonb('highlights')->nullable();     // Öne çıkan özellikler
            $table->jsonb('stay_info')->nullable();      // Konaklama hakkında

            // Kapasiteler
            $table->unsignedSmallInteger('max_guests');
            $table->unsignedTinyInteger('bedroom_count');
            $table->unsignedTinyInteger('bathroom_count');

            // Sınıflandırma
            $table->foreignId('villa_category_id')
                ->nullable()
                ->constrained('villa_categories')
                ->nullOnDelete();

            $table->foreignId('cancellation_policy_id')
                ->nullable()
                ->constrained('cancellation_policies')
                ->nullOnDelete();

            // Konum
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();

            $table->string('address_line', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->jsonb('nearby')->nullable();

            // İletişim
            $table->string('phone', 32)->nullable();
            $table->string('email', 191)->nullable();

            // Medya ilişkili alan
            $table->string('promo_video_id', 64)->nullable();

            // Durum & sıralama
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            // Tarihler
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villas');
    }
};

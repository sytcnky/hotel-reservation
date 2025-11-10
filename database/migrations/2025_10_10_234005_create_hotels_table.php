<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotels', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->nullable();
            $table->jsonb('name');
            $table->string('slug')->unique();
            $table->jsonb('description')->nullable();

            $table->foreignId('star_rating_id')->nullable()->constrained('star_ratings')->nullOnDelete();
            $table->foreignId('hotel_category_id')->nullable()->constrained('hotel_categories')->nullOnDelete();
            $table->foreignId('board_type_id')->nullable()->constrained('board_types')->nullOnDelete();
            $table->foreignId('beach_type_id')->nullable()->constrained('beach_types')->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            // Location
            $table->string('province_slug', 64)->nullable();
            $table->string('district_slug', 64)->nullable();
            $table->string('area_slug', 64)->nullable();
            $table->string('address_line')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Policies and Notes
            $table->jsonb('policies')->nullable();
            $table->jsonb('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index(['province_slug', 'district_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotels');
    }
};

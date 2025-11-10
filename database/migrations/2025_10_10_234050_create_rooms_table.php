<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->cascadeOnDelete();

            $table->jsonb('name');
            $table->string('slug')->unique();

            $table->smallInteger('capacity_adults')->default(0);
            $table->smallInteger('capacity_children')->default(0);
            $table->smallInteger('capacity_infants')->default(0);
            $table->smallInteger('size_m2')->nullable();

            $table->string('bed_type')->nullable();
            $table->boolean('smoking')->default(false);

            $table->foreignId('view_type_id')->nullable()->constrained('view_types')->nullOnDelete();

            $table->jsonb('description')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['hotel_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};

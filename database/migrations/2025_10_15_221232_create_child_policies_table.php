<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('child_policies', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('board_type_id')->nullable()->constrained()->nullOnDelete();

            $table->unsignedTinyInteger('age_min'); // kapalı aralık
            $table->unsignedTinyInteger('age_max');

            // free | percent | fixed
            $table->string('charge_type', 16);
            // percent: 0–100, fixed: para biriminin ölçeği (exponent) ile aynı
            $table->decimal('value', 12, 4)->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('note', 255)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Sorgu performansı
            $table->index(['hotel_id', 'room_id', 'board_type_id']);
            $table->index(['age_min', 'age_max']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_policies');
    }
};


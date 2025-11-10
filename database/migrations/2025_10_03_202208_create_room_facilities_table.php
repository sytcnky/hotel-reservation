<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_facilities', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name')->default(json_encode([]));
            $table->jsonb('slug')->default(json_encode([]));
            $table->jsonb('description')->default(json_encode([]));
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_facilities');
    }
};

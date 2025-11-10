<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name')->default(json_encode([]));
            $table->jsonb('slug')->default(json_encode([]));
            $table->jsonb('description')->default(json_encode([]));
            $table->string('code', 3)->unique();     // ISO 4217 (TRY, EUR, USD...)
            $table->string('symbol', 8)->nullable(); // ₺, €, $, £ ...
            $table->tinyInteger('exponent')->default(2); // Kuruş hanesi (JPY=0 gibi)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->enum('type', ['country', 'province', 'district', 'area'])->index();
            $table->string('code', 16)->nullable()->index();
            $table->string('name');             // Özel isim: sadece Türkçe
            $table->string('slug')->unique();   // ASCII karşılığı, URL/arama için
            $table->string('path')->nullable(); // İleri seviye arama/filtre için (örn: tr/mugla/marmaris/icmeler)
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->index(['type', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};

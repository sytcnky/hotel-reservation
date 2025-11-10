<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 8)->unique();      // tr, en, de, ru
            $table->string('locale', 16)->nullable(); // tr_TR, en_GB, en_US
            $table->string('name');                   // Türkçe, English
            $table->string('native_name')->nullable();// Native ad (optional)
            $table->string('flag')->nullable();       // icon class veya dosya adı
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};

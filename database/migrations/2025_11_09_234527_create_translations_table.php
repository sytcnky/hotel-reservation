<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();

            // Örn: nav, footer, hero, general, emails...
            $table->string('group', 100);

            // Örn: home, hotels, footer_home, hero_title...
            $table->string('key', 150);

            // locale => value map (tr, en, de...)
            $table->json('value')->nullable();

            $table->timestamps();

            $table->unique(['group', 'key']);
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};

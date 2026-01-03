<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) travel_guides: eski JSON builder alanını kaldır
        Schema::table('travel_guides', function (Blueprint $table) {
            if (Schema::hasColumn('travel_guides', 'content')) {
                $table->dropColumn('content');
            }
        });

        // 2) yeni blocks tablosu
        Schema::create('travel_guide_blocks', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->foreignId('travel_guide_id')
                ->constrained('travel_guides')
                ->cascadeOnDelete();

            // content_section | recommendation
            $table->string('type', 32);

            // sıralama
            $table->integer('sort_order')->default(0);

            // block payload
            $table->jsonb('data')->default(DB::raw("'{}'::jsonb"));

            $table->timestamps();
            $table->softDeletes();

            $table->index(['travel_guide_id', 'sort_order']);
            $table->index(['type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_guide_blocks');

        // geri dönüş (opsiyonel): content alanını tekrar ekle
        Schema::table('travel_guides', function (Blueprint $table) {
            if (! Schema::hasColumn('travel_guides', 'content')) {
                $table->jsonb('content')->nullable();
            }
        });
    }
};

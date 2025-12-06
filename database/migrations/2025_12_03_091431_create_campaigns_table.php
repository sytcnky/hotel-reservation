<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();

            // Genel durum
            $table->boolean('is_active')->default(true);

            // Geçerlilik tarihleri (tüm gün için geçerli kabul ediyoruz)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            // Öncelik (yüksek sayı = yüksek öncelik gibi kullanabiliriz)
            $table->integer('priority')->default(0);

            // Kullanım limitleri
            $table->unsignedInteger('global_usage_limit')->nullable();
            $table->unsignedInteger('user_usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);

            // İçerik (dinamik diller)
            // content = [ 'tr' => ['title' => ..., 'subtitle' => ...], 'en' => [...], ... ]
            $table->jsonb('content');

            // İndirim tanımı
            // discount = ['type' => 'percent'|'fixed_amount', 'value' => 10, 'max_discount_amount' => 500]
            $table->jsonb('discount');

            // Koşullar
            // conditions = sepet/ürün/kullanıcı/tarih/cihaz koşullarının tamamı
            $table->jsonb('conditions')->nullable();

            // Placement bilgileri
            // placements = ['homepage_hero', 'listing_top', ...]
            $table->jsonb('placements')->nullable();

            // Görünürlük
            $table->boolean('visible_on_web')->default(true);
            $table->boolean('visible_on_mobile')->default(true);

            // Soft delete + timestamps
            $table->softDeletes();
            $table->timestamps();

            // Bazı yardımcı index'ler
            $table->index(['is_active', 'start_date', 'end_date']);
            $table->index(['priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};

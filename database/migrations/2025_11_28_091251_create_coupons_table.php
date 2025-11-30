<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();

            // Durum
            $table->boolean('is_active')->default(true);

            // Kod (opsiyonel)
            $table->string('code')->nullable();

            // Çoklu dil alanları
            $table->json('title');
            $table->json('description')->nullable();
            $table->json('badge_main')->nullable();
            $table->json('badge_label')->nullable();

            // Tarihler
            $table->dateTime('valid_from');
            $table->dateTime('valid_until')->nullable();

            // Kullanım mantığı
            $table->boolean('is_exclusive')->default(false); // Sepette tek başına mı?
            $table->integer('max_uses_per_user')->nullable();

            // İndirim tipi
            $table->enum('discount_type', ['percent', 'amount']);

            // Percent için global değer
            $table->decimal('percent_value', 8, 2)->nullable();

            // Scope
            $table->enum('scope_type', ['order_total', 'product_type', 'product']);

            // product_type (çoklu)
            $table->json('product_types')->nullable();

            // product (tekil)
            $table->string('product_domain')->nullable(); // hotel | villa | tour | transfer
            $table->unsignedBigInteger('product_id')->nullable();

            // Gece koşulu
            $table->integer('min_nights')->nullable();

            // Para birimi alanları toplu JSON
            // Örnek içerik: { "TRY": { "amount": 500, "min_booking_amount": 3000 }, "EUR": {...} }
            $table->json('currency_data')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};

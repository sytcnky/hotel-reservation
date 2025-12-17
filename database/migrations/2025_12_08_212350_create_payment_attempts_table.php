<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();

            // Sipariş ilişkisi
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Tutar bilgisi
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);

            // Ödeme durumu (pending / success / failed / cancelled / timeout vs.)
            $table->string('status', 50)->default('pending');

            // Kullanılan sanal pos / gateway (ör: isbank, test, etc.)
            $table->string('gateway', 50);

            // Banka / gateway referansı (işlem numarası)
            $table->string('gateway_reference', 191)->nullable();

            // Hata bilgileri
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            // İsteğe bağlı extra bilgiler (bankadan gelen ham response, 3D data vs.)
            $table->json('meta')->nullable();

            // İzleme
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
            $table->softDeletes(); // "soft model" notunu burada uyguluyoruz
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};

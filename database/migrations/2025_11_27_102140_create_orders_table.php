<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // İnsan okunur sipariş kodu: ORD-000001
            $table->string('code', 32)->unique();

            // Kullanıcı (guest siparişleri için nullable)
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // İş akışı durumu (genel)
            // Örn: pending, confirmed, cancelled
            $table->string('status', 32)->default('pending');

            // Ödeme durumu
            // Örn: unpaid, paid, refunded, failed
            $table->string('payment_status', 32)->default('unpaid');

            // Para birimi (cart ile aynı, 3 harfli kod)
            $table->string('currency', 3);

            // Toplam tutar (ürünlerin toplamı)
            $table->decimal('total_amount', 12, 2)->default(0);

            // Şimdi tahsil edilecek tutar (ör: sadece ön ödeme)
            $table->decimal('total_prepayment', 12, 2)->default(0);

            // İndirim / kupon alanları (şimdilik basit)
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('coupon_code', 64)->nullable();

            // Müşteri iletişim bilgileri (snapshot mantığı – form’dan gelecek)
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone', 32)->nullable();

            // Fatura / adres ve diğer meta bilgiler
            $table->jsonb('billing_address')->nullable();
            $table->jsonb('metadata')->nullable();

            // Önemli zaman damgaları
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

            // Ürün tipi: transfer, tour, hotel_room, villa ...
            $table->string('product_type', 32);

            // İlgili ürün kaydının ID'si (tours.id, villas.id vb.)
            $table->unsignedBigInteger('product_id');

            // Admin / müşteri için görünen başlık
            $table->string('title');

            // İhtiyaç olursa (şimdilik hep 1 olacak)
            $table->unsignedInteger('quantity')->default(1);

            $table->string('currency', 3);

            // Satırın birim fiyatı (quantity=1 olduğu için amount ile aynı)
            $table->decimal('unit_price', 12, 2)->default(0);

            // Satır toplamı (unit_price * quantity)
            $table->decimal('total_price', 12, 2)->default(0);

            // Ürün tipi özel snapshot (cart’taki snapshot burada kalıcı)
            $table->jsonb('snapshot');

            // İleride satır bazlı iptal vb. için
            $table->string('status', 32)->default('active');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_type', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

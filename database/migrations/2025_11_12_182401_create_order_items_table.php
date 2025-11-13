<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            $table->string('product_type', 30)->index();   // hotel | transfer | tour | villa
            $table->unsignedBigInteger('product_id')->nullable(); // opsiyonel internal ref

            $table->string('title_snapshot');              // gösterim için başlık
            $table->jsonb('date_snapshot')->nullable();    // {from, to} veya tek tarih
            $table->jsonb('guest_snapshot')->nullable();   // {adults, children, infants}
            $table->decimal('amount', 12, 2)->default(0);
            $table->char('currency', 3);

            $table->jsonb('meta')->nullable();             // ürün özel min-detay

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

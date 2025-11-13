<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->string('order_no')->unique();                  // insan-dostu numara
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status', 20)->index();                 // pending | confirmed | canceled | failed? | refunded?

            $table->char('currency', 3);                           // TRY, EUR, USD
            $table->decimal('fx_rate', 12, 6)->default(1);         // döviz kuru snapshot

            $table->decimal('subtotal',       12, 2)->default(0);
            $table->decimal('discount_total', 12, 2)->default(0);
            $table->decimal('fee_total',      12, 2)->default(0);
            $table->decimal('tax_total',      12, 2)->default(0);
            $table->decimal('total',          12, 2)->default(0);

            // payments tablosu henüz yok; FK’yi sonraki adımda ekleyeceğiz
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->string('payment_method_code')->nullable();     // örn: credit_card

            $table->string('channel')->nullable();                 // web, b2b, vb.
            $table->text('note')->nullable();
            $table->jsonb('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

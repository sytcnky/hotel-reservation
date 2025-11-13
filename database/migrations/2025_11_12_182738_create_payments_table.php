<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('payment_no')->unique();          // dış referans için
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('method_code', 30);               // credit_card, eft, vb.
            $table->string('provider', 50)->nullable();      // örn: iyzico, stripe, payguru
            $table->string('provider_txn_id')->nullable();   // gateway işlem numarası

            $table->decimal('amount', 12, 2)->default(0);
            $table->char('currency', 3);

            $table->string('status', 20)->index();           // pending | paid | failed | refunded
            $table->jsonb('response')->nullable();           // gateway yanıtı
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

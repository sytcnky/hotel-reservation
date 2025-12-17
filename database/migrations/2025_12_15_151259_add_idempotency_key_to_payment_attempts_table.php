<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            // Kolon yoksa ekle
            if (! Schema::hasColumn('payment_attempts', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->after('gateway');
            }

            // Unique index: adı stabil olsun diye isim veriyoruz
            // (Bir checkout_session_id + idempotency_key) ikilisi tekrar etmesin.
            // Bu, aynı session içinde aynı nonce ile iki kayıt oluşmasını engeller.
            $indexName = 'payment_attempts_checkout_idempotency_unique';

            // Laravel'de Schema::hasIndex yok; o yüzden "varsa dene-düş" yaklaşımı.
            try {
                $table->unique(['checkout_session_id', 'idempotency_key'], $indexName);
            } catch (\Throwable $e) {
                // Index zaten varsa sessiz geç
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $indexName = 'payment_attempts_checkout_idempotency_unique';

            // index varsa kaldırmayı dene
            try {
                $table->dropUnique($indexName);
            } catch (\Throwable $e) {
                // yoksa geç
            }

            if (Schema::hasColumn('payment_attempts', 'idempotency_key')) {
                $table->dropColumn('idempotency_key');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolon zaten varsa tekrar ekleme
        if (! Schema::hasColumn('payment_attempts', 'idempotency_key')) {
            Schema::table('payment_attempts', function (Blueprint $table) {
                $table->string('idempotency_key', 64)->nullable()->after('checkout_session_id');
            });
        }

        // Unique index (NULL serbest)
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS payment_attempts_idempotency_key_unique
            ON payment_attempts (idempotency_key)
            WHERE idempotency_key IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS payment_attempts_idempotency_key_unique");

        // Down'da kolonu otomatik silmek riskli olabilir (başka migration/logic bağlıysa).
        // İstersen kaldırabilirsin; güvenli olması için burada bırakıyorum.
        // Eğer kesin istiyorsan:
        // if (Schema::hasColumn('payment_attempts', 'idempotency_key')) {
        //     Schema::table('payment_attempts', fn (Blueprint $table) => $table->dropColumn('idempotency_key'));
        // }
    }
};

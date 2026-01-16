<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1️⃣ Constraint olarak duran unique'i kaldır
        DB::statement("
            ALTER TABLE payment_attempts
            DROP CONSTRAINT IF EXISTS payment_attempts_checkout_idempotency_unique
        ");

        // 2️⃣ Global idempotency unique (gereksiz / tehlikeli)
        DB::statement("
            DROP INDEX IF EXISTS payment_attempts_idempotency_key_unique
        ");

        // 3️⃣ Doğru olan partial unique KALACAK:
        // payment_attempts_session_idempotency_unique
        // (checkout_session_id, idempotency_key)
        // WHERE deleted_at IS NULL AND idempotency_key IS NOT NULL

        // 4️⃣ Pending state çakışması (şimdilik dokunmuyoruz — P0-4)
    }

    public function down(): void
    {
        // ❌ geri alma yok (bilinçli)
    }
};

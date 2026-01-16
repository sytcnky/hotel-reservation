<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Eski: sadece `pending`
        DB::statement("
            DROP INDEX IF EXISTS payment_attempts_one_pending_per_session
        ");

        // Yeni: pending + pending_3ds
        DB::statement("
            CREATE UNIQUE INDEX payment_attempts_one_active_per_session
            ON payment_attempts (checkout_session_id)
            WHERE deleted_at IS NULL
              AND completed_at IS NULL
              AND status IN ('pending', 'pending_3ds')
        ");
    }

    public function down(): void
    {
        // bilinçli olarak boş
    }
};

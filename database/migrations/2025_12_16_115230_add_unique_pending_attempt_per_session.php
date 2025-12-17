<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS payment_attempts_one_pending_per_session
            ON payment_attempts (checkout_session_id)
            WHERE status = 'pending'
              AND completed_at IS NULL
              AND deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS payment_attempts_one_pending_per_session");
    }
};

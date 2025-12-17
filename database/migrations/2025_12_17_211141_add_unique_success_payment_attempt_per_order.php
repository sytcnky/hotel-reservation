<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Order başına sadece 1 adet SUCCESS payment_attempt olsun (PostgreSQL partial unique index)
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS payment_attempts_one_success_per_order
            ON payment_attempts (order_id)
            WHERE order_id IS NOT NULL AND status = 'success'
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS payment_attempts_one_success_per_order
        ");
    }
};

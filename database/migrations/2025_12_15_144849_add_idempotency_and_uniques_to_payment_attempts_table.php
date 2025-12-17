<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_attempts', 'idempotency_key')) {
                $table->string('idempotency_key', 64)->nullable()->after('checkout_session_id');
            }
        });

        // (checkout_session_id, idempotency_key) unique (soft delete hariç)
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS payment_attempts_session_idempotency_unique
            ON payment_attempts (checkout_session_id, idempotency_key)
            WHERE deleted_at IS NULL AND idempotency_key IS NOT NULL
        ");

        // Aynı checkout_session için tek canlı pending attempt garantisi
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS payment_attempts_one_pending_per_session
            ON payment_attempts (checkout_session_id)
            WHERE deleted_at IS NULL AND status = 'pending' AND completed_at IS NULL
        ");

        // Sık sorgu: session + status
        DB::statement("
            CREATE INDEX IF NOT EXISTS payment_attempts_session_status_index
            ON payment_attempts (checkout_session_id, status)
            WHERE deleted_at IS NULL
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS payment_attempts_session_status_index");
        DB::statement("DROP INDEX IF EXISTS payment_attempts_one_pending_per_session");
        DB::statement("DROP INDEX IF EXISTS payment_attempts_session_idempotency_unique");

        Schema::table('payment_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('payment_attempts', 'idempotency_key')) {
                $table->dropColumn('idempotency_key');
            }
        });
    }
};

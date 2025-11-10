<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('room_rate_rules', function (Blueprint $table) {
            $table->date('date_start')->nullable()->change();
            $table->date('date_end')->nullable()->change();
        });

        DB::statement('ALTER TABLE room_rate_rules DROP CONSTRAINT IF EXISTS chk_dates');
        DB::statement(<<<SQL
            ALTER TABLE room_rate_rules
            ADD CONSTRAINT chk_dates
            CHECK (
                date_start IS NULL OR date_end IS NULL OR date_start <= date_end
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE room_rate_rules DROP CONSTRAINT IF EXISTS chk_dates');
        DB::statement(<<<SQL
            ALTER TABLE room_rate_rules
            ADD CONSTRAINT chk_dates
            CHECK (date_start <= date_end)
        SQL);

        Schema::table('room_rate_rules', function (Blueprint $table) {
            $table->date('date_start')->nullable(false)->change();
            $table->date('date_end')->nullable(false)->change();
        });
    }
};

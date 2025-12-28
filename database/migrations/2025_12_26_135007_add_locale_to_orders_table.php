<?php

use App\Support\Helpers\LocaleHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Add column as NULLABLE first (backfill safe)
        Schema::table('orders', function (Blueprint $table) {
            $table->string('locale', 5)->nullable();
        });

        $default = LocaleHelper::defaultCode();

        // 2) Backfill: orders with user_id -> users.locale
        // Postgres UPDATE ... FROM
        DB::statement("
            update orders o
            set locale = u.locale
            from users u
            where o.user_id = u.id
              and o.locale is null
              and u.locale is not null
        ");

        // 3) Backfill remaining NULLs (guest orders or missing user locale) -> system default
        DB::statement("
            update orders
            set locale = ?
            where locale is null
        ", [$default]);

        // 4) Enforce NOT NULL at DB level
        DB::statement("alter table orders alter column locale set not null");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};

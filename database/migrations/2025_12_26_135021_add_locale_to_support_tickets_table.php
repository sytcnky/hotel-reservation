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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('locale', 5)->nullable();
        });

        $default = LocaleHelper::defaultCode();

        // 2) Backfill from users.locale (support_tickets.user_id is NOT NULL)
        DB::statement("
            update support_tickets t
            set locale = u.locale
            from users u
            where t.user_id = u.id
              and t.locale is null
              and u.locale is not null
        ");

        // 3) Any remaining NULLs -> system default (edge-case safety)
        DB::statement("
            update support_tickets
            set locale = ?
            where locale is null
        ", [$default]);

        // 4) Enforce NOT NULL at DB level
        DB::statement("alter table support_tickets alter column locale set not null");
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};

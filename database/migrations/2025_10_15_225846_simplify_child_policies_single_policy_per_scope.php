<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('child_policies', function (Blueprint $table) {
            // Yaş alanlarını kaldır
            if (Schema::hasColumn('child_policies', 'age_min')) $table->dropColumn('age_min');
            if (Schema::hasColumn('child_policies', 'age_max')) $table->dropColumn('age_max');

            // Board opsiyonel kalsın; yoksa ekleyin:
            // if (!Schema::hasColumn('child_policies','board_type_id')) $table->foreignId('board_type_id')->nullable()->constrained()->nullOnDelete();
        });

        // Tekil politika kısıtları (oda > otel > global)
        // Not: Bu kısıtlar PostgreSQL içindir.
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS child_policies_one_per_room ON child_policies (room_id) WHERE room_id IS NOT NULL;");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS child_policies_one_per_hotel ON child_policies (hotel_id) WHERE room_id IS NULL AND hotel_id IS NOT NULL;");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS child_policies_single_global ON child_policies ((true)) WHERE room_id IS NULL AND hotel_id IS NULL;");
    }

    public function down(): void
    {
        // Unique index’leri geri al
        DB::statement("DROP INDEX IF EXISTS child_policies_one_per_room;");
        DB::statement("DROP INDEX IF EXISTS child_policies_one_per_hotel;");
        DB::statement("DROP INDEX IF EXISTS child_policies_single_global;");

        Schema::table('child_policies', function (Blueprint $table) {
            // Yaş kolonlarını geri ekleme (rollback için)
            $table->unsignedTinyInteger('age_min')->default(0);
            $table->unsignedTinyInteger('age_max')->default(17);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 1) new_id kolonu
        DB::statement("ALTER TABLE tours ADD COLUMN new_id BIGINT");

        // 2) sequence + default
        DB::statement("CREATE SEQUENCE IF NOT EXISTS tours_new_id_seq OWNED BY tours.new_id");
        DB::statement("ALTER TABLE tours ALTER COLUMN new_id SET DEFAULT nextval('tours_new_id_seq')");

        // 3) mevcut satırları doldur
        DB::statement("UPDATE tours SET new_id = nextval('tours_new_id_seq') WHERE new_id IS NULL");

        // 4) NOT NULL + unique
        DB::statement("ALTER TABLE tours ALTER COLUMN new_id SET NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS tours_new_id_unique ON tours(new_id)");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS tours_new_id_unique");
        DB::statement("ALTER TABLE tours DROP COLUMN IF EXISTS new_id");
        DB::statement("DROP SEQUENCE IF EXISTS tours_new_id_seq");
    }
};

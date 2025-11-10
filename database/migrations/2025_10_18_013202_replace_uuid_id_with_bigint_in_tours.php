<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Medya ilişkilerini bozmadan id değişimi
        DB::statement('ALTER TABLE tours DROP CONSTRAINT IF EXISTS tours_pkey');
        DB::statement('ALTER TABLE tours ADD COLUMN temp_id BIGINT');
        DB::statement('UPDATE tours SET temp_id = new_id');
        DB::statement('ALTER TABLE tours ALTER COLUMN temp_id SET NOT NULL');
        DB::statement('ALTER TABLE tours DROP COLUMN id');
        DB::statement('ALTER TABLE tours RENAME COLUMN temp_id TO id');
        DB::statement("ALTER TABLE tours ADD PRIMARY KEY (id)");
        DB::statement("ALTER SEQUENCE IF EXISTS tours_new_id_seq OWNED BY tours.id");
        DB::statement("ALTER TABLE tours ALTER COLUMN id SET DEFAULT nextval('tours_new_id_seq')");
        DB::statement("ALTER TABLE tours DROP COLUMN IF EXISTS new_id");
    }

    public function down(): void
    {
        // Geri alma (zorunlu değil ama tutarlılık için)
        DB::statement('ALTER TABLE tours DROP CONSTRAINT IF EXISTS tours_pkey');
        DB::statement('ALTER TABLE tours ADD COLUMN uuid uuid DEFAULT gen_random_uuid()');
        DB::statement('UPDATE tours SET uuid = gen_random_uuid()');
        DB::statement('ALTER TABLE tours DROP COLUMN id');
        DB::statement('ALTER TABLE tours RENAME COLUMN uuid TO id');
        DB::statement('ALTER TABLE tours ADD PRIMARY KEY (id)');
    }
};

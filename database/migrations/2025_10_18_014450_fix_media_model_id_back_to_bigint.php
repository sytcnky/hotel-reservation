<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Eski index'i bırak
        DB::statement('DROP INDEX IF EXISTS media_model_type_model_id_index');

        // uuid -> bigint, mevcut veriler çevrilemiyorsa NULL bırak
        DB::statement("ALTER TABLE media ALTER COLUMN model_id TYPE BIGINT USING NULL");

        // Birleşik index'i tekrar oluştur
        DB::statement('CREATE INDEX media_model_type_model_id_index ON media (model_type, model_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS media_model_type_model_id_index');
        DB::statement("ALTER TABLE media ALTER COLUMN model_id TYPE uuid USING NULL");
        DB::statement('CREATE INDEX media_model_type_model_id_index ON media (model_type, model_id)');
    }
};

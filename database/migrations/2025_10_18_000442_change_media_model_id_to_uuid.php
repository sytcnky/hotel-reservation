<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Eğer veri varsa önce yedeğini alın. UUID’e çevrilemez.
        // Spatie v10 default index: media_model_type_model_id_index
        DB::statement('DROP INDEX IF EXISTS media_model_type_model_id_index');

        // bigint -> uuid
        DB::statement('ALTER TABLE media ALTER COLUMN model_id TYPE uuid USING NULL');

        // yeni birleşik index
        DB::statement('CREATE INDEX media_model_type_model_id_index ON media (model_type, model_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS media_model_type_model_id_index');
        DB::statement('ALTER TABLE media ALTER COLUMN model_id TYPE bigint USING NULL');
        DB::statement('CREATE INDEX media_model_type_model_id_index ON media (model_type, model_id)');
    }
};

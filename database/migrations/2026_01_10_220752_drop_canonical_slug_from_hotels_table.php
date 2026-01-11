<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: index/constraint isimleri bilinmese bile güvenli
        DB::statement('ALTER TABLE hotels DROP COLUMN IF EXISTS canonical_slug');
    }

    public function down(): void
    {
        // Geri dönüş ihtiyacı olursa tekrar ekleriz (nullable bırakalım)
        DB::statement('ALTER TABLE hotels ADD COLUMN IF NOT EXISTS canonical_slug VARCHAR(255)');
    }
};

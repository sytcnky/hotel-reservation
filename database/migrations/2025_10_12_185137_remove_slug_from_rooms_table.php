<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE rooms DROP COLUMN IF EXISTS slug;');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE rooms ADD COLUMN slug varchar(255) NULL;');
    }
};

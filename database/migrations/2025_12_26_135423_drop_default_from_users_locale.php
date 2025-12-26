<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Remove hardcoded default (e.g. 'tr') from users.locale
        DB::statement("alter table users alter column locale drop default");
    }

    public function down(): void
    {
        // Revert: set default back to 'tr' (previous state)
        DB::statement("alter table users alter column locale set default 'tr'");
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('transfer_vehicles', function (Blueprint $table) {
            if (! Schema::hasColumn('transfer_vehicles', 'name')) {
                $table->jsonb('name')->after('id');
            }
            if (! Schema::hasColumn('transfer_vehicles', 'description')) {
                $table->jsonb('description')->nullable()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transfer_vehicles', function (Blueprint $table) {
            if (Schema::hasColumn('transfer_vehicles', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('transfer_vehicles', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Önce constraint olarak duran unique’i kaldır (index drop edilemez çünkü constraint bağlı)
        DB::statement('ALTER TABLE transfer_route_vehicle_prices DROP CONSTRAINT IF EXISTS trvp_route_vehicle_unique');

        // Aynı isimle partial unique index oluştur (soft-deleted satırlar hariç)
        DB::statement(
            'CREATE UNIQUE INDEX trvp_route_vehicle_unique
             ON transfer_route_vehicle_prices (transfer_route_id, transfer_vehicle_id)
             WHERE deleted_at IS NULL'
        );
    }

    public function down(): void
    {
        // Partial index’i kaldır
        DB::statement('DROP INDEX IF EXISTS trvp_route_vehicle_unique');

        // Eski unique constraint’i geri kur (deleted_at dahil hepsinde unique)
        DB::statement(
            'ALTER TABLE transfer_route_vehicle_prices
             ADD CONSTRAINT trvp_route_vehicle_unique
             UNIQUE (transfer_route_id, transfer_vehicle_id)'
        );
    }
};

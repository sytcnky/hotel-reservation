<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_route_vehicle_prices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('transfer_route_id')
                ->constrained('transfer_routes')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('transfer_vehicle_id')
                ->constrained('transfer_vehicles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            // {"TRY": 1500, "GBP": 40} — vehicle başı fiyat
            $table->jsonb('prices')->nullable();

            // Rotada aracı pasifleyebilmek için
            $table->boolean('is_active')->default(true);

            // Rotaya göre araç sırası
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Her rota + araç çifti tekil
            $table->unique(['transfer_route_id', 'transfer_vehicle_id'], 'trvp_route_vehicle_unique');

            $table->index(['transfer_route_id'], 'trvp_route_id_index');
            $table->index(['transfer_vehicle_id'], 'trvp_vehicle_id_index');
            $table->index(['is_active'], 'trvp_is_active_index');
            $table->index(['sort_order'], 'trvp_sort_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_route_vehicle_prices');
    }
};

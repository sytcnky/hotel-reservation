<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_bookings', function (Blueprint $table) {
            $table->id();

            // 1 item ↔ 1 booking
            $table->foreignId('order_item_id')
                ->constrained('order_items')
                ->cascadeOnDelete()
                ->unique();

            $table->foreignId('route_id')->constrained('transfer_routes');
            $table->foreignId('vehicle_id')->constrained('transfer_vehicles');

            $table->enum('direction', ['oneway','roundtrip']);

            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');

            $table->date('departure_date');
            $table->date('return_date')->nullable();

            $table->time('pickup_time_outbound');
            $table->string('flight_number_outbound')->nullable();

            $table->time('pickup_time_return')->nullable();
            $table->string('flight_number_return')->nullable();

            // Fiyat snapshot
            $table->decimal('price_total', 12, 2);
            $table->char('currency', 3);

            // Tam payload snapshot (hesaplama anı)
            $table->jsonb('snapshot')->nullable();

            $table->timestamps();

            $table->index(['route_id','vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_bookings');
    }
};

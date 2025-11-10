<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_routes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_location_id')->constrained('locations');
            $table->foreignId('to_location_id')->constrained('locations');

            $table->unsignedInteger('duration_minutes')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();

            // kişi başı fiyatlar, ters yönde de aynı fiyat geçerli
            // Örn: {"TRY":{"adult":1200,"child":900,"infant":0},"EUR":{"adult":35,"child":26,"infant":0}}
            $table->jsonb('prices')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['from_location_id','to_location_id']);
            $table->index('is_active');
            $table->index('sort_order');
        });

        // Aynı iki lokasyon için tek kayıt (yönsüz benzersizlik)
        DB::statement("
            CREATE UNIQUE INDEX transfer_routes_pair_unique
            ON transfer_routes (
                LEAST(from_location_id, to_location_id),
                GREATEST(from_location_id, to_location_id)
            )
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS transfer_routes_pair_unique');
        Schema::dropIfExists('transfer_routes');
    }
};

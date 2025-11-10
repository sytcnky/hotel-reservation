<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('room_rate_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();

            $table->foreignId('currency_id')->constrained('currencies')->cascadeOnDelete();
            $table->foreignId('board_type_id')->nullable()->constrained('board_types')->nullOnDelete();

            $table->string('price_type', 32); // room_per_night | person_per_night

            $table->date('date_start');
            $table->date('date_end');

            // 7-bit maske: 1=Mon ... 7=Sun. 127 tüm günler.
            $table->unsignedSmallInteger('weekday_mask')->default(127);

            $table->unsignedSmallInteger('occupancy_min')->default(1);
            $table->unsignedSmallInteger('occupancy_max')->default(1);

            $table->decimal('amount', 12, 2);

            $table->unsignedSmallInteger('los_min')->nullable();
            $table->unsignedSmallInteger('los_max')->nullable();

            $table->unsignedSmallInteger('allotment')->nullable();

            $table->boolean('closed')->default(false);
            $table->boolean('cta')->default(false);
            $table->boolean('ctd')->default(false);

            $table->integer('priority')->default(10);
            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // indeksler
            $table->index(['room_id', 'currency_id']);
            $table->index(['date_start', 'date_end']);
            $table->index(['priority']);
            $table->index(['board_type_id']);
        });

        // Basit CHECK’ler (PostgreSQL varsayımı)
        DB::statement('ALTER TABLE room_rate_rules
            ADD CONSTRAINT chk_dates CHECK (date_start <= date_end)');
        DB::statement('ALTER TABLE room_rate_rules
            ADD CONSTRAINT chk_occupancy CHECK (occupancy_min <= occupancy_max)');
        DB::statement("ALTER TABLE room_rate_rules
            ADD CONSTRAINT chk_price_type CHECK (price_type IN ('room_per_night','person_per_night'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('room_rate_rules');
    }
};

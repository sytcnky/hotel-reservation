<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('transfer_vehicles', function (Blueprint $table) {
            $table->id();
            $table->jsonb('name');                 // {tr, en}
            $table->jsonb('description')->nullable();

            $table->unsignedSmallInteger('capacity_total');
            $table->unsignedSmallInteger('capacity_adult_max')->nullable();
            $table->unsignedSmallInteger('capacity_child_max')->nullable();
            $table->unsignedSmallInteger('capacity_infant_max')->nullable();

            $table->boolean('infants_count_towards_total')->default(true); // bebekler toplam kapasiteye sayılır
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_vehicles');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_bed', function (Blueprint $table) {
            $table->id();

            $table->foreignId('room_id')
                ->constrained('rooms')
                ->cascadeOnDelete();

            $table->foreignId('bed_type_id')
                ->constrained('bed_types')
                ->cascadeOnDelete();

            $table->unsignedInteger('quantity')->default(1);

            $table->unique(['room_id', 'bed_type_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_bed');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('person_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // enum key: adult, child, infant, senior
            $table->string('label');
            $table->tinyInteger('min_age')->nullable();
            $table->tinyInteger('max_age')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('person_types');
    }
};

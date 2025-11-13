<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_counters', function (Blueprint $table) {
            $table->date('counter_date')->primary();   // YYYY-MM-DD
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        Schema::create('payment_counters', function (Blueprint $table) {
            $table->date('counter_date')->primary();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_counters');
        Schema::dropIfExists('order_counters');
    }
};

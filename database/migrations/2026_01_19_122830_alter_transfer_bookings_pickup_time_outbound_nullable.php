<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transfer_bookings', function (Blueprint $table) {
            $table->time('pickup_time_outbound', 0)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('transfer_bookings', function (Blueprint $table) {
            $table->time('pickup_time_outbound', 0)->nullable(false)->change();
        });
    }
};

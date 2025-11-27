<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->unsignedSmallInteger('prepayment_rate')
                ->nullable()
                ->after('is_active')
                ->comment('Ön ödeme yüzdesi (0–100) – sepette tahsil edilecek oran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            $table->dropColumn('prepayment_rate');
        });
    }
};

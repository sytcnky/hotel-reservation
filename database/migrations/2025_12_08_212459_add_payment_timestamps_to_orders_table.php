<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Ödeme için son geçerlilik tarihi (pending_payment için)
            if (! Schema::hasColumn('orders', 'payment_expires_at')) {
                $table->timestampTz('payment_expires_at')
                    ->nullable()
                    ->after('paid_at');
            }

            // İade edilmiş siparişler için
            if (! Schema::hasColumn('orders', 'refunded_at')) {
                $table->timestampTz('refunded_at')
                    ->nullable()
                    ->after('cancelled_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_expires_at')) {
                $table->dropColumn('payment_expires_at');
            }

            if (Schema::hasColumn('orders', 'refunded_at')) {
                $table->dropColumn('refunded_at');
            }
        });
    }
};

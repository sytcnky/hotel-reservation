<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Kuponların sipariş anındaki snapshot'ı
            // Örn:
            // [
            //   {
            //     "user_coupon_id": 10,
            //     "coupon_id": 4,
            //     "code": "TOUR200",
            //     "discount": 200.0,
            //     "title": "Turlarda 200₺ indirim"
            //   },
            //   ...
            // ]
            $table->jsonb('coupon_snapshot')->nullable()->after('coupon_code');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('coupon_snapshot');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // İndirimin nereye uygulanacağı (target)
            $table->string('target_type', 255)
                ->default('order_total')
                ->after('scope_type');

            // product_type target için (TEK seçim)
            $table->string('target_product_type', 255)
                ->nullable()
                ->after('target_type');

            // product target için domain + id
            $table->string('target_product_domain', 255)
                ->nullable()
                ->after('target_product_type');

            $table->unsignedBigInteger('target_product_id')
                ->nullable()
                ->after('target_product_domain');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn([
                'target_type',
                'target_product_type',
                'target_product_domain',
                'target_product_id',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'total_prepayment')) {
                $table->dropColumn('total_prepayment');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Geri alma durumunda alanı tekrar ekleyelim
            if (! Schema::hasColumn('orders', 'total_prepayment')) {
                $table->decimal('total_prepayment', 12, 2)->default(0);
            }
        });
    }
};

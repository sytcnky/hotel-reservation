<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('checkout_sessions', 'expires_at')) {
                $table->timestamp('expires_at', 0)->nullable()->after('completed_at');
                $table->index('expires_at', 'checkout_sessions_expires_at_index');
            }

            if (!Schema::hasColumn('checkout_sessions', 'order_id')) {
                $table->unsignedBigInteger('order_id')->nullable()->after('user_id');
                $table->foreign('order_id')
                    ->references('id')->on('orders')
                    ->nullOnDelete();
                $table->index('order_id', 'checkout_sessions_order_id_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('checkout_sessions', 'order_id')) {
                $table->dropForeign(['order_id']);
                $table->dropIndex('checkout_sessions_order_id_index');
                $table->dropColumn('order_id');
            }

            if (Schema::hasColumn('checkout_sessions', 'expires_at')) {
                $table->dropIndex('checkout_sessions_expires_at_index');
                $table->dropColumn('expires_at');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('order_items_order_id_foreign');
            $table->foreign('order_id', 'order_items_order_id_foreign')
                ->references('id')->on('orders')
                ->restrictOnDelete();
        });

        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropForeign('payment_attempts_checkout_session_id_foreign');
            $table->foreign('checkout_session_id', 'payment_attempts_checkout_session_id_foreign')
                ->references('id')->on('checkout_sessions')
                ->restrictOnDelete();

            $table->dropForeign('payment_attempts_order_id_foreign');
            $table->foreign('order_id', 'payment_attempts_order_id_foreign')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign('order_items_order_id_foreign');
            $table->foreign('order_id', 'order_items_order_id_foreign')
                ->references('id')->on('orders')
                ->cascadeOnDelete();
        });

        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropForeign('payment_attempts_checkout_session_id_foreign');
            $table->foreign('checkout_session_id', 'payment_attempts_checkout_session_id_foreign')
                ->references('id')->on('checkout_sessions')
                ->cascadeOnDelete();

            $table->dropForeign('payment_attempts_order_id_foreign');
            $table->foreign('order_id', 'payment_attempts_order_id_foreign')
                ->references('id')->on('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }
};

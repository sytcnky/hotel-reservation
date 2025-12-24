<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('support_ticket_category_id');

            $table->unsignedBigInteger('order_id')->nullable();

            $table->string('subject', 255);

            $table->string('status', 40)->default('open');

            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['support_ticket_category_id', 'status']);
            $table->index(['last_message_at']);
            $table->index(['created_at']);

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onDelete('restrict');

            $table->foreign('support_ticket_category_id')
                ->references('id')->on('support_ticket_categories')
                ->onDelete('restrict');

            // order_id FK: orders tablosu mevcut varsayımı var.
            // Eğer orders tablonuz farklı isimdeyse burada değiştireceğiz.
            $table->foreign('order_id')
                ->references('id')->on('orders')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};

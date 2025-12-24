<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('support_ticket_id');

            $table->unsignedBigInteger('author_user_id');

            // Mesajın yazıldığı anki rol fotoğrafı:
            // - customer / agent
            $table->string('author_type', 20);

            $table->text('body');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['support_ticket_id', 'created_at']);

            $table->foreign('support_ticket_id')
                ->references('id')->on('support_tickets')
                ->onDelete('cascade');

            $table->foreign('author_user_id')
                ->references('id')->on('users')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};

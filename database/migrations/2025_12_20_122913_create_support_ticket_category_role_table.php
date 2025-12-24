<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_category_role', function (Blueprint $table) {
            $table->unsignedBigInteger('support_ticket_category_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(['support_ticket_category_id', 'role_id']);

            $table->foreign('support_ticket_category_id')
                ->references('id')
                ->on('support_ticket_categories')
                ->onDelete('cascade');

            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_category_role');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_ticket_categories', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->json('name');
            $table->json('slug');
            $table->json('description')->nullable();

            $table->boolean('requires_order')->default(false);
            $table->boolean('is_active')->default(true);
            $table->smallInteger('sort_order')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort_order'], 'support_ticket_categories_is_active_sort_order_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_ticket_categories');
    }
};

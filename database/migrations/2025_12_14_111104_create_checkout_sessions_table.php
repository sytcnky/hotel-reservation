<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkout_sessions', function (Blueprint $table) {
            $table->id();

            // guest_xxx / user_xxx
            $table->string('code', 64)->unique();

            // guest | user
            $table->string('type', 20);

            // Üye ise dolu, misafir ise null
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Misafir bilgileri veya normalize müşteri snapshot
            $table->jsonb('customer_snapshot')->nullable();

            $table->decimal('cart_total', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('currency', 3);

            // active | completed | expired | abandoned
            $table->string('status', 30)->default('active');

            // Teknik metadata
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Zamanlar
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['type']);
            $table->index(['status']);
            $table->index(['user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkout_sessions');
    }
};

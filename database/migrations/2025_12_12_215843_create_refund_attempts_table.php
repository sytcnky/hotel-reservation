<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refund_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Hangi ödeme attempt’inden refund alındı bilgisi (opsiyonel ama faydalı)
            $table->foreignId('payment_attempt_id')
                ->nullable()
                ->constrained('payment_attempts')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->decimal('amount', 12, 2);
            $table->string('currency', 3);

            $table->string('status', 50)->default('pending'); // pending|success|failed|cancelled
            $table->string('gateway', 50);

            $table->string('gateway_reference', 191)->nullable();

            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();

            $table->jsonb('meta')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Operasyon iptal/iadede gerekebilir (kısmî/tam + açıklama vb.)
            $table->text('reason')->nullable();

            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->jsonb('raw_request')->nullable();
            $table->jsonb('raw_response')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id']);
            $table->index(['payment_attempt_id']);
            $table->index(['status']);
            $table->index(['gateway', 'gateway_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_attempts');
    }
};

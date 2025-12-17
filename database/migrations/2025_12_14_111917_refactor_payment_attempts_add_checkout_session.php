<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {

            // CheckoutSession bağlantısı (yeni)
            if (! Schema::hasColumn('payment_attempts', 'checkout_session_id')) {
                $table->foreignId('checkout_session_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('checkout_sessions')
                    ->cascadeOnDelete();
            }

            // order_id nullable (checkout_session öncesi attempt'ler için)
            // ⚠️ change() için kolon var ama nullable değilse çalışır
            $table->foreignId('order_id')
                ->nullable()
                ->change();

            // Teknik alanlar — sadece yoksa ekle
            if (! Schema::hasColumn('payment_attempts', 'error_code')) {
                $table->string('error_code', 50)
                    ->nullable()
                    ->after('status');
            }

            if (! Schema::hasColumn('payment_attempts', 'error_message')) {
                $table->text('error_message')
                    ->nullable()
                    ->after('error_code');
            }

            if (! Schema::hasColumn('payment_attempts', 'ip_address')) {
                $table->string('ip_address', 45)
                    ->nullable()
                    ->after('error_message');
            }

            if (! Schema::hasColumn('payment_attempts', 'user_agent')) {
                $table->text('user_agent')
                    ->nullable()
                    ->after('ip_address');
            }

            // Index'ler (varsa Laravel sessiz geçer)
            $table->index(['status']);
            $table->index(['gateway']);
            $table->index(['checkout_session_id']);
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {

            if (Schema::hasColumn('payment_attempts', 'checkout_session_id')) {
                $table->dropForeign(['checkout_session_id']);
                $table->dropColumn('checkout_session_id');
            }

            if (Schema::hasColumn('payment_attempts', 'error_code')) {
                $table->dropColumn('error_code');
            }

            if (Schema::hasColumn('payment_attempts', 'error_message')) {
                $table->dropColumn('error_message');
            }

            if (Schema::hasColumn('payment_attempts', 'ip_address')) {
                $table->dropColumn('ip_address');
            }

            if (Schema::hasColumn('payment_attempts', 'user_agent')) {
                $table->dropColumn('user_agent');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table) {
            // expires_at zaten varsa dokunma
            if (! Schema::hasColumn('checkout_sessions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('started_at');
                $table->index('expires_at');
            }

            // order_id yoksa ekle
            if (! Schema::hasColumn('checkout_sessions', 'order_id')) {
                $table->foreignId('order_id')
                    ->nullable()
                    ->after('completed_at')
                    ->constrained('orders')
                    ->nullOnDelete();

                $table->index('order_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('checkout_sessions', function (Blueprint $table) {
            // order_id varsa kaldır
            if (Schema::hasColumn('checkout_sessions', 'order_id')) {
                // index isimleri Laravel tarafından farklı üretilebilir;
                // dropConstrainedForeignId FK'yi de kaldırır.
                $table->dropConstrainedForeignId('order_id');

                // Bazı durumlarda index otomatik kalkar; kalırsa sorun olmaz.
                // Elle dropIndex yapmak istersen, index adını DB'den teyit etmek gerekir.
            }

            // expires_at varsa kaldır
            if (Schema::hasColumn('checkout_sessions', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};

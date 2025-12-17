<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_attempts', function (Blueprint $table) {
            // Net rol: admin|customer|support|finance... (Spatie role slug vs.)
            $table->string('initiator_role', 50)->nullable()->after('initiator_type');

            // initiator_type artık gereksiz (admin/customer ayrımı role ile yapılacak)
            // Not: Önce kolon ekliyoruz, sonra drop ediyoruz.
        });

        Schema::table('refund_attempts', function (Blueprint $table) {
            if (Schema::hasColumn('refund_attempts', 'initiator_type')) {
                $table->dropColumn('initiator_type');
            }

            // Index (rol üzerinden filtre/rapor için)
            $table->index(['initiator_role']);
        });
    }

    public function down(): void
    {
        Schema::table('refund_attempts', function (Blueprint $table) {
            // geri al: initiator_type'ı geri ekle
            $table->string('initiator_type', 20)->nullable()->after('user_agent');

            // initiator_role index + kolon kaldır
            $table->dropIndex(['initiator_role']);
            $table->dropColumn('initiator_role');
        });
    }
};

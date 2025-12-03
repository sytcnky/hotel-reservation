<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // badge_main kolonu artık kullanılmıyor
            if (Schema::hasColumn('coupons', 'badge_main')) {
                $table->dropColumn('badge_main');
            }
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            // Projede önceki tip ne ise onu kullan:
            // json / jsonb / text vb. — ben json örneği veriyorum
            if (! Schema::hasColumn('coupons', 'badge_main')) {
                $table->json('badge_main')->nullable();
            }
        });
    }
};

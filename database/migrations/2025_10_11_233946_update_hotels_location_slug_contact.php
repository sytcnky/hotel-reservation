<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Eski slug'ı koru
        Schema::table('hotels', function (Blueprint $table) {
            // unique index adı tipik olarak "hotels_slug_unique"
            try {
                $table->dropUnique('hotels_slug_unique');
            } catch (\Throwable $e) {
            }
            if (Schema::hasColumn('hotels', 'slug')) {
                $table->renameColumn('slug', 'old_slug');
            }
        });

        // 2) Yeni sütunlar
        Schema::table('hotels', function (Blueprint $table) {
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->jsonb('slug')->nullable();
            $table->string('canonical_slug')->unique();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Eski konum alanlarını kaldır
            if (Schema::hasColumn('hotels', 'province_slug')) {
                $table->dropColumn('province_slug');
            }
            if (Schema::hasColumn('hotels', 'district_slug')) {
                $table->dropColumn('district_slug');
            }
            if (Schema::hasColumn('hotels', 'area_slug')) {
                $table->dropColumn('area_slug');
            }
        });

        // 3) Veri devri (varsayılan baz dil: tr)
        if (Schema::hasColumn('hotels', 'old_slug')) {
            DB::statement("
                UPDATE hotels
                SET canonical_slug = COALESCE(old_slug, ''),
                    slug = CASE
                        WHEN old_slug IS NULL THEN NULL
                        ELSE jsonb_build_object('tr', old_slug)
                    END
            ");
        }

        // 4) Eski sütunu kaldır
        Schema::table('hotels', function (Blueprint $table) {
            if (Schema::hasColumn('hotels', 'old_slug')) {
                $table->dropColumn('old_slug');
            }
        });
    }

    public function down(): void
    {
        // 1) Eski slug alanını geri ekle
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('old_slug')->nullable();
        });

        // 2) Veriyi geri taşı
        DB::statement("
            UPDATE hotels
            SET old_slug = NULLIF(canonical_slug, '')
        ");

        // 3) Eski şemayı geri kur
        Schema::table('hotels', function (Blueprint $table) {
            // eski konum alanları
            $table->string('province_slug', 64)->nullable();
            $table->string('district_slug', 64)->nullable();
            $table->string('area_slug', 64)->nullable();

            // yeni alanları bırak
            if (Schema::hasColumn('hotels', 'slug')) {
                $table->dropColumn('slug');
            }
            if (Schema::hasColumn('hotels', 'canonical_slug')) {
                $table->dropColumn('canonical_slug');
            }
            if (Schema::hasColumn('hotels', 'phone')) {
                $table->dropColumn('phone');
            }
            if (Schema::hasColumn('hotels', 'email')) {
                $table->dropColumn('email');
            }

            // FK kaldır
            if (Schema::hasColumn('hotels', 'location_id')) {
                $table->dropConstrainedForeignId('location_id');
            }
        });

        // 4) old_slug -> slug ve unique geri
        Schema::table('hotels', function (Blueprint $table) {
            $table->renameColumn('old_slug', 'slug');
            try {
                $table->unique('slug', 'hotels_slug_unique');
            } catch (\Throwable $e) {
            }
        });
    }
};

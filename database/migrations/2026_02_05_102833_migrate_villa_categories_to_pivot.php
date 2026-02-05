<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_category_villa', function (Blueprint $table) {
            $table->unsignedBigInteger('villa_id');
            $table->unsignedBigInteger('villa_category_id');

            $table->timestamps();

            $table->primary(['villa_id', 'villa_category_id']);

            $table->foreign('villa_id')
                ->references('id')
                ->on('villas')
                ->onDelete('cascade');

            $table->foreign('villa_category_id')
                ->references('id')
                ->on('villa_categories')
                ->onDelete('cascade');
        });

        // Mevcut tekli ilişkiyi pivot'a taşı
        DB::table('villa_category_villa')->insertUsing(
            ['villa_id', 'villa_category_id', 'created_at', 'updated_at'],
            DB::table('villas')
                ->select([
                    'id as villa_id',
                    'villa_category_id',
                    DB::raw('now() as created_at'),
                    DB::raw('now() as updated_at'),
                ])
                ->whereNotNull('villa_category_id')
        );

        // Eski FK + kolon kaldır
        Schema::table('villas', function (Blueprint $table) {
            $table->dropForeign(['villa_category_id']);
            $table->dropColumn('villa_category_id');
        });
    }

    public function down(): void
    {
        // Kolonu geri ekle
        Schema::table('villas', function (Blueprint $table) {
            $table->unsignedBigInteger('villa_category_id')->nullable()->after('bathroom_count');

            $table->foreign('villa_category_id')
                ->references('id')
                ->on('villa_categories');
        });

        // Pivot -> tekli kolon geri doldur (çoklu varsa en küçük id)
        DB::statement("
            update villas v
            set villa_category_id = x.villa_category_id
            from (
                select villa_id, min(villa_category_id) as villa_category_id
                from villa_category_villa
                group by villa_id
            ) x
            where v.id = x.villa_id
        ");

        Schema::dropIfExists('villa_category_villa');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tours', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // i18n
            $table->jsonb('name')->nullable();               // {tr,en}
            $table->jsonb('slug')->nullable();               // {tr,en}
            $table->jsonb('short_description')->nullable();  // {tr,en}
            $table->jsonb('long_description')->nullable();   // {tr,en}
            $table->jsonb('notes')->nullable();              // {tr:[{value}],en:[{value}]}

            // fiyatlar (transfer ile aynı JSONB)
            $table->jsonb('prices')->nullable();             // {"TRY":{"adult":0,"child":0,"infant":0}, ...}

            // ortak
            $table->string('duration')->nullable();
            $table->timeTz('start_time')->nullable();
            $table->unsignedSmallInteger('min_age')->nullable();
            $table->jsonb('days_of_week')->nullable();       // ["mon","tue",...]

            // servisler (JSONB id listesi)
            $table->jsonb('included_service_ids')->nullable(); // [1,2,3]
            $table->jsonb('excluded_service_ids')->nullable(); // [4,5]

            // sınıflandırma
            $table->foreignId('tour_category_id')->nullable()->constrained('tour_categories');

            // genel
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('code')->nullable()->unique();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['is_active','sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tours');
    }
};

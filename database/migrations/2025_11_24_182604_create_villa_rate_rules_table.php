<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_rate_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('villa_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();

            $table->string('label')->nullable();        // Kural adı
            $table->integer('priority')->default(10);   // Öncelik

            $table->date('date_start')->nullable();
            $table->date('date_end')->nullable();

            // 1..7 (Mon..Sun) bit mask
            $table->integer('weekday_mask')->default(0);

            $table->decimal('amount', 10, 2)->default(0); // Gece başı fiyat

            $table->boolean('closed')->default(false);    // Kapalı
            $table->boolean('cta')->default(false);       // Girişe kapalı
            $table->boolean('ctd')->default(false);       // Çıkışa kapalı

            $table->boolean('is_active')->default(true);

            $table->text('note')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['villa_id', 'currency_id']);
            $table->index(['villa_id', 'date_start', 'date_end']);
            $table->index(['villa_id', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('villa_rate_rules');
    }
};

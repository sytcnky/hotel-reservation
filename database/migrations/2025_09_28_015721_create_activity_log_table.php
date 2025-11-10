<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');

            // ✅ subject => string id, her iki tipi de taşır (int/uuid)
            $table->string('subject_type')->nullable();
            $table->string('subject_id')->nullable();
            $table->index(['subject_type', 'subject_id']);

            // ✅ causer => mevcut users int olduğu için bigint kalsın
            $table->nullableMorphs('causer'); // causer_type (string) + causer_id (bigint)

            $table->json('properties')->nullable();
            $table->string('event')->nullable();
            $table->uuid('batch_uuid')->nullable();

            $table->timestamps();
            $table->index('log_name');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};

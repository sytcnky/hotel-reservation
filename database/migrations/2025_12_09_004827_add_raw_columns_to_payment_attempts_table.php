<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            // İsteğe bağlı, nullable JSON(B) alanlar
            $table->jsonb('raw_request')->nullable();
            $table->jsonb('raw_response')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->dropColumn(['raw_request', 'raw_response']);
        });
    }
};

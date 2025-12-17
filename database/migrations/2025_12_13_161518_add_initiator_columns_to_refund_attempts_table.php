<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('refund_attempts', function (Blueprint $table) {
            // admin | customer
            $table->string('initiator_type', 20)->default('admin')->after('user_agent');

            // Admin panel refund'larında dolacak
            $table->foreignId('initiator_user_id')
                ->nullable()
                ->after('initiator_type')
                ->constrained('users')
                ->nullOnDelete();

            // Snapshot isim (admin için de yazacağız; customer için de)
            $table->string('initiator_name', 255)
                ->nullable()
                ->after('initiator_user_id');

            $table->index(['initiator_type']);
            $table->index(['initiator_user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('refund_attempts', function (Blueprint $table) {
            $table->dropIndex(['initiator_type']);
            $table->dropIndex(['initiator_user_id']);

            $table->dropConstrainedForeignId('initiator_user_id');
            $table->dropColumn(['initiator_type', 'initiator_name']);
        });
    }
};
